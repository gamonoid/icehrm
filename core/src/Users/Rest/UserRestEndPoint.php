<?php
namespace Users\Rest;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\PasswordManager;
use Classes\RestApiManager;
use Classes\RestEndPoint;
use Users\Common\Model\User;
use Utils\LogManager;

class UserRestEndPoint extends RestEndPoint
{
    public function post(User $user)
    {
        $body = $this->getRequestBody();

        if (!isset($body['grant_type']) || $body['grant_type'] !== 'password') {
            return new IceResponse(IceResponse::ERROR, 'Missing grant_type', 400);
        }

        if (!isset($body['client_id'])) {
            return new IceResponse(IceResponse::ERROR, 'Missing client_id', 400);
        }

        if (!isset($body['client_secret'])) {
            return new IceResponse(IceResponse::ERROR, 'Missing client_secret', 400);
        }

        if (!isset($body['username'])) {
            return new IceResponse(IceResponse::ERROR, 'Missing username', 400);
        }

        if (!isset($body['password'])) {
            return new IceResponse(IceResponse::ERROR, 'Missing password', 400);
        }

        $user = new User();
        $user->Load(
            "username = ? or email = ?",
            [
                $body['username'],
                $body['username'],
            ]
        );

        if (!PasswordManager::verifyPassword($body['password'], $user->password)) {
            return new IceResponse(IceResponse::ERROR, 'Incorrect username or password', 401);
        }

        $resp = RestApiManager::getInstance()->getAccessTokenForUser($user);

        if ($resp->getStatus() != IceResponse::SUCCESS) {
            LogManager::getInstance()->error(
                "Error occurred while creating REST Api access token for ".$user->username
            );

            return new IceResponse(IceResponse::ERROR, 'Error generating access token', 401);
        }

        $responseData = [
            "access_token" => $resp->getData(),
            "token_type" => "bearer",
            "expires_in" => 3600,
            "scope" => strtolower($user->user_level),
        ];

        return new IceResponse(IceResponse::SUCCESS, $responseData, 200);
    }
}
