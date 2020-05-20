<?php
namespace Classes\Migration;

class v20190805_260007_fix_expense_table extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
Alter table EmployeeTravelRecords modify column `funding` decimal(10,2) NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
Alter table EmployeeExpenses modify column `amount` decimal(10,2) NULL;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}
