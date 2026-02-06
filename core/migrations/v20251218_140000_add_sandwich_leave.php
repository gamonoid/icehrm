<?php
namespace Classes\Migration;

class v20251218_140000_add_sandwich_leave extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
Alter table LeaveTypes add column `sandwich_leave` tinyint not null default 0;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }
}
