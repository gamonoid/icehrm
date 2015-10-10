<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
define('CLIENT_PATH',dirname(__FILE__));
include ("config.base.php");
include ("include.common.php");
include("server.includes.inc.php");

//clean request uri
LogManager::getInstance()->info("REQUEST_URI :".$_SERVER['REQUEST_URI']);
$parts = explode("?", $_SERVER['REQUEST_URI']);
$uri = $parts[0];
if(substr($uri, -1) == "/"){
	$uri = substr($uri, 0, -1);
}

LogManager::getInstance()->info("REQUEST_URI Cleaned :".$uri);

$type = strtolower($_SERVER['REQUEST_METHOD']);

$supportedMethods = array('get','post','put','delete');
if(!in_array($type, $supportedMethods)){
	echo json_encode(new IceResponse(IceResponse::ERROR, "Method not supported"));
	exit();
}
$response = RestApiManager::getInstance()->process($type, $uri, $_REQUEST);
if($response->getStatus() == IceResponse::SUCCESS){
	echo json_encode($response,JSON_PRETTY_PRINT);
}else{
	echo json_encode($response,JSON_PRETTY_PRINT);
}
exit();