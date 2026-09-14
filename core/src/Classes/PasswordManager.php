<?php

namespace Classes;

use Classes\Crypt\IceCrypt;
use Users\Common\Model\User;
use Utils\CalendarTools;

class PasswordManager
{
    /**
     * A real cost-13 bcrypt hash used only to equalize timing when no stored hash is
     * available (unknown username/email). Verifying against it does the same bcrypt
     * work a real account's wrong-password attempt does, so response latency no longer
     * reveals whether an account exists. It matches no real password.
     */
    const TIMING_EQUALIZER_HASH = '$2y$13$5lDBSlVDTX/Bdy9kz1zZa.mv1dJOcW0S18oDRmbe/wHR.RNRyLjRG';

    /** Session key marking "this session authenticated against a legacy MD5 hash". */
    const SESSION_RESET_REQUIRED = 'password_reset_required';

    /**
     * True for a legacy unsalted-MD5 stored hash (32 hex chars). bcrypt hashes are 60
     * chars and start with $2y$, so the length test is unambiguous.
     */
    public static function isLegacyHash($hash)
    {
        return is_string($hash) && strlen($hash) === 32;
    }

    public static function verifyPassword($password, $hash)
    {
        // Unknown account (empty stored hash): still run one bcrypt verify so the
        // request takes the same time as a real wrong-password attempt, then fail.
        // Without this, an empty hash returns instantly and the timing difference
        // enumerates valid usernames/emails.
        if (empty($hash)) {
            password_verify((string) $password, self::TIMING_EQUALIZER_HASH);
            return false;
        }

        $result = password_verify($password, $hash);
        if ($result) {
            return true;
        }

        if (self::isLegacyHash($hash)) {
            $matched = md5($password) === $hash;
            if ($matched) {
                // The credential is correct but the stored hash is unsalted MD5. Mark the
                // session so the app shell forces a reset before anything else loads.
                // Only when a session is already running: the interactive login path has
                // one, while the REST/CLI paths must not have one started for them.
                if (session_status() === PHP_SESSION_ACTIVE) {
                    \Utils\SessionUtils::saveSessionString(self::SESSION_RESET_REQUIRED, '1');
                }
            }

            return $matched;
        }

        return false;
    }

    /**
     * Must this user be sent through a forced password reset before using the app?
     *
     * Two independent signals, either of which is enough:
     *  - the session marker set by verifyPassword() when a legacy hash was accepted, and
     *  - the stored hash on the session's own user object still being MD5.
     *
     * The second is the authoritative one and needs no query — the User object kept in
     * the session carries its password hash — so a session that predates this feature,
     * or one whose marker was never written, is still caught.
     */
    public static function userNeedsPasswordReset($user)
    {
        if (!empty($user) && !empty($user->password) && self::isLegacyHash($user->password)) {
            return true;
        }

        return \Utils\SessionUtils::getSessionString(self::SESSION_RESET_REQUIRED) === '1';
    }

    /** Drop the marker once the password has actually been upgraded. */
    public static function clearPasswordResetRequired()
    {
        \Utils\SessionUtils::saveSessionString(self::SESSION_RESET_REQUIRED, '');
    }

    public static function createPasswordHash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 13]);
    }

    // --- Account lockout after repeated failed logins (brute-force protection) ---

    const MAX_FAILED_LOGIN_ATTEMPTS = 5;

    /**
     * How long a lock lasts, from the last failed attempt. The lock decays after
     * this window so that repeated wrong passwords cannot PERMANENTLY lock an
     * account: previously the counter had no time component (last_wrong_attempt_at
     * was written but never read), so anyone could lock any known username forever
     * with five guesses — an anonymous, org-wide denial of service. A time-boxed
     * lock still blocks online brute force (five tries then a cooldown) without that.
     */
    const LOCKOUT_WINDOW_MINUTES = 15;

    /**
     * After this many failed attempts an ACTIVE email login code is invalidated, so
     * the 6-digit code cannot be brute-forced. It is deliberately above the password
     * lock threshold: a user recovering by code gets a few fumbles, but a machine
     * gunning the 10^6 space burns the code (and must request another, which is
     * rate-limited) long before it lands.
     */
    const LOGIN_CODE_KILL_THRESHOLD = 10;

    /**
     * True while the account is locked: at/over the failed-attempt threshold AND the
     * last failure is within the lockout window. Outside the window the lock has
     * decayed — see LOCKOUT_WINDOW_MINUTES.
     *
     * A locked account cannot log in with its password; it recovers via an email
     * login code (the 'rlc' flow) or an admin password change, either of which
     * resets the counter.
     */
    public static function isAccountLocked($user)
    {
        if (empty($user) || empty($user->id)
            || intval($user->wrong_password_count) < self::MAX_FAILED_LOGIN_ATTEMPTS
        ) {
            return false;
        }

        // No recorded time (legacy rows) is treated as "still locked" — fail safe.
        if (empty($user->last_wrong_attempt_at)) {
            return true;
        }

        $windowEnd = strtotime($user->last_wrong_attempt_at) + (self::LOCKOUT_WINDOW_MINUTES * 60);
        return time() < $windowEnd;
    }

    /**
     * Should an active email login code be killed now? True once failures pass the
     * kill threshold, so brute forcing the 6-digit code cannot continue indefinitely.
     */
    public static function shouldInvalidateLoginCode($user)
    {
        return !empty($user) && !empty($user->id)
            && intval($user->wrong_password_count) >= self::LOGIN_CODE_KILL_THRESHOLD;
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
