<?php
/**
 * Simple extension path resolver that doesn't require autoloader.
 * Used in app/index.php before the full autoloader is loaded.
 *
 * Extensions may live under TWO roots: the free `extensions/` directory and the
 * paid `extensions-pro/` directory (the pro split — only present in the pro
 * distribution). Every resolver below is seeded with the primary `extensions/`
 * base path (what all callers pass) and transparently also searches the sibling
 * `extensions-pro/` root when it exists, so a moved extension keeps resolving
 * with no caller changes. When `extensions-pro/` is absent (free build), the
 * behaviour is identical to the single-root original.
 */

/**
 * The sibling extensions-pro directory for a given primary extensions base path.
 * e.g. ".../core/../extensions/" -> ".../core/../extensions-pro/".
 */
function iceExtensionsProDir($extensionsBasePath) {
    return rtrim(dirname(rtrim($extensionsBasePath, '/')), '/') . '/extensions-pro/';
}

/**
 * Whether paid extensions-pro/ extensions should be loaded. Defined centrally in
 * config.base.php (iceProExtensionsEnabled); fall back to reading the constants
 * directly in case this runs before that helper is defined.
 */
function iceProRootEnabled() {
    if (function_exists('iceProExtensionsEnabled')) {
        return iceProExtensionsEnabled();
    }
    return (defined('IS_ICEHRM_PRO') && IS_ICEHRM_PRO)
        || (defined('IS_CLOUD') && IS_CLOUD);
}

/**
 * All extension root dirs to search, in priority order: the free base first, then
 * the paid extensions-pro/ root — but only when a Pro/Cloud build is enabled AND
 * that root exists on disk.
 */
function iceExtensionRoots($extensionsBasePath) {
    $roots = array($extensionsBasePath);
    if (iceProRootEnabled()) {
        $pro = iceExtensionsProDir($extensionsBasePath);
        if ($pro !== $extensionsBasePath && is_dir($pro)) {
            $roots[] = $pro;
        }
    }
    return $roots;
}

/**
 * Find the group an extension belongs to WITHIN a single root, or null.
 */
function iceExtensionGroupInRoot($extensionName, $root) {
    if (!is_dir($root)) {
        return null;
    }
    $dirs = scandir($root);
    foreach ($dirs as $dir) {
        if ($dir === '.' || $dir === '..') {
            continue;
        }

        $groupJsonPath = $root . $dir . '/group.json';
        if (file_exists($groupJsonPath)) {
            $content = file_get_contents($groupJsonPath);
            $groupData = json_decode($content, true);

            if ($groupData && isset($groupData['extensions'])) {
                if (in_array($extensionName, $groupData['extensions'])) {
                    return $dir;
                }
            }
        }
    }
    return null;
}

/**
 * The group an extension belongs to (searched across all roots), or null.
 */
function getExtensionGroup($extensionName, $extensionsBasePath) {
    foreach (iceExtensionRoots($extensionsBasePath) as $root) {
        $group = iceExtensionGroupInRoot($extensionName, $root);
        if ($group !== null) {
            return $group;
        }
    }
    return null;
}

/**
 * The root dir (extensions/ or extensions-pro/) that actually contains the
 * extension. Falls back to the primary base path when not found anywhere.
 */
function resolveExtensionRoot($extensionName, $extensionsBasePath) {
    foreach (iceExtensionRoots($extensionsBasePath) as $root) {
        $group = iceExtensionGroupInRoot($extensionName, $root);
        if ($group !== null) {
            if (is_dir($root . $group . '/' . $extensionName)) {
                return $root;
            }
        } elseif (is_dir($root . $extensionName)) {
            return $root;
        }
    }
    return $extensionsBasePath;
}

/**
 * Absolute filesystem path to an extension's directory (group-aware, dual-root).
 */
function resolveExtensionPath($extensionName, $extensionsBasePath) {
    $root = resolveExtensionRoot($extensionName, $extensionsBasePath);
    $group = iceExtensionGroupInRoot($extensionName, $root);
    if ($group) {
        return $root . $group . '/' . $extensionName . '/';
    }
    return $root . $extensionName . '/';
}

/**
 * Path relative to the extension's own root (e.g. "recruitment/candidates" for a
 * grouped extension, or just "tasks"). Used to build served asset URLs together
 * with extensionAssetUrlBase().
 */
function getExtensionRelativePath($extensionName, $extensionsBasePath) {
    $group = getExtensionGroup($extensionName, $extensionsBasePath);
    if ($group) {
        return $group . '/' . $extensionName;
    }
    return $extensionName;
}

/**
 * The base URL under which an extension's built assets are served — the pro URL
 * root when the extension physically lives in extensions-pro/, else the free
 * EXTENSIONS_URL. Requires the URL constants (defined in config.base.php).
 */
function extensionAssetUrlBase($extensionName, $extensionsBasePath) {
    $root = resolveExtensionRoot($extensionName, $extensionsBasePath);
    $proDir = iceExtensionsProDir($extensionsBasePath);
    if (rtrim($root, '/') === rtrim($proDir, '/') && defined('EXTENSIONS_PRO_URL')) {
        return EXTENSIONS_PRO_URL;
    }
    return defined('EXTENSIONS_URL') ? EXTENSIONS_URL : '';
}
