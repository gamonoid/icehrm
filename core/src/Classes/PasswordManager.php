<?php

namespace Classes;

use Classes\Crypt\AesCtr;
use Users\Common\Model\User;
use Utils\CalendarTools;

class PasswordManager
{
    public static function verifyPassword($password, $hash)
    {
        $result = password_verify($password, $hash);
        if ($result) {
            return true;
        }

        if (strlen($hash) === 32) {
            return md5($password) === $hash;
        }

        return false;
    }

    public static function createPasswordHash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 13]);
    }

    public static function passwordChangeWaitingTimeMinutes($user)
    {
        if (empty($user->last_password_requested_at)) {
            $user->last_password_requested_at = date('Y-m-d H:i:s');
            $user->Save();

            return 0;
        }

        $diff = CalendarTools::getTimeDiffInMinutes($user->last_password_requested_at, date('Y-m-d H:i:s'));
        if ($diff < 1) {
            return ceil($diff);
        }

        $user->last_password_requested_at = date('Y-m-d H:i:s');
        $user->Save();

        return 0;
    }

    public static function createPasswordRestKey($user)
    {
        $newPassHash = array();
        $newPassHash["client"] = CLIENT_NAME;
        $newPassHash["email"] = $user->email;
        $newPassHash["time"] = date('Y-m-d H:i:s');
        $json = json_encode($newPassHash);

        $encJson = AesCtr::encrypt($json, $user->password, 256);

        return urlencode(AesCtr::encrypt($user->id, APP_PASSWORD, 256).'-'.$encJson);
    }

    public static function verifyPasswordRestKey($key)
    {
        $arr = explode("-", $key);
        $userId = AesCtr::decrypt($arr[0], APP_PASSWORD, 256);
        $user = new User();
        $user->Load("id = ?", array($userId));

        if (empty($user->id)) {
            return false;
        }

        array_shift($arr);
        $data = AesCtr::decrypt(implode('', $arr), $user->password, 256);

        if (empty($data)) {
            return false;
        }

        $data = json_decode($data, true);

        if (empty($data)) {
            return false;
        }

        if ($data['client'] != CLIENT_NAME || $data['email'] != $user->email) {
            return false;
        }

        if (CalendarTools::getTimeDiffInMinutes($data['time'], date('Y-m-d H:i:s')) < 30) {
            return $user;
        }

        return false;
    }

    public static function isQualifiedPassword($password)
    {
        if (strlen($password) < 8) {
            $error = "Password too short";

            return new IceResponse(IceResponse::ERROR, $error);
        }

        if (strlen($password) > 30) {
            $error = "Password too long";

            return new IceResponse(IceResponse::ERROR, $error);
        }

        if (!preg_match("#[0-9]+#", $password)) {
            $error = "Password must include at least one number";

            return new IceResponse(IceResponse::ERROR, $error);
        }

        if (!preg_match("#[a-z]+#", $password)) {
            $error = "Password must include at least one lowercase letter";

            return new IceResponse(IceResponse::ERROR, $error);
        }

        if (!preg_match("#[A-Z]+#", $password)) {
            $error = "Password must include at least one uppercase letter";

            return new IceResponse(IceResponse::ERROR, $error);
        }

        if (!preg_match("#\W+#", $password)) {
            $error = "Password must include at least one symbol";

            return new IceResponse(IceResponse::ERROR, $error);
        }

        return new IceResponse(IceResponse::SUCCESS);
    }
}
