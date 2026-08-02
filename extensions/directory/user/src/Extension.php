<?php

namespace Directory\User;

use Classes\IceExtension;
use DirectoryUser\Migrations\CreateTables;

class Extension extends IceExtension
{
	
	public function initialize() {
	}
	
	public function setupModuleClassDefinitions() {
		$this->addModelClass('StaffDirectory');
	}
	
	public function setupRestEndPoints() {
		(new ApiController())->registerEndPoints();
	}
}

