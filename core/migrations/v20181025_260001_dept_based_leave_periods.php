<?php
namespace Classes\Migration;

class v20181025_260001_dept_based_leave_periods extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
    ('Leave: Select Leave Period from Employee Department Country', '0', 'The leave period for the employee should be decided based on the country of the department which the employee is attached to','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]');
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
Alter table LeavePeriods ADD COLUMN `country` bigint(20) DEFAULT NULL;
SQL;
        return $this->executeQuery($sql);

    }

    public function down(){

        return true;
    }

}
