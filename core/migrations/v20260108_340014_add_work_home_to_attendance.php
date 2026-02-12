<?php
namespace Classes\Migration;

class v20260108_340014_add_work_home_to_attendance extends AbstractMigration {

    public function up(){
        $sql = <<<'SQL'
ALTER TABLE Attendance ADD COLUMN work_from_home TINYINT(1) DEFAULT 0 AFTER note;
SQL;
        $this->executeQuery($sql);

        return true;
    }

    public function down(){
        $sql = <<<'SQL'
ALTER TABLE Attendance DROP COLUMN work_from_home;
SQL;
        $this->executeQuery($sql);

        return true;
    }

}
