<?php

namespace DemoModeAdmin\Migrations;

use Classes\Migration\AbstractMigration;
use Classes\Migration\MigrationInterface;

class AddDemoModeSetting extends AbstractMigration implements MigrationInterface
{

    public function getName()
    {
        return 'demo-mode_add_setting';
    }

    public function up()
    {
        $sql = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) VALUES
    ('System: Demo Mode', '0', 'When enabled, all data entries are tracked for easy cleanup when moving to production.', '["value", {"label":"Demo Mode","type":"select","source":[["1","Enabled"],["0","Disabled"]]}]', 'System');
SQL;
        return $this->executeQuery($sql);
    }

    public function down()
    {
        $sql = "DELETE FROM `Settings` WHERE `name` = 'System: Demo Mode';";
        return $this->executeQuery($sql);
    }
}
