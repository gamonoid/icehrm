<?php
namespace Classes\Migration;

class v20170908_200000_payroll_group extends AbstractMigration{

	public function up(){

		$sql = <<<'SQL'
        Alter table Payroll add COLUMN `deduction_group` bigint(20) DEFAULT NULL;
SQL;


		return $this->executeQuery($sql);
	}

	public function down(){

		$sql = <<<'SQL'
        Alter table Payroll drop COLUMN `deduction_group`;
SQL;

		return $this->executeQuery($sql);
	}

}
