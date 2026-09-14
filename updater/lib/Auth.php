<?php
/**
 * Administrator login for the updater, and CSRF for its forms.
 *
 * This endpoint can overwrite every file in the installation, so it is the most
 * sensitive thing shipped. Rules:
 *
 *   - Admins only. Users.user_level must be exactly 'Admin'; a Manager with access to
 *     most of the application still cannot run an update.
 *   - Always log in. The updater cannot validate an IceHRM session (it has to keep
 *     working while core/ is being replaced), so it never tries — a fresh login every
 *     visit, in the updater's own session.
 *   - Password checking mirrors Classes\PasswordManager exactly: password_verify()
 *     first, then the unsalted-MD5 fallback that real installs still carry for
 *     accounts created years ago. Getting this wrong would either lock legitimate
 *     admins out of their own updater or accept credentials the application rejects.
 *   - Failed attempts are counted ON DISK per client address, with an escalating delay
 *     and then a lockout. A session-scoped counter would be decoration: an attacker
 *     resets it by discarding the cookie.
 */

class UpdaterAuth
{
    const SESSION_USER = 'updater_user';
    const SESSION_CSRF = 'updater_csrf';

    /** Cost of a wrong password, in seconds, per failure after the first two. */
    const PENALTY_SECONDS = 2;
    const MAX_PENALTY_SECONDS = 10;

    /** Attempts from one address before it is refused outright, and for how long. */
    const MAX_ATTEMPTS = 10;
    const LOCKOUT_SECONDS = 900;
    const ATTEMPT_WINDOW = 900;

    public static function isLoggedIn()
    {
        return !empty($_SESSION[self::SESSION_USER]);
    }

    public static function currentUser()
    {
        return self::isLoggedIn() ? $_SESSION[self::SESSION_USER] : null;
    }

    /**
     * @return string|null null on success, otherwise a message to show the user
     */
    public static function login($username, $password)
    {
        // Throttling is recorded on DISK, keyed by client address, not in the session.
        // A session counter is reset by discarding the cookie, which made it decoration:
        // an attacker could guess passwords indefinitely, one fresh session each time.
        $failures = self::recentFailures();
        if ($failures >= self::MAX_ATTEMPTS) {
            UpdaterLog::error('Updater sign-in refused: too many failed attempts from '
                . self::clientKey());
            return 'Too many failed sign-in attempts. Wait '
                . ceil(self::LOCKOUT_SECONDS / 60) . ' minutes and try again.';
        }
        if ($failures > 2) {
            sleep(min(self::MAX_PENALTY_SECONDS, ($failures - 2) * self::PENALTY_SECONDS));
        }

        if ($username === '' || $password === '') {
            return 'Enter your IceHRM administrator username and password.';
        }

        if (UpdaterDb::connect() === null) {
            return 'Could not connect to the IceHRM database. Check that the application '
                . 'is configured correctly, then reload this page.';
        }

        $rows = UpdaterDb::select(
            'SELECT id, username, email, password, user_level FROM Users '
            . 'WHERE username = ? OR email = ? LIMIT 1',
            array($username, $username)
        );

        $row = !empty($rows) ? $rows[0] : null;
        $storedHash = $row === null ? '' : (string) $row['password'];

        if (!self::verifyPassword($password, $storedHash)) {
            self::recordFailure($username, 'bad credentials');
            return 'Incorrect username or password.';
        }

        // Authenticated — but only administrators may update.
        if ($row['user_level'] !== 'Admin') {
            self::recordFailure($username, 'not an administrator (' . $row['user_level'] . ')');
            // Deliberately the same message: whether an account exists and what level it
            // holds is not something this form should confirm.
            return 'Incorrect username or password.';
        }

        self::clearFailures();
        // Fresh session id now that the visitor is authenticated.
        session_regenerate_id(true);
        $_SESSION[self::SESSION_USER] = array(
            'id' => $row['id'],
            'username' => $row['username'],
            'email' => $row['email'],
        );
        UpdaterLog::info('Administrator ' . $row['username'] . ' signed in to the updater');

        return null;
    }

    public static function logout()
    {
        $_SESSION = array();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * Same order and semantics as Classes\PasswordManager::verifyPassword().
     */
    private static function verifyPassword($password, $hash)
    {
        if ($hash === '') {
            // Spend the same time as a real check so a missing account is not
            // distinguishable by how quickly this returns. A COMPLETE bcrypt hash, not a
            // bare salt: a malformed one can be rejected without doing the work, which
            // would reintroduce exactly the timing difference this exists to remove.
            password_verify($password, '$2y$10$C6UzMDM.H6dfI/f/IKcEe.4nO0oFbYbHXqLVWjZ8kGYqPqZ4nHXFq');
            return false;
        }

        if (password_verify($password, $hash)) {
            return true;
        }

        // Legacy unsalted MD5, still present on long-lived installs.
        if (strlen($hash) === 32 && ctype_xdigit($hash)) {
            return hash_equals($hash, md5($password));
        }

        return false;
    }

    private static function recordFailure($username, $reason)
    {
        $attempts = self::readAttempts();
        $key = self::clientKey();
        $attempts[$key][] = time();
        self::writeAttempts($attempts);
        UpdaterLog::error('Updater sign-in refused for "' . $username . '": ' . $reason);
    }

    /** Failures from this address inside the window. */
    private static function recentFailures()
    {
        $attempts = self::readAttempts();
        $key = self::clientKey();
        if (empty($attempts[$key])) {
            return 0;
        }
        $cutoff = time() - self::ATTEMPT_WINDOW;
        $recent = array_filter($attempts[$key], function ($t) use ($cutoff) {
            return $t > $cutoff;
        });
        return count($recent);
    }

    private static function clearFailures()
    {
        $attempts = self::readAttempts();
        unset($attempts[self::clientKey()]);
        self::writeAttempts($attempts);
    }

    /**
     * The client address.
     *
     * REMOTE_ADDR only — deliberately NOT X-Forwarded-For, which the client controls
     * and could vary per request to sidestep the limit entirely. Behind a proxy this
     * throttles the proxy as a whole, which is the safe direction to be wrong in for an
     * endpoint that replaces every file on the server.
     */
    private static function clientKey()
    {
        $addr = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : 'unknown';
        return substr(preg_replace('/[^0-9a-fA-F:.]/', '', $addr), 0, 45);
    }

    private static function attemptsFile()
    {
        return UpdaterBootstrap::$dataDir . '/signin-attempts.json';
    }

    private static function readAttempts()
    {
        $path = self::attemptsFile();
        if (!is_readable($path)) {
            return array();
        }
        $data = json_decode((string) @file_get_contents($path), true);
        if (!is_array($data)) {
            return array();
        }
        // Drop anything outside the window on every read, so the file cannot grow
        // without bound under a sustained attack.
        $cutoff = time() - self::ATTEMPT_WINDOW;
        $clean = array();
        foreach ($data as $key => $times) {
            if (!is_array($times)) {
                continue;
            }
            $kept = array();
            foreach ($times as $t) {
                if (is_int($t) && $t > $cutoff) {
                    $kept[] = $t;
                }
            }
            if (!empty($kept)) {
                $clean[$key] = array_slice($kept, -self::MAX_ATTEMPTS);
            }
        }
        return $clean;
    }

    private static function writeAttempts($attempts)
    {
        @file_put_contents(self::attemptsFile(), json_encode($attempts), LOCK_EX);
    }

    // ------------------------------------------------------------------------- CSRF

    public static function csrfToken()
    {
        if (empty($_SESSION[self::SESSION_CSRF])) {
            $_SESSION[self::SESSION_CSRF] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_CSRF];
    }

    /** Every POST in the updater is checked, including the final confirmation. */
    public static function checkCsrf()
    {
        $sent = isset($_POST['csrf']) ? (string) $_POST['csrf'] : '';
        $expected = isset($_SESSION[self::SESSION_CSRF]) ? (string) $_SESSION[self::SESSION_CSRF] : '';
        return $expected !== '' && hash_equals($expected, $sent);
    }
}
