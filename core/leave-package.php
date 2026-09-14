<?php
/**
 * Leave package resolver.
 *
 * Leave management ships as a *package* extension: unlike a normal extension it
 * contributes core-style modules (admin/leaves, modules/leaves, modules/leavecal),
 * its own src tree, its own migrations and its own JS bundles. Everything that has
 * to reach into it (module scanning, the package loader, bundle URLs, dashboards)
 * goes through the helpers here so the location lives in exactly one place.
 *
 * Lookup order:
 *   1. extensions/leave                       — the free, bundled package
 *   2. extensions/leave_and_performance       — legacy combined package (free root)
 *   3. extensions-pro/leave_and_performance   — legacy combined package, paid split
 *      (only on a Pro/Cloud build, i.e. when iceProExtensionsEnabled() is true)
 *
 * No autoloading here: this file is included directly (it runs before Composer on
 * some entry points), so every function is function_exists-guarded.
 */

if (!function_exists('iceLeavePackageCandidates')) {
    /**
     * The candidate package locations, best first.
     *
     * Each entry is array('dir' => <absolute dir, trailing slash>,
     *                     'root' => 'extensions'|'extensions-pro',
     *                     'name' => <package directory name>).
     *
     * @return array
     */
    function iceLeavePackageCandidates()
    {
        $base = dirname(__FILE__) . '/../';
        $candidates = array(
            array('dir' => $base . 'extensions/leave/', 'root' => 'extensions', 'name' => 'leave'),
            array('dir' => $base . 'extensions/leave_and_performance/', 'root' => 'extensions', 'name' => 'leave_and_performance'),
        );
        if (function_exists('iceProExtensionsEnabled') && iceProExtensionsEnabled()) {
            $candidates[] = array(
                'dir' => $base . 'extensions-pro/leave_and_performance/',
                'root' => 'extensions-pro',
                'name' => 'leave_and_performance',
            );
        }
        return $candidates;
    }
}

if (!function_exists('iceLeavePackage')) {
    /**
     * The installed leave package, or null when leave is not installed.
     *
     * @return array|null
     */
    function iceLeavePackage()
    {
        foreach (iceLeavePackageCandidates() as $candidate) {
            if (is_dir($candidate['dir'])) {
                return $candidate;
            }
        }
        return null;
    }
}

if (!function_exists('iceLeavePackageDir')) {
    /**
     * Absolute path (trailing slash) of the installed leave package, or null.
     *
     * @return string|null
     */
    function iceLeavePackageDir()
    {
        $package = iceLeavePackage();
        return $package === null ? null : $package['dir'];
    }
}

if (!function_exists('iceLeavePackageName')) {
    /**
     * Directory name of the installed leave package ('leave' or the legacy
     * 'leave_and_performance'), or null when leave is not installed.
     *
     * @return string|null
     */
    function iceLeavePackageName()
    {
        $package = iceLeavePackage();
        return $package === null ? null : $package['name'];
    }
}

if (!function_exists('iceLeavePackageUrl')) {
    /**
     * Served URL base (trailing slash) of the installed leave package — used to
     * build bundle URLs, e.g. iceLeavePackageUrl() . 'web/dist/admin-bundle.js'.
     * Null when leave is not installed or the URL constants are unavailable.
     *
     * @return string|null
     */
    function iceLeavePackageUrl()
    {
        $package = iceLeavePackage();
        if ($package === null) {
            return null;
        }
        if ($package['root'] === 'extensions-pro') {
            return defined('EXTENSIONS_PRO_URL') ? EXTENSIONS_PRO_URL . $package['name'] . '/' : null;
        }
        return defined('EXTENSIONS_URL') ? EXTENSIONS_URL . $package['name'] . '/' : null;
    }
}
