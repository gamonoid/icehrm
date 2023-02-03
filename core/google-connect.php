<?php

use Classes\Exception\GoogleSyncException;
use Classes\GoogleUserDataManager;

include ("config.base.php");
include ("include.common.php");
include("server.includes.inc.php");

if (!\Classes\BaseService::getInstance()->getCurrentUser()) {
    exit();
}

try {
    $client = GoogleUserDataManager::getGoogleApiClient();
} catch (GoogleSyncException $e) {
    echo 'Invalid google API credentials!!';
    exit();
}

$oauth2 = new Google_Service_Oauth2($client);

if (isset($_GET['code'])) {
    $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $guser = $oauth2->userinfo->get();
    if (empty($guser)) {
        echo 'Error occurred while connecting your google account!!';
        exit();
    }
    $user = GoogleUserDataManager::getUserMatchingGoogleAuthenticatedUser($guser);
    if (empty($user)) {
        echo "Error occurred while connecting your google account. Your IceHrm email doesn't match the google account email";
        exit();
    }

    GoogleUserDataManager::saveGoogleUserData($guser, $accessToken);

    header('Location: ' . CLIENT_BASE_URL);
    exit();

} else {
    session_start();
    $_SESSION['googleauthredirect'] = CLIENT_BASE_URL.'google-connect.php';
    session_write_close();
    $authUrl = $client->createAuthUrl();
    header('Location: ' . $authUrl);
    exit();
}
