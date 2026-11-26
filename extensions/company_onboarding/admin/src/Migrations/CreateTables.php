<?php

namespace CompanyOnboardingAdmin\Migrations;

use Classes\Migration\AbstractMigration;
use Classes\Migration\DbDelta;
use Classes\Migration\MigrationInterface;
use Utils\LogManager;

/**
 * Saved progress of the company setup wizard: one row per step.
 *
 * DbDelta rules: no commas in column defaults, indexes inline. A failed CREATE is
 * silent, so up() checks the table exists.
 */
class CreateTables extends AbstractMigration implements MigrationInterface
{
    public function getName()
    {
        return 'company_onboarding_create_tables';
    }

    private function getDDL()
    {
        return <<<'SQL'
create table CompanySetupSteps
(
    id           bigint auto_increment primary key,
    step         varchar(40)  not null,
    status       varchar(20)  default 'NotStarted' not null,
    updated_by   bigint       null,
    updated      datetime     null,
    unique key Ux_CompanySetupSteps_Step (step)
);
SQL;
    }

    public function up()
    {
        $dbDelta = new DbDelta();
        $dbDelta->delta($this->getDDL(), true);
        $errors = $dbDelta->getErrors();
        if (!empty($errors)) {
            LogManager::getInstance()->error('Company onboarding migration errors: ' . implode('; ', $errors));
            return false;
        }
        $rows = $this->db()->Execute("SHOW TABLES LIKE 'CompanySetupSteps'");
        if (!is_array($rows) || count($rows) === 0) {
            LogManager::getInstance()->error('Company onboarding: CompanySetupSteps was not created');
            return false;
        }
        return true;
    }

    public function down()
    {
        return true;
    }
}
