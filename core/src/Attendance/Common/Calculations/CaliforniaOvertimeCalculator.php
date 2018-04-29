<?php
namespace Attendance\Common\Calculations;

use Classes\SettingsManager;

class CaliforniaOvertimeCalculator extends BasicOvertimeCalculator
{

    public function getData($atts, $actualStartDate, $aggregate = false)
    {

        if (count($atts) == 0) {
            return array();
        }

        $atSummary = $this->createAttendanceSummary($atts);
        $overtime = $this->calculateOvertime($atSummary);

        $workWeekStartDate = SettingsManager::getInstance()->getSetting('Attendance: Work Week Start Day');

        //TODO - just assume a work week from Sunday to Saturday

        //Find first Sunday in array
        $firstDate = null;
        $prvDate = null;
        $consecutiveWorkDays = 1;
        foreach ($overtime as $k => $v) {
            if ($firstDate == null) {
                $dw = date("w", strtotime($k));
                if ($dw == $workWeekStartDate) {
                    $firstDate = $k;
                }
            }

            if ($firstDate != null) {
                if ($prvDate != null && date('Y-m-d', strtotime('-1 day', strtotime($k))) == $prvDate) {
                    $consecutiveWorkDays++;
                    if ($consecutiveWorkDays == 7) {
                        //This is a double time day
                        $overtime[$k]['d'] = $overtime[$k]['d'] + $overtime[$k]['o'];
                        $overtime[$k]['o'] = 0;
                    }
                }

                //Resetting $consecutiveWorkDays at the start of the work week
                if ($prvDate != null && date("w", strtotime($k)) == $workWeekStartDate) {
                    $consecutiveWorkDays = 1;
                    $prvDate = null;
                }

                $prvDate = $k;
            }
        }

        $overtime = $this->removeAdditionalDays($overtime, $actualStartDate);
        if ($aggregate) {
            $overtime = $this->aggregateData($overtime);
            return $this->convertToHoursAggregated($overtime);
        } else {
            return $this->convertToHours($overtime);
        }
    }
}
