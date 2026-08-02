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

        // Brute-force lockout — shared counter with the web login. A locked account
        // must be unlocked via a web email-code login or an admin password change.
        if (PasswordManager::isAccountLocked($user)) {
            return new IceResponse(
                IceResponse::ERROR,
                'Account locked after too many failed attempts. Log in via the web using an email code, or contact an administrator.',
                403
            );
        }

        if (!PasswordManager::verifyPassword($body['password'], $user->password)) {
            PasswordManager::recordFailedLogin($user);
            return new IceResponse(IceResponse::ERROR, 'Incorrect username or password', 401);
        }

        // Successful password login — clear the failed-attempt counter.
        PasswordManager::resetFailedLogins($user);

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
            // FullAPI tokens are valid for 180 days (reset early only by a password
            // change); advertise the real lifetime rather than a misleading 3600.
            "expires_in" => \Classes\JwtTokenService::SESSION_LIFETIME,
            "scope" => strtolower($user->user_level),
        ];

        return new IceResponse(IceResponse::SUCCESS, $responseData, 200);
    }

	public function updatePassword(User $user) {
		$body = $this->getRequestBody();
		$resp = $this->changePassword($user, $body['id'], $body['password'], $body['confirm_password']);
		return $resp;
	}

	private function changePassword(User $requestingUser, $userId, $password, $confirmPassword )
	{
		if (defined('DEMO_MODE')) {
			return new IceResponse(
				IceResponse::ERROR,
				"You are not allowed to change the password in demo mode.",
				400

			);
		}

		if ($password !== $confirmPassword) {
			return new IceResponse(
				IceResponse::ERROR,
				"Password and confirm password doesn't match.",
				400

			);
		}

		$user = new User();
		$user->Load("id = ?", array($userId));
		if ($requestingUser->user_level == 'Admin' || $requestingUser->id == $user->id) {
			if (empty($user->id)) {
				return new IceResponse(
					IceResponse::ERROR,
					"Error updating password due to an invalid user.",
					400

				);
			}

			$passwordStrengthResponse = PasswordManager::isQualifiedPassword($password);
			if ($passwordStrengthResponse->getStatus() === IceResponse::ERROR) {
				return $passwordStrengthResponse;
			}

			$user->password = PasswordManager::createPasswordHash($password);
			$ok = $user->Save();
			if (!$ok) {
				return new IceResponse(IceResponse::ERROR, $user->ErrorMsg(), 400);
			}
			// A password change (including by an admin) clears any failed-attempt lock.
			PasswordManager::resetFailedLogins($user);
			return new IceResponse(IceResponse::SUCCESS, $user);
		}
		return new IceResponse(
			IceResponse::ERROR,
			"Permission denied.",
			403

		);
	}
}
