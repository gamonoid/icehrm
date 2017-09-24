<?php
namespace Classes;

use Classes\Crypt\AesCtr;
use Model\RestAccessToken;
use Users\Common\Model\User;
use Utils\LogManager;

class RestApiManager
{

    private static $me = null;

    protected $endPoints = array();

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$me)) {
            self::$me = new RestApiManager();
        }

        return self::$me;
    }

    public function generateUserAccessToken($user)
    {

        $data = array();
        $data['userId'] = $user->id;
        $data['expires'] = strtotime('now') + 60*60;

        $accessTokenTemp = AesCtr::encrypt(json_encode($data), $user->password, 256);
        $accessTokenTemp = $user->id."|".$accessTokenTemp;
        $accessToken = AesCtr::encrypt($accessTokenTemp, APP_SEC, 256);

        return new IceResponse(IceResponse::SUCCESS, $accessToken);
    }

    public function getAccessTokenForUser($user)
    {
        $accessTokenObj = new RestAccessToken();
        $accessTokenObj->Load("userId = ?", array($user->id));

        $generateAccessToken = false;
        $accessToken = $accessTokenObj->token;
        if (!empty($accessToken)) {
            $resp = $this->validateAccessTokenInner($accessToken);
            if ($resp->getStatus() != IceResponse::SUCCESS) {
                $generateAccessToken = true;
            }
        } else {
            $generateAccessToken = true;
        }

        if ($generateAccessToken) {
            $accessToken = $this->generateUserAccessToken($user)->getData();
            if (!empty($accessTokenObj->id)) {
                $accessTokenObj->token = $accessToken;
                $accessTokenObj->hash = md5(CLIENT_BASE_URL.$accessTokenObj->token);
                $accessTokenObj->updated = date("Y-m-d H:i:s");
                $accessTokenObj->Save();
            } else {
                $accessTokenObj = new RestAccessToken();
                $accessTokenObj->userId = $user->id;
                $accessTokenObj->token = $accessToken;
                $accessTokenObj->hash = md5(CLIENT_BASE_URL.$accessTokenObj->token);
                $accessTokenObj->updated = date("Y-m-d H:i:s");
                $accessTokenObj->created = date("Y-m-d H:i:s");
                $accessTokenObj->Save();
            }
        }

        return new IceResponse(IceResponse::SUCCESS, $accessTokenObj->hash);
    }

    public function validateAccessToken($hash)
    {
        $accessTokenObj = new RestAccessToken();
        LogManager::getInstance()->info("AT Hash:".$hash);
        $accessTokenObj->Load("hash = ?", array($hash));
        LogManager::getInstance()->info("AT Hash Object:".json_encode($accessTokenObj));
        if (!empty($accessTokenObj->id) && $accessTokenObj->hash == $hash) {
            //No need to do user based validation for now
            //return $this->validateAccessTokenInner($accessTokenObj->token);
            return new IceResponse(IceResponse::SUCCESS, true);
        }

        return new IceResponse(IceResponse::ERROR, "Authorization bearer token not found or invalid", 401);
    }

    private function validateAccessTokenInner($accessToken)
    {
        $accessTokenTemp = AesCtr::decrypt($accessToken, APP_SEC, 256);
        $parts = explode("|", $accessTokenTemp);

        $user = new User();
        $user->Load("id = ?", array($parts[0]));
        if (empty($user->id) || $user->id != $parts[0] || empty($parts[0])) {
            return new IceResponse(IceResponse::ERROR, -1);
        }

        $accessToken = AesCtr::decrypt($parts[1], $user->password, 256);

        $data = json_decode($accessToken, true);
        if ($data['userId'] == $user->id) {
            return new IceResponse(IceResponse::SUCCESS, true);
        }

        return new IceResponse(IceResponse::ERROR, false);
    }

    /**
     * @param RestEndPoint $endPoint
     */
    // TODO - not used can be removed
    public function addEndPoint($endPoint)
    {
        $url = $endPoint->getUrl();
        LogManager::getInstance()->info("Adding REST end point for - ".$url);
        $this->endPoints[$url] = $endPoint;
    }

    public function process($type, $url, $parameters)
    {

        $accessTokenValidation = $this->validateAccessToken($parameters['access_token']);

        if ($accessTokenValidation->getStatus() == IceResponse::ERROR) {
            return $accessTokenValidation;
        }

        if (isset($this->endPoints[$url])) {
            return $this->endPoints[$url]->$type($parameters);
        }

        return new IceResponse(IceResponse::ERROR, "End Point ".$url." - Not Found");
    }
}
