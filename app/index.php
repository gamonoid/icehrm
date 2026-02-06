<?php
if(!file_exists('config.php')){
	header("Location:install/");
	exit();
}
include ('config.php');

if (isset($_REQUEST['auth_code'])) {
	include APP_BASE_PATH.'auth-code.php';
}

// Load pro main.php if it exists (for pro-only modules)
$proMainPath = APP_BASE_PATH . '../extensions/leave_and_performance/main.php';
if (file_exists($proMainPath)) {
	require_once $proMainPath;
}

if(!isset($_REQUEST['g']) || !isset($_REQUEST['n'])){
header("Location:".CLIENT_BASE_URL."login.php");
exit();
}
$group = $_REQUEST['g'];
$name= $_REQUEST['n'];

$groups = array('admin','modules');

if($group == 'admin' || $group == 'modules'){
	$name = str_replace("..","",$name);
	$name = str_replace("/","",$name);

	// Check if module should be loaded from pro directory
	if (class_exists('ProModuleConfig') && ProModuleConfig::isProModule($group, $name)) {
		$proModulePath = ProModuleConfig::getProModulePath($group, $name);
		include $proModulePath.'/index.php';
	} else {
		include APP_BASE_PATH.'/'.$group.'/'.$name.'/index.php';
	}
}else if ($group == 'extension'){
    $name = str_replace("..","",$name);
    $name = str_replace("/","",$name);
    $moduleName = str_replace('|', '/', $name);
    $moduleGroup = 'extensions';

    // Parse extension name and type from moduleName (format: "extensionName/type")
    $parts = explode('/', $moduleName);
    $extensionName = $parts[0];
    $extensionType = isset($parts[1]) ? $parts[1] : 'admin';

    // Get actual path considering grouped extensions
    require_once APP_BASE_PATH.'extensions/path-resolver.php';
    $extensionPath = resolveExtensionPath($extensionName, APP_BASE_PATH . '../extensions/');
    $extensionIndex = $extensionPath . $extensionType . '/web/index.php';
    include APP_BASE_PATH.'extensions/wrapper.php';
}else{
	exit();
}
