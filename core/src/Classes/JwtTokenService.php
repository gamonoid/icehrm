<?php

namespace Classes;

use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;

class JwtTokenService
{
    // Default session-token lifetime, in seconds. Matches the legacy app's
    // 180-day sessions (see index.php / NativeModuleRegistry includeJwtToken) so
    // the SPA doesn't force users to refresh every hour to renew the token.
    const SESSION_LIFETIME = 15552000; // 180 days — FullAPI (mobile/API) tokens
    const WEB_SESSION_LIFETIME = 86400; // 24 hours — Web (SPA) session tokens

    /**
     * Token lifetime (seconds) for a given type — the single source of truth shared
     * with RestApiManager so the JWT wrapper and the encrypted token's own expiry
     * always agree. FullAPI lives 180 days; Web lives 24 hours.
     */
    public static function lifetimeForType($type)
    {
        return ($type === 'Web') ? self::WEB_SESSION_LIFETIME : self::SESSION_LIFETIME;
    }

    /**
     * Create a JWT wrapping the current user's access token of the given type.
     *   'FullAPI' — long-lived API/mobile token (default), 180 days.
     *   'Web'     — web SPA session token, 24 hours, revoked on logout.
     * When $expire is null the wrapper's lifetime defaults to the type's TTL so the
     * JWT can never outlive the underlying access token.
     */
    public function create($expire = null, $type = 'FullAPI')
    {
        if ($expire === null) {
            $expire = self::lifetimeForType($type);
        }
        $secret = APP_SEC.APP_PASSWORD;
        $resp = BaseService::getInstance()->getAccessToken($type);

        $payload = array(
            "token" => $resp->getData(),
            "expire" => time() + $expire,
        );

        return JWT::encode($payload, $secret);
    }

    public function getBaseToken($jwtToken)
    {
        $secret = APP_SEC.APP_PASSWORD;
        try {
            $jwt = JWT::decode($jwtToken, $secret, array('HS256'));
        } catch (SignatureInvalidException $e) {
            return null;
        }

        if (time() > intval($jwt->expire)) {
            return null;
        }

        return $jwt->token;
    }
}
