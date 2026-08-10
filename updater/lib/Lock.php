<?php
/**
 * Stops two updates running at once.
 *
 * Two administrators starting an update simultaneously — or one impatient double-click
 * — would have two processes renaming and copying the same directories, which produces
 * a mixture of two versions and a backup that restores neither. A file in updater/data
 * is enough; it is the same directory everything else stages into.
 *
 * The lock is released on completion, and treated as stale after MAX_AGE so a process
 * killed mid-update (PHP timeout, container restart) does not block every later attempt.
 */

class UpdaterLock
{
    /** Longer than any plausible update, short enough not to strand a customer. */
    const MAX_AGE_SECONDS = 1800;

    private static function path()
    {
        return UpdaterBootstrap::$dataDir . '/update.lock';
    }

    /**
     * @return array ['ok'=>bool, 'message'=>string|null, 'stale'=>bool]
     */
    public static function acquire($owner)
    {
        $path = self::path();

        if (file_exists($path)) {
            $age = time() - (int) @filemtime($path);
            if ($age < self::MAX_AGE_SECONDS) {
                $held = trim((string) @file_get_contents($path));
                return array(
                    'ok' => false,
                    'stale' => false,
                    'message' => 'An update is already running' . ($held !== '' ? ' (' . htmlspecialchars($held) . ')' : '')
                        . '. Wait for it to finish, or try again in '
                        . ceil((self::MAX_AGE_SECONDS - $age) / 60) . ' minutes if it has stopped responding.',
                );
            }
            UpdaterLog::info('Clearing a stale lock (' . $age . 's old)');
            @unlink($path);
        }

        // 'x' fails if the file appeared between the check above and here, which is the
        // race this is guarding against.
        $handle = @fopen($path, 'x');
        if ($handle === false) {
            return array(
                'ok' => false,
                'stale' => false,
                'message' => 'Another update started at the same moment. Try again in a minute.',
            );
        }
        fwrite($handle, $owner . ' at ' . date('Y-m-d H:i:s'));
        fclose($handle);

        return array('ok' => true, 'stale' => false, 'message' => null);
    }

    public static function release()
    {
        $path = self::path();
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
