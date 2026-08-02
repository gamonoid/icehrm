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

    /**
     * Keys whose values must never reach the log. Matched case-insensitively against
     * the key name, so 'password', 'APP_PASSWORD' and 'new_password' are all covered.
     *
     * Kept here rather than at each call site so there is one list to extend when a new
     * credential-bearing parameter appears.
     */
    private static $sensitiveKeys = array(
        'csrf',
        'key',
        'signature',
        'authorization',
        'samlresponse',
        'hash',
        'code',
    );

    /**
     * Substrings that make a key sensitive wherever they appear, so APP_PASSWORD,
     * client_secret and refresh_token are caught without listing every variant.
     *
     * Kept separate from the exact list on purpose: 'key' is matched exactly because as
     * a substring it would redact ordinary words like 'monkey' and 'keywords'.
     */
    private static $sensitiveKeyFragments = array(
        'password',
        'passwd',
        'secret',
        'token',
        'apikey',
        'accesskey',
        'privatekey',
    );

    /**
     * Replace the values of credential-bearing keys with a placeholder, recursively.
     *
     * Use this on anything derived from a request before logging it. A raw
     * print_r($_REQUEST) writes the bearer token (which is also passed as a ?token=
     * query parameter), and on POST oauth/token it writes the user's password.
     *
     * @param mixed $data
     * @param int $depth guards against a pathological nesting depth
     * @return mixed
     */
    public static function redact($data, $depth = 0)
    {
        if (!is_array($data) || $depth > 8) {
            return $data;
        }

        $clean = array();
        foreach ($data as $key => $value) {
            $normalised = strtolower(str_replace(array('_', '-', ' '), '', (string) $key));

            $sensitive = in_array($normalised, self::$sensitiveKeys, true);
            if (!$sensitive) {
                foreach (self::$sensitiveKeyFragments as $fragment) {
                    if (strpos($normalised, $fragment) !== false) {
                        $sensitive = true;
                        break;
                    }
                }
            }

            if ($sensitive) {
                $clean[$key] = '***redacted***';
                continue;
            }

            $clean[$key] = is_array($value) ? self::redact($value, $depth + 1) : $value;
        }

        return $clean;
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
