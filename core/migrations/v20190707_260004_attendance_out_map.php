<?php
namespace Classes\Migration;

class v20190707_260004_attendance_out_map extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
        Alter table Attendance add column `map_out_lat` DECIMAL(10, 8) NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Alter table Attendance add column `map_out_lng` DECIMAL(10, 8) NULL;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }
}
