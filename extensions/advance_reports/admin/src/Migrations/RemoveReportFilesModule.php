<?php

namespace Advance_reportsAdmin\Migrations;

use Classes\Migration\AbstractMigration;
use Classes\Migration\MigrationInterface;

class RemoveReportFilesModule extends AbstractMigration implements MigrationInterface
{
	public function getName() {
		return 'advance_reports_remove_report_files_module';
	}

	public function up() {
		$sql = "DELETE FROM Modules WHERE name = 'report_files'";
		return $this->executeQuery($sql);
	}

	public function down() {
		return true;
	}
}
