<?php

use Classes\IceResponse;
use Classes\SettingsManager;
use Employees\Services\UserInvitationService;

include 'includes.inc.php';

$logoFileUrl = \Classes\UIManager::getInstance()->getCompanyLogoUrl();
$loginUrl = CLIENT_BASE_URL . 'login.php';

$companyName = SettingsManager::getInstance()->getSetting('Company: Name');
if ($companyName === '' || $companyName === 'Sample Company Pvt Ltd') {
    $companyName = 'IceHrm';
}

// Resolve the screen the invitee should see. One of:
//   invalid  - no/expired/already-used link
//   success  - account created, temporary password emailed
//   created  - account created, but the password email could not be sent
//   error    - something went wrong before the account could be created
$state = 'invalid';
$title = 'This invitation link is no longer valid';
$message = 'It may have expired or already been used. If you still need access, '
    . 'please contact your HR department to request a new invitation.';

$hash = isset($_REQUEST['hash']) ? $_REQUEST['hash'] : '';
if (!empty($hash)) {
    $uiService = new UserInvitationService();
    $ui = $uiService->getInvitationByHash($hash);

    if (!empty($ui)) {
        $resp = $uiService->processUserInvitation($ui);

        if ($resp->getStatus() === IceResponse::SUCCESS) {
            $state = 'success';
            $title = 'You\'re all set!';
            $message = 'Your account has been created. We\'ve emailed you a temporary '
                . 'password — check your inbox, then sign in to get started.';
        } elseif (!empty($ui->created_user_id)) {
            // The account exists; only the password email failed to go out.
            $state = 'created';
            $title = 'Your account is ready';
            $message = 'We created your account, but couldn\'t email your temporary '
                . 'password automatically. Please contact your HR department to '
                . 'receive your login details.';
        } else {
            $state = 'error';
            $title = 'We couldn\'t process your invitation';
            $message = $resp->getData();
        }
    }
}

// Per-state visual treatment (accent colour + inline SVG icon, no external deps).
$themes = array(
    'success' => array('#1f9d57', '<path d="M20 6 9 17l-5-5"/>'),
    'created' => array('#d9822b', '<path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/>'),
    'error' => array('#d64545', '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>'),
    'invalid' => array('#2f7fb5', '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>'),
);
list($accent, $iconPath) = $themes[$state];
$showLogin = in_array($state, array('success', 'created', 'invalid'), true);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=htmlspecialchars($companyName)?> · Invitation</title>
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
            width: 72px;
            height: 72px;
            margin: 0 auto 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: color-mix(in srgb, var(--accent) 12%, #fff);
            color: var(--accent);
        }
        .badge svg { width: 34px; height: 34px; fill: none; stroke: currentColor; stroke-width: 2.4; stroke-linecap: round; stroke-linejoin: round; }
        h1 { font-size: 1.5rem; line-height: 1.3; margin: 0 0 12px; font-weight: 650; }
        p.msg { font-size: 1rem; line-height: 1.6; color: #5b6b7c; margin: 0 auto; max-width: 360px; }
        .btn {
            display: inline-block;
            margin-top: 28px;
            padding: 12px 28px;
            border-radius: 10px;
            background: var(--accent);
            color: #fff;
            font-size: .95rem;
            font-weight: 600;
            text-decoration: none;
            transition: filter .15s ease, transform .15s ease;
        }
        .btn:hover { filter: brightness(1.07); transform: translateY(-1px); }
        .foot { margin-top: 26px; font-size: .8rem; color: #9aa7b4; }
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
        <?php if ($showLogin) { ?>
            <a class="btn" href="<?=htmlspecialchars($loginUrl)?>">Go to login</a>
        <?php } ?>
        <div class="foot">&copy; <?=date('Y')?> <?=htmlspecialchars($companyName)?></div>
    </div>
</body>
</html>
