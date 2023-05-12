<?php

namespace KudosAdmin;

use Classes\BaseService;
use Classes\IceExtension;
use KudosAdmin\Migrations\CreateTables;

class Extension extends IceExtension
{
	
	public function initialize() {
		// BaseService::getInstance()->registerExtensionMigration(new CreateTables());
	}
	
	public function setupModuleClassDefinitions() {
		// $this->addModelClass('ClassName');
	}
	
	public function setupRestEndPoints() {
		(new ApiController())->registerEndPoints();
	}
}

