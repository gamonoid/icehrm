<?php

namespace Classes;

use Classes\Crypt\IceCrypt;
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

    // --- Account lockout after repeated failed logins (brute-force protection) ---

    const MAX_FAILED_LOGIN_ATTEMPTS = 5;

    /**
     * True once the account has reached the failed-attempt threshold. A locked
     * account cannot log in with its password; it must be unlocked via an email
     * login code (see the 'rlc' flow) or an admin password change.
     */
    public static function isAccountLocked($user)
    {
        return !empty($user) && !empty($user->id)
            && intval($user->wrong_password_count) >= self::MAX_FAILED_LOGIN_ATTEMPTS;
    }

    /**
     * Count one failed password attempt. At MAX_FAILED_LOGIN_ATTEMPTS the account
     * becomes locked (see isAccountLocked). No-op for an unknown user.
     */
    public static function recordFailedLogin($user)
    {
        if (empty($user) || empty($user->id)) {
            return;
        }
        $user->wrong_password_count = intval($user->wrong_password_count) + 1;
        $user->last_wrong_attempt_at = date('Y-m-d H:i:s');
        $user->Save();
    }

    /**
     * Clear the failed-attempt counter (unlock). Called on any successful login
     * (password or email code) and whenever a password is changed, including by an
     * admin. No-op (no write) when the counter is already clear.
     */
    public static function resetFailedLogins($user)
    {
        if (empty($user) || empty($user->id)) {
            return;
        }
        if (intval($user->wrong_password_count) === 0 && empty($user->last_wrong_attempt_at)) {
            return;
        }
        $user->wrong_password_count = 0;
        $user->last_wrong_attempt_at = null;
        $user->Save();
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

        // AES-256-GCM via IceCrypt. The old AesCtr had no MAC, so the user-id segment
        // below was malleable: recovering keystream for a nonce let the segment be
        // rewritten to point at another user.
        $encJson = IceCrypt::encrypt($json, $user->password);

        return urlencode(IceCrypt::encrypt($user->id, APP_PASSWORD).'-'.$encJson);
    }

    public static function verifyPasswordRestKey($key)
    {
        $arr = explode("-", $key);
        // Accepts both the v2 format and legacy AesCtr, so reset links already in
        // people's inboxes keep working.
        $userId = IceCrypt::decrypt($arr[0], APP_PASSWORD);
        $user = new User();
        $user->Load("id = ?", array($userId));

        if (empty($user->id)) {
            return false;
        }

        array_shift($arr);
        $data = IceCrypt::decrypt(implode('', $arr), $user->password);

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

        // bcrypt hashes the first 72 bytes, so 72 is the real ceiling; the old cap of 30
        // needlessly blocked passphrases.
        if (strlen($password) > 72) {
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
