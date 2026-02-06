<?php

use Classes\SettingsManager;

$client = new Google_Client();
$client->setApplicationName("IceHrm");
$client->setRedirectUri(CLIENT_BASE_URL.'google-login.php');

$scopes = array();
$scopes[] = "https://www.googleapis.com/auth/userinfo.profile";
$scopes[] = "https://www.googleapis.com/auth/userinfo.email";
$client->setScopes($scopes);

$oauth2 = new Google_Service_Oauth2($client);
try {
    //$googleAuthJson = \Classes\SettingsManager::getInstance()->getSetting('System: Google Client Secret Path');
    $config = [];
    $config['web'] = [];
    $config['web']['client_id'] = SettingsManager::getInstance()->getSetting('Google: Client ID');
    $config['web']['client_secret'] = SettingsManager::getInstance()->getSetting('Google: Client Secret');
    $config['web']['redirect_uris'][0] = CLIENT_BASE_URL.'login.php';

    $client->setAuthConfig($config);
} catch (Google_Exception $e) {
}

if (isset($_GET['code'])) {
    $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $user = $oauth2->userinfo->get();
    if (empty($user)) {
        header('Location: ' . CLIENT_BASE_URL."login.php?f=1&fm=Error occurred while authenticating with google");
        exit();
    }
    $googleAuth = new \Classes\GoogleAuthManager();
    $suser = $googleAuth->checkGoogleAuthUser($user);

    if ($suser === null) {
        header('Location: ' . CLIENT_BASE_URL."login.php?f=1&fm=Your email address is not registered as a user");
        exit();
    }

    $ssoUserLoaded = true;

} else {
    session_start();
    $_SESSION['auth_type'] = 'google';
    session_write_close();
    $authUrl = $client->createAuthUrl();
    header('Location: ' . $authUrl);
    exit();
}



