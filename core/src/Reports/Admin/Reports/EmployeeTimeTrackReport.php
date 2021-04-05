<?php
namespace Reports\Admin\Reports;

use Attendance\Common\Model\Attendance;
use Company\Common\Model\CompanyStructure;
use Employees\Common\Model\Employee;
use Reports\Admin\Api\ClassBasedReportBuilder;
use Reports\Admin\Api\ReportBuilderInterface;
use TimeSheets\Common\Model\EmployeeTimeEntry;
use Utils\LogManager;

class EmployeeTimeTrackReport extends ClassBasedReportBuilder implements ReportBuilderInterface
{
    public function getData($report, $req)
    {

        LogManager::getInstance()->info(json_encode($report));
        LogManager::getInstance()->info(json_encode($req));

        if (empty($req['period'])
            && (
                empty($req['date_start'])
                || 'NULL' === $req['date_start']
                || empty($req['date_end'])
                || 'NULL' === $req['date_end']
            )
        ) {
            $req['period'] = 'Current Month';
        }

        $employeeTimeEntry = new EmployeeTimeEntry();

        $timeEntryList = $employeeTimeEntry->Find(
            "employee = ? and date(date_start) >= ? and  date(date_end) <= ?",
            array($req['employee'], $req['date_start'], $req['date_end'])
        );


        $graphTimeArray = array();
        foreach ($timeEntryList as $entry) {
            $seconds = (strtotime($entry->date_end) - strtotime($entry->date_start));
            $key = date("Y-m-d", strtotime($entry->date_end));
            if (isset($graphTimeArray[$key])) {
                $graphTimeArray[$key] += $seconds;
            } else {
                $graphTimeArray[$key] = $seconds;
            }
        }

        //$minutes = (int)($seconds/60);
        //Find Attendance Entries

        $req = $this->setRequestDatesBasedOnThePeriod($req);

        $attendance = new Attendance();
        $atteandanceList =  $attendance->Find(
            "employee = ? and date(in_time) >= ? and  date(out_time) <= ? and in_time < out_time",
            array($req['employee'], $req['date_start'], $req['date_end'])
        );

        $graphAttendanceArray = array();
        $firstTimeInArray = array();
        $lastTimeOutArray = array();
        foreach ($atteandanceList as $entry) {
            $seconds = (strtotime($entry->out_time) - strtotime($entry->in_time));
            $key = date("Y-m-d", strtotime($entry->in_time));
            if (isset($graphAttendanceArray[$key])) {
                $graphAttendanceArray[$key] += $seconds;
                $lastTimeOutArray[$key] = $entry->out_time;
            } else {
                $graphAttendanceArray[$key] = $seconds;
                $firstTimeInArray[$key] = $entry->in_time;
                $lastTimeOutArray[$key] = $entry->out_time;
            }
        }


        /////////////////////////////////////////

        $employeeObject = new Employee();
        $employeeObject->Load("id = ?", array($req['employee']));

        $company = new CompanyStructure();
        $company->Load('id = ?', [$employeeObject->department]);

        $reportData = [];
        $reportData[] = [
            "Date",
            "First Punch-In Time",
            "Last Punch-Out Time",
            "Time in Attendance (Hours)",
            "Time in Time-sheets (Hours)",
            ];
        $reportData[] = ["Employee:",$employeeObject->first_name." ".$employeeObject->last_name,"","",""];
        $reportData[] = ["Department:",$company->title,"","",""];
        $reportData[] = ["Total Days:","","","",""];


        //Iterate date range

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod(
            new \DateTime($req['date_start']),
            $interval,
            (new \DateTime($req['date_end']))->modify('+1 day')
        );

        $totalHoursOffice = 0;
        $totalHoursTimeSheets = 0;
        $totalDaysForThePeriod = 0;

        foreach ($period as $dt) {
            $dataRow = array();
            $key = $dt->format("Y-m-d");

            if (!isset($firstTimeInArray[$key])) {
                continue;
            }

            $totalDaysForThePeriod++;
            $dataRow[] = $key;

            if (isset($firstTimeInArray[$key])) {
                $dataRow[] = $firstTimeInArray[$key];
            } else {
                $dataRow[] = "Not Found";
            }

            if (isset($lastTimeOutArray[$key])) {
                $dataRow[] = $lastTimeOutArray[$key];
            } else {
                $dataRow[] = "Not Found";
            }

            if (isset($graphAttendanceArray[$key])) {
                $dataRow[] = round(($graphAttendanceArray[$key]/3600), 2);
            } else {
                $dataRow[] = 0;
            }

            if (isset($graphTimeArray[$key])) {
                $dataRow[] = round(($graphTimeArray[$key]/3600), 2);
            } else {
                $dataRow[] = 0;
            }

            $totalHoursOffice += $dataRow[3];
            $totalHoursTimeSheets += $dataRow[4];

            $dataRow[3] = number_format($dataRow[3], 2, '.', '');
            $dataRow[4] = number_format($dataRow[4], 2, '.', '');

            $reportData[] = $dataRow;
        }

        $reportData[3][1] = $totalDaysForThePeriod;

        $totalHoursOffice = number_format($totalHoursOffice, 2, '.', '');
        $totalHoursTimeSheets = number_format($totalHoursTimeSheets, 2, '.', '');

        $reportData[] = ["Total","","",$totalHoursOffice,$totalHoursTimeSheets];

        return $reportData;
    }

    private function setRequestDatesBasedOnThePeriod($req)
    {
        if (empty($req['period'])) {
            return $req;
        }

        if ($req['period'] === 'Current Month') {
            $req['date_start'] = date('Y-m-01', strtotime('now'));
            $req['date_end'] = date('Y-m-d', strtotime('now'));
        } elseif ($req['period'] === 'Last Month') {
            $req['date_start'] = date('Y-m-d', strtotime('first day of last month'));
            $req['date_end'] = date('Y-m-d', strtotime('last day of last month'));
        } elseif ($req['period'] === 'Last Week') {
            $req['date_start'] = date("Y-m-d", strtotime("-7 days"));
            $req['date_end'] = date('Y-m-d', strtotime('now'));
        } elseif ($req['period'] === 'Last 2 Weeks') {
            $req['date_start'] = date("Y-m-d", strtotime("-14 days"));
            $req['date_end'] = date('Y-m-d', strtotime('now'));
        }

        return $req;
    }
}
