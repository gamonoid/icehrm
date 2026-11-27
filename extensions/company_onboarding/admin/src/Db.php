<?php

namespace CompanyOnboardingAdmin;

use Classes\BaseService;

/** Small database helpers (see OnboardingAdmin\Db for the transaction notes). */
class Db
{
    public static function db()
    {
        return BaseService::getInstance()->getDB();
    }

    public static function transaction(callable $fn)
    {
        static $depth = 0;
        if ($depth > 0) {
            return $fn();
        }
        $db = self::db();
        $db->Execute('SET autocommit = 0');
        $depth++;
        try {
            $result = $fn();
            $db->Execute('COMMIT');
            return $result;
        } catch (\Throwable $e) {
            $db->Execute('ROLLBACK');
            throw $e;
        } finally {
            $depth--;
            $db->Execute('SET autocommit = 1');
        }
    }

    public static function rows($sql, array $params = array())
    {
        $rows = self::db()->Execute($sql, $params);
        return is_array($rows) ? $rows : array();
    }

    public static function value($sql, array $params = array())
    {
        $rows = self::rows($sql, $params);
        if (empty($rows)) {
            return null;
        }
        $first = array_values($rows[0]);
        return $first[0];
    }

    public static function tableExists($table)
    {
        static $cache = array();
        if (!isset($cache[$table])) {
            // Not "SHOW TABLES LIKE ?": SHOW does not take a placeholder here.
            $cache[$table] = (int) self::value(
                'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
                array($table)
            ) > 0;
        }
        return $cache[$table];
    }

    public static function now()
    {
        return date('Y-m-d H:i:s');
    }
}
