<?php

use Classes\Common\IceContainer;
use Classes\LanguageManager;
use Utils\InputCleaner;

ini_set('display_errors', false);
error_reporting(E_ERROR);
require dirname(__FILE__).'/lib/composer/vendor/autoload.php';

// Load the leave package loader if it is installed (it registers the package's
// modules, autoloader, model classes and migrations). See core/leave-package.php
// for the lookup order (extensions/leave, then the legacy leave_and_performance).
require_once dirname(__FILE__) . '/leave-package.php';
$leavePackageDir = iceLeavePackageDir();
$proMainPath = $leavePackageDir === null ? null : $leavePackageDir . 'main.php';
if ($proMainPath !== null && file_exists($proMainPath)) {
    require_once $proMainPath;
    // Initialize pro model classes after BaseService is available
    if (class_exists('ProModuleInitializer')) {
        ProModuleInitializer::initialize();
    }
}

//$whoops = new \Whoops\Run;
//$whoops->pushHandler(new \Whoops\Handler\PlainTextHandler);
//$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
//$whoops->register();

$container = new IceContainer();

function t($text)
{
	return LanguageManager::translateTnrText($text);
}

if(!defined('TAGS_TO_PRESERVE')){define('TAGS_TO_PRESERVE', '<b><i><span><p><br><strong><h1><h2><h3><h4><h5><em><u><a><li><ul><ol>');}
$jsVersion = defined('CACHE_VALUE')?CACHE_VALUE:"v".VERSION;
$cssVersion = defined('CACHE_VALUE')?CACHE_VALUE:"v".VERSION;

$_REQUEST = InputCleaner::cleanParameters($_REQUEST);
$_GET = InputCleaner::cleanParameters($_GET);
$_POST = InputCleaner::cleanParameters($_POST);

date_default_timezone_set('Asia/Colombo');
//Find timezone diff with GMT
$dateTimeZoneColombo = new DateTimeZone("Asia/Colombo");
$dateTimeColombo = new DateTime("now", $dateTimeZoneColombo);
$dateTimeColomboStr = $dateTimeColombo->format("Y-m-d H:i:s");
$dateTimeNow = date("Y-m-d H:i:s");

$diffHoursBetweenServerTimezoneWithGMT = (strtotime($dateTimeNow) - (strtotime($dateTimeColomboStr) - 5.5*60*60))/(60*60);
