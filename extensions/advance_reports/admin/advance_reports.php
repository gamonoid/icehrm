<?php
$dir = \Classes\BaseService::getInstance()->getExtensionSourceDirectory(__DIR__);

require_once $dir.'Extension.php';
require_once $dir.'Controller.php';
require_once $dir.'ApiController.php';

// Migrations
require_once $dir.'Migrations/CreateTables.php';
require_once $dir.'Migrations/RemoveReportFilesModule.php';

// Reports
require_once $dir.'Reports/BaseReport.php';
require_once $dir.'Reports/ReportRegistry.php';
require_once $dir.'Reports/ActiveEmployeeReport.php';
require_once $dir.'Reports/NewHiresEmployeeReport.php';
require_once $dir.'Reports/TerminatedEmployeeReport.php';
require_once $dir.'Reports/EmployeeDetailsReport.php';
require_once $dir.'Reports/EmployeeAttendanceReport.php';
require_once $dir.'Reports/EmployeeTimeTrackReport.php';
require_once $dir.'Reports/EmployeeTimeEntryReport.php';
require_once $dir.'Reports/EmployeeTimeSheetReport.php';
require_once $dir.'Reports/OvertimeReport.php';
require_once $dir.'Reports/OvertimeSummaryReport.php';
require_once $dir.'Reports/OvertimeRequestReport.php';
require_once $dir.'Reports/EmployeeLeavesReport.php';
require_once $dir.'Reports/EmployeeLeaveEntitlementReport.php';
require_once $dir.'Reports/TravelRequestReport.php';
require_once $dir.'Reports/ExpenseReport.php';
require_once $dir.'Reports/CompanyAssetReport.php';
