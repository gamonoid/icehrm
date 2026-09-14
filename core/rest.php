<?php
// CORS. This file runs before the settings/DB layer bootstraps (api-rest.php loads that),
// so the allowlist is a config constant rather than a DB setting: deployments that need
// cross-origin API access define ICEHRM_CORS_ALLOWED_ORIGINS in their app/config.php as a
// comma-separated list of exact scheme://host[:port] values.
//
// Default is same-origin only. `Access-Control-Allow-Origin: *` is never emitted, and
// Access-Control-Allow-Credentials is never sent — with credentials a reflected origin
// would let any site read authenticated API responses.
$icehrmCorsAllowlist = defined('ICEHRM_CORS_ALLOWED_ORIGINS') ? ICEHRM_CORS_ALLOWED_ORIGINS : '';
$icehrmRequestOrigin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

if ($icehrmRequestOrigin !== '' && $icehrmCorsAllowlist !== '') {
    $icehrmAllowedOrigins = array_filter(array_map('trim', explode(',', $icehrmCorsAllowlist)));
    if (in_array($icehrmRequestOrigin, $icehrmAllowedOrigins, true)) {
        header('Access-Control-Allow-Origin: '.$icehrmRequestOrigin);
    }
}
// Always vary on Origin: the response differs by origin, so a shared cache must not
// replay one origin's response to another.
header('Vary: Origin');
header('Access-Control-Allow-Methods: DELETE, POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');
if (isset($_REQUEST['method']) && isset($_REQUEST['url'])) {
    include(APP_BASE_PATH . 'api-url-based.php');
} else {
    include(APP_BASE_PATH . 'api-rest.php');
}
