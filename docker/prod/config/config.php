<?php
/**
 * IceHrm Production Configuration
 *
 * This configuration reads from environment variables for flexibility.
 * Set these variables in your .env file or docker-compose environment section.
 */

ini_set('error_log', '/var/www/html/app/data/icehrm.log');

define('CLIENT_NAME', 'icehrm');
define('APP_BASE_PATH', '/var/www/html/core/');
define('CLIENT_BASE_PATH', '/var/www/html/app/');

// Base URL - set via APP_BASE_URL environment variable
$baseUrl = getenv('APP_BASE_URL') ?: 'http://localhost:5555';
define('BASE_URL', $baseUrl . '/web/');
define('CLIENT_BASE_URL', $baseUrl . '/app/');

// Database configuration from environment variables
define('APP_DB', getenv('DB_NAME') ?: 'icehrm');
define('APP_USERNAME', getenv('DB_USER') ?: 'icehrm');
define('APP_PASSWORD', getenv('DB_PASSWORD') ?: 'icehrm');
define('APP_HOST', getenv('DB_HOST') ?: 'mysql');
define('APP_CON_STR', 'mysqli://' . APP_USERNAME . ':' . APP_PASSWORD . '@' . APP_HOST . '/' . APP_DB);

// File upload settings
define('FILE_TYPES', 'jpg,png,jpeg,pdf,doc,docx,xls,xlsx,txt');
define('MAX_FILE_SIZE_KB', 10 * 1024);

define('LOG_STDERR', '1');

// Hide the core Settings "Leave" tab. LEAVE_ENABLED is read only by the settings
// page (core/admin/settings/index.php) and the SPA registry
// (NativeModuleRegistry::settingsTabs); config.base.php defaults it to true when
// unset, so defining it false here (loaded before config.base.php) suppresses that
// tab without affecting the leave extension's own module/menu/area.
define('LEAVE_ENABLED', false);

if (!defined('APP_WEB_URL')) {
    define('APP_WEB_URL', 'https://icehrm.com');
}
if (!defined('EXT_SRC_PATH')) {
    define('EXT_SRC_PATH', '/src/');
}
