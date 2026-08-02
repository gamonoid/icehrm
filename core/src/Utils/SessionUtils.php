<?php
namespace Utils;

class SessionUtils
{
    public static function getSessionObject($name)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION[$name.CLIENT_NAME])) {
            $obj = $_SESSION[$name.CLIENT_NAME];
        }
        if (empty($obj)) {
            return null;
        }
        return json_decode($obj);
    }

    public static function saveSessionObject($name, $obj)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION[$name.CLIENT_NAME] = json_encode($obj);
    }

    public static function getSessionString($name)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION[$name.CLIENT_NAME])) {
            $string = $_SESSION[$name.CLIENT_NAME];
        }
        if (empty($string)) {
            return null;
        }
        return $string;
    }

    public static function saveSessionString($name, $str)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION[$name.CLIENT_NAME] = $str;
    }

    /**
     * Issue a fresh session ID, deleting the old session file. Call on any
     * privilege change (i.e. successful login) to prevent session fixation: a
     * session ID an attacker planted or captured before authentication is discarded.
     */
    public static function regenerateSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
    }

    public static function unsetClientSession()
    {
        $names = [
            "user",
            "modulePath",
            "admin_current_profile",
            "csrf-login"
        ];
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        setcookie('icehrmLF', '');
        foreach ($names as $name) {
            unset($_SESSION[$name.CLIENT_NAME]);
        }
        // Invalidate the current session ID so a captured/fixed ID cannot be reused
        // after logout, and delete the old session file.
        session_regenerate_id(true);
        session_write_close();
    }
}
