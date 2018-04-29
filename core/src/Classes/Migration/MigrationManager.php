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

    private $migrationPath;

    protected $db = null;

    public function setMigrationPath($migrationPath)
    {
        $this->migrationPath = $migrationPath;
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
        $ams = scandir($this->migrationPath);
        foreach ($ams as $am) {
            if (is_file($this->migrationPath . $am) && $am !== '.' && $am !== '..' && !empty($am)) {
                $migrations[$am] = $this->migrationPath . $am;
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
                    $this->createMigration($file);
                }
            }
        }
    }

    public function createMigration($file)
    {
        if (file_exists($this->migrationPath . $file)) {
            $migration = new Migration();
            $migration->file = $file;
            $parts = explode("_", $file);
            $migration->version = intval($parts[1]);
            $migration->created = date("Y-m-d H:i:s");
            $migration->updated = date("Y-m-d H:i:s");
            $migration->status = 'Pending';
            $migration->Save();
            return true;
        }
        return false;
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
     * @param string $migrationFileName
     * @return AbstractMigration
     */

    public function getMigrationObject($migrationFileName)
    {
        $path = $this->migrationPath . $migrationFileName;
        $migrationName = str_replace('.php', '', $migrationFileName);
        $migrationName = '\\Classes\\Migration\\'.$migrationName;

        if (!class_exists($migrationName)) {
            include $path;
        }
        if (!class_exists($migrationName)) {
            return false;
        }
        /* @var AbstractMigration $migClass */
        return new $migrationName($migrationFileName);
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

        /* @var AbstractMigration $migObject */
        $migObject = $this->getMigrationObject($migration->file);
        if (!$migObject) {
            return false;
        }
        $res = $migObject->up();
        if (!$res) {
            $migration->last_error = $migObject->getLastError();
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

        /* @var AbstractMigration $migObject */
        $migObject = $this->getMigrationObject($migration->file);
        if (!$migObject) {
            return false;
        }
        $res = $migObject->down();
        if (!$res) {
            $migration->last_error = $migObject->getLastError();
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

        include $this->migrationPath . "list.php";
        /* @var array $migrationList */
        if (count($migrationList) > 0 && (empty($migration->id) || $migrationList[0].".php" != $migration->file)) {
            LogManager::getInstance()->info("ensureMigrations - execute migrations");
            $this->queueMigrations();
            $this->runPendingMigrations();
        }
    }
}
