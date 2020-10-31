<?php
namespace Classes\Migration;

class v20200828_270102_module_user_role_blacklist extends AbstractMigration {

    public function up(){
        $sql = <<<'SQL'
Alter table Modules add column `user_roles_blacklist` TEXT NULL; 
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }
}
