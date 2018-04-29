<?php
if(php_sapi_name() != 'cli'){
	exit();
}

define('CLIENT_PATH',dirname(__FILE__)."/..");

include (APP_BASE_PATH."config.base.php");

include (APP_BASE_PATH."include.common.php");
include(APP_BASE_PATH."server.includes.inc.php");