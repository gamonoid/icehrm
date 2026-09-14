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
// iceProExtensionsEnabled() are available before the leave package loader below
// decides whether the legacy combined package may come from extensions-pro/.
include APP_BASE_PATH.'config.base.php';

if (isset($_REQUEST['auth_code'])) {
	include APP_BASE_PATH.'auth-code.php';
}

// Load the leave package loader if it is installed — it defines ProModuleConfig,
// which routes the package's modules (admin/leaves, modules/leaves, ...) below.
require_once APP_BASE_PATH . 'leave-package.php';
$leavePackageDir = iceLeavePackageDir();
if ($leavePackageDir !== null && file_exists($leavePackageDir . 'main.php')) {
	require_once $leavePackageDir . 'main.php';
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
	// The legacy per-module pages are gone. Every core admin/ and modules/ module —
	// and the ones the leave package contributes — now mounts natively in the SPA
	// (NativeModuleRegistry::coreMap(), an unconditional list, so a core module can
	// never fall back to an iframe). Nothing renders these any more, so the page
	// bodies were deleted.
	//
	// Only an *embedded* request can still reach this branch: a top-level visit was
	// already bounced to the SPA above. Answer it with 410 Gone rather than
	// redirecting, because redirecting an iframe to the SPA would nest a second copy
	// of the whole app inside the frame. There is no live caller; this exists so a
	// stale bookmark or a crafted URL fails visibly instead of fataling on a missing
	// include.
	http_response_code(410);
	header('Content-Type: text/html; charset=utf-8');
	echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Page moved</title></head><body>'
		. '<p>This page is part of the retired legacy interface. '
		. '<a href="' . htmlspecialchars(CLIENT_BASE_URL . 'ui/', ENT_QUOTES) . '" target="_top">Open IceHrm</a>.</p>'
		. '</body></html>';
	exit();
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
