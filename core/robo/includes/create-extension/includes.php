<?php
require dirname(__FILE__).'/src/CodeEmitter.php';
require dirname(__FILE__).'/src/CodeFormatter.php';
require dirname(__FILE__).'/src/ExtensionBuilder.php';
require dirname(__FILE__).'/src/PhpClass.php';
require dirname(__FILE__).'/src/Method.php';
require dirname(__FILE__).'/src/Property.php';
require dirname(__FILE__).'/src/PhpFile.php';

function getExtensionRoot() {
    return dirname(__FILE__)."/../../../../extensions";
}

define('EXT_ROOT', getExtensionRoot());
define('TEMPLATE_PATH', dirname(__FILE__).'/templates');