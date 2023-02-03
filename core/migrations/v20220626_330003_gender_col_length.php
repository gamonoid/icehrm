<?php
namespace Classes\Migration;

class v20220626_330003_gender_col_length extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
ALTER TABLE `ArchivedEmployees` MODIFY COLUMN `gender` varchar(20) null;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE `Employees` MODIFY COLUMN `gender` varchar(20) null;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}
