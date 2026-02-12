<?php
namespace Classes\Migration;

class v20251226_350002_add_payroll_id_to_documents extends AbstractMigration {

	public function up(){

		$sql = <<<'SQL'
ALTER TABLE EmployeeDocuments ADD COLUMN `payroll_id` BIGINT(20) NULL;
SQL;
		$this->executeQuery($sql);

		return true;
	}

	public function down(){

		$sql = <<<'SQL'
ALTER TABLE EmployeeDocuments DROP COLUMN `payroll_id`;
SQL;
		$this->executeQuery($sql);

		return true;
	}
}
