<?php


namespace Classes\Cron;

use Model\Cron;

class CronRegistry
{
    private static $registry = [];

    public static function addCron(string $name, IceTask $executor, int $frequency, string $type)
    {
        $cron = new Cron();
        $cron->name = $name;
        $cron->class = get_class($executor);
        $cron->frequency = $frequency;
        $cron->time = '';
        $cron->type = $type;
        $cron->status = 'Enabled';

        self::$registry[$name] = [
            'cron' => $cron,
            'exe' => $executor
        ];
    }

    public static function getRegisteredCrons()
    {
        return self::$registry;
    }

    public static function syncWithDatabase()
    {
        $cron = new Cron();
        $dbCrons = $cron->Find("status = ?", array('Enabled'));
        $cronRegistryCopy = [];
        $dbCronNames = [];
        foreach ($dbCrons as $dbCron) {
            $dbCronNames[] = $dbCron->name;
            if (isset(self::$registry[$dbCron->name])) {
                $dbCron->class = self::$registry[$dbCron->name]['cron']->class;
                self::$registry[$dbCron->name]['cron'] = $dbCron;
                if ($dbCron->status === 'Enabled') {
                    $cronRegistryCopy[$dbCron->name] = self::$registry[$dbCron->name];
                }
            }
        }

        $cronsNotInDB = array_diff(array_keys(self::$registry), $dbCronNames);
        foreach ($cronsNotInDB as $item) {
            $cronRegistryCopy[$item] = self::$registry[$item];
        }

        self::$registry = $cronRegistryCopy;
    }
}
