<?php

use Classes\BaseService;

$dir = BaseService::getInstance()->getExtensionSourceDirectory(__DIR__);
require_once $dir.'Extension.php';
require_once $dir.'Controller.php';
require_once $dir.'ApiController.php';

// Migrations
require_once $dir.'Common/Model/StaffDirectory.php';

