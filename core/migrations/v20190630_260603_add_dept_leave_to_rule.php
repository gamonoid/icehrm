<?php
namespace Classes\Migration;

class v20190630_260603_add_dept_leave_to_rule extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
Alter table LeaveRules add column `department` bigint(20) null;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
Alter table LeaveRules ADD CONSTRAINT `Fk_LeaveRules_department` FOREIGN KEY (`department`) REFERENCES `CompanyStructures` (`id`);
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }

}
