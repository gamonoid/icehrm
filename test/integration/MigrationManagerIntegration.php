<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 9/17/17
 * Time: 7:06 PM
 */

namespace Test\Integration;

use Classes\Migration\MigrationManager;
use Model\Migration;

class MigrationManagerIntegration extends \TestTemplate
{
    /* @var MigrationManager $migrationManager */
    private $migrationManager;
    protected function setUp()
    {
        parent::setUp();
        $this->migrationManager = new MigrationManager();
        for ($i = 0; $i < 5; $i++) {
            $migration = new Migration();
            $migration->file = 'migration'.$i;
            $migration->version = '190000';
            $migration->created = date("Y-m-d H:i:s", intval(time()) - 10 * $i);
            $migration->updated = date("Y-m-d H:i:s", intval(time()) - 10 * $i);
            $migration->status = 'Pending';
            $migration->Save();
        }
    }

    protected function tearDown()
    {
        parent::tearDown();
        $migration = new Migration();
        $migration->DB()->execute("delete from Migrations");
    }

    public function testGetCurrentMigrations()
    {
        $migrations = $this->migrationManager->getCurrentMigrations();
        self::assertEquals(5, count($migrations));
    }
}
