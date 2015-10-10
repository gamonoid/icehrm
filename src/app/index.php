<?php
if(!file_exists('config.php')){
	header("Location:install/");
	exit();
}
include ('config.php');
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
	include APP_BASE_PATH.'/'.$group.'/'.$name.'/index.php';
}else{
	exit();
}