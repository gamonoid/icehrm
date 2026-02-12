<?php

namespace EditorUser;

use Classes\BaseService;
use Classes\IceExtension;
use EditorUser\Migrations\CreateTables;

class Extension extends IceExtension
{
	
	public function initialize() {
		BaseService::getInstance()->registerExtensionMigration(new CreateTables());
	}
	
	public function setupModuleClassDefinitions() {
		$this->addModelClass('Content');
	}
	
	public function setupRestEndPoints() {
		(new ApiController())->registerEndPoints();
	}
}

