<?php
/**
 * Append-only log for an update run.
 *
 * Every step writes here, because when an update goes wrong the customer is looking at
 * a broken application and support has nothing else to go on. The file lives in
 * updater/data/, which is created and web-denied by Bootstrap.
 *
 * Self-contained: no IceHRM class is referenced anywhere in the updater.
 */

class UpdaterLog
{
    /** @var string|null */
    private static $file = null;

    public static function setFile($path)
    {
        self::$file = $path;
    }

    public static function info($message)
    {
        self::write('INFO', $message);
    }

    public static function error($message)
    {
        self::write('ERROR', $message);
    }

    private static function write($level, $message)
    {
        if (self::$file === null) {
            return;
        }
        $line = sprintf(
            "[%s] %-5s %s\n",
            date('Y-m-d H:i:s'),
            $level,
            is_string($message) ? $message : var_export($message, true)
        );
        // Suppressed: logging must never be the thing that breaks an update.
        @file_put_contents(self::$file, $line, FILE_APPEND | LOCK_EX);
    }

    /** @return string the whole log, for display on the result screen */
    public static function tail($lines = 200)
    {
        if (self::$file === null || !is_readable(self::$file)) {
            return '';
        }
        $all = @file(self::$file, FILE_IGNORE_NEW_LINES);
        if (!is_array($all)) {
            return '';
        }
        return implode("\n", array_slice($all, -$lines));
    }
}
