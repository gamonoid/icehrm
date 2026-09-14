<?php

namespace DemoModeAdmin;

use Classes\BaseService;
use Classes\IceExtension;
use DemoModeAdmin\Migrations\CreateTables;
use DemoModeAdmin\Migrations\AddDemoModeSetting;

class Extension extends IceExtension
{

	public function initialize() {
		BaseService::getInstance()->registerExtensionMigration(new CreateTables());
		BaseService::getInstance()->registerExtensionMigration(new AddDemoModeSetting());
	}

	public function setupModuleClassDefinitions() {
		$this->addModelClass('DemoDataEntry');
	}

	public function setupRestEndPoints() {
		(new ApiController())->registerEndPoints();
	}
}

