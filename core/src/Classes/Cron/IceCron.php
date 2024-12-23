<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:33 AM
 */

namespace Classes\Cron;

use Model\Cron;
use Utils\LogManager;

class IceCron
{

    const MINUTELY = "Minutely";
    const HOURLY = "Hourly";
    const DAILY = "Daily";
    const WEEKLY = "Weekly";
    const MONTHLY = "Monthly";
    const YEARLY = "Yearly";

    /**
 * @var Cron $cron
*/
    private $cron;

    /**
 * @var IceTask
*/
    private $executor;

    public function __construct(Cron $cron, IceTask $executor)
    {
        $this->cron = $cron;
        $this->executor = $executor;
    }

    public function isRunNow()
    {
        $lastRunTime = $this->cron->lastrun;
        if (empty($lastRunTime)) {
            LogManager::getInstance()->debug("Cron ".$this->cron->name." is running since last run time is empty");
            return true;
        }

        $type = $this->cron->type;
        $frequency = intval($this->cron->frequency);
        $time = intval($this->cron->time);

        if (empty($frequency) || !is_int($frequency)) {
            LogManager::getInstance()->error(
                "Cron ".$this->cron->name." is not running since frequency is not an integer"
            );
            return false;
        }

        if ($type == self::MINUTELY) {
            $diff = (strtotime("now") - strtotime($lastRunTime));
            if (empty($time) || !is_int($time)) {
                if ($diff > 60) {
                    return true;
                }
            } else {
                if ($diff > 60 * $time) {
                    return true;
                }
            }
        } elseif ($type == self::HOURLY) {
            if (empty($time) || !is_int($time)) {
                if (date('H') != date('H', strtotime($lastRunTime))) {
                    return true;
                }
            } else {
                if (intval(date('i')) >= intval($time) && date('H') != date('H', strtotime($lastRunTime))) {
                    return true;
                }
            }
        } elseif ($type == self::DAILY) {
            if (empty($time) || !is_int($time)) {
                if (date('d') != date('d', strtotime($lastRunTime))) {
                    return true;
                }
            } else {
                if (intval(date('H')) >= intval($time) && date('d') != date('d', strtotime($lastRunTime))) {
                    return true;
                }
            }
        } elseif ($type == self::MONTHLY) {
            if (empty($time) || !is_int($time)) {
                if (date('m') != date('m', strtotime($lastRunTime))) {
                    return true;
                }
            } else {
                if (intval(date('d')) >= intval($time) && date('m') != date('m', strtotime($lastRunTime))) {
                    return true;
                }
            }
        } elseif ($type == self::YEARLY) {
            if (empty($time) || !is_int($time)) {
                if (date('Y') != date('Y', strtotime($lastRunTime))) {
                    return true;
                }
            } else {
                if (intval(date('m')) >= intval($time) && date('Y') != date('Y', strtotime($lastRunTime))) {
                    return true;
                }
            }
        }

        return false;
    }

    public function execute()
    {
        $this->executor->execute($this->cron);
        $this->cronCompleted();
    }

    private function cronCompleted()
    {
        $this->cron->lastrun = date("Y-m-d H:i:s");
        $ok = $this->cron->Save();
        if (!$ok) {
            LogManager::getInstance()->error("Error saving cron due to :".$this->cron->ErrorMsg());
        }
    }
}
