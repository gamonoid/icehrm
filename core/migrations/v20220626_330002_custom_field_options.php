<?php
namespace Classes\Migration;

class v20220626_330002_custom_field_options extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
ALTER TABLE `CustomFields` MODIFY COLUMN `field_options` TEXT NULL;
SQL;
        return $this->executeQuery($sql);

    }

    public function down(){

        return true;
    }

}
