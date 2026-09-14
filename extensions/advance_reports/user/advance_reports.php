<?php
$dir = \Classes\BaseService::getInstance()->getExtensionSourceDirectory(__DIR__);

require_once $dir.'Extension.php';
require_once $dir.'Controller.php';
require_once $dir.'ApiController.php';

// Migrations
require_once $dir.'Migrations/CreateTables.php';

// Reports
require_once $dir.'Reports/BaseUserReport.php';
require_once $dir.'Reports/UserReportRegistry.php';
require_once $dir.'Reports/UserLeavesReport.php';
require_once $dir.'Reports/UserTimeEntryReport.php';
require_once $dir.'Reports/UserAttendanceReport.php';
require_once $dir.'Reports/UserTimeTrackReport.php';
require_once $dir.'Reports/UserTravelRequestReport.php';
require_once $dir.'Reports/UserTimeSheetReport.php';
require_once $dir.'Reports/UserOvertimeReport.php';
require_once $dir.'Reports/UserOvertimeSummaryReport.php';
require_once $dir.'Reports/UserClientProjectTimeReport.php';
require_once $dir.'Reports/UserExpenseReport.php';
