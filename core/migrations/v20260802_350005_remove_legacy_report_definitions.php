<?php

namespace Classes\Migration;

/**
 * Removes the legacy core report definitions from the Reports table.
 *
 * The legacy report execution path (core/service.php -> Classes\ReportHandler ->
 * Reports\{Admin,User}\Reports\* classes) has been removed. It was reachable by
 * any logged-in user and built SQL by concatenating request parameters
 * (employee/department) straight into IN (...) clauses. Reporting is now served
 * entirely by the advance_reports extension, which has its own parameterised
 * endpoints and registries and does not use any of the deleted classes.
 *
 * These rows are the seed report definitions that drove the deleted classes, so
 * they are now non-functional. They are removed by their class name (the Reports
 * .query column for Class-type reports) plus the one inline-SQL Query report, so
 * any custom user-created report rows are left untouched.
 */
class v20260802_350005_remove_legacy_report_definitions extends AbstractMigration
{

    public function up()
    {
        $sql = <<<'SQL'
DELETE FROM `Reports` WHERE `query` IN (
    'EmployeeAttendanceReport',
    'EmployeeTimeTrackReport',
    'EmployeeTimesheetReport',
    'ActiveEmployeeReport',
    'NewHiresEmployeeReport',
    'TerminatedEmployeeReport',
    'TravelRequestReport',
    'EmployeeTimeSheetData',
    'OvertimeReport',
    'OvertimeSummaryReport',
    'OvertimeRequestReport',
    'PayrollDataExport',
    'AssetUsageReport',
    'EmployeeLeavesReport',
    'EmployeeLeaveEntitlementReport',
    'ExpenseReport'
) OR (`type` = 'Query' AND `name` = 'Employee Details Report');
SQL;

        return $this->executeQuery($sql);
    }

    public function down()
    {
        return true;
    }
}
