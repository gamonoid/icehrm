<?php
$dir = \Classes\BaseService::getInstance()->getExtensionSourceDirectory(__DIR__);

require_once $dir.'Extension.php';
require_once $dir.'Controller.php';
require_once $dir.'ApiController.php';
require_once $dir.'MarketplaceService.php';
require_once $dir.'InstalledExtensionsService.php';
require_once $dir.'ExtensionUpdateService.php';
require_once $dir.'ExtensionData.php';

// Migrations
require_once $dir.'Migrations/CreateTables.php';

