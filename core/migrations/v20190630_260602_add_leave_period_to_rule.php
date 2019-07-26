<?php
namespace Classes\Migration;

class v20190630_260602_add_leave_period_to_rule extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
Alter table LeaveRules add column `leave_period` bigint(20) null;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
Alter table LeaveRules ADD CONSTRAINT `Fk_LeaveRules_leave_period` FOREIGN KEY (`leave_period`) REFERENCES `LeavePeriods` (`id`);
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }

}
