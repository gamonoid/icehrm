<?php
namespace Classes\Migration;

class v20190707_260006_google_map_api extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
    ('System: Google Maps Api Key', '', 'Google Map Api Key','');
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}
