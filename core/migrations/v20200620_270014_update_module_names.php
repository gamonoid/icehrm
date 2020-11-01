<?php
namespace Classes\Migration;

class v20200620_270014_update_module_names extends AbstractMigration {

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
