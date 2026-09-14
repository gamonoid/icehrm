<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:20 PM
 */

namespace TimeSheets\User\Api;

use Classes\AbstractModuleManager;
use Classes\BaseService;
use Classes\IceResponse;
use TimeSheets\Common\Model\EmployeeTimeEntry;
use TimeSheets\Common\Model\EmployeeTimeSheet;
use TimeSheets\Rest\TimesheetRestEndPoint;

class TimeSheetsModulesManager extends AbstractModuleManager
{

    public function initializeUserClasses()
    {
        $this->addUserClass("EmployeeTimeSheet");
        $this->addUserClass("EmployeeTimeEntry");
    }

    public function initializeFieldMappings()
    {
    }

    public function initializeDatabaseErrorMappings()
    {
    }

    public function setupModuleClassDefinitions()
    {

        $this->addModelClass('EmployeeTimeSheet');
        $this->addModelClass('EmployeeTimeEntry');
        $this->addModelClass('QTDays');
    }

    public function setupRestEndPoints()
    {
        // Get user's timesheets
        \Classes\Macaw::get(
            REST_API_PATH . 'timesheets',
            function () {
                $restEndPoint = new TimesheetRestEndPoint();
                $restEndPoint->process('getMyTimesheets', []);
            }
        );

        // Create timesheet for current week
        \Classes\Macaw::post(
            REST_API_PATH . 'timesheets/create-current',
            function () {
                $restEndPoint = new TimesheetRestEndPoint();
                $restEndPoint->process('createCurrentWeekTimesheet', []);
            }
        );

        // Create timesheet for previous week (based on existing timesheet)
        \Classes\Macaw::post(
            REST_API_PATH . 'timesheets/(:num)/create-previous',
            function ($timesheetId) {
                $restEndPoint = new TimesheetRestEndPoint();
                $restEndPoint->process('createPreviousWeekTimesheet', [$timesheetId]);
            }
        );

        // Create timesheet for next week (based on existing timesheet)
        \Classes\Macaw::post(
            REST_API_PATH . 'timesheets/(:num)/create-next',
            function ($timesheetId) {
                $restEndPoint = new TimesheetRestEndPoint();
                $restEndPoint->process('createNextWeekTimesheet', [$timesheetId]);
            }
        );

        // Get timesheet details
        \Classes\Macaw::get(
            REST_API_PATH . 'timesheets/(:num)',
            function ($timesheetId) {
                $restEndPoint = new TimesheetRestEndPoint();
                $restEndPoint->process('getTimesheetDetails', [$timesheetId]);
            }
        );

        // Submit timesheet
        \Classes\Macaw::post(
            REST_API_PATH . 'timesheets/(:num)/submit',
            function ($timesheetId) {
                $restEndPoint = new TimesheetRestEndPoint();
                $restEndPoint->process('submitTimesheet', [$timesheetId]);
            }
        );

        // Add time entry to timesheet
        \Classes\Macaw::post(
            REST_API_PATH . 'timesheets/(:num)/entries',
            function ($timesheetId) {
                $restEndPoint = new TimesheetRestEndPoint();
                $restEndPoint->process('addTimeEntry', [$timesheetId]);
            }
        );

        // Update time entry
        \Classes\Macaw::post(
            REST_API_PATH . 'timesheets/entries/(:num)/update',
            function ($entryId) {
                $restEndPoint = new TimesheetRestEndPoint();
                $restEndPoint->process('updateTimeEntry', [$entryId]);
            }
        );

        // Delete time entry
        \Classes\Macaw::post(
            REST_API_PATH . 'timesheets/entries/(:num)/delete',
            function ($entryId) {
                $restEndPoint = new TimesheetRestEndPoint();
                $restEndPoint->process('deleteTimeEntry', [$entryId]);
            }
        );

        // Get direct reports' timesheets (for managers)
        \Classes\Macaw::get(
            REST_API_PATH . 'timesheets/direct-reports',
            function () {
                $restEndPoint = new TimesheetRestEndPoint();
                $restEndPoint->process('getDirectReportsTimesheets', []);
            }
        );

        // Get pending timesheets for approval
        \Classes\Macaw::get(
            REST_API_PATH . 'timesheets/direct-reports/pending',
            function () {
                $restEndPoint = new TimesheetRestEndPoint();
                $restEndPoint->process('getPendingTimesheets', []);
            }
        );

        // Approve timesheet
        \Classes\Macaw::post(
            REST_API_PATH . 'timesheets/(:num)/approve',
            function ($timesheetId) {
                $restEndPoint = new TimesheetRestEndPoint();
                $restEndPoint->process('approveTimesheet', [$timesheetId]);
            }
        );

        // Reject timesheet
        \Classes\Macaw::post(
            REST_API_PATH . 'timesheets/(:num)/reject',
            function ($timesheetId) {
                $restEndPoint = new TimesheetRestEndPoint();
                $restEndPoint->process('rejectTimesheet', [$timesheetId]);
            }
        );

        // Get available projects for time entries
        \Classes\Macaw::get(
            REST_API_PATH . 'timesheets/projects',
            function () {
                $restEndPoint = new TimesheetRestEndPoint();
                $restEndPoint->process('getProjects', []);
            }
        );
    }

    public function getInitializer()
    {
        return new TimeSheetsInitialize();
    }

    public function getDashboardItemData()
    {
        $data = array();
        $data['timeSheetHoursWorked'] = $this->getLastTimeSheetHours()->getData();
        return $data;
    }

    private function getLastTimeSheetHours()
    {
        $timeSheet = new EmployeeTimeSheet();
        $timeSheet->Load(
            "employee = ? order by date_end desc limit 1",
            array(BaseService::getInstance()->getCurrentProfileId())
        );

        if (empty($timeSheet->employee)) {
            return new IceResponse(IceResponse::SUCCESS, "0:00");
        }

        $timeSheetEntry = new EmployeeTimeEntry();
        $list = $timeSheetEntry->Find("timesheet = ?", array($timeSheet->id));

        $seconds = 0;
        foreach ($list as $entry) {
            $seconds += (strtotime($entry->date_end) - strtotime($entry->date_start));
        }

        $minutes = (int)($seconds/60);
        $rem = $minutes % 60;
        $hours = ($minutes - $rem)/60;
        if ($rem < 10) {
            $rem = "0".$rem;
        }
        return new IceResponse(IceResponse::SUCCESS, $hours.":".$rem);
    }

    public function initCalculationHooks()
    {
        $this->addCalculationHook(
            'TimeSheetsPayrollUtils_getApprovedTimeInTimeSheets',
            'Total Hours from Approved Time Sheets',
            TimeSheetsPayrollUtils::class,
            'getApprovedTimeInTimeSheets'
        );
    }
}
