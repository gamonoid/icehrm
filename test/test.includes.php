<?php
//Mock Session class
if (!class_exists('SessionUtils')) {
    class SessionUtils
    {

        public static $data;

        public static function getSessionObject($name)
        {
            if (empty(self::$data)) {
                self::$data = array();
            }
            if (isset(self::$data[$name.CLIENT_NAME])) {
                $obj = self::$data[$name.CLIENT_NAME];
            }
            if (empty($obj)) {
                return null;
            }
            return json_decode($obj);
        }

        public static function saveSessionObject($name, $obj)
        {
            if (empty(self::$data)) {
                self::$data = array();
            }
            self::$data[$name.CLIENT_NAME] = json_encode($obj);
        }
    }


}
if (!class_exists('TestTemplate')) {
    include(dirname(__FILE__).'/TestTemplate.php');
    include(dirname(__FILE__).'/helper/EmployeeTestDataHelper.php');
    include(APP_BASE_PATH."/includes.inc.php");
}
