<?php
namespace MarketplaceAdmin;

use Classes\BaseService;
use Classes\SettingsManager;
use Employees\Common\Model\Employee;
use Users\Common\Model\User;

/**
 * Collects lightweight debug/health stats about this installation for the
 * connected icehrm.com server (surfaced via marketplace/debug-stats).
 */
class DebugStatsService
{
    public function getStats()
    {
        return array(
            'company_name'       => $this->getCompanyName(),
            'mysql_version'      => $this->getMysqlVersion(),
            'php_version'        => phpversion(),
            'employees_active'   => $this->countEmployees('status = ?', array('Active')),
            'employees_inactive' => $this->countEmployees('status <> ?', array('Active')),
            'users_count'        => $this->countUsers(),
            'last_fatal_errors'  => $this->getLastFatalErrors(10),
        );
    }

    private function getCompanyName()
    {
        try {
            return SettingsManager::getInstance()->getSetting('Company: Name');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getMysqlVersion()
    {
        try {
            $rows = BaseService::getInstance()->getDB()->Execute('SELECT VERSION() AS v');
            if (is_array($rows) && isset($rows[0]['v'])) {
                return $rows[0]['v'];
            }
        } catch (\Throwable $e) {
            // fall through
        }
        return null;
    }

    private function countEmployees($where, $binds)
    {
        try {
            return intval((new Employee())->Count($where, $binds));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function countUsers()
    {
        try {
            // Count requires a WHERE clause; "1 = 1" counts every row.
            return intval((new User())->Count('1 = ?', array(1)));
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * The last $limit fatal-error entries from the PHP/app error log (newest
     * last). Only the entry header lines (timestamp + message) are returned;
     * multi-line stack traces are omitted for brevity. Reads only the tail of
     * the file so it stays fast on very large logs.
     *
     * @param int $limit
     * @return string[]
     */
    private function getLastFatalErrors($limit)
    {
        $path = ini_get('error_log');
        if (empty($path) || !is_file($path) || !is_readable($path)) {
            return array();
        }

        $lines = $this->tailLines($path, 4000, 2 * 1024 * 1024);
        $fatals = array();
        foreach ($lines as $line) {
            // PHP fatals log as "PHP Fatal error: ..."; the app/Monolog logs
            // uncaught errors at CRITICAL. Stack-trace continuation lines don't
            // contain these markers, so only the entry headers are captured.
            if (stripos($line, 'Fatal error') !== false || stripos($line, '.CRITICAL:') !== false) {
                $fatals[] = rtrim($line);
            }
        }
        if (count($fatals) > $limit) {
            $fatals = array_slice($fatals, -$limit);
        }
        return array_values($fatals);
    }

    /**
     * Read at most the last $maxBytes of a file and return up to $maxLines of
     * its trailing lines.
     *
     * @param string $path
     * @param int    $maxLines
     * @param int    $maxBytes
     * @return string[]
     */
    private function tailLines($path, $maxLines, $maxBytes)
    {
        $size = @filesize($path);
        $fp = @fopen($path, 'rb');
        if ($fp === false || $size === false) {
            return array();
        }
        $read = (int) min($size, $maxBytes);
        if ($read > 0) {
            fseek($fp, -$read, SEEK_END);
        }
        $data = $read > 0 ? fread($fp, $read) : '';
        fclose($fp);
        if ($data === false || $data === '') {
            return array();
        }
        $lines = explode("\n", $data);
        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, -$maxLines);
        }
        return $lines;
    }
}
