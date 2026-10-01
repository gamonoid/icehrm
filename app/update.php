<?php
include ('config.php');
include (APP_BASE_PATH.'config.base.php');
include (APP_BASE_PATH.'include.common.php');

// Require an authenticated Admin session before doing anything else.
$_updateUser = \Utils\SessionUtils::getSessionObject('user');
if (empty($_updateUser) || empty($_updateUser->id) || $_updateUser->user_level !== 'Admin') {
    header("Location:".CLIENT_BASE_URL."login.php");
    exit();
}
unset($_updateUser);

if(!isset($_REQUEST['g']) || !isset($_REQUEST['n'])){
    header("Location:".CLIENT_BASE_URL."login.php");
    exit();
}
$group = $_REQUEST['g'];
$name= $_REQUEST['n'];

// Allowlist both the group and the module name.
$allowedGroups = array('admin', 'modules');
if (!in_array($group, $allowedGroups, true)) {
    exit();
}

$allowedModules = array_map(
    'basename',
    glob(APP_BASE_PATH.'/'.$group.'/*/update.php') ?: []
);
$name = basename($name);
if (!in_array($name, $allowedModules, true)) {
    exit();
}

include APP_BASE_PATH.'/'.$group.'/'.$name.'/update.php';