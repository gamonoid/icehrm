<?php
namespace Classes\Migration;

class v20191024_270004_add_object_type_import extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
Alter Table DataImport add column objectType varchar(60) NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE DataImport ADD CONSTRAINT KEY_DataImport_name UNIQUE (name)
SQL;

        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}
