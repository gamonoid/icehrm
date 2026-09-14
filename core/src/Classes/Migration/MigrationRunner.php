<?php

namespace Classes\Migration;

use Classes\BaseService;
use Utils\LogManager;

/**
 * The single place that applies every pending migration — core, extension and pro.
 *
 * Previously this sequence lived only inside Settings\Admin\Api\SettingsInitialize, so
 * schema changes were applied only when an Admin's request happened to initialise the
 * settings module. A Manager or Employee logging in first met the NEW code against the
 * OLD schema, which surfaces as "Unknown column ..." rather than as anything that hints
 * at a pending upgrade. After a file-level update (see the updater) that window is
 * exactly when people log in.
 *
 * runAll() is therefore called on every successful interactive login, whatever the
 * user's level. It is cheap when there is nothing to do — one indexed lookup per
 * migration set — and it can never block a login: any failure is logged and swallowed,
 * because a half-applied schema is a problem to investigate, not a reason to lock
 * everybody out of the application.
 *
 * ORDER MATTERS. Core migrations first (extension and pro migrations may reference
 * tables or columns core creates), then extensions, then pro.
 */
class MigrationRunner
{
    /** Advisory lock name. Scoped by database so two IceHRM installs on one MySQL server do not block each other. */
    const LOCK_PREFIX = 'icehrm_migrations_';

    /** Seconds a migration run may hold the lock before another process assumes it died. */
    const LOCK_TIMEOUT = 0;

    /** @var bool one attempt per request, however many times this is called */
    private static $attempted = false;

    /**
     * Apply every pending migration, once per request.
     *
     * @param bool $serialise take an advisory lock so only one process migrates at a
     *                        time. Costs two round trips to MySQL, so it is used on the
     *                        LOGIN path — where a post-upgrade rush of logins would
     *                        otherwise run the same DDL concurrently — and not on the
     *                        per-request path, which runs on every authenticated
     *                        request and where those two queries measured as a ~30%
     *                        latency increase.
     * @return void
     */
    public static function runAll($serialise = false)
    {
        if (self::$attempted) {
            return;
        }
        self::$attempted = true;

        $locked = false;
        try {
            if ($serialise) {
                // Not waited on: a second login has nothing useful to do while the
                // first migrates, and blocking would just stall that login.
                if (!self::acquireLock()) {
                    LogManager::getInstance()->info(
                        'MigrationRunner: another process is already applying migrations, skipping'
                    );
                    return;
                }
                $locked = true;
            }

            self::runCoreMigrations();
            self::runExtensionMigrations();
            self::runProMigrations();
        } catch (\Throwable $e) {
            // Never propagate: this runs during login, and a schema problem is
            // something to investigate — not a reason to lock everybody out.
            LogManager::getInstance()->error(
                'MigrationRunner: ' . $e->getMessage() . ' - ' . $e->getTraceAsString()
            );
        }

        if ($locked) {
            self::releaseLock();
        }
    }

    private static function runCoreMigrations()
    {
        BaseService::getInstance()->getMigrationManager()->ensureMigrations();
    }

    private static function runExtensionMigrations()
    {
        $migrations = BaseService::getInstance()->getExtensionMigrations();
        if (empty($migrations)) {
            return;
        }
        BaseService::getInstance()->getMigrationManager()->ensureExtensionMigrations($migrations);
    }

    private static function runProMigrations()
    {
        // Loaded only on Pro installs, by extensions-pro/leave_and_performance/main.php.
        if (class_exists('ProModuleInitializer')) {
            \ProModuleInitializer::runMigrations();
        }
    }

    /**
     * MySQL advisory lock. Connection-scoped, so it is released automatically if the
     * process dies mid-migration rather than wedging every later login.
     *
     * @return bool true when this process owns the lock
     */
    private static function acquireLock()
    {
        try {
            $rows = BaseService::getInstance()->getDB()->Execute(
                'SELECT GET_LOCK(?, ?) AS acquired',
                array(self::lockName(), self::LOCK_TIMEOUT)
            );
            return is_array($rows) && isset($rows[0]['acquired']) && (int) $rows[0]['acquired'] === 1;
        } catch (\Throwable $e) {
            // A database that cannot do advisory locks should not stop migrations from
            // running at all — fall back to the previous (unlocked) behaviour.
            LogManager::getInstance()->info('MigrationRunner: advisory lock unavailable, proceeding without it');
            return true;
        }
    }

    private static function releaseLock()
    {
        try {
            BaseService::getInstance()->getDB()->Execute(
                'SELECT RELEASE_LOCK(?)',
                array(self::lockName())
            );
        } catch (\Throwable $e) {
            // Connection-scoped, so it is released when the request ends regardless.
        }
    }

    private static function lockName()
    {
        $database = defined('APP_DB') ? APP_DB : 'icehrm';
        // MySQL truncates lock names over 64 chars, which would silently merge the
        // locks of two installs whose database names share a long prefix.
        return substr(self::LOCK_PREFIX . $database, 0, 64);
    }
}
