<?php
namespace Classes\Migration;

class v20200518_270011_add_al_language extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
        REPLACE INTO `SupportedLanguages` (`name`, `description`) VALUES ('al', 'Albanian');
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}
