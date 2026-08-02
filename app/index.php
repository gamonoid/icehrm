<?php
// Not installed yet? A fresh deployment ships an empty config.php (the install
// marker), so treat "missing OR empty" as not-installed and send the user to the
// installer instead of including an empty config and fataling on the constants it
// is supposed to define.
if (!file_exists('config.php') || trim((string) @file_get_contents('config.php')) === '') {
	header("Location:install/");
	exit();
}
include ('config.php');

// Load config.base.php early (it is idempotent) so IS_ICEHRM_PRO / IS_CLOUD and
// iceProExtensionsEnabled() are available before the pro loader below decides
// whether to pull leave_and_performance from the paid extensions-pro/ split.
include APP_BASE_PATH.'config.base.php';

if (isset($_REQUEST['auth_code'])) {
	include APP_BASE_PATH.'auth-code.php';
}

// Load pro main.php if it exists (for pro-only modules). leave_and_performance
// lives under extensions/ (free) or the paid extensions-pro/ split — the latter
// only on a Pro/Cloud build.
$proMainPath = APP_BASE_PATH . '../extensions/leave_and_performance/main.php';
if (!file_exists($proMainPath) && function_exists('iceProExtensionsEnabled') && iceProExtensionsEnabled()) {
	$proMainPath = APP_BASE_PATH . '../extensions-pro/leave_and_performance/main.php';
}
if (file_exists($proMainPath)) {
	require_once $proMainPath;
}

if(!isset($_REQUEST['g']) || !isset($_REQUEST['n'])){
header("Location:".CLIENT_BASE_URL."login.php");
exit();
}
$group = $_REQUEST['g'];
$name= $_REQUEST['n'];

// The standalone legacy UI is retired and unreachable: every legacy page load is
// bounced to the new UI. The ONLY thing allowed to render a legacy page is the new
// UI's own iframe-embedded modules, identified by BOTH an explicit _embed=1 marker
// (added to the shell's iframe src) and/or the browser's Sec-Fetch-Dest: iframe
// (which also covers navigation that happens INSIDE that iframe). Anything else —
// a bookmark, a pasted URL, a notification link, a non-browser client — redirects.
$iceEmbedded = (isset($_REQUEST['_embed']) && $_REQUEST['_embed'] === '1')
	|| (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] === 'iframe');
if (!$iceEmbedded) {
	header('Location: ' . CLIENT_BASE_URL . 'ui/');
	exit();
}

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
