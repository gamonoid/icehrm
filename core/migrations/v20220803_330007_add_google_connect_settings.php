<?php
namespace Classes\Migration;

class v20220803_330007_add_google_connect_settings extends AbstractMigration {

    public function up(){
        $res = true;
        $sql = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) VALUES
    ('System: Google Client Secret Path', '', 'Google Client Secret JSON file path','', 'System');
SQL;
        $res = $res && $this->executeQuery($sql);

        $sql = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) VALUES
    ('System: Google Sync Calendar', '1', 'Sync Events to Google Calender','["value", {"label":"Value","type":"text"}]', 'System');
SQL;
        $res = $res && $this->executeQuery($sql);

        return $res;
    }

    public function down(){

        return true;
    }
}
