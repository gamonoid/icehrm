<?php
namespace Classes\Migration;

class v20201028_280002_update_gender extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
ALTER TABLE Candidates modify column `gender` varchar(15) NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
Update Candidates set gender = 'Other' WHERE gender = 'Divers';
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE Employees modify column `gender` varchar(15) NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
Update Employees set gender = 'Other' WHERE gender = 'Divers';
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE ArchivedEmployees modify column `gender` varchar(15) NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
Update ArchivedEmployees set gender = 'Other' WHERE gender = 'Divers';
SQL;

        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }
}
