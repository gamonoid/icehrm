<?php
ini_set('error_log', '/var/log/nginx/iceframework_test.log');

define('TEST_BASE_PATH', dirname(__FILE__).'/');

define('APP_NAME', 'IceHrm');
define('FB_URL', 'IceHrm');
define('TWITTER_URL', 'IceHrm');

define('SIGN_IN_ELEMENT_MAPPING_FIELD_NAME','employee');

define('CLIENT_NAME', 'app');
define('APP_BASE_PATH', dirname(__FILE__).'/../src/');
define('CLIENT_BASE_PATH', APP_BASE_PATH.'app/');
define('BASE_URL','http://apps.gamonoid.com/icehrmcore/');
define('CLIENT_BASE_URL','http://apps.gamonoid.com/icehrm/');

define('APP_DB', 'icehrm_os_db_test');
define('APP_USERNAME', MYSQL_ROT_USER);
define('APP_PASSWORD', MYSQL_ROT_PASS);
define('APP_HOST', 'localhost');
define('APP_CON_STR', 'mysql://'.APP_USERNAME.':'.APP_PASSWORD.'@'.APP_HOST.'/'.APP_DB);

//file upload
define('FILE_TYPES', 'jpg,png,jpeg');
define('MAX_FILE_SIZE_KB', 10 * 1024);
