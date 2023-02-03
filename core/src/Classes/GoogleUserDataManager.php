<?php

namespace Classes;

use Classes\Exception\GoogleSyncException;
use Google_Client;
use Users\Common\Model\User;
use Utils\SessionUtils;

class GoogleUserDataManager
{
    protected static $client = null;
    protected static $accessToken = [];

    /**
     * @return Google_Client
     * @throws GoogleSyncException
     */
    public static function getGoogleApiClient()
    {


        $client = new Google_Client();
        $client->setApplicationName("IceHrm");
        $client->setRedirectUri(CLIENT_BASE_URL.'/google-connect.php');
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        $path = SettingsManager::getInstance()->getSetting('System: Google Client Secret Path');
        if (!trim($path)) {
            throw new GoogleSyncException('Google client secret is not defined');
        }

        try {
            $client->setAuthConfig($path);
        } catch (\Google_Exception $e) {
            throw new GoogleSyncException($e->getMessage());
        }

        $scopes = array();
        $scopes[] = "https://www.googleapis.com/auth/userinfo.profile";
        $scopes[] = "https://www.googleapis.com/auth/userinfo.email";
        $scopes[] = "https://www.googleapis.com/auth/calendar";

        $client->setScopes($scopes);
        self::$client = $client;

        return self::$client;
    }

    public static function isConnected($user)
    {
        return !empty(self::getAccessToken($user));
    }

    public static function getAccessToken($user)
    {

        if (self::$accessToken[$user->id]) {
            return self::$accessToken[$user->id];
        }

        if (empty($user->googleUserData)) {
            return null;
        }

        $data = json_decode($user->googleUserData, true);

        if (!empty($data['accessToken']['access_token'])) {
            self::$accessToken[$user->id] = json_encode($data['accessToken']);
            return self::$accessToken[$user->id];
        }

        return null;
    }

    public static function getUserMatchingGoogleAuthenticatedUser($guser)
    {
        $loggedInUser = SessionUtils::getSessionObject('user');

        if (!empty($loggedInUser->email) && $loggedInUser->email === $guser->email) {
            return $loggedInUser;
        }

        return null;
    }

    public static function saveGoogleUserData($guser, $accessToken)
    {

        $client = self::getGoogleApiClient();
        $refreshToken = $client->getRefreshToken();

        $user = new User();
        $user->Load('email = ?', array($guser->email));
        $user->googleUserData = json_encode(
            [
            'user'  =>  $guser,
            'accessToken'  =>  $accessToken,
            'refreshToken'  =>  $refreshToken,
            ]
        );

        $ok = $user->Save();

        if ($ok) {
            $sessionUser = BaseService::getInstance()->getCurrentUser();
            $sessionUser->googleUserData = $user->googleUserData;
            \Utils\SessionUtils::saveSessionObject('user', $sessionUser);
        }

        return $ok;
    }

    public static function updateGoogleUserData($cuser)
    {
        $user = new User();
        $user->Load('email = ?', array($cuser->email));

        if (empty($user->googleUserData)) {
            return false;
        }

        $googleUserData = json_decode($user->googleUserData, true);
        $client = self::getGoogleApiClient();

        $accessToken = $client->fetchAccessTokenWithRefreshToken($googleUserData['refreshToken']);

        if (!empty($accessToken['error'])) {
            $googleUserData['accessToken'] = $accessToken;
            $user->googleUserData = json_encode($googleUserData);
            return $user->Save();
        }

        return false;
    }
}
