<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
namespace Users\Admin\Api;

use Classes\PasswordManager;
use Users\Common\Model\User;
use Classes\IceResponse;
use Classes\SubActionManager;
use Utils\LogManager;
use Utils\SessionUtils;

class UsersActionManager extends SubActionManager
{
    public function saveUser($req)
    {
        if (empty($req->csrf) || $req->csrf !== SessionUtils::getSessionObject('csrf-User')) {
            return new IceResponse(
                IceResponse::ERROR,
                "Invalid CSRF token."
            );
        }

        $req->email = trim($req->email);
        $req->username = trim($req->username);

        if ($this->user->user_level !== 'Admin') {
			return new IceResponse(IceResponse::ERROR, "Only an admin can add a user");
		}

		if ( $req->user_level !== 'Admin' && $req->user_level !== 'Restricted Admin' && empty($req->employee)) {
			return new IceResponse(IceResponse::ERROR, "You should assign an employee to this user");
		}

		if (empty($req->id)) {
			// This is a new user
			$user = new User();
			$user->Load("email = ?", array($req->email));
			if ($user->email == $req->email) {
				return new IceResponse(
					IceResponse::ERROR,
					"User with same email already exists"
				);
			}

			$user->Load("username = ?", array($req->username));

			if ($user->username == $req->username) {
				return new IceResponse(
					IceResponse::ERROR,
					"User with same username already exists"
				);
			}

			$user = new User();
			$user->email = $req->email;
			$user->username = $req->username;
			$password = $this->generateRandomString(6);
			$user->password = md5($password);
			$user->employee = (empty($req->employee) || $req->employee == "NULL" )?null:$req->employee;
			$user->user_level = $req->user_level;
			$user->lang = $req->lang;
			$user->default_module = $req->default_module;
			$user->last_login = date("Y-m-d H:i:s");
			$user->last_update = date("Y-m-d H:i:s");
			$user->created = date("Y-m-d H:i:s");

			$ok = $user->Save();
		} else {
			$user = new User();
			$user->Load("id = ?", array($req->id));

			if (empty($user->id)) {
				return new IceResponse(
					IceResponse::ERROR,
					"User does not exists"
				);
			}

			$user->email = $req->email;
			$user->username = $req->username;
			$user->employee = empty($req->employee)?null:$req->employee;
			$user->user_level = $req->user_level;
			$user->lang = $req->lang;
			$user->default_module = $req->default_module;
			$user->last_update = date("Y-m-d H:i:s");

			$ok = $user->Save();
		}

		if (!$ok) {
			LogManager::getInstance()->info($user->ErrorMsg()."|".json_encode($user));
			return new IceResponse(IceResponse::ERROR, "Error occurred while saving the user");
		}


		$employee = null;
		if (!empty($user->employee)) {
			$employee = $this->baseService->getElement('Employee', $user->employee, null, true);
		}

		$user->password = "";
		$user = $this->baseService->cleanUpAdoDB($user);

		$mailResponse = 'none';
		if (empty($req->id)) {
			if (!empty($this->emailSender)) {
				$usersEmailSender = new UsersEmailSender($this->emailSender, $this);
				$mailResponse = $usersEmailSender->sendWelcomeUserEmail($user, $password, $employee);
				$mailResponse = false !== $mailResponse ? 'sent' : 'not_sent';
			}
		}
		return new IceResponse(IceResponse::SUCCESS, [$user, $mailResponse]);
    }

    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
