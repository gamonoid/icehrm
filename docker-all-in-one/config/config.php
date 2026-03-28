<?php
/**
 * IceHrm All-in-One Configuration
 *
 * This configuration is for the all-in-one Docker container.
 * Database connects to localhost since MySQL runs in the same container.
 */

ini_set('error_log', '/var/www/html/app/data/icehrm.log');

define('CLIENT_NAME', 'icehrm');
define('APP_BASE_PATH', '/var/www/html/core/');
define('CLIENT_BASE_PATH', '/var/www/html/app/');

// Base URL - set via APP_BASE_URL environment variable
// Falls back to RENDER_EXTERNAL_URL (auto-set by Render) or localhost
$baseUrl = getenv('APP_BASE_URL') ?: getenv('RENDER_EXTERNAL_URL') ?: 'http://localhost:5566';
define('BASE_URL', $baseUrl . '/web/');
define('CLIENT_BASE_URL', $baseUrl . '/app/');

// Database configuration - localhost since MySQL is in the same container
define('APP_DB', getenv('MYSQL_DATABASE') ?: 'icehrm');
define('APP_USERNAME', getenv('MYSQL_USER') ?: 'icehrm');
define('APP_PASSWORD', getenv('MYSQL_PASSWORD') ?: 'icehrm');
define('APP_HOST', '127.0.0.1');
define('APP_CON_STR', 'mysqli://' . APP_USERNAME . ':' . APP_PASSWORD . '@' . APP_HOST . '/' . APP_DB);

// File upload settings
define('FILE_TYPES', 'jpg,png,jpeg,pdf,doc,docx,xls,xlsx,txt');
define('MAX_FILE_SIZE_KB', 10 * 1024);

define('LOG_STDERR', '1');

if (!defined('APP_WEB_URL')) {
    define('APP_WEB_URL', 'https://icehrm.com');
}
if (!defined('EXT_SRC_PATH')) {
    define('EXT_SRC_PATH', '/src/');
}
