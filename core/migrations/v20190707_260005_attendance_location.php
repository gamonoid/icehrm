<?php
namespace Classes\Migration;

class v20190707_260005_attendance_location extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
        Alter table Attendance add column `map_out_snapshot` longtext default null;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Alter table Attendance add column `in_ip` varchar(25) default null;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Alter table Attendance add column `out_ip` varchar(25) default null;
SQL;
        return $this->executeQuery($sql);

    }

    public function down(){

        return true;
    }

}
