<?php
namespace Utils;

use Classes\BaseService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LogManager
{
    private static $me;
    private $active = null;

    private $log;

    private $logCollector;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$me)) {
            self::$me = new LogManager();
            self::$me->log = new Logger(APP_NAME);

            if (defined('LOG_STDERR') && LOG_STDERR === '1') {
                self::$me->log->pushHandler(new StreamHandler('php://stderr', LOG_LEVEL));
            } elseif (is_writable(ini_get('error_log'))) {
                self::$me->log->pushHandler(new StreamHandler(ini_get('error_log'), LOG_LEVEL));
            } elseif (is_writable(BaseService::getInstance()->getDataDirectory().'app.log')) {
                self::$me->log->pushHandler(
                    new StreamHandler(
                        BaseService::getInstance()->getDataDirectory().'app.log',
                        LOG_LEVEL
                    )
                );
            } else {
                self::$me->log->pushHandler(new StreamHandler('php://stderr', LOG_LEVEL));
            }
        }
        return self::$me;
    }

    public function info($message)
    {
        $this->log->addInfo(sprintf('(client=%s) %s', CLIENT_NAME, $message));
    }

    public function debug($message)
    {
        $this->log->addDebug(sprintf('(client=%s) %s', CLIENT_NAME, $message));
    }

    public function error($message)
    {
        $this->log->addError(sprintf('(client=%s) %s', CLIENT_NAME, $message));
    }

    public function collectLogs($logMessage)
    {
        $this->logCollector[] = sprintf('(client=%s) %s', CLIENT_NAME, $logMessage);
    }

    public function flushCollectedLogs()
    {
        $this->logCollector = [];
    }

    /**
     * @return array
     */
    public function getLogCollector()
    {
        return $this->logCollector;
    }

    public function notifyException($error)
    {
        if ($this->isNewRelicActive()) {
            newrelic_notice_error(sprintf('(client=%s) %s', CLIENT_NAME, $error->getMessage()), $error);
        }
    }

    private function isNewRelicActive()
    {
        if (is_null($this->active)) {
            $this->active = extension_loaded('newrelic');
        }
        return $this->active;
    }
}
