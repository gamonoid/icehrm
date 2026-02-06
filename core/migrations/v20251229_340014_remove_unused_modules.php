<?php

namespace Classes\Migration;

class v20251229_340014_remove_unused_modules extends AbstractMigration
{

    public function up()
    {
        // Remove modules that no longer exist in the codebase
        $sql = <<<'SQL'
DELETE FROM `Modules` WHERE `update_path` IN (
    'admin>charts',
    'admin>clients',
    'admin>data',
    'admin>leave_charts',
    'admin>payroll',
    'admin>report_files',
    'modules>salary',
    'modules>report_files',
    'extension>teams|user'
);
SQL;

        return $this->executeQuery($sql);
    }

    public function down()
    {
        return true;
    }
}
