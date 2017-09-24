<?php
namespace Reports\User\Reports;

use Classes\BaseService;
use Classes\SettingsManager;
use Leaves\Common\Model\HoliDay;
use Leaves\Common\Model\WorkDay;
use Metadata\Common\Model\Country;
use Reports\Admin\Api\PDFReportBuilder;
use Reports\Admin\Api\PDFReportBuilderInterface;
use Employees\Common\Model\Employee;
use Projects\Common\Model\Client;
use Projects\Common\Model\Project;
use TimeSheets\Common\Model\EmployeeTimeEntry;
use Utils\CalendarTools;

class ClientProjectTimeReport extends PDFReportBuilder implements PDFReportBuilderInterface
{
    public function getData($report, $request)
    {
        $data = $this->getDefaultData();

        $data['company'] = SettingsManager::getInstance()->getSetting('Company: Name');

        $client = new Client();
        $client->Load("id = ?", array($request['client']));
        $data['client'] = $client->name;

        $data['period'] = $request['date_start']." to ".$request['date_end'];

        $project = new Project();
        $projects = $project->Find("client = ?", array($request['client']));

        $projectsStr = "";
        $projectIds = '';
        $projectsMap = array();
        foreach ($projects as $pro) {
            $projectIds[] = $pro->id;
            $projectsMap[$pro->id] = $pro->name;
        }

        $employeeId = BaseService::getInstance()->getCurrentProfileId();
        $employee = new Employee();
        $employee->Load("id = ?", array($employeeId));

        $data['employee'] = $employee->first_name." ".$employee->last_name;

        $employeeTimeEntry = new EmployeeTimeEntry();
        $timeEntryList = $employeeTimeEntry->Find(
            "employee = ? and date(date_start) >= ? and  date(date_end) <= ? and project in ("
            .implode(",", $projectIds).") order by date_start",
            array($employeeId, $request['date_start'], $request['date_end'])
        );

        $totalHours = 0;
        $nonWorkingDayHours = 0;

        $projectTimes = array();

        $timeEntryListNew = array();

        $country = new Country();
        $country->Load("code = ?", $employee->country);

        $countryCode = null;
        if (!empty($country->id)) {
            $countryCode = $country->id;
        }

        $projectsWorked = array();

        foreach ($timeEntryList as $timeEntry) {
            if (!in_array($timeEntry->project, $projectsWorked)) {
                $projectsWorked[] = $timeEntry->project;
            }

            $entry = new \stdClass();
            $entry->date = date("d.m.Y", strtotime($timeEntry->date_start));
            $entry->startTime = $timeEntry->time_start;
            $entry->endTime = $timeEntry->time_end;
            $entry->details = $timeEntry->details;
            $entry->project = $projectsMap[$timeEntry->project];
            $entry->duration = CalendarTools::getTimeDiffInHours(
                $timeEntry->date_start,
                $timeEntry->date_end
            );
            $timeEntryListNew[] = $entry;

            $totalHours += $entry->duration;

            $isWorkingDay = $this->isWorkingDay($timeEntry->date_start, $countryCode);

            if (!$isWorkingDay) {
                $nonWorkingDayHours += $entry->duration;
            }

            if (!isset($projectTimes[$projectsMap[$timeEntry->project]])) {
                $projectTimes[$projectsMap[$timeEntry->project]] = 0;
            }

            $projectTimes[$projectsMap[$timeEntry->project]] += $entry->duration;
        }

        foreach ($projects as $pro) {
            if (in_array($pro->id, $projectsWorked)) {
                if ($projectsStr != "") {
                    $projectsStr .= " ,";
                }
                $projectsStr .= $pro->name;
            }
        }

        $data['projects'] = $projectsStr;

        $data['entries'] = $timeEntryListNew;
        $data['projectTimes'] = $projectTimes;
        $data['totalHours'] = $totalHours;
        $data['totalHoursWorking'] = $totalHours - $nonWorkingDayHours;
        $data['totalHoursNonWorking'] = $nonWorkingDayHours;
        return $data;
    }

    private function isHoliday($date, $countryId)
    {

        $hd = new HoliDay();
        $allHolidays = $hd->Find(
            "dateh = ?  and country IS NULL",
            array(date('Y-m-d', strtotime($date)))
        );

        if (count($allHolidays) > 0) {
            return true;
        }

        if (!empty($countryId)) {
            $countryHolidays = $hd->Find(
                "dateh = ? and country = ?",
                array(date('Y-m-d', strtotime($date)), $countryId)
            );

            if (count($countryHolidays) > 0) {
                return true;
            }
        }

        return false;
    }

    private function isWorkingDay($date, $countryId)
    {
        $day = date("l", strtotime($date));

        $isHoliday = $this->isHoliday($date, $countryId);

        if ($isHoliday) {
            return false;
        }

        $wdCountry = new WorkDay();
        $wdCountry->Load("name = ? and country = ?", array($day, $countryId));
        if (!empty($wdCountry->id)) {
            if ($wdCountry->status == 'Full Day' || $wdCountry->status == 'Half Day') {
                return true;
            } else {
                return false;
            }
        } else {
            $wdAll = new WorkDay();
            $wdAll->Load("name = ? and country IS NULL", array($day));
            if ($wdAll->status == 'Full Day' || $wdAll->status == 'Half Day') {
                return true;
            } else {
                return false;
            }
        }
    }

    public function getTemplate()
    {
        return "client_project_time_report.html";
    }
}
