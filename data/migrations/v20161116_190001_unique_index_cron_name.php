<?php
namespace Classes\Migration;

class v20161116_190001_unique_index_cron_name extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
        Alter table Crons add unique key `KEY_Crons_name` (`name`);
SQL;


        return $this->executeQuery($sql);
    }

    public function down(){

        $sql = <<<'SQL'
        Alter table Crons drop key `KEY_Crons_name`;
SQL;

        return $this->executeQuery($sql);
    }

}
