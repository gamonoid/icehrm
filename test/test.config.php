<?php
ini_set('error_log', '/tmp/icehrm.test.log');

define('TEST_BASE_PATH', dirname(__FILE__).'/');

define('APP_NAME', 'IceHrm');
define('FB_URL', 'IceHrm');
define('TWITTER_URL', 'IceHrm');

define('SIGN_IN_ELEMENT_MAPPING_FIELD_NAME', 'employee');

define('CLIENT_NAME', 'app');

if (!defined('MYSQL_ROOT_USER') || !defined('MYSQL_ROOT_PASS')) {
    //Tests running on vagrant
    define('APP_BASE_PATH', TEST_BASE_PATH.'../');
    define('CLIENT_BASE_PATH', TEST_BASE_PATH.'../../deployment/clients/test/');
    define('BASE_URL', 'http://app.app.dev/');
    define('CLIENT_BASE_URL', 'http://clients.app.dev/dev/');
} else {
    //Tests running on deploy
    define('APP_BASE_PATH', realpath(dirname(__FILE__).'/../app')."/");
    define('CLIENT_BASE_PATH', APP_BASE_PATH.'');
    define('BASE_URL', 'http://apps.gamonoid.com/icehrmcore/');
    define('CLIENT_BASE_URL', 'http://apps.gamonoid.com/icehrm/');
}

if (!defined('MYSQL_ROOT_USER') || !defined('MYSQL_ROOT_PASS')) {
    define('APP_DB', 'testing');
    define('APP_USERNAME', 'root');
    define('APP_PASSWORD', 'dev');
} else {
    define('APP_DB', 'icehrmht');
    define('APP_USERNAME', MYSQL_ROOT_USER);
    define('APP_PASSWORD', MYSQL_ROOT_PASS);
}

if (!defined('MYSQL_ROOT_USER') || !defined('MYSQL_ROOT_PASS')) {
    define('MYSQL_ROOT_USER', 'root');
    define('MYSQL_ROOT_PASS', 'dev');
}

define('APP_HOST', 'localhost');
define('APP_CON_STR', 'mysqli://'.APP_USERNAME.':'.APP_PASSWORD.'@'.APP_HOST.'/'.APP_DB);

//file upload
define('FILE_TYPES', 'jpg,png,jpeg');
define('MAX_FILE_SIZE_KB', 10 * 1024);
define('CLIENT_PATH', APP_BASE_PATH);
