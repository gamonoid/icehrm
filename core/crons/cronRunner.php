<?php
if(php_sapi_name() != 'cli'){
	exit();
}
include dirname(__FILE__)."/../config.base.php";
ini_set('error_log',CRON_LOG);
$opts = getopt('f:p:');
$file = $opts['f'];
$basePath = $opts['p'];

include(dirname(__FILE__) . "/../Classes/CronUtils.php");

$cronUtils = \Classes\Cron\CronUtils::getInstance($basePath, $file);

echo "Cron Runner created \r\n";

$cronUtils->run();

