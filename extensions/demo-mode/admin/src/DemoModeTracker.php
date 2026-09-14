<?php

namespace DemoModeAdmin;

use Classes\SettingsManager;
use DemoModeAdmin\Common\Model\DemoDataEntry;

/**
 * Tracks database entries created when Demo Mode is enabled.
 * This class is called from BaseModel after successful inserts.
 */
class DemoModeTracker
{
    private static $enabled = null;
    private static $isTracking = false;

    // Maximum number of entries allowed in DemoDataEntries table
    public const MAX_DEMO_ENTRIES = 15000;

    // Tables to exclude from tracking (system tables)
    private static $excludedTables = [
        'Settings',
        'DemoDataEntries',
        'Users',
        'Migrations',
        'SystemTasks',
        'AuditLog',
        'Notifications',
        'RestAccessTokens',
        'UserReports',
        'Sessions',
    ];

    /**
     * Check if demo mode is enabled
     */
    public static function isDemoModeEnabled(): bool
    {
        if (self::$enabled === null) {
            self::$enabled = SettingsManager::getInstance()->getSetting('System: Demo Mode') === '1';
        }
        return self::$enabled;
    }

    /**
     * Clear the cached demo mode status (call when setting changes)
     */
    public static function clearCache(): void
    {
        self::$enabled = null;
    }

    /**
     * Track a newly inserted record if demo mode is enabled
     *
     * @param string $tableName The table name where record was inserted
     * @param int $recordId The ID of the newly inserted record
     */
    public static function trackInsert(string $tableName, int $recordId): void
    {
        // Prevent recursive tracking
        if (self::$isTracking) {
            return;
        }

        // Skip if demo mode is not enabled
        if (!self::isDemoModeEnabled()) {
            return;
        }

        // Skip excluded tables
        if (in_array($tableName, self::$excludedTables)) {
            return;
        }

        // Skip if record ID is invalid
        if ($recordId <= 0) {
            return;
        }

        // Skip if we've reached the maximum number of demo entries
        if (self::getDemoEntryCount() >= self::MAX_DEMO_ENTRIES) {
            return;
        }

        try {
            self::$isTracking = true;

            // Determine data type from table name
            $dataType = self::getDataTypeFromTable($tableName);

            $entry = new DemoDataEntry();
            $entry->table_name = $tableName;
            $entry->record_id = $recordId;
            $entry->data_type = $dataType;
            $entry->created = date('Y-m-d H:i:s');
            $entry->Save();

        } catch (\Exception $e) {
            // Silently fail to not disrupt normal operations
            error_log('DemoModeTracker error: ' . $e->getMessage());
        } finally {
            self::$isTracking = false;
        }
    }

    /**
     * Map table names to data types for easier categorization
     */
    private static function getDataTypeFromTable(string $tableName): string
    {
        $typeMap = [
            'Employees' => 'employee',
            'Attendance' => 'attendance',
            'EmployeeTimeSheets' => 'timesheet',
            'EmployeeTimeEntry' => 'timesheet_entry',
            'EmployeeLeaves' => 'leave',
            'EmployeeLeaveDays' => 'leave_day',
            'Payroll' => 'payroll',
            'PayrollData' => 'payroll_data',
            'EmployeeLeaveLog' => 'leave_log',
            'EmployeeSalary' => 'salary',
            'EmployeeSkills' => 'employee_data',
            'EmployeeEducation' => 'employee_data',
            'EmployeeCertifications' => 'employee_data',
            'EmployeeLanguages' => 'employee_data',
            'EmergencyContacts' => 'employee_data',
            'EmployeeDependents' => 'employee_data',
            'EmployeeDocuments' => 'employee_data',
            'Clients' => 'client',
            'Projects' => 'project',
            'EmployeeProjects' => 'employee_project',
            'EmployeeExpenses' => 'expense',
            'Job' => 'job',
            'Candidates' => 'candidate',
            'Applications' => 'application',
            'EmployeeCareer' => 'employee_data',
            'EmployeeEducations' => 'employee_data',
            'EmployeeTeams' => 'team',
            'EmployeeTeamMembers' => 'team_member',
            'PerformanceReviews' => 'performance_review',
            'EmployeeGoals' => 'employee_goal',
            'ReviewFeedbacks' => 'review_feedback',
            'TaskList' => 'task_list',
            'TaskListAssignment' => 'task_assignment',
            'Content' => 'task_content',
        ];

        return $typeMap[$tableName] ?? 'other';
    }

    /**
     * Enable demo mode programmatically
     */
    public static function enableDemoMode(): bool
    {
        $result = SettingsManager::getInstance()->setSetting('System: Demo Mode', '1');
        self::clearCache();
        return $result !== false;
    }

    /**
     * Disable demo mode programmatically
     */
    public static function disableDemoMode(): bool
    {
        $result = SettingsManager::getInstance()->setSetting('System: Demo Mode', '0');
        self::clearCache();
        return $result !== false;
    }

    /**
     * Get the current count of demo data entries
     */
    public static function getDemoEntryCount(): int
    {
        try {
            $entry = new DemoDataEntry();
            $entries = $entry->Find('1=1');
            return count($entries);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Check if demo entries limit has been reached
     */
    public static function isLimitReached(): bool
    {
        return self::getDemoEntryCount() >= self::MAX_DEMO_ENTRIES;
    }
}
