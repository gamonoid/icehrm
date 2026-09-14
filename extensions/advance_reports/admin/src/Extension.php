<?php

namespace Advance_reportsAdmin;

use Classes\BaseService;
use Classes\IceExtension;
use Advance_reportsAdmin\Migrations\CreateTables;
use Advance_reportsAdmin\Migrations\RemoveReportFilesModule;
use Modules\Common\Model\Module;

class Extension extends IceExtension
{

	public function initialize() {
		// Disable the core reports module when advance_reports is enabled
		$this->disableCoreReportsModule();

		// Register migration to remove report_files module
		BaseService::getInstance()->registerExtensionMigration(new RemoveReportFilesModule());
	}

	public function setupModuleClassDefinitions() {
		// $this->addModelClass('ClassName');
	}

	public function setupRestEndPoints() {
		(new ApiController())->registerEndPoints();
	}

	private function disableCoreReportsModule() {
		$module = new Module();
		$coreReports = $module->Find("name = ? AND mod_group = ?", ['reports', 'admin']);
		if (!empty($coreReports)) {
			$coreReportsModule = $coreReports[0];
			if ($coreReportsModule->status !== 'Disabled') {
				$coreReportsModule->status = 'Disabled';
				$coreReportsModule->Save();
			}
		}
	}
}

