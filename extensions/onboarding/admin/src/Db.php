<?php

namespace OnboardingAdmin;

use Classes\BaseService;

/**
 * Small database helpers. All models share one mysqli connection, so a transaction
 * opened here covers every model Save() made inside the callback.
 *
 * `SET autocommit` is used rather than START TRANSACTION because statements run as
 * prepared statements, and MySQL does not allow START TRANSACTION to be prepared.
 */
class Db
{
    public static function db()
    {
        return BaseService::getInstance()->getDB();
    }

    /**
     * Run $fn inside a transaction. Commits when it returns, rolls back and rethrows
     * when it throws. Not re-entrant: nested calls join the outer transaction.
     */
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
        } catch (\Exception $e) {
            $db->Execute('ROLLBACK');
            throw $e;
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

    public static function now()
    {
        return date('Y-m-d H:i:s');
    }
}
