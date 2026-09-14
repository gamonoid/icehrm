<?php
namespace Classes;

use Classes\Crypt\IceCrypt;
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

    public function generateUserAccessToken($user, $type = 'FullAPI')
    {
        $data = array();
        $data['userId'] = $user->id;
        // Bake the type-appropriate expiry into the (encrypted) token itself so it
        // is enforced for EVERY client — including mobile/API clients that present
        // the raw token hash and never pass through the JWT layer.
        //   FullAPI -> 180 days. Reset early only by a password change: the inner
        //              layer is keyed on the password hash, so a change makes the
        //              old token undecryptable (and changePassword also resets it).
        //   Web     -> 24 hours.
        $data['expires'] = time() + JwtTokenService::lifetimeForType($type);

        // AES-256-GCM via IceCrypt: the previous AesCtr layers were unauthenticated,
        // so a token could be tampered with and nothing detected it.
        $accessTokenTemp = IceCrypt::encrypt(json_encode($data), $user->password);
        $accessTokenTemp = $user->id."|".$accessTokenTemp;
        $accessToken = IceCrypt::encrypt($accessTokenTemp, APP_SEC);

        return new IceResponse(IceResponse::SUCCESS, $accessToken);
    }

    /**
     * Random, unguessable lookup handle for a stored token (fills the varchar(32)
     * `hash` column). The client presents this as the bearer credential, so it must
     * not be a predictable function of the token or anything else.
     */
    private function generateTokenHash()
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Get (or lazily create) the user's access token of the given type.
     *   'FullAPI' — long-lived API/mobile token (default; unaffected by logout).
     *   'Web'     — the web SPA session token (revoked on logout).
     */
    public function getAccessTokenForUser($user, $type = 'FullAPI')
    {
        $accessTokenObj = new RestAccessToken();
        $accessTokenObj->Load("userId = ? and type = ?", array($user->id, $type));

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
            $accessToken = $this->generateUserAccessToken($user, $type)->getData();
            if (!empty($accessTokenObj->id)) {
                $accessTokenObj->token = $accessToken;
                $accessTokenObj->hash = $this->generateTokenHash();
                $accessTokenObj->updated = date("Y-m-d H:i:s");
                $accessTokenObj->Save();
            } else {
                $accessTokenObj = new RestAccessToken();
                $accessTokenObj->userId = $user->id;
                $accessTokenObj->type = $type;
                $accessTokenObj->token = $accessToken;
                $accessTokenObj->hash = $this->generateTokenHash();
                $accessTokenObj->updated = date("Y-m-d H:i:s");
                $accessTokenObj->created = date("Y-m-d H:i:s");
                $accessTokenObj->Save();
            }
        }

        return new IceResponse(IceResponse::SUCCESS, $accessTokenObj->hash);
    }

    /**
     * Force a fresh REST access token of the given type for the user,
     * invalidating the previous one (its old hash stops resolving). Returns the
     * new token hash.
     */
    public function resetAccessTokenForUser($user, $type = 'FullAPI')
    {
        $accessToken = $this->generateUserAccessToken($user, $type)->getData();

        $accessTokenObj = new RestAccessToken();
        $accessTokenObj->Load("userId = ? and type = ?", array($user->id, $type));
        if (empty($accessTokenObj->id)) {
            $accessTokenObj = new RestAccessToken();
            $accessTokenObj->userId = $user->id;
            $accessTokenObj->type = $type;
            $accessTokenObj->created = date("Y-m-d H:i:s");
        }
        $accessTokenObj->token = $accessToken;
        $accessTokenObj->hash = $this->generateTokenHash();
        $accessTokenObj->updated = date("Y-m-d H:i:s");
        $accessTokenObj->Save();

        return new IceResponse(IceResponse::SUCCESS, $accessTokenObj->hash);
    }

    /**
     * Delete the user's access token of the given type so its hash no longer
     * resolves. Used on logout to revoke the web session token ('Web') while
     * leaving the long-lived API/mobile token ('FullAPI') intact.
     */
    public function deleteAccessTokenForUser($user, $type = 'Web')
    {
        if (empty($user) || empty($user->id)) {
            return;
        }
        $accessTokenObj = new RestAccessToken();
        $accessTokenObj->Load("userId = ? and type = ?", array($user->id, $type));
        if (!empty($accessTokenObj->id)) {
            $accessTokenObj->Delete();
        }
    }

    public function validateAccessToken($hash)
    {
        if (empty($hash)) {
            return new IceResponse(IceResponse::ERROR, "Authorization bearer token is empty", 403);
        }
        $accessTokenObj = new RestAccessToken();
        // Deliberately not logged: $hash is the value clients present as their bearer
        // credential, and the loaded row serialises both it and the encrypted token. This
        // runs on every authenticated REST request, so logging either wrote a replayable
        // credential to the log on every call. A short, non-reversible fingerprint is
        // enough to correlate a request with a token, and only at DEBUG.
        LogManager::getInstance()->debug('Access token lookup: '.substr(hash('sha256', $hash), 0, 8));
        $accessTokenObj->Load("hash = ?", array($hash));
        // hash_equals: constant-time, and strict — the loose == it replaces would accept a
        // type-juggled match, and the SQL lookup above can match case-insensitively
        // depending on the column collation.
        if (!empty($accessTokenObj->id)
            && is_string($hash)
            && hash_equals((string) $accessTokenObj->hash, $hash)
        ) {
            //No need to do user based validation for now
            return $this->validateAccessTokenInner($accessTokenObj->token);
        }

        return new IceResponse(IceResponse::ERROR, "Authorization bearer token is invalid", 403);
    }

    private function validateAccessTokenInner($accessToken)
    {
        // Accepts v2 and legacy AesCtr, so tokens issued before this change stay valid
        // until they expire. false means the outer layer failed to authenticate.
        $accessTokenTemp = IceCrypt::decrypt($accessToken, APP_SEC);
        if ($accessTokenTemp === false) {
            return new IceResponse(IceResponse::ERROR, -1);
        }
        $parts = explode("|", $accessTokenTemp);
        if (count($parts) < 2) {
            return new IceResponse(IceResponse::ERROR, -1);
        }

        $user = new User();
        $user->Load("id = ?", array($parts[0]));
        if (empty($user->id) || $user->id != $parts[0] || empty($parts[0])) {
            return new IceResponse(IceResponse::ERROR, -1);
        }

        $accessToken = IceCrypt::decrypt($parts[1], $user->password);

        $data = json_decode($accessToken, true);
        // Invalid/garbage inner payload — e.g. the password changed, so the inner
        // layer (keyed on the password hash) no longer decrypts. Reject.
        if (!is_array($data) || empty($data['userId']) || $data['userId'] != $user->id) {
            return new IceResponse(IceResponse::ERROR, false);
        }

        // Enforce the token's baked-in expiry (previously ignored). Past its TTL the
        // token is rejected; getAccessTokenForUser mints a fresh one on next login.
        // FullAPI tokens carry a 180-day TTL, Web tokens 24 hours.
        if (empty($data['expires']) || time() > intval($data['expires'])) {
            return new IceResponse(IceResponse::ERROR, "Access token expired", 403);
        }

        unset($user->password);
        return new IceResponse(IceResponse::SUCCESS, $user);
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
