<?php
namespace Classes\Migration;

class v20230916_340001_google_map_api_key extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) VALUES
    ('System: Google Maps Api Key', '', 'Google Map Api Key','', 'System');
SQL;
        return $this->executeQuery($sql);

    }

    public function down(){

        return true;
    }

}
