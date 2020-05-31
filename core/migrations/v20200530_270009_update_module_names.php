<?php
namespace Classes\Migration;

class v20200530_270009_update_module_names extends AbstractMigration {

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
