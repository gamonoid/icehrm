<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
error_reporting(0);
class RoboFile extends \Robo\Tasks
{
    private function includeCientConfig($client){
        include dirname(__FILE__)."/../config.base.php";
        include ALL_CLIENT_BASE_PATH. $client . "/config.php";

        include (dirname(__FILE__)."/../include.common.php");

        include(dirname(__FILE__)."/../server.includes.inc.php");
    }


    function hello(array $world)
    {
        $this->say("Hello, " . implode(', ', $world));
    }

    function migrate($client, $action){
        $this->includeCientConfig($client);
        $this->say("DB Migrating " . $action . " for ". $client);
        $migrationManager = new \Classes\Migration\MigrationManager();
        $migrationManager->setMigrationPath(APP_BASE_PATH .'/migrations/');
        $res = $migrationManager->runMigration($action);
        $this->say("DB Migrating Result : " . print_r($res, true));
    }

    function migrateAll($client){
        $this->includeCientConfig($client);
        $this->say("Run all pending migrations " . " for ". $client);
        $migrationManager = new \Classes\Migration\MigrationManager();
        $migrationManager->setMigrationPath(APP_BASE_PATH .'/migrations/');
        $migrationManager->queueMigrations();
        $migrations = $migrationManager->getPendingMigrations();
        foreach ($migrations as $migration) {
            $res = $migrationManager->runMigrationUp($migration);
            if(empty($res)){
                $this->yell("Migration not found", 40, 'yellow');
            }elseif($res->status != 'Up'){
                $this->yell("Migration error ".$res->file." (".$res->status.")", 40, 'red');
            }else{
                $this->yell("Migration OK ".$res->file." (".$res->status.")");
            }
        }
        $this->say("DB Migration Completed !!!");
    }
}
