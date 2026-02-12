<?php
namespace Classes\Migration;

class v20240221_340005_add_leave_restrictions extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
Alter table LeaveTypes add column `leave_lock_period` int null default 0;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
Alter table LeaveTypes add column `notice_period` int null default 0;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }
}
