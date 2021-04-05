<?php

namespace Classes;

use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;

class JwtTokenService
{
    public function create($expire = 3600)
    {
        $secret = APP_SEC.APP_PASSWORD;
        $resp = BaseService::getInstance()->getAccessToken();

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
