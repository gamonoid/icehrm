<?php
namespace Classes\Migration;

class v20190707_260005_attendance_location extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
    ('Attendance: Request Attendance Location on Mobile', '1', 'Push attendance location when marking attendance via mobile app','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]');
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
    ('System: Google Maps Api Key', '', 'Google Map Api Key','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]');
SQL;
        $this->executeQuery($sql);

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
