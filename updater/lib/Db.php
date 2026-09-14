<?php
/**
 * The updater's own database connection.
 *
 * A deliberately tiny mysqli wrapper rather than the application's ORM: the updater
 * must keep working while core/ is being replaced, so it cannot load a single class
 * from it. Credentials come from the updater's copy of app/config.php.
 *
 * Only ever used to authenticate an administrator. The updater performs no schema
 * changes — migrations are applied by the application itself on the next login (see
 * Classes\Migration\MigrationRunner).
 */

class UpdaterDb
{
    /** @var mysqli|null */
    private static $connection = null;

    /**
     * @return mysqli|null null when the connection could not be made
     */
    public static function connect()
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        if (!function_exists('mysqli_connect')) {
            UpdaterLog::error('mysqli extension is not available');
            return null;
        }

        $host = defined('APP_HOST') ? APP_HOST : 'localhost';
        $port = null;

        // APP_HOST may carry a port ("db:3307"); mysqli wants them separately.
        if (strpos($host, ':') !== false) {
            list($host, $portPart) = explode(':', $host, 2);
            if (ctype_digit($portPart)) {
                $port = (int) $portPart;
            }
        }

        $previous = error_reporting(0);
        try {
            $connection = $port === null
                ? @new mysqli($host, APP_USERNAME, APP_PASSWORD, APP_DB)
                : @new mysqli($host, APP_USERNAME, APP_PASSWORD, APP_DB, $port);
        } catch (\Throwable $e) {
            error_reporting($previous);
            UpdaterLog::error('Database connection failed: ' . $e->getMessage());
            return null;
        }
        error_reporting($previous);

        if ($connection->connect_errno) {
            UpdaterLog::error('Database connection failed: ' . $connection->connect_error);
            return null;
        }

        $connection->set_charset('utf8mb4');
        self::$connection = $connection;
        return self::$connection;
    }

    /**
     * Run a prepared SELECT and return all rows.
     *
     * Prepared statements only — there is no string-interpolating query method here, so
     * a username cannot become SQL.
     *
     * @param string $sql    with ? placeholders
     * @param array  $params bound as strings, which mysqli coerces correctly for the
     *                       comparisons this updater makes
     * @return array
     */
    public static function select($sql, $params = array())
    {
        $connection = self::connect();
        if ($connection === null) {
            return array();
        }

        $statement = $connection->prepare($sql);
        if ($statement === false) {
            UpdaterLog::error('Query preparation failed: ' . $connection->error);
            return array();
        }

        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $bind = array($types);
            foreach ($params as $index => $value) {
                $params[$index] = (string) $value;
                $bind[] = &$params[$index];
            }
            call_user_func_array(array($statement, 'bind_param'), $bind);
        }

        if (!$statement->execute()) {
            UpdaterLog::error('Query failed: ' . $statement->error);
            $statement->close();
            return array();
        }

        $result = $statement->get_result();
        $rows = array();
        if ($result !== false) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            $result->free();
        }
        $statement->close();

        return $rows;
    }
}
