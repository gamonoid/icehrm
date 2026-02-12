<?php
namespace Classes\Migration;

class v20200311_270005_update_gender extends AbstractMigration {

    public function up(){
        $sql = <<<'SQL'
ALTER TABLE Employees modify column `gender` enum('Male','Female', 'Divers') default NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE ArchivedEmployees modify column `gender` enum('Male','Female', 'Divers') default NULL;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }
}
