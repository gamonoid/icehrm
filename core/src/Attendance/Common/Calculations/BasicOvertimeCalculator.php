<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/13/17
 * Time: 6:56 PM
 */

namespace Attendance\Common\Calculations;

use Classes\SettingsManager;

class BasicOvertimeCalculator
{

    public function createAttendanceSummary($atts)
    {

        $atTimeByDay = array();

        foreach ($atts as $atEntry) {
            if ($atEntry->out_time == "0000-00-00 00:00:00" || empty($atEntry->out_time)) {
                continue;
            }

            $atDate = date("Y-m-d", strtotime($atEntry->in_time));

            if (!isset($atTimeByDay[$atDate])) {
                $atTimeByDay[$atDate]   = 0;
            }

            $diff = strtotime($atEntry->out_time) - strtotime($atEntry->in_time);
            if ($diff < 0) {
                $diff = 0;
            }

            $atTimeByDay[$atDate] += $diff;
        }

        return $atTimeByDay;
    }

    public function calculateOvertime($atTimeByDay)
    {
        $overtimeStarts = SettingsManager::getInstance()->getSetting('Attendance: Overtime Start Hour');
        $doubletimeStarts = SettingsManager::getInstance()->getSetting('Attendance: Double time Start Hour');

        $overtimeStarts = (is_numeric($overtimeStarts))?floatval($overtimeStarts)*60*60:0;
        $doubletimeStarts = (is_numeric($doubletimeStarts))?floatval($doubletimeStarts)*60*60:0;

        $atTimeByDayNew = array();
        foreach ($atTimeByDay as $k => $v) {
            $atTimeByDayNewEntry = array("t"=>$v,"r"=>0,"o"=>0,"d"=>0);
            if ($overtimeStarts > 0 && $v > $overtimeStarts) {
                $atTimeByDayNewEntry["r"] = $overtimeStarts;
                if ($doubletimeStarts > 0 && $doubletimeStarts > $overtimeStarts) {
                    //calculate double time
                    if ($v > $doubletimeStarts) {
                        $atTimeByDayNewEntry['d'] =  $v - $doubletimeStarts;
                        $atTimeByDayNewEntry['o'] = $doubletimeStarts - $overtimeStarts;
                    } else {
                        $atTimeByDayNewEntry['d'] = 0 ;
                        $atTimeByDayNewEntry['o'] = $v - $overtimeStarts;
                    }
                } else {
                    //ignore double time
                    $atTimeByDayNewEntry['o'] = $v - $overtimeStarts;
                }
            } else {
                //ignore overtime
                $atTimeByDayNewEntry['r'] = $v;
            }

            $atTimeByDayNew[$k] = $atTimeByDayNewEntry;
        }

        return $atTimeByDayNew;
    }

    protected function removeAdditionalDays($atSummary, $actualStartDate)
    {
        $newAtSummary = array();
        foreach ($atSummary as $k => $v) {
            if (strtotime($k) >= strtotime($actualStartDate)) {
                $newAtSummary[$k] = $v;
            }
        }

        return $newAtSummary;
    }

    public function getData($atts, $actualStartDate, $aggregate = false)
    {
        $atSummary = $this->createAttendanceSummary($atts);
        $overtime = $this->calculateOvertime($this->removeAdditionalDays($atSummary, $actualStartDate));
        if ($aggregate) {
            $overtime = $this->aggregateData($overtime);
            return $this->convertToHoursAggregated($overtime);
        } else {
            return $this->convertToHours($overtime);
        }
    }

    public function getDataSeconds($atts, $actualStartDate, $aggregate = false)
    {
        $atSummary = $this->createAttendanceSummary($atts);
        $overtime = $this->calculateOvertime($this->removeAdditionalDays($atSummary, $actualStartDate));
        if ($aggregate) {
            $overtime = $this->aggregateData($overtime);
            return $overtime;
        } else {
            return $overtime;
        }
    }

    public function convertToHours($overtime)
    {
        foreach ($overtime as $k => $v) {
            $overtime[$k]['t'] =  $this->convertToHoursAndMinutes($overtime[$k]['t']);
            $overtime[$k]['r'] =  $this->convertToHoursAndMinutes($overtime[$k]['r']);
            $overtime[$k]['o'] =  $this->convertToHoursAndMinutes($overtime[$k]['o']);
            $overtime[$k]['d'] =  $this->convertToHoursAndMinutes($overtime[$k]['d']);
        }

        return $overtime;
    }

    public function convertToHoursAggregated($overtime)
    {
        $overtime['t'] =  $this->convertToHoursAndMinutes($overtime['t']);
        $overtime['r'] =  $this->convertToHoursAndMinutes($overtime['r']);
        $overtime['o'] =  $this->convertToHoursAndMinutes($overtime['o']);
        $overtime['d'] =  $this->convertToHoursAndMinutes($overtime['d']);

        return $overtime;
    }

    protected function aggregateData($overtime)
    {
        $ag = array("t"=>0,"r"=>0,"o"=>0,"d"=>0);
        foreach ($overtime as $k => $v) {
            $ag['t'] += $v['t'];
            $ag['r'] += $v['r'];
            $ag['o'] += $v['o'];
            $ag['d'] += $v['d'];
        }

        return $ag;
    }

    public function convertToHoursAndMinutes($val)
    {
        $sec = $val % 60;
        $minutesTot = ($val - $sec)/60;

        $minutes = $minutesTot % 60;
        $hours = ($minutesTot - $minutes)/60;

        if ($hours < 10) {
            $hours = "0".$hours;
        }
        if ($minutes < 10) {
            $minutes = "0".$minutes;
        }

        return $hours.":".$minutes;
    }
}
