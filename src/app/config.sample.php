<?php 
ini_set('error_log', '_LOG_');

define('APP_NAME', 'Ice Framework');
define('FB_URL', 'Ice Framework');
define('TWITTER_URL', 'Ice Framework');

define('CLIENT_NAME', '_CLIENT_');
define('APP_BASE_PATH', '_APP_BASE_PATH_');
define('CLIENT_BASE_PATH', '_CLIENT_BASE_PATH_');
define('BASE_URL','_BASE_URL_');
define('CLIENT_BASE_URL','_CLIENTBASE_URL_');

define('APP_DB', '_APP_DB_');
define('APP_USERNAME', '_APP_USERNAME_');
define('APP_PASSWORD', '_APP_PASSWORD_');
define('APP_HOST', '_APP_HOST_');
define('APP_CON_STR', 'mysql://'.APP_USERNAME.':'.APP_PASSWORD.'@'.APP_HOST.'/'.APP_DB);

//file upload
define('FILE_TYPES', 'jpg,png,jpeg');
define('MAX_FILE_SIZE_KB', 10 * 1024);

//Home Links
define('HOME_LINK_ADMIN', CLIENT_BASE_URL."?g=admin&n=dashboard&m=admin_Admin");
define('HOME_LINK_OTHERS', CLIENT_BASE_URL."?g=modules&n=dashboard&m=module_My_Account");