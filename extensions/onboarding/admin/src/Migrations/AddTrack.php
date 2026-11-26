<?php

namespace OnboardingAdmin\Migrations;

use Classes\Migration\AbstractMigration;
use Classes\Migration\MigrationInterface;
use Utils\LogManager;

/**
 * Onboarding gets a second track: besides new hires ("new_hire"), a company that starts
 * using IceHrm can onboard its existing staff ("workforce", from Company Onboarding).
 * Workflows and onboardings record their track; workforce onboardings also keep the
 * employee's real joined date.
 */
class AddTrack extends AbstractMigration implements MigrationInterface
{
    public function getName()
    {
        return 'onboarding_add_track';
    }

    public function up()
    {
        $columns = array(
            array('OnboardingTemplates', 'track', "ALTER TABLE `OnboardingTemplates` ADD COLUMN `track` varchar(20) NOT NULL DEFAULT 'new_hire'"),
            array('Onboardings', 'track', "ALTER TABLE `Onboardings` ADD COLUMN `track` varchar(20) NOT NULL DEFAULT 'new_hire'"),
            array('Onboardings', 'joined_date', 'ALTER TABLE `Onboardings` ADD COLUMN `joined_date` date NULL'),
        );
        foreach ($columns as $column) {
            list($table, $name, $sql) = $column;
            if ($this->hasColumn($table, $name)) {
                continue;
            }
            $this->executeQuery($sql);
        }
        foreach ($columns as $column) {
            if (!$this->hasColumn($column[0], $column[1])) {
                LogManager::getInstance()->error('Onboarding migration: column ' . $column[0] . '.' . $column[1] . ' was not added');
                return false;
            }
        }
        return true;
    }

    /**
     * information_schema rather than "SHOW COLUMNS ... LIKE ?": SHOW statements cannot
     * take a bound parameter with this database layer.
     */
    private function hasColumn($table, $column)
    {
        $rows = $this->db()->Execute(
            'SELECT COUNT(*) AS c FROM information_schema.COLUMNS '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            array($table, $column)
        );
        return is_array($rows) && !empty($rows[0]['c']);
    }

    public function down()
    {
        return true;
    }
}
