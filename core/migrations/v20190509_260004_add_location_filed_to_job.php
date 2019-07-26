<?php
namespace Classes\Migration;

class v20190509_260004_add_location_filed_to_job extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
        Alter table Job add column `location` varchar(500) NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Alter table Job add column `postalCode` varchar(20) NULL;
SQL;
        return $this->executeQuery($sql);
    }
}
