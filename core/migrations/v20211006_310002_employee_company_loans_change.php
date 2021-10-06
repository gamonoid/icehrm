<?php

namespace Classes\Migration;

class v20211006_310002_employee_company_loans_change extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
        alter table EmployeeCompanyLoans add column `yearly_interest_percentage` decimal default(0.00) NULL;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        $sql = <<<'SQL'
        alter table EmployeeCompanyLoans drop column `yearly_interest_percentage`;
SQL;

        return $this->executeQuery($sql);
    }

}
{

}
