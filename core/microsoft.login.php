<?php

use Classes\MicrosoftGraphApiClient;
use Classes\SettingsManager;
use Users\Common\Model\User;

$config = array(
    'tenant_id' => SettingsManager::getInstance()->getSetting('Microsoft: Tenant ID'),
    'client_id' => SettingsManager::getInstance()->getSetting('Microsoft: Client ID'),
    'client_secret' => SettingsManager::getInstance()->getSetting('Microsoft: Client Secret'),
    'redirect_uri' => CLIENT_BASE_URL.'login.php'
);

$client = new MicrosoftGraphApiClient(
    $config['client_id'],
    $config['client_secret'],
    $config['redirect_uri'],
    $config['tenant_id'],
);

if (!isset($_GET['code'])) {
    $url = $client->getAuthorizationUrl();
    session_start();
    $_SESSION['auth_type'] = 'microsoft';
    session_write_close();
    header("Location: " . $url);
    exit();
} else {
    // CSRF (OAuth "state"): getAuthorizationUrl() stored a random nonce in the session
    // and sent it to Microsoft as `state`; the callback must echo it back. The nonce was
    // generated but never verified, so a forged callback could log the victim into the
    // attacker's Microsoft account. Reject a missing/mismatched state and consume it.
    $expectedMsState = \Utils\SessionUtils::getSessionString('microsoft_auth_state');
    \Utils\SessionUtils::saveSessionString('microsoft_auth_state', '');
    if (empty($expectedMsState)
        || empty($_GET['state'])
        || !is_string($_GET['state'])
        || !hash_equals($expectedMsState, $_GET['state'])
    ) {
        header("Location:" . CLIENT_BASE_URL . "login.php?f=1&fm=Invalid authentication state");
        exit();
    }

    $code = $_GET['code'];
    $response = $client->sendAccessTokenRequest($code);
    $profile = $client->getProfile();

    $suser = new User();
    $suser->Load("email = ?", array($profile['userPrincipalName']));
    if (empty($suser->id)) {
        $suser->Load("username = ?", array($profile['userPrincipalName']));
    }
    if (empty($suser->id)) {
        $message = "No user found for ".$profile['userPrincipalName'];
        header("Location:" . CLIENT_BASE_URL . "login.php?f=1&fm=$message");
        exit();
    }
    // Prevent session fixation: issue a fresh session ID on this SSO login.
    \Utils\SessionUtils::regenerateSession();
    \Utils\SessionUtils::saveSessionObject('user', $suser);
    $suser->last_login = date("Y-m-d H:i:s");
    $suser->Save();

    // Same as the password path in login.php: apply pending core/extension/pro
    // migrations for any user level. Needed separately here because this redirects to
    // login.php ALREADY authenticated, which takes the early-return branch and never
    // reaches that hook.
    \Classes\Migration\MigrationRunner::runAll(true);
    header("Location:" . CLIENT_BASE_URL . "login.php");
    exit();
}