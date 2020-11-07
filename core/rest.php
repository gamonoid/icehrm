<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
header('Content-Type: application/json');
if (isset($_REQUEST['method']) && isset($_REQUEST['url'])) {
    include(APP_BASE_PATH . 'api-url-based.php');
} else {
    include(APP_BASE_PATH . 'api-rest.php');
}
