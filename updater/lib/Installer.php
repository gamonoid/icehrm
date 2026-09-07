<?php
/**
 * Replaces the installed files with the new version.
 *
 * The strategy is backup-then-copy, never delete-then-copy. Each directory being
 * replaced is renamed into updater/data/backup-<version>-<timestamp>/ first — atomic
 * and instant on the same filesystem — and only then is the new one copied in. If any
 * step fails, or the application does not come back up, everything can be renamed back.
 * Deleting first would mean a disk-full or permission error part way through leaves a
 * dead installation with no way home.
 *
 * What is preserved, and why:
 *   app/config.php, app/config-dev.php   the deployment's own settings
 *   app/data/, app/cache/                uploads and generated files
 *   extensions/<dir> not in the release  marketplace or bespoke extensions, which the
 *                                        release archive knows nothing about
 *   updater/config.php, updater/data/    the updater's own state, including the backup
 *                                        it is standing on
 *
 * The updater updates itself LAST. Linux allows a running PHP file to be replaced —
 * execution continues from the already-loaded copy — but doing it last keeps the window
 * as small as possible.
 */

class UpdaterInstaller
{
    /** Replaced wholesale from the release. */
    private static $replaceable = array('core', 'web', 'bin');

    /** Copied individually from the release root. docker/ is NOT among them: build.xml
     *  excludes it from the archive, so it is never present to copy. */
    private static $rootFiles = array(
        'docker-compose.yaml',
        'docker-compose-prod.yaml',
        'docker-compose-release-test.yaml',
        'docker-compose-testing.yaml',
        'docker-prod.env.example',
        'Dockerfile',
        'Dockerfile-prod',
        'Dockerfile-release-test',
        'Dockerfile-testing',
        'Dockerfile-worker',
    );

    /** Never replaced inside app/. */
    private static $appPreserve = array('config.php', 'config-dev.php', 'data', 'cache');

    /**
     * Run the whole installation.
     *
     * @return array ['ok'=>bool, 'message'=>string, 'backupDir'=>string|null, 'preserved'=>string[]]
     */
    public static function install()
    {
        $root = UpdaterBootstrap::$rootDir;
        $new = UpdaterPackage::extractedPath();
        $fromVersion = UpdaterBootstrap::currentVersion();
        $toVersion = UpdaterPackage::packagedVersion();

        if (!is_dir($new)) {
            return self::failure('The extracted files are missing. Download the release again.', null, array());
        }

        $backupDir = UpdaterBootstrap::$dataDir . '/backup-' . $fromVersion . '-' . date('Ymd-His');
        if (!is_dir($backupDir) && !@mkdir($backupDir, 0775, true) && !is_dir($backupDir)) {
            return self::failure('Could not create the backup directory ' . $backupDir . '.', null, array());
        }

        UpdaterLog::info(str_repeat('=', 70));
        UpdaterLog::info('Update starting: ' . UpdaterPackage::formatVersion($fromVersion)
            . ' -> ' . UpdaterPackage::formatVersion($toVersion));
        UpdaterLog::info('Backup directory: ' . $backupDir);

        // Collected as extensions are merged and reported to the administrator, on the
        // failure path too — an interrupted update is exactly when it matters which
        // directories were left alone.
        $preserved = array();

        // 1. core, web, bin — replaced wholesale.
        foreach (self::$replaceable as $dir) {
            if (!is_dir($new . '/' . $dir)) {
                UpdaterLog::info('Release contains no ' . $dir . '/, leaving the existing one in place');
                continue;
            }
            $result = self::replaceDirectory($root . '/' . $dir, $new . '/' . $dir, $backupDir . '/' . $dir);
            if ($result !== null) {
                return self::failure($result, $backupDir, $preserved);
            }
        }

        // 2. app/ — copied over the existing directory rather than replaced, so the
        //    deployment's config and its uploads survive. The release does not ship
        //    config.php, config-dev.php or the contents of data/ and cache/ (build.xml
        //    excludes them), but they are named in the skip list as well: relying on the
        //    packaging to protect a customer's uploads would be one packaging change
        //    away from data loss.
        $appResult = self::updateAppDirectory($root . '/app', $new . '/app', $backupDir . '/app');
        if ($appResult !== null) {
            return self::failure($appResult, $backupDir, $preserved);
        }

        // 3. extensions/ — merge, so extensions the release does not know about survive.
        $extResult = self::mergeExtensions(
            $root . '/extensions',
            $new . '/extensions',
            $backupDir . '/extensions',
            $preserved
        );
        if ($extResult !== null) {
            return self::failure($extResult, $backupDir, $preserved);
        }

        // 4. extensions-pro/ — installed whenever the RELEASE ships it, not when the
        //    running installation happens to be Pro. A free release has no
        //    extensions-pro/, so the existing directory (if any) is left untouched
        //    rather than deleted, exactly as before.
        //
        //    Asking the release rather than the config is what makes open source -> Pro
        //    work at all: during that update this is still a free installation, and the
        //    Pro core/config.base.php about to be installed refuses to boot without
        //    extensions-pro/util/admin/util.php. Gating on isPro() here would leave the
        //    customer with a 503 and no application.
        if (is_dir($new . '/extensions-pro')) {
            $proResult = self::mergeExtensions(
                $root . '/extensions-pro',
                $new . '/extensions-pro',
                $backupDir . '/extensions-pro',
                $preserved
            );
            if ($proResult !== null) {
                return self::failure($proResult, $backupDir, $preserved);
            }
        }

        // 5. Root-level docker files.
        foreach (self::$rootFiles as $file) {
            $from = $new . '/' . $file;
            if (!is_file($from)) {
                continue;
            }
            $to = $root . '/' . $file;
            if (is_file($to)) {
                @copy($to, $backupDir . '/' . $file);
            }
            if (!@copy($from, $to)) {
                return self::failure('Could not copy ' . $file . ' into the installation.', $backupDir, $preserved);
            }
        }
        UpdaterLog::info('Root configuration files copied');

        // 6. The updater itself, last — and never its own config or staged data.
        if (is_dir($new . '/updater')) {
            if (!UpdaterFiles::copyTree($new . '/updater', UpdaterBootstrap::$updaterDir, array('config.php', 'config.base.php', 'data'))) {
                // Not fatal: the application is already updated, only the updater is
                // still the old one, and it can be replaced by hand.
                UpdaterLog::error('The updater could not update itself; the application was still updated');
            } else {
                UpdaterLog::info('Updater files refreshed');
            }
        }

        UpdaterLog::info('File replacement complete');

        return array(
            'ok' => true,
            'message' => 'Files updated successfully.',
            'backupDir' => $backupDir,
            'preserved' => $preserved,
        );
    }

    /**
     * Move the existing directory into the backup, then copy the new one in.
     *
     * @return string|null null on success, otherwise the error to report
     */
    private static function replaceDirectory($current, $source, $backup)
    {
        if (!UpdaterFiles::moveAside($current, $backup)) {
            return 'Could not move ' . basename($current) . '/ aside for backup. '
                . 'Check permissions on ' . dirname($current) . '.';
        }
        if (!UpdaterFiles::copyTree($source, $current)) {
            return 'Could not copy the new ' . basename($current) . '/ into place.';
        }
        UpdaterLog::info('Replaced ' . basename($current) . '/');
        return null;
    }

    /**
     * app/ is updated in place: the new files are copied over the top and the
     * deployment's own config, uploads and cache are left alone.
     *
     * @return string|null
     */
    private static function updateAppDirectory($current, $source, $backup)
    {
        if (!is_dir($source)) {
            UpdaterLog::info('Release contains no app/, leaving the existing one in place');
            return null;
        }

        // Back up only what is about to be overwritten — copying app/data/ could mean
        // duplicating gigabytes of uploads, and it is not being touched.
        if (is_dir($current)) {
            if (!is_dir($backup) && !@mkdir($backup, 0775, true) && !is_dir($backup)) {
                return 'Could not create the app backup directory.';
            }
            foreach (scandir($current) as $entry) {
                if ($entry === '.' || $entry === '..' || in_array($entry, self::$appPreserve, true)) {
                    continue;
                }
                $from = $current . '/' . $entry;
                if (is_dir($from)) {
                    UpdaterFiles::copyTree($from, $backup . '/' . $entry);
                } else {
                    @copy($from, $backup . '/' . $entry);
                }
            }
        }

        if (!UpdaterFiles::copyTree($source, $current, self::$appPreserve)) {
            return 'Could not copy the new app/ files into place.';
        }

        UpdaterLog::info('Updated app/ (preserved: ' . implode(', ', self::$appPreserve) . ')');
        return null;
    }

    /**
     * Replace every extension the release ships, and leave the rest alone.
     *
     * A straight delete-and-copy of extensions/ would destroy anything the customer
     * installed from the marketplace or wrote themselves, because the release archive
     * only contains the extensions IceHRM ships. Those directories are recorded and
     * reported, since an untouched extension may still need a compatibility check
     * against the new version.
     *
     * @param string[] $preserved collects the names left in place, by reference
     * @return string|null
     */
    private static function mergeExtensions($current, $source, $backup, &$preserved)
    {
        if (!is_dir($source)) {
            return null;
        }
        if (!is_dir($current) && !@mkdir($current, 0775, true) && !is_dir($current)) {
            return 'Could not create ' . $current . '.';
        }

        $shipped = UpdaterFiles::subdirectories($source);
        $installed = UpdaterFiles::subdirectories($current);

        foreach ($installed as $name) {
            if (!in_array($name, $shipped, true)) {
                $preserved[] = basename($current) . '/' . $name;
            }
        }

        foreach ($shipped as $name) {
            $result = self::replaceDirectory($current . '/' . $name, $source . '/' . $name, $backup . '/' . $name);
            if ($result !== null) {
                return $result;
            }
        }

        // Loose files at the extension-root level (rare, but they exist).
        foreach (scandir($source) as $entry) {
            if ($entry === '.' || $entry === '..' || is_dir($source . '/' . $entry)) {
                continue;
            }
            @copy($source . '/' . $entry, $current . '/' . $entry);
        }

        UpdaterLog::info('Updated ' . basename($current) . '/ (' . count($shipped) . ' shipped, '
            . count($preserved) . ' preserved)');
        return null;
    }

    /**
     * Put everything back from a backup directory.
     *
     * Offered when the health check fails. Each directory present in the backup is
     * moved back over the newly installed one, which is the exact inverse of the
     * install, so the installation returns to the state it was in beforehand.
     *
     * @return array ['ok'=>bool,'message'=>string]
     */
    public static function rollback($backupDir)
    {
        if (!is_dir($backupDir)) {
            return array('ok' => false, 'message' => 'The backup directory no longer exists.');
        }

        UpdaterLog::info('Rolling back from ' . $backupDir);
        $root = UpdaterBootstrap::$rootDir;
        $failures = array();

        foreach (UpdaterFiles::subdirectories($backupDir) as $name) {
            $restored = $backupDir . '/' . $name;
            $target = $root . '/' . $name;

            if ($name === 'app') {
                // app/ was updated in place, so restore file-by-file and leave the
                // preserved entries (config, uploads, cache) exactly as they are.
                if (!UpdaterFiles::copyTree($restored, $target, array())) {
                    $failures[] = $name;
                }
                continue;
            }

            if ($name === 'extensions' || $name === 'extensions-pro') {
                // These were merged, not replaced: the backup holds ONLY the extensions
                // the release shipped. Restoring the directory wholesale would delete
                // every marketplace or bespoke extension the install had — the exact
                // thing mergeExtensions() went to the trouble of preserving. Restore
                // one extension at a time and leave everything else untouched.
                foreach (UpdaterFiles::subdirectories($restored) as $extension) {
                    $extensionTarget = $target . '/' . $extension;
                    if (!UpdaterFiles::deleteTree($extensionTarget)) {
                        $failures[] = $name . '/' . $extension;
                        continue;
                    }
                    if (!@rename($restored . '/' . $extension, $extensionTarget)
                        && !UpdaterFiles::copyTree($restored . '/' . $extension, $extensionTarget)
                    ) {
                        $failures[] = $name . '/' . $extension;
                    }
                }
                continue;
            }

            // core, web, bin — replaced wholesale, so restored wholesale.
            if (!UpdaterFiles::deleteTree($target)) {
                $failures[] = $name;
                continue;
            }
            if (!@rename($restored, $target) && !UpdaterFiles::copyTree($restored, $target)) {
                $failures[] = $name;
            }
        }

        // Root-level files.
        foreach (self::$rootFiles as $file) {
            if (is_file($backupDir . '/' . $file)) {
                @copy($backupDir . '/' . $file, $root . '/' . $file);
            }
        }

        if (!empty($failures)) {
            UpdaterLog::error('Rollback incomplete: ' . implode(', ', $failures));
            return array(
                'ok' => false,
                'message' => 'Rollback could not restore: ' . implode(', ', $failures)
                    . '. The previous files are still in ' . $backupDir
                    . ' — restore them manually before using the application.',
            );
        }

        UpdaterLog::info('Rollback complete');
        return array('ok' => true, 'message' => 'The previous version has been restored.');
    }

    private static function failure($message, $backupDir, $preserved = array())
    {
        UpdaterLog::error($message);
        return array(
            'ok' => false,
            'message' => $message,
            'backupDir' => $backupDir,
            'preserved' => $preserved,
        );
    }
}
