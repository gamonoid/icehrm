<?php
namespace Classes\Migration;

class v20190125_260003_attendance_with_map extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
        Alter table Attendance add column `map_lat` DECIMAL(10, 8) NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Alter table Attendance add column `map_lng` DECIMAL(10, 8) NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Alter table Attendance add column `map_snapshot` longtext default null;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        $sql = <<<'SQL'
        Alter table Attendance drop column `map_lat`;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Alter table Attendance drop column `map_lng`;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Alter table Attendance drop column `map_snapshot`;
SQL;
        return $this->executeQuery($sql);
    }
}
