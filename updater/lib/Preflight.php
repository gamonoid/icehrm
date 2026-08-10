<?php
/**
 * Checks that must pass before an update is offered.
 *
 * All of these fail loudly here rather than half way through replacing core/, which is
 * the difference between "fix this and try again" and "restore from backup".
 */

class UpdaterPreflight
{
    /**
     * @return array list of ['ok'=>bool,'label'=>string,'detail'=>string,'command'=>string|null]
     */
    public static function run()
    {
        $checks = array();

        $checks[] = self::check(
            class_exists('ZipArchive'),
            'PHP zip extension',
            'Required to unpack the downloaded release.',
            'Install it, e.g.  sudo apt-get install php-zip  then restart PHP.'
        );

        $checks[] = self::check(
            function_exists('curl_init'),
            'PHP curl extension',
            'Required to download the release.',
            'Install it, e.g.  sudo apt-get install php-curl  then restart PHP.'
        );

        $checks[] = self::check(
            function_exists('mysqli_connect') && UpdaterDb::connect() !== null,
            'Database connection',
            'The updater signs administrators in against the IceHRM database.',
            null
        );

        $dataDir = UpdaterBootstrap::$dataDir;
        $probe = $dataDir . '/.write-test';
        $writable = @file_put_contents($probe, 'ok') !== false;
        @unlink($probe);
        $checks[] = self::check(
            $writable,
            'updater/data is writable',
            'Holds the download, the extracted files and the backup of your current installation.',
            'sudo chown -R www-data:www-data ' . $dataDir . "\n" . 'sudo chmod -R 775 ' . $dataDir
        );

        // The root has to be writable too — the update renames and replaces directories
        // directly inside it. Checking here turns a mid-update permission failure into
        // a pre-update message.
        $rootDir = UpdaterBootstrap::$rootDir;
        $rootProbe = $rootDir . '/.updater-write-test';
        $rootWritable = @file_put_contents($rootProbe, 'ok') !== false;
        @unlink($rootProbe);
        $checks[] = self::check(
            $rootWritable,
            'IceHRM directory is writable',
            'The update replaces core/, web/, app/, bin/ and the extension directories in '
                . htmlspecialchars($rootDir) . '.',
            'sudo chown -R www-data:www-data ' . $rootDir . "\n" . 'sudo chmod -R 775 ' . $rootDir
        );

        // Zip + extracted copy + backup of the current installation all exist at once.
        $free = @disk_free_space($dataDir);
        $needed = self::estimatedSpaceNeeded();
        $checks[] = self::check(
            $free === false || $free > $needed,
            'Free disk space',
            $free === false
                ? 'Could not determine free space; make sure at least '
                    . self::formatBytes($needed) . ' is available.'
                : self::formatBytes($free) . ' available, about '
                    . self::formatBytes($needed) . ' needed for the download, the extracted '
                    . 'files and a backup of your current installation.',
            null
        );

        return $checks;
    }

    public static function allPassed($checks)
    {
        foreach ($checks as $check) {
            if (!$check['ok']) {
                return false;
            }
        }
        return true;
    }

    /**
     * Rough requirement: the archive, its extracted contents, and a backup of what is
     * being replaced, all on disk simultaneously. Measured from the current install
     * rather than guessed, with a floor for a small or partial installation.
     */
    private static function estimatedSpaceNeeded()
    {
        $current = 0;
        foreach (array('core', 'web', 'app', 'bin', 'extensions', 'extensions-pro') as $dir) {
            $path = UpdaterBootstrap::$rootDir . '/' . $dir;
            if (is_dir($path)) {
                $current += self::directorySize($path, 20000);
            }
        }
        $estimate = (int) ($current * 2.5);
        return max($estimate, 600 * 1024 * 1024);
    }

    /**
     * @param int $fileLimit stop after this many files — this runs on a page load and
     *                       an exhaustive walk of a large installation is not worth the
     *                       wait for what is only a sanity threshold
     */
    private static function directorySize($path, $fileLimit)
    {
        $total = 0;
        $seen = 0;
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $total += $file->getSize();
                    if (++$seen >= $fileLimit) {
                        // Extrapolate rather than keep walking.
                        return (int) ($total * 1.5);
                    }
                }
            }
        } catch (\Throwable $e) {
            return $total;
        }
        return $total;
    }

    public static function formatBytes($bytes)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $index = 0;
        $value = (float) $bytes;
        while ($value >= 1024 && $index < count($units) - 1) {
            $value /= 1024;
            $index++;
        }
        return round($value, 1) . ' ' . $units[$index];
    }

    private static function check($ok, $label, $detail, $command)
    {
        return array(
            'ok' => (bool) $ok,
            'label' => $label,
            'detail' => $detail,
            'command' => $command,
        );
    }
}
