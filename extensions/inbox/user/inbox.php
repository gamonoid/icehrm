<?php
$dir = \Classes\BaseService::getInstance()->getExtensionSourceDirectory(__DIR__);

require_once $dir.'Extension.php';
require_once $dir.'Controller.php';
require_once $dir.'ApiController.php';

// Models
require_once $dir.'Common/Model/InboxConversation.php';
require_once $dir.'Common/Model/InboxMessage.php';

// Migrations
require_once $dir.'Migrations/CreateTables.php';
