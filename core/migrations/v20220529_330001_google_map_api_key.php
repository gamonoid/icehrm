<?php
namespace Classes\Migration;

class v20220529_330001_google_map_api_key extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) VALUES
    ('Attendance: Request Attendance Location on Mobile', '1', 'Push attendance location when marking attendance via mobile app','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]', 'System');
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) VALUES
    ('System: Google Maps Api Key', '', 'Google Map Api Key','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]', 'System');
SQL;
        return $this->executeQuery($sql);

    }

    public function down(){

        return true;
    }

}
