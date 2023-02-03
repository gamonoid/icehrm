<?php
require dirname(__FILE__).'/create-extension.php';

use CreateExtension\CodeFormatter;
use CreateExtension\ExtensionBuilder;
use CreateExtension\Method;
use CreateExtension\Property;

$extensionName = 'expenses';
$namespace = ExtensionBuilder::getCamelCaseNameFromExtensionName($extensionName);
$className = 'Extension';


$class = new \CreateExtension\PhpClass($namespace, $className, 'IceExtension');

$class->addProperty(new Property('test'));
$class->addProperty(new Property('test1'));

$initializeMethod = new Method('initialize');
$initializeMethod->setBody([
    'BaseService::getInstance()->registerExtensionMigration(new CreateTables());',
]);
$class->addMethod($initializeMethod);
echo $class->getCode(new CodeFormatter());


