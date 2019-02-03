<?php
namespace Classes\Migration;

class v20180912_250006_remove_null_users extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
delete from Users where username is NULL;
SQL;
        return $this->executeQuery($sql);

    }

    public function down(){

        return true;
    }

}
