<?php
namespace Classes\Migration;

class v20200224_270004_update_module_names extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
        Update Settings set value = '1' where name = 'System: Add New Permissions';
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
Update Settings set value = '1' where name = 'System: Reset Module Names';
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }

}
