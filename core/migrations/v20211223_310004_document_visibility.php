<?php

namespace Classes\Migration;

class v20211223_310004_document_visibility extends AbstractMigration
{

    public function up()
    {

        $sql = <<<'SQL'
ALTER TABLE `EmployeeDocuments`
MODIFY COLUMN `visible_to`  enum('Owner','Manager','Admin', 'Owner Only') NULL DEFAULT 'Owner';
SQL;

        return $this->executeQuery($sql);
    }

    public function down()
    {
        return true;
    }
}
