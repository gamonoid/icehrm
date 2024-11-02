<?php
namespace Classes\Migration;

class v20230718_1_rename_add_emp_columns extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
ALTER TABLE Employees change custom1 name_preferred VARCHAR(60);
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE Employees change custom2 name_pronunciation VARCHAR(40);
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE Employees change custom3 pronouns VARCHAR(40);
SQL;
        return $this->executeQuery($sql);

    }

    public function down(){

        return true;
    }

}