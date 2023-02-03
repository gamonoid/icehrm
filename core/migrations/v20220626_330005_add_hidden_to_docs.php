<?php
namespace Classes\Migration;

class v20220626_330005_add_hidden_to_docs extends AbstractMigration {

    public function up(){
        $sql = <<<'SQL'
ALTER TABLE `EmployeeDocuments` ADD COLUMN `hidden` tinyint default 0;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
    }

}
