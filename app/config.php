<?php
$file = getenv( 'ICE_CONFIG_FILE' );
$configFile = __DIR__.'/'.$file;
if ( $file ) {
	require_once $configFile;
} else {
	require_once 'config-dev.php';
}
if(!defined('EXT_SRC_PATH')) {define('EXT_SRC_PATH', '/src-ob/yakpro-po/obfuscated/');}
