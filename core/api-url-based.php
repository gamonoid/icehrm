<?php
define('CLIENT_PATH',dirname(__FILE__));
include ("config.base.php");
include ("include.common.php");
include("server.includes.inc.php");

if(\Classes\SettingsManager::getInstance()->getSetting('Api: REST Api Enabled') == '1') {

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    define('REST_API_PATH', '/');

    $echoRoute = \Classes\Macaw::get(REST_API_PATH . 'echo', function () {
        echo "Echo " . rand();
    });

    \Utils\LogManager::getInstance()->debug('Api registered URI: '.$echoRoute);

    $moduleManagers = \Classes\BaseService::getInstance()->getModuleManagers();

    foreach ($moduleManagers as $moduleManagerObj) {

        $moduleManagerObj->setupRestEndPoints();
    }

    $method = strtoupper($_REQUEST['method']);
    \Classes\IceRoute::dispatch($_GET['url'], $method);


}else{
    echo "REST Api is not enabled. Please set 'Api: REST Api Enabled' setting to true";
}