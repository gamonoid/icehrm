<?php
namespace Company\Common\Model;

use Model\BaseModel;
use Utils\LogManager;

class Timezone extends BaseModel
{
    public $table = 'Timezones';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }

    public function getAnonymousAccess()
    {
        return array("get","element");
    }

    public function getTimezonesWithOffset()
    {
        $tz = new Timezone();
        $tzs = $tz->Find("1 = 1");
        $modifiedTimeZones = [];
        foreach ($tzs as $tz) {
            try {
                $z = new \DateTimeZone($tz->name);
                $c = new \DateTime(null, $z);
                $tz->details = sprintf("(%s) %s", $this->formatOffset($z->getOffset($c)), $tz->name);
                $modifiedTimeZones[] = $tz;
            } catch (\Exception $e) {
                LogManager::getInstance()->notifyException($e);
            }
        }

        usort($modifiedTimeZones, function ($a, $b) {
            return strcmp($a->details, $b->details);
        });
        return $modifiedTimeZones;
    }

    public function formatOffset($offset)
    {
        $hours = $offset / 3600;
        $remainder = $offset % 3600;
        $sign = $hours > 0 ? '+' : '-';
        $hour = (int) abs($hours);
        $minutes = (int) abs($remainder / 60);

        if ($hour == 0 and $minutes == 0) {
            $sign = ' ';
        }
        return 'GMT' . $sign . str_pad($hour, 2, '0', STR_PAD_LEFT)
        .':'. str_pad($minutes, 2, '0');
    }

    public function fieldValueMethods()
    {
        return ['getTimezonesWithOffset'];
    }
}
