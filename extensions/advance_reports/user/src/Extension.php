<?php

namespace Advance_reportsUser;

use Classes\BaseService;
use Classes\IceExtension;
use Advance_reportsUser\Migrations\CreateTables;
use Modules\Common\Model\Module;

class Extension extends IceExtension
{
	public function initialize() {
		// Disable the core user reports module when advance_reports is enabled
		$this->disableCoreReportsModule();
	}

	public function setupModuleClassDefinitions() {
		// $this->addModelClass('ClassName');
	}

	public function setupRestEndPoints() {
		(new ApiController())->registerEndPoints();
	}

	private function disableCoreReportsModule() {
		$module = new Module();
		$coreReports = $module->Find("name = ? AND mod_group = ?", ['reports', 'user']);
		if (!empty($coreReports)) {
			$coreReportsModule = $coreReports[0];
			if ($coreReportsModule->status !== 'Disabled') {
				$coreReportsModule->status = 'Disabled';
				$coreReportsModule->Save();
			}
		}
	}
}
