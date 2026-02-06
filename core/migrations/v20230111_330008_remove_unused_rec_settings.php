<?php
namespace Classes\Migration;

class v20230111_330008_remove_unused_rec_settings extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
DELETE from Settings where name = 'Recruitment: Show Apply';
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
DELETE from Settings where name = 'Recruitment: Show Quick Apply';
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}