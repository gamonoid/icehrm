<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
define('CLIENT_PATH',dirname(__FILE__));
include ("config.base.php");
include ("include.common.php");
include("server.includes.inc.php");

if(\Classes\SettingsManager::getInstance()->getSetting('Api: REST Api Enabled') == '1') {

	if (defined('SYM_CLIENT')) {
		define('REST_API_PATH', '/'.SYM_CLIENT.'/');
	} else {
		define('REST_API_PATH', '/Api/');
	}


	\Utils\LogManager::getInstance()->info("Request: " . $_REQUEST);

	\Classes\Macaw::get(REST_API_PATH . 'echo', function () {
		echo "Echo " . rand();
	});

	$moduleManagers = \Classes\BaseService::getInstance()->getModuleManagers();

	foreach ($moduleManagers as $moduleManagerObj) {

		$moduleManagerObj->setupRestEndPoints();
	}
	if (!defined('SYM_CLIENT')) {
		//For hosted installations, dispatch will be done in app/index
		\Classes\Macaw::dispatch();
	}


}else{
	echo "REST Api is not enabled. Please set 'Api: REST Api Enabled' setting to true";
}
