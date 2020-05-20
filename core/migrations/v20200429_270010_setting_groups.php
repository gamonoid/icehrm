<?php
namespace Classes\Migration;

class v20200429_270010_setting_groups extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
Alter table Settings add column category varchar(15) NOT NULL;
SQL;

        $this->executeQuery($sql);

        $sql = <<<'SQL'
Update Settings set category = TRIM(SUBSTR(name, 1, LOCATE(':', name) - 1));
SQL;

        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }
}
