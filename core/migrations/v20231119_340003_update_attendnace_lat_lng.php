<?php
namespace Classes\Migration;

use FieldNames\Common\Model\FieldNameMapping;

class v20231119_340003_update_attendnace_lat_lng extends AbstractMigration {

    public function up(){
        $sql = <<<'SQL'
ALTER TABLE Attendance MODIFY COLUMN map_lat decimal(12,8) DEFAULT NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE Attendance MODIFY COLUMN map_lng decimal(12,8) DEFAULT NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE Attendance MODIFY COLUMN map_out_lat decimal(12,8) DEFAULT NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE Attendance MODIFY COLUMN map_out_lng decimal(12,8) DEFAULT NULL;
SQL;
        $this->executeQuery($sql);

        return true;
    }

    public function down(){

        return true;
    }

}
