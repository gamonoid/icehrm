<?php

namespace Utils;

use Company\Common\Model\Timezone;
use DateTime;
use DateTimeZone;

class TimeZonesUtils
{
    public static function getTimezoneById($id) 
    {
        $timezone = new Timezone();
        $timezone->Load('id = ?', [$id]);

        if (empty($timezone->id)) {
            $timezone = new Timezone();
            $timezone->Load('name = ?', ['UTC']);
        }

        return $timezone;
    }

    public static function getTimezoneOffset($timezone)
    {
        $time = new DateTime('now', new DateTimeZone($timezone));
        return $time->format('P');
    }

    public static function getTimezoneAbbreviation($timezone)
    {
        $time = new DateTime('now', new DateTimeZone($timezone));
        return $time->format('T');
    }
}
