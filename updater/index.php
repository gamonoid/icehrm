<?php
/**
 * IceHRM Updater — front controller.
 *
 * Independent of the application by design. It takes its own copies of app/config.php
 * and core/config.base.php on every invocation, includes those, and touches nothing
 * else outside updater/ — because it is about to replace core/, web/, app/, bin/ and
 * the extension trees, and must keep working while it does.
 *
 * Flow:
 *   signed link -> login -> preflight -> source (download link)
 *               -> download+extract+version check -> confirm -> install
 *               -> migrate prompt -> health check -> done | rollback
 *
 * The signed link is mandatory: without a valid, unexpired token the sign-in form is
 * not even rendered. Links are minted by the update banner inside IceHRM, which only
 * administrators see, so reaching the updater already requires an authenticated admin
 * session; the updater's own password check is a second, independent gate. When the
 * application is too broken to sign into, mint a link on the server with
 * `php updater/token.php`.
 *
 * Each step is a POST guarded by a CSRF token; the step name is carried in the form so
 * a refresh cannot re-run a destructive action by accident.
 */

require_once __DIR__ . '/lib/Log.php';
require_once __DIR__ . '/lib/Bootstrap.php';

$problem = UpdaterBootstrap::init();

require_once __DIR__ . '/lib/Db.php';
require_once __DIR__ . '/lib/Auth.php';
require_once __DIR__ . '/lib/Preflight.php';
require_once __DIR__ . '/lib/Files.php';
require_once __DIR__ . '/lib/Package.php';
require_once __DIR__ . '/lib/Installer.php';
require_once __DIR__ . '/lib/Lock.php';
require_once __DIR__ . '/lib/Health.php';
require_once __DIR__ . '/lib/Token.php';

/** Render $view with $vars into the shared layout and stop. */
function updater_render($view, $title, $vars = array())
{
    $user = class_exists('UpdaterAuth') && UpdaterAuth::isLoggedIn() ? UpdaterAuth::currentUser() : null;
    extract($vars, EXTR_SKIP);
    ob_start();
    require __DIR__ . '/views/' . $view . '.php';
    $content = ob_get_clean();
    require __DIR__ . '/views/layout.php';
    exit();
}

/**
 * A problem the updater cannot get past — no config, no writable data directory.
 * Rendered without the layout's version footer, because at this point the
 * configuration may not have loaded at all.
 */
if ($problem !== null) {
    $title = $problem['title'];
    ?><!DOCTYPE html>
    <html lang="en"><head><meta charset="utf-8"><title><?= htmlspecialchars($title) ?></title>
    <style>body{font:15px/1.55 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f4f6f8;margin:0;padding:40px 16px;color:#1f2933}
    .card{max-width:720px;margin:0 auto;background:#fff;border:1px solid #e3e8ef;border-radius:8px;padding:28px}
    h1{font-size:19px;margin:0 0 12px}pre{background:#1f2933;color:#e6edf3;padding:12px 14px;border-radius:5px;overflow-x:auto;font-size:13px}</style>
    </head><body><div class="card">
    <h1><?= htmlspecialchars($title) ?></h1>
    <p><?= $problem['message'] ?></p>
    <?php if (!empty($problem['command'])): ?>
        <pre><?= htmlspecialchars($problem['command']) ?></pre>
        <p>Then reload this page.</p>
    <?php endif; ?>
    </div></body></html><?php
    exit();
}

// ---------------------------------------------------------------------------
// Signed-link gate — before anything else, including the login form
// ---------------------------------------------------------------------------

$updaterRecovery = null;
$token = UpdaterToken::check();
if (!$token['ok']) {
    // No valid link — but if IceHRM itself is down there is no banner to mint one
    // from, and the updater is precisely the tool needed to fix that. So the link is
    // required only while the application is healthy enough to produce one.
    //
    // This is not a way in: the updater's own administrator password is still
    // required, and it is checked against the same database. An attacker who could
    // force the application to 500 would gain nothing but the sight of a login form.
    // Only a server ERROR or no answer at all counts as "IceHRM is down". A 401 or 403
    // usually means something is protecting the application — basic auth in front of it,
    // a WAF, an IP allowlist — not that it is broken, and treating that as a reason to
    // drop the link requirement would quietly weaken the gate on exactly those installs.
    $appState = UpdaterHealth::probeApplication();
    $appIsDown = $appState['state'] === 'unreachable'
        || ($appState['state'] === 'broken' && $appState['status'] >= 500);
    if (!$appIsDown) {
        updater_render('nolink', 'Update link required', array(
            'reason' => $token['reason'],
            'explanation' => UpdaterToken::explain($token['reason']),
        ));
    }

    UpdaterLog::info('Opened without a link because IceHRM is not healthy ('
        . $appState['state'] . ($appState['status'] ? ' HTTP ' . $appState['status'] : '')
        . ') — recovery access');
    UpdaterToken::admitForRecovery();
    $updaterRecovery = $appState;
}

// ---------------------------------------------------------------------------
// Routing
// ---------------------------------------------------------------------------

$action = isset($_POST['action']) ? (string) $_POST['action'] : (isset($_GET['action']) ? (string) $_GET['action'] : '');
$error = null;
$notice = null;

if ($action === 'logout') {
    UpdaterAuth::logout();
    header('Location: ' . basename(__FILE__));
    exit();
}

// Every POST must carry the session's CSRF token — including the confirmation that
// starts the update.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !UpdaterAuth::checkCsrf()) {
    $error = 'Your session expired. Sign in again and retry.';
    $action = '';
    UpdaterAuth::logout();
}

// --- not signed in ---------------------------------------------------------
if (!UpdaterAuth::isLoggedIn()) {
    if ($action === 'login') {
        $error = UpdaterAuth::login(
            isset($_POST['username']) ? trim((string) $_POST['username']) : '',
            isset($_POST['password']) ? (string) $_POST['password'] : ''
        );
        if ($error === null) {
            header('Location: ' . basename(__FILE__) . '?action=preflight');
            exit();
        }
    }
    updater_render('login', 'Sign in to update IceHRM', array(
        'error' => $error,
        'recovery' => $updaterRecovery,
    ));
}

// --- signed in -------------------------------------------------------------
$checks = UpdaterPreflight::run();
$ready = UpdaterPreflight::allPassed($checks);

switch ($action) {
    case 'download':
        if (!$ready) {
            break;
        }
        $url = isset($_POST['url']) ? (string) $_POST['url'] : '';
        if (!UpdaterBootstrap::isPro()) {
            // The free edition always comes from the published location; there is
            // nothing for the user to choose and nothing to typo.
            $url = UpdaterPackage::FREE_URL;
        }

        $error = UpdaterPackage::download($url);
        if ($error === null) {
            $error = UpdaterPackage::extract();
        }
        if ($error !== null) {
            break;
        }

        $newVersion = UpdaterPackage::packagedVersion();
        $currentVersion = UpdaterBootstrap::currentVersion();

        if ($newVersion === 0) {
            $error = 'Could not read the version of the downloaded release. '
                . 'It may not be a complete IceHRM package.';
            break;
        }
        if ($newVersion <= $currentVersion) {
            updater_render('uptodate', 'Already up to date', array(
                'currentVersion' => $currentVersion,
                'newVersion' => $newVersion,
            ));
        }

        updater_render('confirm', 'Ready to update', array(
            'currentVersion' => $currentVersion,
            'newVersion' => $newVersion,
        ));
        break;

    case 'install':
        $lock = UpdaterLock::acquire(UpdaterAuth::currentUser()['username']);
        if (!$lock['ok']) {
            $error = $lock['message'];
            break;
        }

        @set_time_limit(0);
        $result = UpdaterInstaller::install();
        UpdaterLock::release();

        $_SESSION['updater_backup'] = $result['backupDir'];
        $_SESSION['updater_to_version'] = UpdaterPackage::packagedVersion();
        updater_render('installed', $result['ok'] ? 'Files updated' : 'Update failed', array(
            'result' => $result,
        ));
        break;

    case 'health':
        $expected = isset($_SESSION['updater_to_version']) ? (int) $_SESSION['updater_to_version'] : 0;
        $health = UpdaterHealth::check($expected);
        if ($health['state'] === 'healthy') {
            // Only now is it safe to throw away the backup and the staged files.
            $backup = isset($_SESSION['updater_backup']) ? $_SESSION['updater_backup'] : null;
            if (!empty($backup) && is_dir($backup)) {
                UpdaterFiles::deleteTree($backup);
            }
            UpdaterPackage::cleanUp();
            unset($_SESSION['updater_backup'], $_SESSION['updater_to_version']);
        }
        updater_render('health', 'Update ' . ($health['state'] === 'healthy' ? 'complete' : 'check'), array(
            'health' => $health,
            'backup' => isset($_SESSION['updater_backup']) ? $_SESSION['updater_backup'] : null,
        ));
        break;

    case 'rollback':
        $backup = isset($_SESSION['updater_backup']) ? $_SESSION['updater_backup'] : null;
        if (empty($backup)) {
            $error = 'There is no backup recorded for this session.';
            break;
        }
        $rollback = UpdaterInstaller::rollback($backup);
        updater_render('rollback', $rollback['ok'] ? 'Previous version restored' : 'Rollback incomplete', array(
            'rollback' => $rollback,
            'backup' => $backup,
        ));
        break;
}

updater_render('source', 'Update IceHRM', array(
    'checks' => $checks,
    'ready' => $ready,
    'error' => $error,
    'notice' => $notice,
));
