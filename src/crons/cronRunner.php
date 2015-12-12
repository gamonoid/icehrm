<?php
if(php_sapi_name() != 'cli'){
	exit();
}
include "../config.base.php";
ini_set('error_log',CRON_LOG);
$opts = getopt('f:p:');
$file = $opts['f'];
$basePath = $opts['p'];

include (dirname(__FILE__)."/../classes/CronUtils.php");

$cronUtils = CronUtils::getInstance($basePath, $file);

echo "Cron Runner created \r\n";

$cronUtils->run();

