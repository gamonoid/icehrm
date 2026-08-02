<?php
/**
 * React app shell entry (SPA migration Phase 1).
 *
 * Serves the single-page shell: authenticates via the existing session, mints a
 * short-lived JWT (same as core/footer.php), and renders an HTML page that loads
 * the antd vendor bundles + app-shell.js. The shell then calls /appshell/bootstrap
 * to render the sidebar/top-nav. Reachable at: {CLIENT_BASE_URL}ui/
 *
 * This runs ALONGSIDE the legacy shell (header.php) behind a separate URL, so it
 * can be validated without disturbing the existing app. See docs/SPA_MIGRATION_PLAN.md.
 */

include 'includes.inc.php';

if (empty($user) || empty($user->email)) {
    header('Location:' . CLIENT_BASE_URL . 'login.php');
    exit();
}

// Loading the SPA means the user is on the new UI — remember it in the session
// AND persist it on the user record so logout/login keeps them here.
\Utils\SessionUtils::saveSessionString('uiMode', 'new');
$spaUser = new \Users\Common\Model\User();
$spaUser->Load("id = ?", array($user->id));
if (!empty($spaUser->id) && $spaUser->ui_mode !== 'new') {
    $spaUser->ui_mode = 'new';
    $spaUser->Save();
}

// Web SPA session token (type 'Web') — revoked on logout, separate from the
// long-lived 'FullAPI' token used by mobile/API clients. Long-lived so the SPA
// doesn't force a page refresh every hour to renew it.
$token = $jwtService->create(\Classes\JwtTokenService::WEB_SESSION_LIFETIME, 'Web');
$restApiBase = CLIENT_BASE_URL . 'api/';

$companyNameSetting = \Classes\SettingsManager::getInstance()->getSetting('Company: Name');
if (empty($companyNameSetting) || $companyNameSetting === 'Sample Company Pvt Ltd') {
    $companyNameSetting = 'IceHrm';
}

$shellConfig = array(
    'token' => $token,
    'restApiBase' => $restApiBase,
    'clientBaseUrl' => CLIENT_BASE_URL,
    'baseUrl' => BASE_URL,
    'userLevel' => $user->user_level,
);
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($companyNameSetting) ?></title>
    <link rel="shortcut icon" href="https://icehrm.s3.amazonaws.com/images/icon16.png">
    <link href="<?= BASE_URL ?>css/fa-6.4.0/css/all.css?v=<?= $cssVersion ?>" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        html, body {
            margin: 0; padding: 0; height: 100%; background: #f4f6f8;
            font-family: "Roboto", "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 14px; color: rgba(0, 0, 0, 0.87);
            -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;
        }
        #app-shell-root { height: 100%; }
        /* Kill the browser focus ring antd draws around focusable tab panes */
        .ant-tabs-tabpane:focus, .ant-tabs-tabpane:focus-visible { outline: none !important; }
        /* Natively-mounted legacy modules: neutralise leftover legacy block styling */
        .reviewBlock { border: 0 !important; box-shadow: none !important; padding: 0 !important; margin: 0 !important; }
        .app-shell-loading {
            display: flex; align-items: center; justify-content: center;
            height: 100vh; color: #999; font-family: -apple-system, system-ui, sans-serif;
        }
        /* Admin/Employee "Viewing as" role switch chip (sidebar) */
        .ice-role-switch {
            transition: background .15s ease, border-color .15s ease, transform .05s ease;
        }
        .ice-role-switch:hover {
            background: rgba(255, 255, 255, 0.11) !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
        }
        .ice-role-switch:hover .ice-role-switch-swap {
            color: rgba(255, 255, 255, 0.9) !important;
        }
        .ice-role-switch:active { transform: translateY(1px); }
        /* g2plot donut centre label is rendered as HTML with a hardcoded dark
           grey colour (#4D4D4D) — override it in dark mode so it stays legible. */
        body[data-color-mode="dark"] .ring-guide-html,
        body[data-color-mode="dark"] .ring-guide-name,
        body[data-color-mode="dark"] .ring-guide-value {
            color: rgba(255, 255, 255, 0.85) !important;
        }
        /* Legacy view-mode form fields render as disabled inputs with a hardcoded
           near-black colour (IceForm.js placeholder type) — invisible on dark.
           Force legible light text inside dark-mode modals. */
        body[data-color-mode="dark"] .ant-modal .ant-input[disabled],
        body[data-color-mode="dark"] .ant-modal .ant-input-disabled,
        body[data-color-mode="dark"] .ant-modal textarea.ant-input[disabled] {
            color: rgba(255, 255, 255, 0.85) !important;
            -webkit-text-fill-color: rgba(255, 255, 255, 0.85) !important;
        }
    </style>
</head>
<body>
    <script>
        // Apply the saved colour mode before React mounts, so dark-mode users
        // never see a light flash. Mirrors readColorMode() in the shell bundle.
        (function () {
            try {
                var m = localStorage.getItem('shell-color-mode');
                if (m !== 'dark' && m !== 'light') {
                    m = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)
                        ? 'dark' : 'light';
                }
                var bg = m === 'dark' ? '#0f141b' : '#f4f6f8';
                var fg = m === 'dark' ? 'rgba(255,255,255,0.92)' : 'rgba(0,0,0,0.87)';
                document.documentElement.style.background = bg;
                document.body.style.background = bg;
                document.body.style.color = fg;
                document.body.setAttribute('data-color-mode', m);
            } catch (e) { /* ignore */ }
        })();
    </script>
    <div id="app-shell-root">
        <div class="app-shell-loading">Loading&hellip;</div>
    </div>

    <script>
        window.__shellErrors = [];
        window.addEventListener('error', function (e) {
            window.__shellErrors.push('error: ' + (e.message || '') + ' @ ' + (e.filename || '') + ':' + (e.lineno || ''));
        });
        window.addEventListener('unhandledrejection', function (e) {
            window.__shellErrors.push('unhandledrejection: ' + ((e.reason && (e.reason.stack || e.reason.message)) || e.reason));
        });
    </script>

    <script type="application/json" id="app-shell-config"><?= json_encode($shellConfig) ?></script>

<?php
    // Cache-bust the shell bundle on every rebuild during development.
    $shellBundlePath = APP_BASE_PATH . '../web/dist/app-shell.js';
    $shellBundleVer = file_exists($shellBundlePath) ? filemtime($shellBundlePath) : $jsVersion;
?>
    <script src="<?= BASE_URL ?>dist/vendorReact.js?v=<?= $jsVersion ?>"></script>
    <script src="<?= BASE_URL ?>dist/vendorAntd.js?v=<?= $jsVersion ?>"></script>
    <script src="<?= BASE_URL ?>dist/vendorAntdIcons.js?v=<?= $jsVersion ?>"></script>
    <script src="<?= BASE_URL ?>dist/vendorAntv.js?v=<?= $jsVersion ?>"></script>
    <script src="<?= BASE_URL ?>dist/app-shell.js?v=<?= $shellBundleVer ?>"></script>
</body>
</html>
