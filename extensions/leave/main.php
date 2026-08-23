<?php
/**
 * Leave Management — package loader
 *
 * This file is loaded by core/include.common.php (and app/index.php) when the
 * leave extension is present. Leave ships as a *package* extension: instead of
 * the single-module `admin/<name>.php` layout, it contributes a set of core-style
 * modules (admin/leaves, modules/leaves, modules/leavecal) plus its own src tree
 * and migrations.
 *
 * It provides:
 * 1. Mapping of the modules that should be loaded from this package
 * 2. A custom autoloader for the classes in core/src
 * 3. Model-class registration + migration running hooks
 *
 * The PRO_* constant / ProModule* class names are the historical contract with
 * core (ProMigrationManager, MigrationRunner, app/index.php); they are kept as-is
 * so no core plumbing has to change. Leave itself is a free, bundled extension.
 */

// A single package owns these globals. If another package loader already ran,
// leave everything it registered alone rather than fatally redeclaring.
if (defined('PRO_PATH')) {
    return;
}

define('PRO_PATH', dirname(__FILE__));
define('PRO_SRC_PATH', PRO_PATH . '/core/src/');

/**
 * Package Module Configuration
 *
 * Maps module paths to their location inside this package.
 * Format: 'group/name' => true
 *
 * When a module is listed here, the system will load it from:
 * - PHP: <package>/core/{group}/{name}/index.php
 * - JS: <package>/web/{group}/src/{name}/
 */
class ProModuleConfig
{
    /** @var array */
    private static $proModules = [
        'admin/leaves' => true,
        'modules/leaves' => true,
        'modules/leavecal' => true,
    ];

    /** @var array */
    private static $proWebAssets = [
        'admin/leaves' => true,
        'modules/leaves' => true,
        'modules/leavecal' => true,
    ];

    /**
     * Check if a module should be loaded from the package directory
     */
    public static function isProModule(string $group, string $name): bool
    {
        $key = $group . '/' . $name;
        return isset(self::$proModules[$key]) && self::$proModules[$key];
    }

    /**
     * Check if web assets should be loaded from the package directory
     */
    public static function isProWebAsset(string $group, string $name): bool
    {
        $key = $group . '/' . $name;
        return isset(self::$proWebAssets[$key]) && self::$proWebAssets[$key];
    }

    /**
     * Get the package module path
     */
    public static function getProModulePath(string $group, string $name): string
    {
        return PRO_PATH . '/core/' . $group . '/' . $name;
    }

    /**
     * Get the package web asset path
     */
    public static function getProWebAssetPath(string $group, string $name): string
    {
        return PRO_PATH . '/web/' . $group . '/src/' . $name;
    }

    /**
     * Add a module to the package modules list
     */
    public static function registerProModule(string $group, string $name): void
    {
        $key = $group . '/' . $name;
        self::$proModules[$key] = true;
    }

    /**
     * Add a web asset to the package web assets list
     */
    public static function registerProWebAsset(string $group, string $name): void
    {
        $key = $group . '/' . $name;
        self::$proWebAssets[$key] = true;
    }
}

/**
 * Package Autoloader
 *
 * Custom autoloader for classes in <package>/core/src. These classes are not
 * loaded via Composer autoload.
 */
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    // e.g., Leaves\Common\Model\LeaveType -> Leaves/Common/Model/LeaveType.php
    $relativePath = str_replace('\\', '/', $class) . '.php';
    $filePath = PRO_SRC_PATH . $relativePath;

    if (file_exists($filePath)) {
        require_once $filePath;
        return true;
    }

    // Handle Pro\ namespace - strip the Pro\ prefix
    if (strpos($class, 'Pro\\') === 0) {
        $classWithoutPrefix = substr($class, 4); // Remove 'Pro\' prefix
        $relativePath = str_replace('\\', '/', $classWithoutPrefix) . '.php';
        $filePath = PRO_SRC_PATH . $relativePath;

        if (file_exists($filePath)) {
            require_once $filePath;
            return true;
        }
    }

    return false;
}, true, true); // prepend=true to check the package directory first

/**
 * Register Package Model Classes
 *
 * This function is called after BaseService is available to register
 * model classes from the package modules.
 */
function registerProModelClasses()
{
    $baseService = \Classes\BaseService::getInstance();

    // Leaves module model classes
    $baseService->addModelClass('LeaveType', '\\Leaves\\Common\\Model\\LeaveType');
    $baseService->addModelClass('LeaveRule', '\\Leaves\\Common\\Model\\LeaveRule');
    $baseService->addModelClass('LeavePeriod', '\\Leaves\\Common\\Model\\LeavePeriod');
    $baseService->addModelClass('WorkDay', '\\Leaves\\Common\\Model\\WorkDay');
    $baseService->addModelClass('HoliDay', '\\Leaves\\Common\\Model\\HoliDay');
    $baseService->addModelClass('LeaveGroup', '\\Leaves\\Common\\Model\\LeaveGroup');
    $baseService->addModelClass('LeaveGroupEmployee', '\\Leaves\\Common\\Model\\LeaveGroupEmployee');
    $baseService->addModelClass('EmployeeLeave', '\\Leaves\\Common\\Model\\EmployeeLeave');
    $baseService->addModelClass('EmployeeLeaveDay', '\\Leaves\\Common\\Model\\EmployeeLeaveDay');
    $baseService->addModelClass('EmployeeLeaveLog', '\\Leaves\\Common\\Model\\EmployeeLeaveLog');
    $baseService->addModelClass('EmployeeLeaveApprove', '\\Leaves\\Common\\Model\\EmployeeLeaveApprove');
    $baseService->addModelClass('EmployeeLeaveEntitlement', '\\Leaves\\Common\\Model\\EmployeeLeaveEntitlement');
    $baseService->addModelClass('LeaveStartingBalance', '\\Leaves\\Common\\Model\\LeaveStartingBalance');
}

/**
 * Run Package Migrations
 *
 * This function is called after BaseService is available to run
 * pending package migrations.
 */
function runProMigrations()
{
    try {
        \Utils\LogManager::getInstance()->info('Starting leave migrations...');
        $proMigrationManager = \Classes\Migration\ProMigrationManager::getInstance();
        $proMigrationManager->ensureMigrations();
        \Utils\LogManager::getInstance()->info('Leave migrations completed.');
    } catch (\Throwable $e) {
        \Utils\LogManager::getInstance()->error('Error running leave migrations: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
    }
}

// Register a callback to be called when BaseService is ready
// This is done via the initializeModuleManagers hook
class ProModuleInitializer
{
    /** @var bool */
    private static $initialized = false;
    /** @var bool */
    private static $migrationsRun = false;

    public static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
        registerProModelClasses();
    }

    /**
     * Run package migrations after core migrations have completed
     * This should be called from SettingsInitialize or similar
     */
    public static function runMigrations(): void
    {
        if (self::$migrationsRun) {
            return;
        }
        self::$migrationsRun = true;
        runProMigrations();
    }
}
