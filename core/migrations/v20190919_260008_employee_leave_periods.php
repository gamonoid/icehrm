<?php
namespace Classes\Migration;

class v20190919_260008_employee_leave_periods extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
alter table LeaveTypes add column employee_leave_period enum ('Yes', 'No') default 'No' null;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
alter table LeaveRules add column employee_leave_period enum ('Yes', 'No') default 'No' null;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}
