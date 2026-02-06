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
    \Utils\SessionUtils::saveSessionObject('user', $suser);
    $suser->last_login = date("Y-m-d H:i:s");
    $suser->Save();
    header("Location:" . CLIENT_BASE_URL . "login.php");
    exit();
}