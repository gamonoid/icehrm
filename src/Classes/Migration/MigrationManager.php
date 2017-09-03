<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:45 AM
 */

namespace Classes\Migration;

use Model\Migration;
use Utils\LogManager;

class MigrationManager
{

    private $migration_path;

    protected $db = null;

    public function __construct()
    {
        $this->migration_path = APP_BASE_PATH .'/migrations/';
    }

    public function setMigrationPath($migration_path)
    {
        $this->migration_path = $migration_path;
    }

    public function getMigrationById($id)
    {
        $migration = new Migration();
        $migration->Load("id = ?", array($id));
        return $migration;
    }

    public function getCurrentMigrations()
    {
        $migration = new Migration();
        return $migration->Find("1 = 1");
    }

    public function getPendingMigrations()
    {
        $migration = new Migration();
        return $migration->Find("status = ?", array('Pending'));
    }

    public function getFirstAddedMigration($statuses)
    {
        $migration = new Migration();
        return $migration->Find("status in ('".implode("','", $statuses)."') order by created limit 1", array());
    }

    public function getLastRunMigration($statuses)
    {
        $migration = new Migration();
        return $migration->Find("status in ('".implode("','", $statuses)."') order by updated desc limit 1", array());
    }

    public function queueMigrations()
    {

        $migrations = array();
        $ams = scandir($this->migration_path);
        foreach ($ams as $am) {
            if (is_file($this->migration_path . $am)) {
                $migrations[$am] = $this->migration_path . $am;
            }
        }

        ksort($migrations);

        if (!empty($migrations)) {
            $migrationsInDB = $this->getCurrentMigrations();
            $migrationsInDBKeyVal = array();
            foreach ($migrationsInDB as $migration) {
                $migrationsInDBKeyVal[$migration->file] = $migration;
            }

            foreach ($migrations as $file => $path) {
                if (!isset($migrationsInDBKeyVal[$file])) {
                    if ($file == 'list.php') {
                        continue;
                    }
                    $migration = new Migration();
                    $migration->file = $file;
                    $parts = explode("_", $file);
                    $migration->version = intval($parts[1]);
                    $migration->created = date("Y-m-d H:i:s");
                    $migration->updated = date("Y-m-d H:i:s");
                    $migration->status = 'Pending';
                    $migration->Save();
                }
            }
        }
    }

    public function runPendingMigrations()
    {
        $migrations = $this->getPendingMigrations();
        foreach ($migrations as $migration) {
            $this->runMigrationUp($migration);
        }
    }

    public function runMigration($action)
    {
        $method = 'runMigration'.ucfirst($action);

        if ($action == 'up') {
            $statuses = array("Pending","Down");
            $queryMethod = 'getFirstAddedMigration';
        } elseif ($action == 'down') {
            $statuses = array("Up");
            $queryMethod = 'getLastRunMigration';
        } else {
            return false;
        }
        $migrations = $this->$queryMethod($statuses);
        if (count($migrations) > 0) {
            $this->$method($migrations[0]);
            return $this->getMigrationById($migrations[0]->id);
        } else {
            $this->queueMigrations();
            $migrations = $this->$queryMethod($statuses);
            if (count($migrations) > 0) {
                $this->$method($migrations[0]);
                return $this->getMigrationById($migrations[0]->id);
            }
        }

        return false;
    }

    /**
     * @param Migration $migration
     * @return bool
     */
    public function runMigrationUp($migration)
    {
        if ($migration->status != 'Pending' && $migration->status != 'UpError' && $migration->status != 'Down') {
            return false;
        }

        $path = $this->migration_path . $migration->file;
        if (!file_exists($path)) {
            return false;
        }
        $migrationName = str_replace('.php', '', $migration->file);
        if (!class_exists($migrationName)) {
            include $path;
        }
        /* @var AbstractMigration $migClass */
        $migClass = new $migrationName;
        $res = $migClass->up();
        if (!$res) {
            $migration->last_error = $migClass->getLastError();
            $migration->status = "UpError";
            $migration->updated = date("Y-m-d H:i:s");
            $migration->Save();
        }

        $migration->status = "Up";
        $migration->updated = date("Y-m-d H:i:s");
        $migration->Save();
        return $migration;
    }

    /**
     * @param Migration $migration
     * @return bool
     */
    public function runMigrationDown($migration)
    {
        if ($migration->status != 'Up' && $migration->status != 'UpError') {
            return false;
        }

        $path = $this->migration_path . $migration->file;
        if (!file_exists($path)) {
            return false;
        }

        $migrationName = str_replace('.php', '', $migration->file);
        if (!class_exists($migrationName)) {
            include $path;
        }
        /* @var AbstractMigration $migClass */
        $migClass = new $migrationName;
        $res = $migClass->down();
        if (!$res) {
            $migration->last_error = $migClass->getLastError();
            $migration->status = "DownError";
            $migration->updated = date("Y-m-d H:i:s");
            $migration->Save();
        }

        $migration->status = "Down";
        $migration->updated = date("Y-m-d H:i:s");
        $migration->Save();
        return $migration;
    }

    public function ensureMigrations()
    {

        $migration = new Migration();
        $migration->Load("1 = 1 order by id desc limit 1");

        include $this->migration_path . "list.php";
        /* @var array $migrationList */
        if (count($migrationList) > 0 && (empty($migration->id) || $migrationList[0].".php" != $migration->file)) {
            LogManager::getInstance()->info("ensureMigrations - execute migrations");
            $this->queueMigrations();
            $this->runPendingMigrations();
        }
    }
}
