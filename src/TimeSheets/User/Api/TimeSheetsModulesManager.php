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
}
