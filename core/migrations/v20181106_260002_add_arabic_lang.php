<?php
namespace Classes\Migration;

class v20181106_260002_add_arabic_lang extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
        INSERT INTO `SupportedLanguages` (`name`, `description`) VALUES ('ar', 'Arabic');
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}
