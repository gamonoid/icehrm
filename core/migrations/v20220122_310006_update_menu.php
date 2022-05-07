<?php
namespace Classes\Migration;

class v20220122_310006_update_menu extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
Update Settings set value = '1' where name = 'System: Reset Module Names';
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }

}
