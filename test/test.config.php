<?php
ini_set('error_log', '/tmp/icehrm.test.log');

define('TEST_BASE_PATH', dirname(__FILE__).'/');

define('APP_NAME', 'IceHrm');
define('FB_URL', 'IceHrm');
define('TWITTER_URL', 'IceHrm');

define('SIGN_IN_ELEMENT_MAPPING_FIELD_NAME','employee');

define('CLIENT_NAME', 'app');

//Tests running on vagrant
define('APP_BASE_PATH', '/vagrant/build/app/');
define('CLIENT_BASE_PATH', APP_BASE_PATH.'test/');
define('BASE_URL','http://app.app.dev/');
define('CLIENT_BASE_URL','http://clients.app.dev/dev/');

define('APP_DB', 'testing');
define('APP_USERNAME', 'testing');
define('APP_PASSWORD', 'testing');

define('MYSQL_ROOT_USER', 'root');
define('MYSQL_ROOT_PASS', 'dev');

define('APP_HOST', 'localhost');
define('APP_CON_STR', 'mysqli://'.APP_USERNAME.':'.APP_PASSWORD.'@'.APP_HOST.'/'.APP_DB);

//file upload
define('FILE_TYPES', 'jpg,png,jpeg');
define('MAX_FILE_SIZE_KB', 10 * 1024);
define('CLIENT_PATH',APP_BASE_PATH);
