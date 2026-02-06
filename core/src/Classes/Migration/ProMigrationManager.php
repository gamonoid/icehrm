<?php
/**
 * Pro Migration Manager
 *
 * Handles migrations from extensions/leave_and_performance/migrations directory.
 * Uses the same Migrations table as core migrations.
 */

namespace Classes\Migration;

use Classes\BaseService;
use Model\Migration;
use Utils\LogManager;

class ProMigrationManager
{
    /** @var ProMigrationManager|null */
    private static $instance = null;

    /** @var string */
    private $migrationPath;

    private function __construct()
    {
        $this->migrationPath = PRO_PATH . '/migrations/';
    }

    /**
     * @return ProMigrationManager
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new ProMigrationManager();
        }
        return self::$instance;
    }

    /**
     * @return string
     */
    public function getMigrationPath()
    {
        return $this->migrationPath;
    }

    /**
     * Get all migrations currently in the database
     *
     * @return array
     */
    public function getCurrentMigrations()
    {
        $migration = new Migration();
        return $migration->Find("1 = 1");
    }

    /**
     * Get pending migrations
     *
     * @return array
     */
    public function getPendingMigrations()
    {
        $migration = new Migration();
        return $migration->Find("status = ? AND file LIKE ?", array('Pending', 'pro_%'));
    }

    /**
     * Queue pro migrations by scanning the migrations directory
     *
     * @return void
     */
    public function queueMigrations()
    {
        if (!is_dir($this->migrationPath)) {
            LogManager::getInstance()->info("Pro migrations directory not found: " . $this->migrationPath);
            return;
        }

        $migrations = array();
        $files = scandir($this->migrationPath);
        foreach ($files as $file) {
            if (is_file($this->migrationPath . $file) && $file !== '.' && $file !== '..' && $file !== 'list.php') {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    // Prefix with 'pro_' to distinguish from core migrations
                    $migrations[$file] = $this->migrationPath . $file;
                }
            }
        }

        ksort($migrations);
        LogManager::getInstance()->info("Found " . count($migrations) . " pro migration files");

        if (!empty($migrations)) {
            $migrationsInDB = $this->getCurrentMigrations();
            $migrationsInDBKeyVal = array();
            foreach ($migrationsInDB as $migration) {
                $migrationsInDBKeyVal[$migration->file] = $migration;
            }

            foreach ($migrations as $file => $path) {
                $dbFileName = 'pro_' . $file;
                if (!isset($migrationsInDBKeyVal[$dbFileName])) {
                    LogManager::getInstance()->info("Queueing pro migration: " . $dbFileName);
                    $this->createMigration($file);
                }
            }
        }
    }

    /**
     * Create a migration record in the database
     *
     * @param string $file
     * @return bool
     */
    public function createMigration($file)
    {
        if (file_exists($this->migrationPath . $file) && !is_dir($this->migrationPath . $file)) {
            $migration = new Migration();
            $migration->file = 'pro_' . $file; // Prefix with 'pro_' to distinguish
            $parts = explode("_", $file);
            $migration->version = isset($parts[1]) ? intval($parts[1]) : 0;
            $migration->created = date("Y-m-d H:i:s");
            $migration->updated = date("Y-m-d H:i:s");
            $migration->status = 'Pending';
            $migration->Save();
            return true;
        }
        return false;
    }

    /**
     * Get a migration object from a file
     *
     * @param string $migrationFileName
     * @return AbstractProMigration|false
     */
    public function getMigrationObject($migrationFileName)
    {
        // Remove 'pro_' prefix to get actual filename
        $actualFileName = preg_replace('/^pro_/', '', $migrationFileName);
        $path = $this->migrationPath . $actualFileName;

        if (!file_exists($path)) {
            LogManager::getInstance()->error("Pro migration file not found: $path");
            return false;
        }

        $migrationName = str_replace('.php', '', $actualFileName);
        $migrationClass = '\\Classes\\Migration\\' . $migrationName;

        if (!class_exists($migrationClass)) {
            include $path;
        }

        if (!class_exists($migrationClass)) {
            LogManager::getInstance()->error("Pro migration class not found: $migrationClass");
            return false;
        }

        return new $migrationClass($migrationFileName);
    }

    /**
     * Run a single migration up
     *
     * @param Migration $migration
     * @return bool
     */
    public function runMigrationUp(Migration $migration)
    {
        LogManager::getInstance()->info("Running pro migration: " . $migration->file);

        if ($migration->status !== 'Pending') {
            LogManager::getInstance()->info("Skipping migration - status is: " . $migration->status);
            return false;
        }

        $migObject = $this->getMigrationObject($migration->file);
        if (!$migObject) {
            $migration->last_error = "Migration class not found";
            $migration->status = "UpError";
            $migration->updated = date("Y-m-d H:i:s");
            $migration->Save();
            LogManager::getInstance()->error("Pro migration class not found: " . $migration->file);
            return false;
        }

        try {
            $res = $migObject->up();
            if (!$res) {
                $migration->last_error = $migObject->getLastError();
                $migration->status = "UpError";
                $migration->updated = date("Y-m-d H:i:s");
                $migration->Save();
                LogManager::getInstance()->error("Pro migration failed: " . $migration->file . " - " . $migObject->getLastError());
                return false;
            }
        } catch (\Throwable $e) {
            $migration->last_error = $e->getMessage();
            $migration->status = "UpError";
            $migration->updated = date("Y-m-d H:i:s");
            $migration->Save();
            LogManager::getInstance()->error("Pro migration error: " . $migration->file . " - " . $e->getMessage());
            return false;
        }

        $migration->status = "Up";
        $migration->updated = date("Y-m-d H:i:s");
        $migration->Save();
        LogManager::getInstance()->info("Pro migration completed: " . $migration->file);
        return true;
    }

    /**
     * Run all pending pro migrations
     *
     * @return void
     */
    public function runPendingMigrations()
    {
        $migrations = $this->getPendingMigrations();
        LogManager::getInstance()->info("Found " . count($migrations) . " pending pro migrations");
        foreach ($migrations as $migration) {
            $this->runMigrationUp($migration);
        }
    }

    /**
     * Ensure all pro migrations are queued and run
     *
     * @return void
     */
    public function ensureMigrations()
    {
        if (!is_dir($this->migrationPath)) {
            return;
        }

        $listFile = $this->migrationPath . 'list.php';
        if (!file_exists($listFile)) {
            return;
        }

        // Get the latest pro migration from the database
        $migration = new Migration();
        $latestMigrations = $migration->Find("file LIKE ? ORDER BY id DESC LIMIT 1", array('pro_%'));
        $latestMigration = !empty($latestMigrations) ? $latestMigrations[0] : null;

        include $listFile;
        /** @var array $proMigrationList */
        $expectedLatestFile = 'pro_' . $proMigrationList[0] . '.php';
        if (count($proMigrationList) > 0 && (empty($latestMigration) || $expectedLatestFile !== $latestMigration->file)) {
            LogManager::getInstance()->info("ensureProMigrations - execute migrations");
            $this->queueMigrations();
        }

        $this->runPendingMigrations();
    }
}
