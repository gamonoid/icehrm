<?php
namespace Utils;

class SessionUtils
{
    public static function getSessionObject($name)
    {
        session_start();
        if (isset($_SESSION[$name.CLIENT_NAME])) {
            $obj = $_SESSION[$name.CLIENT_NAME];
        }
        session_write_close();
        if (empty($obj)) {
            return null;
        }
        return json_decode($obj);
    }

    public static function saveSessionObject($name, $obj)
    {
        session_start();
        $_SESSION[$name.CLIENT_NAME] = json_encode($obj);
        session_write_close();
    }

    public function unsetClientSession()
    {
        $names = [
            "user",
            "modulePath",
            "loginRedirect",
            "admin_current_profile"
        ];
        session_start();
        foreach ($names as $name) {
            unset($_SESSION[$name.CLIENT_NAME]);
        }
        session_write_close();
    }
}
