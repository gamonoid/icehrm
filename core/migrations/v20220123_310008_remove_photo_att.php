<?php
namespace Classes\Migration;

class v20220123_310008_remove_photo_att extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
Delete from Settings where name = 'Attendance: Photo Attendance';
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }

}