<?php
error_reporting(E_ERROR);
define('CURRENT_PATH', dirname(__FILE__));
define('CLIENT_APP_PATH', realpath(dirname(__FILE__) . "/..") . "/");
define('APP_PATH', realpath(dirname(__FILE__) . "/../..") . "/core/");
define('APP_NAME', "IceHrm");
define('APP_ID', "icehrm");
// Absolute log path so it doesn't depend on the process working directory.
ini_set("error_log", CLIENT_APP_PATH . "data/icehrm_install.log");
