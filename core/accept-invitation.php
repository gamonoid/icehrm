<?php
/**
 * Accept an invitation (public page).
 *
 * GET ?token=… (or the legacy ?hash=…) shows the invitation and a "choose a password"
 * form. Nothing is created by opening the link. POST (with the CSRF token from the
 * form) creates the employee and their login with the chosen password, signs the new
 * user in and opens the app.
 */

use Classes\BaseService;
use Classes\IceResponse;
use Classes\SettingsManager;
use Employees\Services\UserInvitationService;
use Utils\SessionUtils;

include 'includes.inc.php';

$logoFileUrl = \Classes\UIManager::getInstance()->getCompanyLogoUrl();
$loginUrl = CLIENT_BASE_URL . 'login.php';

$companyName = SettingsManager::getInstance()->getSetting('Company: Name');
if ($companyName === '' || $companyName === 'Sample Company Pvt Ltd') {
    $companyName = 'IceHrm';
}

// The invitee has no account yet, so the page is shown in the system's default language.
// IceHrm open source has no UI translation catalogs: the page is in English.
$catalog = array();
/** Translate an English key (gettext style) and fill its {placeholders}. */
$tr = function ($text, $vars = array()) use ($catalog) {
    $text = (string) $text;
    $out = (isset($catalog[$text]) && $catalog[$text] !== '') ? $catalog[$text] : $text;
    foreach ($vars as $name => $value) {
        $out = str_replace('{' . $name . '}', (string) $value, $out);
    }
    return $out;
};

$token = '';
if (isset($_REQUEST['token']) && is_string($_REQUEST['token'])) {
    $token = $_REQUEST['token'];
} elseif (isset($_REQUEST['hash']) && is_string($_REQUEST['hash'])) {
    $token = $_REQUEST['hash'];
}

$service = new UserInvitationService();
$invitation = $token !== '' ? $service->findByToken($token) : null;
$state = $invitation ? $service->getTokenState($invitation) : 'invalid';
$error = '';

if ($state === 'valid' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = SessionUtils::getSessionObject('csrf-acceptInvitation');
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['confirm']) && is_string($_POST['confirm']) ? $_POST['confirm'] : '';
    if (empty($_POST['csrf']) || !is_string($_POST['csrf']) || empty($csrf) || !hash_equals((string) $csrf, $_POST['csrf'])) {
        $error = $tr('Your session expired. Please try again.');
    } elseif ($password !== $confirm) {
        $error = $tr('The passwords do not match.');
    } else {
        $resp = $service->acceptInvitation($invitation, $password);
        if ($resp->getStatus() === IceResponse::SUCCESS) {
            $data = $resp->getData();
            $user = $data['user'];
            // Sign the new user in, as login.php does after a successful login.
            SessionUtils::regenerateSession();
            $user->ui_mode = 'new';
            $user->last_login = date('Y-m-d H:i:s');
            $user->Save();
            SessionUtils::saveSessionObject('user', $user);
            SessionUtils::saveSessionString('uiMode', 'new');
            header('Location: ' . CLIENT_BASE_URL . 'ui/');
            exit();
        }
        // Service messages are fixed English sentences, so they are catalog keys too.
        $error = $tr((string) $resp->getData());
        // The invitation may have changed state (e.g. accepted by a double submit).
        $state = $service->getTokenState($invitation);
    }
}

$extras = ($invitation && $state === 'valid') ? $service->getAcceptPageExtras($invitation) : [];
$csrfToken = $state === 'valid' ? BaseService::getInstance()->generateCsrf('acceptInvitation') : '';

/**
 * Titles from accept-page providers arrive already formatted in English (e.g. the
 * Onboarding extension's "<company> now uses IceHrm"). Recognise the known shapes and
 * translate them with their placeholders; anything else is looked up as it is.
 */
$translateExtraTitle = function ($title) use ($tr, $companyName, $invitation) {
    $title = (string) $title;
    if ($title === sprintf('%s now uses IceHrm', $companyName)) {
        return $tr('{company} now uses IceHrm', array('company' => $companyName));
    }
    if ($invitation && $title === sprintf('Welcome to %s, %s!', $companyName, $invitation->first_name)) {
        return $tr('Welcome to {company}, {name}!', array('company' => $companyName, 'name' => $invitation->first_name));
    }
    return $tr($title);
};

$screens = array(
    'valid' => array(
        'title' => !empty($extras['title'])
            ? $translateExtraTitle($extras['title'])
            : $tr('Welcome to {company}', array('company' => $companyName)),
        // A custom welcome message from HR is not in the catalog and stays as written.
        'message' => !empty($extras['message'])
            ? $tr($extras['message'])
            : $tr('Choose a password to set up your account.'),
    ),
    'accepted' => array(
        'title' => $tr('This invitation has already been used'),
        'message' => $tr('Your account is set up. Sign in with your email or username and the password you chose.'),
    ),
    'expired' => array(
        'title' => $tr('This invitation link has expired'),
        'message' => $tr('Please ask your HR team to send you a new invitation.'),
    ),
    'revoked' => array(
        'title' => $tr('This invitation was withdrawn'),
        'message' => $tr('If you still need access, please contact your HR team.'),
    ),
    'invalid' => array(
        'title' => $tr('This invitation link is not valid'),
        'message' => $tr('It may have been replaced by a newer invitation. Use the latest email you received, or ask your HR team to send a new one.'),
    ),
);
$title = $screens[$state]['title'];
$message = $screens[$state]['message'];
$details = array();
if (!empty($extras['details']) && is_array($extras['details'])) {
    foreach ($extras['details'] as $label => $value) {
        // Labels such as "Start date" and "Role" are catalog keys; values are data.
        $details[$tr($label)] = $value;
    }
}

$themes = array(
    'valid' => array('#1f7ae0', '<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a7 7 0 0 1 14 0v1"/>'),
    'accepted' => array('#1f9d57', '<path d="M20 6 9 17l-5-5"/>'),
    'expired' => array('#d9822b', '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'),
    'revoked' => array('#d64545', '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>'),
    'invalid' => array('#2f7fb5', '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>'),
);
list($accent, $iconPath) = $themes[$state];
$email = $invitation ? $invitation->email : '';

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?=htmlspecialchars($companyName)?> · <?=htmlspecialchars($tr('Invitation'))?></title>
    <link rel="icon" type="image/png" href="https://icehrm.s3.amazonaws.com/images/icon16.png">
    <style>
        :root { --accent: <?=$accent?>; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: linear-gradient(160deg, #f4f7fb 0%, #e9eff6 100%);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1f2d3d;
            -webkit-font-smoothing: antialiased;
        }
        .card {
            width: 100%;
            max-width: 460px;
            background: #fff;
            border: 1px solid #e7edf3;
            border-radius: 18px;
            box-shadow: 0 18px 48px rgba(31, 45, 61, 0.12);
            padding: 40px 36px 32px;
            text-align: center;
            animation: rise .4s ease both;
        }
        @keyframes rise { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }
        .logo { max-height: 46px; max-width: 220px; margin-bottom: 28px; }
        .badge {
            width: 72px; height: 72px; margin: 0 auto 22px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--accent) 12%, #fff); color: var(--accent);
        }
        .badge svg { width: 34px; height: 34px; fill: none; stroke: currentColor; stroke-width: 2.4; stroke-linecap: round; stroke-linejoin: round; }
        h1 { font-size: 1.5rem; line-height: 1.3; margin: 0 0 12px; font-weight: 650; }
        p.msg { font-size: 1rem; line-height: 1.6; color: #5b6b7c; margin: 0 auto; max-width: 380px; white-space: pre-line; }
        dl.details { margin: 20px auto 0; max-width: 360px; text-align: left; display: grid; grid-template-columns: auto 1fr; gap: 6px 16px; font-size: .92rem; }
        dl.details dt { color: #8796a5; }
        dl.details dd { margin: 0; font-weight: 600; }
        form { margin-top: 26px; text-align: left; }
        label { display: block; font-size: .85rem; font-weight: 600; margin: 14px 0 6px; color: #3c4b5a; }
        input[type=password], input[type=text] {
            width: 100%; padding: 11px 12px; font-size: 1rem; border: 1px solid #cfd8e3; border-radius: 10px; outline: none;
        }
        input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 18%, #fff); }
        input[readonly] { background: #f5f8fb; color: #5b6b7c; }
        .hint { font-size: .8rem; color: #8796a5; margin-top: 6px; }
        .error { margin-top: 18px; padding: 10px 12px; border-radius: 10px; background: #fdecec; color: #a12e2e; font-size: .9rem; text-align: left; }
        .btn {
            display: inline-block; margin-top: 24px; padding: 12px 28px; border: 0; border-radius: 10px;
            background: var(--accent); color: #fff; font-size: .95rem; font-weight: 600; text-decoration: none; cursor: pointer;
            transition: filter .15s ease, transform .15s ease;
        }
        form .btn { width: 100%; }
        .btn:hover { filter: brightness(1.07); transform: translateY(-1px); }
        .foot { margin-top: 26px; font-size: .8rem; color: #9aa7b4; text-align: center; }
        @media (max-width: 480px) { .card { padding: 32px 22px 26px; } h1 { font-size: 1.3rem; } }
    </style>
</head>
<body>
    <div class="card">
        <?php if (!empty($logoFileUrl)) { ?>
            <img class="logo" src="<?=htmlspecialchars($logoFileUrl)?>" alt="<?=htmlspecialchars($companyName)?>">
        <?php } ?>
        <div class="badge">
            <svg viewBox="0 0 24 24" aria-hidden="true"><?=$iconPath?></svg>
        </div>
        <h1><?=htmlspecialchars($title)?></h1>
        <p class="msg"><?=htmlspecialchars($message)?></p>

        <?php if (!empty($details)) { ?>
            <dl class="details">
                <?php foreach ($details as $label => $value) { ?>
                    <dt><?=htmlspecialchars($label)?></dt><dd><?=htmlspecialchars($value)?></dd>
                <?php } ?>
            </dl>
        <?php } ?>

        <?php if ($state === 'valid') { ?>
            <?php if ($error !== '') { ?>
                <div class="error" role="alert"><?=htmlspecialchars($error)?></div>
            <?php } ?>
            <form method="post" action="accept-invitation.php" autocomplete="on">
                <input type="hidden" name="token" value="<?=htmlspecialchars($token, ENT_QUOTES)?>">
                <input type="hidden" name="csrf" value="<?=htmlspecialchars($csrfToken, ENT_QUOTES)?>">
                <label for="email"><?=htmlspecialchars($tr('Email'))?></label>
                <input type="text" id="email" name="username" value="<?=htmlspecialchars($email, ENT_QUOTES)?>" autocomplete="username" readonly>
                <label for="password"><?=htmlspecialchars($tr('Choose a password'))?></label>
                <input type="password" id="password" name="password" autocomplete="new-password" minlength="8" maxlength="72" required>
                <div class="hint"><?=htmlspecialchars($tr('At least 8 characters, with a number, an uppercase letter, a lowercase letter and a symbol.'))?></div>
                <label for="confirm"><?=htmlspecialchars($tr('Confirm password'))?></label>
                <input type="password" id="confirm" name="confirm" autocomplete="new-password" minlength="8" maxlength="72" required>
                <button class="btn" type="submit"><?=htmlspecialchars($tr('Set up my account'))?></button>
            </form>
        <?php } else { ?>
            <a class="btn" href="<?=htmlspecialchars($loginUrl)?>"><?=htmlspecialchars($tr('Go to login'))?></a>
        <?php } ?>
        <div class="foot">&copy; <?=date('Y')?> <?=htmlspecialchars($companyName)?></div>
    </div>
</body>
</html>
