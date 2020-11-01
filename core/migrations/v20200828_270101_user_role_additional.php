<?php
namespace Classes\Migration;

class v20200828_270101_user_role_additional extends AbstractMigration {

    public function up(){
        $sql = <<<'SQL'
Alter table UserRoles add column `additional_permissions` TEXT NULL; 
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }
}
