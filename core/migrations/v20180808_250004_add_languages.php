<?php
namespace Classes\Migration;

class v20180808_250004_add_languages extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
        INSERT INTO `SupportedLanguages` (`name`, `description`) VALUES ('sr', 'Serbian');
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        INSERT INTO `SupportedLanguages` (`name`, `description`) VALUES ('sv', 'Swedish');
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        INSERT INTO `SupportedLanguages` (`name`, `description`) VALUES ('no', 'Norwegian');
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        INSERT INTO `SupportedLanguages` (`name`, `description`) VALUES ('pt', 'Portuguese');
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        INSERT INTO `SupportedLanguages` (`name`, `description`) VALUES ('nl', 'Dutch');
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}
