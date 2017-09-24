<?php
namespace Utils;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LogManager
{

    private static $me;

    private $log;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$me)) {
            self::$me = new LogManager();
            self::$me->log = new Logger(APP_NAME);
            if (is_writable(ini_get('error_log'))) {
                self::$me->log->pushHandler(new StreamHandler(ini_get('error_log'), LOG_LEVEL));
            } elseif (is_writable(CLIENT_BASE_PATH.'data/app.log')) {
                self::$me->log->pushHandler(new StreamHandler(CLIENT_BASE_PATH.'data/app.log', LOG_LEVEL));
            }
        }
        return self::$me;
    }

    public function info($message)
    {
        $this->log->addInfo($message);
    }

    public function debug($message)
    {
        $this->log->addDebug($message);
    }

    public function error($message)
    {
        $this->log->addError($message);
    }
}
