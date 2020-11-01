<?php
namespace Charts\Admin\Api;

use Attendance\Common\Model\Attendance;
use Classes\IceResponse;
use Classes\SubActionManager;
use TimeSheets\Common\Model\EmployeeTimeEntry;

class ChartsActionManager extends SubActionManager
{

    public function getTimeUtilization($req)
    {

        if (empty($req->start)) {
            $req->start = date("Y-m-01");
        }

        if (empty($req->end)) {
            $req->end = date("Y-m-t", strtotime($req->start));
        }

        //Find Time Entries

        $employeeTimeEntry = new EmployeeTimeEntry();
        if (empty($req->employee)) {
            $timeEntryList = $employeeTimeEntry->Find(
                "date(date_start) >= ? and  date(date_end) <= ?",
                array($req->start, $req->end)
            );
        } else {
            $timeEntryList = $employeeTimeEntry->Find(
                "employee = ? and date(date_start) >= ? and  date(date_end) <= ?",
                array($req->employee, $req->start, $req->end)
            );
        }

        $seconds = 0;
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

        $attendance = new Attendance();
        if (empty($req->employee)) {
            $atteandanceList =  $attendance->Find(
                "date(in_time) >= ? and  date(out_time) <= ? and in_time < out_time",
                array($req->start, $req->end)
            );
        } else {
            $atteandanceList =  $attendance->Find(
                "employee = ? and date(in_time) >= ? and  date(out_time) <= ? and in_time < out_time",
                array($req->employee, $req->start, $req->end)
            );
        }

        $seconds = 0;
        $graphAttendanceArray = array();
        foreach ($atteandanceList as $entry) {
            $seconds = (strtotime($entry->out_time) - strtotime($entry->in_time));
            $key = date("Y-m-d", strtotime($entry->in_time));
            if (isset($graphAttendanceArray[$key])) {
                $graphAttendanceArray[$key] += $seconds;
            } else {
                $graphAttendanceArray[$key] = $seconds;
            }
        }

        $data = array();
        $data[] = array("key"=>"Hours in Attendance", "values"=>array());
        $data[] = array("key"=>"Hours Worked", "values"=>array());

        //Iterate date range

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod(new \DateTime($req->start), $interval, new \DateTime($req->end));
        /* @var \DateTime $dt */
        foreach ($period as $dt) {
            $key = $dt->format("Y-m-d");
            if (isset($graphAttendanceArray[$key])) {
                $data[0]['values'][] = array("x"=>$key, "y"=>round(($graphAttendanceArray[$key]/3600), 2));
            } else {
                $data[0]['values'][] = array("x"=>$key, "y"=>0);
            }

            if (isset($graphTimeArray[$key])) {
                $data[1]['values'][] = array("x"=>$key, "y"=>round(($graphTimeArray[$key]/3600), 2));
            } else {
                $data[1]['values'][] = array("x"=>$key, "y"=>0);
            }
        }

        return new IceResponse(IceResponse::SUCCESS, $data);
    }

    public function getAttendance($req)
    {

        if (empty($req->start)) {
            $req->start = date("Y-m-01");
        }

        if (empty($req->end)) {
            $req->end = date("Y-m-t", strtotime($req->start));
        }

        //Find Attendance Entries

        $attendance = new Attendance();
        if (empty($req->employee)) {
            $atteandanceList =  $attendance->Find(
                "date(in_time) >= ? and  date(out_time) <= ? and in_time < out_time",
                array($req->start, $req->end)
            );
        } else {
            $atteandanceList =  $attendance->Find(
                "employee = ? and date(in_time) >= ? and  date(out_time) <= ? and in_time < out_time",
                array($req->employee, $req->start, $req->end)
            );
        }

        $seconds = 0;
        $graphAttendanceArray = array();
        foreach ($atteandanceList as $entry) {
            $seconds = (strtotime($entry->out_time) - strtotime($entry->in_time));
            $key = date("Y-m-d", strtotime($entry->in_time));
            if (isset($graphAttendanceArray[$key])) {
                $graphAttendanceArray[$key] += $seconds;
            } else {
                $graphAttendanceArray[$key] = $seconds;
            }
        }

        $data[0] = array();
        $data[0] = array("key"=>"Attendance", "values"=>array());

        //Iterate date range

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod(new \DateTime($req->start), $interval, new \DateTime($req->end));

        /* @var \DateTime $dt */
        foreach ($period as $dt) {
            $key = $dt->format("Y-m-d");
            if (isset($graphAttendanceArray[$key])) {
                $data[0]['values'][] = array("x"=>$key, "y"=>round(($graphAttendanceArray[$key]/3600), 2));
            } else {
                $data[0]['values'][] = array("x"=>$key, "y"=>0);
            }
        }

        return new IceResponse(IceResponse::SUCCESS, $data);
    }
}
