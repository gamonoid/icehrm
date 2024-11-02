<?php
namespace Classes;

use Users\Common\Model\User;
use Utils\SessionUtils;

class GoogleAuthManager
{
    public function checkGoogleAuthUser($guser)
    {
        $user = new User();
        $user->Load('email = ?', array($guser->email));

        if (!empty($user->email) && $user->email === $guser->email) {
            // $user->googleUserData = json_encode($guser);
            return $user;
        }

        return null;
    }
}
