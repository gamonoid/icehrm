<?php
/**
 * Filesystem primitives for the install phase.
 *
 * Kept separate and deliberately small, because these are the functions that can
 * destroy an installation. Every one of them refuses to act outside the two directories
 * the updater owns (the IceHRM root and updater/data), so a bug in a caller cannot walk
 * off into the filesystem.
 */

class UpdaterFiles
{
    /**
     * Guard: a path must sit inside the IceHRM root or updater/data before anything
     * deletes or moves it. Anything else is a programming error, and it stops here.
     */
    private static function assertInsideOwnedPaths($path)
    {
        $real = realpath($path);
        if ($real === false) {
            return false;
        }
        $roots = array(
            realpath(UpdaterBootstrap::$rootDir),
            realpath(UpdaterBootstrap::$dataDir),
        );
        foreach ($roots as $root) {
            if ($root !== false && strpos($real, $root . DIRECTORY_SEPARATOR) === 0) {
                return true;
            }
        }
        UpdaterLog::error('Refused to operate on a path outside the installation: ' . $path);
        return false;
    }

    /**
     * Recursively delete a directory.
     *
     * Symlinks are unlinked, never followed — otherwise a symlink inside the tree
     * could point at anything and this would delete through it.
     */
    public static function deleteTree($path)
    {
        if (!file_exists($path) && !is_link($path)) {
            return true;
        }
        if (!self::assertInsideOwnedPaths($path)) {
            return false;
        }

        if (is_link($path) || !is_dir($path)) {
            return @unlink($path);
        }

        $entries = @scandir($path);
        if ($entries === false) {
            return false;
        }
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $child = $path . '/' . $entry;
            if (is_link($child) || !is_dir($child)) {
                if (!@unlink($child)) {
                    return false;
                }
                continue;
            }
            if (!self::deleteTree($child)) {
                return false;
            }
        }

        return @rmdir($path);
    }

    /**
     * Recursively copy $source into $destination, creating it if needed.
     *
     * @param array $skipNames entries (relative to $source's root) not to copy
     */
    public static function copyTree($source, $destination, $skipNames = array())
    {
        if (!is_dir($source)) {
            return false;
        }
        if (!is_dir($destination) && !@mkdir($destination, 0775, true) && !is_dir($destination)) {
            UpdaterLog::error('Could not create ' . $destination);
            return false;
        }

        $entries = @scandir($source);
        if ($entries === false) {
            return false;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || in_array($entry, $skipNames, true)) {
                continue;
            }
            $from = $source . '/' . $entry;
            $to = $destination . '/' . $entry;

            if (is_dir($from) && !is_link($from)) {
                // Nested entries are copied in full; skipNames only applies at this level,
                // which is what the app/ rules need (preserve app/config.php, not every
                // config.php anywhere in the tree).
                if (!self::copyTree($from, $to, array())) {
                    return false;
                }
                continue;
            }

            if (!@copy($from, $to)) {
                UpdaterLog::error('Could not copy ' . $from . ' to ' . $to);
                return false;
            }
            // Keep the executable bit on anything in bin/.
            $mode = @fileperms($from);
            if ($mode !== false) {
                @chmod($to, $mode & 0777);
            }
        }

        return true;
    }

    /**
     * Move a directory aside instead of deleting it.
     *
     * rename() is atomic and instant within a filesystem, which is what makes rollback
     * possible: the old tree still exists, complete, until the update is confirmed
     * healthy. Deleting first and copying second leaves no way back if the copy fails
     * half way — the difference between "click rollback" and "restore from backup".
     *
     * @return bool false when the source exists but could not be moved
     */
    public static function moveAside($source, $destination)
    {
        if (!file_exists($source)) {
            return true; // nothing there to preserve
        }
        if (!self::assertInsideOwnedPaths($source)) {
            return false;
        }
        $parent = dirname($destination);
        if (!is_dir($parent) && !@mkdir($parent, 0775, true) && !is_dir($parent)) {
            return false;
        }
        if (@rename($source, $destination)) {
            return true;
        }

        // Different filesystems (a bind-mounted volume, say) make rename fail. Fall back
        // to copy-then-delete, which is slower but preserves the same guarantee.
        UpdaterLog::info('rename() failed for ' . $source . ', falling back to copy');
        if (!self::copyTree($source, $destination)) {
            return false;
        }
        return self::deleteTree($source);
    }

    /** Directory names directly inside a path, excluding dot entries. */
    public static function subdirectories($path)
    {
        $out = array();
        $entries = @scandir($path);
        if ($entries === false) {
            return $out;
        }
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (is_dir($path . '/' . $entry)) {
                $out[] = $entry;
            }
        }
        return $out;
    }
}
