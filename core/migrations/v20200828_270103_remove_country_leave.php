<?php
namespace Classes\Migration;

class v20200828_270103_remove_country_leave extends AbstractMigration {

    public function up(){
        $sql = <<<'SQL'
DELETE from Settings where name = 'Leave: Select Leave Period from Employee Department Country';
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }
}
