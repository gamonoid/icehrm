<?php
define('CLIENT_PATH',dirname(__FILE__));
include ("config.base.php");
include ("include.common.php");
include("server.includes.inc.php");

if(\Classes\SettingsManager::getInstance()->getSetting('Api: REST Api Enabled') == '1') {

    if (defined('SYM_CLIENT')) {
        define('REST_API_PATH', '/'.SYM_CLIENT.'/');
    } else if (!defined('REST_API_PATH')){
        define('REST_API_PATH', '/');
    }


    // Redacted and demoted to DEBUG. This ran at INFO — the production default — and
    // dumped the whole request on every REST call: the bearer token (which the JS client
    // also sends as ?token=), and on POST oauth/token the user's password.
    \Utils\LogManager::getInstance()->debug(
        "Request: " . print_r(\Utils\LogManager::redact($_REQUEST), true)
    );
    \Utils\LogManager::getInstance()->debug("REST_API_PATH: " . REST_API_PATH);

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    $echoRoute = \Classes\Macaw::get(REST_API_PATH . 'echo', function () {
        echo "Echo " . rand();
    });

    \Utils\LogManager::getInstance()->info('Api registered URI: '.$echoRoute);

    require __DIR__ . '/appshell-routes.php';

    $moduleManagers = \Classes\BaseService::getInstance()->getModuleManagers();

    foreach ($moduleManagers as $moduleManagerObj) {

        $moduleManagerObj->setupRestEndPoints();
    }
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];
    \Utils\LogManager::getInstance()->debug('Api dispatch URI: '.$uri);
    \Utils\LogManager::getInstance()->debug('Api dispatch method: '.$uri);

    // Global auth gate: every endpoint requires a valid bearer token (or an
    // authenticated session) unless allowlisted in RestApiAuthGate.
    \Classes\RestApiAuthGate::enforce($uri, $method);

    if (!defined('SYM_CLIENT')) {
        //For hosted installations, dispatch will be done in app/index
        \Classes\Macaw::dispatch();
    }


}else{
    echo "REST Api is not enabled. Please set 'Api: REST Api Enabled' setting to true";
}
