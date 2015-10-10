<?php
if(!class_exists('CalendarTools')) {
    class CalendarTools
    {

        const MODE_MONTH = "MONTH";
        const MODE_WEEK = "WEEK";
        const MODE_DAY = "DAY";

        public static function getCalendarMode($start, $end)
        {
            $diff = (intval($end) - intval($start)) / (60 * 60 * 24);
            if ($diff > 8) {
                return CalendarTools::MODE_MONTH;
            } else if ($diff > 2) {
                return CalendarTools::MODE_WEEK;
            } else {
                return CalendarTools::MODE_DAY;
            }

        }
    }
}