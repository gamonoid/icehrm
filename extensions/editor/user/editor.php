<?php
use Classes\BaseService;

$dir = BaseService::getInstance()->getExtensionSourceDirectory(__DIR__);

require_once $dir.'Extension.php';
require_once $dir.'Controller.php';
require_once $dir.'ApiController.php';
require_once $dir.'EditorService.php';
require_once $dir.'EditorJs.php';

// Models
require_once $dir.'Common/Model/Content.php';

// Migrations
require_once $dir.'Migrations/CreateTables.php';
