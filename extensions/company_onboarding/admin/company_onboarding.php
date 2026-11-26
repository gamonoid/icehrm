<?php
$dir = \Classes\BaseService::getInstance()->getExtensionSourceDirectory(__DIR__);

require_once $dir.'Extension.php';
require_once $dir.'Controller.php';

// Services
require_once $dir.'Db.php';
require_once $dir.'SetupService.php';
require_once $dir.'CompanyService.php';
require_once $dir.'LookupService.php';
require_once $dir.'CalendarService.php';
require_once $dir.'ApiController.php';

// Migrations
require_once $dir.'Migrations/CreateTables.php';
