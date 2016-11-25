<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
define('CLIENT_PATH',dirname(__FILE__));
include ("config.base.php");
include ("include.common.php");
include("server.includes.inc.php");

if(SettingsManager::getInstance()->getSetting('Api: REST Api Enabled') == '1') {


	define('REST_API_PATH', '/api/');

	LogManager::getInstance()->info("Request: " . $_REQUEST);

	\NoahBuscher\Macaw\Macaw::get(REST_API_PATH . 'echo', function () {
		echo "Echo " . rand();
	});

	$moduleManagers = BaseService::getInstance()->getModuleManagers();

	foreach ($moduleManagers as $moduleManagerObj) {

		$moduleManagerObj->setupRestEndPoints();
	}

	\NoahBuscher\Macaw\Macaw::dispatch();

}else{
	echo "REST Api is not enabled. Please set 'Api: REST Api Enabled' setting to true";
}