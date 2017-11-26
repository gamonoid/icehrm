<?php
namespace Classes\Migration;

class v20171003_200302_payroll_meta_export extends AbstractMigration{

	public function up(){

		$sql = <<<'SQL'
        REPLACE INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
    ('Payroll Meta Data Export', 'Export payroll module configurations',
     '[\r\n[ "deduction_group", {"label":"Calculation Group","type":"select2","allow-null":false,"remote-source":["DeductionGroup","id","name"]}],\r\n[ "payroll", {"label":"Sample Payroll","type":"select2","allow-null":false,"remote-source":["Payroll","id","name"]}]]',
     'PayrollDataExport',
     '["deduction_group","payroll"]', 'Class','Payroll','JSON');
SQL;
		$this->executeQuery($sql);


		$sql = <<<'SQL'
        INSERT INTO `DataImport` (`name`, `dataType`, `details`, `columns`, `updated`, `created`) VALUES
		('Payroll Data Import', 'PayrollDataImporter', '', '[]', '2016-08-14 02:51:56', '2016-08-14 02:51:56');

SQL;
		return $this->executeQuery($sql);
	}

	public function down(){
		$sql = <<<'SQL'
        delete from Reports where name = 'Payroll Meta Data Export';
SQL;

		$this->executeQuery($sql);

		$sql = <<<'SQL'
        delete from DataImport where name = 'Attendance Data Import';
SQL;

		return $this->executeQuery($sql);
	}

}

