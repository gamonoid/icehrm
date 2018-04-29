<?php
namespace Classes\Migration;

class v20171003_200301_add_deduction_group_payroll extends AbstractMigration{

	public function up(){

		$sql = <<<'SQL'
        alter table PayrollColumns add column `deduction_group` bigint(20) NULL;
SQL;
		return $this->executeQuery($sql);
	}

	public function down(){
		$sql = <<<'SQL'
        alter table PayrollColumns drop column `deduction_group`;
SQL;

		return $this->executeQuery($sql);
	}

}

