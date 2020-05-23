<?php
ini_set('error_log', 'data/icehrm.log');

define('CLIENT_NAME', 'icehrm');
define('APP_BASE_PATH', '/var/www/html/core/');
define('CLIENT_BASE_PATH', '/var/www/html/app/');
define('BASE_URL','http://icehrm-testing:8090/web/');
define('CLIENT_BASE_URL','http://icehrm-testing:8090/app/');

define('APP_DB', 'icehrm');
define('APP_USERNAME', 'testing');
define('APP_PASSWORD', 'testing');
define('APP_HOST', 'mysql-testing');
define('APP_CON_STR', 'mysqli://'.APP_USERNAME.':'.APP_PASSWORD.'@'.APP_HOST.'/'.APP_DB);

//file upload
define('FILE_TYPES', 'jpg,png,jpeg');
define('MAX_FILE_SIZE_KB', 10 * 1024);
