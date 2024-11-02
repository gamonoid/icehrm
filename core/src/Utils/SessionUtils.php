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
        session_write_close();
    }
}
