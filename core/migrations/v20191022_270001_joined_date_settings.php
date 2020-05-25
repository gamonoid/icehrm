<?php
namespace Classes\Migration;

class v20191022_270001_joined_date_settings extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
    ('Leave: Use Confirmation Date Instead of Joined Date', '0', 'Use confirmation date instead of joined date for leave calculations','["value", {"label":"Value","type":"select","source":[["0","No"], ["1","Yes"]]}]');
SQL;

        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}
