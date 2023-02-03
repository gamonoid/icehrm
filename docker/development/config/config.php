<?php
$protocol = $_SERVER["REQUEST_SCHEME"] ?? 'http';
define('CLIENT_NAME', 'icehrm');

ini_set('error_log', '/var/www/html/app/data/icehrm.log');
define('APP_BASE_PATH', '/var/www/html/core/');
define('CLIENT_BASE_PATH', '/var/www/html/app/');
define('BASE_URL','http://localhost:9080/web/');
define('CLIENT_BASE_URL','http://localhost:9080/app/');

define('LOG_STDERR', '1');

define('APP_DB', 'icehrm');
define('APP_USERNAME', 'dev');
define('APP_PASSWORD', 'dev');
define('APP_HOST', 'mysql');
define('APP_CON_STR', 'mysqli://'.APP_USERNAME.':'.APP_PASSWORD.'@'.APP_HOST.'/'.APP_DB);

//file upload
define('FILE_TYPES', 'jpg,png,jpeg');
define('MAX_FILE_SIZE_KB', 10 * 1024);
if(!defined('APP_WEB_URL')) {define('APP_WEB_URL', 'https://icehrm.com');}