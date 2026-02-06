<?php
namespace Classes\Migration;

class v20240221_340006_add_leave_need_attachment extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
Alter table LeaveTypes add column `attachment_mandatory` enum('Yes','No') default 'No';
SQL;
        $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }
}
