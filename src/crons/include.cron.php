<?php
if(php_sapi_name() != 'cli'){
	exit();
}
$opts = getopt('c:');
$clientPath = $opts['c'];

if(empty($clientPath)){
	echo "No client path defined\r\n";
	exit();
}

include $clientPath."/config.php";
include (APP_BASE_PATH."include.common.php");
include("server.includes.inc.php");