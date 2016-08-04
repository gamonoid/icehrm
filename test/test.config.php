<?php
ini_set('error_log', '/tmp/icehrm.test.log');

define('TEST_BASE_PATH', dirname(__FILE__).'/');

define('APP_NAME', 'IceHrm');
define('FB_URL', 'IceHrm');
define('TWITTER_URL', 'IceHrm');

define('SIGN_IN_ELEMENT_MAPPING_FIELD_NAME','employee');

define('CLIENT_NAME', 'app');
define('APP_BASE_PATH', realpath(dirname(__FILE__).'/../app')."/");
echo 'APP_BASE_PATH :'.APP_BASE_PATH."\r\n";
define('CLIENT_BASE_PATH', APP_BASE_PATH.'app/');
define('BASE_URL','http://apps.gamonoid.com/icehrmcore/');
define('CLIENT_BASE_URL','http://apps.gamonoid.com/icehrm/');



define('APP_DB', 'icehrmht');
if(!defined('MYSQL_ROOT_USER')){
    define('APP_USERNAME', 'root');
    define('APP_PASSWORD', '');
}else{
    define('APP_USERNAME', MYSQL_ROOT_USER);
    define('APP_PASSWORD', MYSQL_ROOT_PASS);
}

define('APP_HOST', 'localhost');
define('APP_CON_STR', 'mysqli://'.APP_USERNAME.':'.APP_PASSWORD.'@'.APP_HOST.'/'.APP_DB);

//file upload
define('FILE_TYPES', 'jpg,png,jpeg');
define('MAX_FILE_SIZE_KB', 10 * 1024);
define('CLIENT_PATH',APP_BASE_PATH);
