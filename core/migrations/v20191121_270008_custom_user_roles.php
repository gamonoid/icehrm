<?php
namespace Classes\Migration;

class v20191121_270008_custom_user_roles extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
ALTER TABLE Users modify column `user_level` enum('Admin','Employee','Manager','Other','Restricted Admin') default NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
Update Users set user_level = 'Restricted Admin' where user_level = 'Other';
SQL;

        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE Users modify column `user_level` enum('Admin','Employee','Manager','Restricted Admin', 'Restricted Manager', 'Restricted Employee') default NULL;
SQL;

        return $this->executeQuery($sql);
    }
    public function down(){
        return true;
    }
}
