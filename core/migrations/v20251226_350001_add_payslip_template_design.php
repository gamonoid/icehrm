<?php
namespace Classes\Migration;

class v20251226_350001_add_payslip_template_design extends AbstractMigration {

	public function up(){

		$sql = <<<'SQL'
ALTER TABLE PayslipTemplates ADD COLUMN `design` LONGTEXT NULL;
SQL;
		$this->executeQuery($sql);

		return true;
	}

	public function down(){

		$sql = <<<'SQL'
ALTER TABLE PayslipTemplates DROP COLUMN `design`;
SQL;
		$this->executeQuery($sql);

		return true;
	}
}
