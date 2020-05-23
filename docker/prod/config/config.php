<?php
ini_set('error_log', 'data/icehrm.log');

define('CLIENT_NAME', 'icehrm');
define('APP_BASE_PATH', '/var/www/html/core/');
define('CLIENT_BASE_PATH', '/var/www/html/app/');
define('BASE_URL','http://localhost/web/');
define('CLIENT_BASE_URL','http://localhost/app/');

define('APP_DB', 'icehrm');
define('APP_USERNAME', 'prod');
define('APP_PASSWORD', 'prod');
define('APP_HOST', 'mysql-prod');
define('APP_CON_STR', 'mysqli://'.APP_USERNAME.':'.APP_PASSWORD.'@'.APP_HOST.'/'.APP_DB);

//file upload
define('FILE_TYPES', 'jpg,png,jpeg');
define('MAX_FILE_SIZE_KB', 10 * 1024);
