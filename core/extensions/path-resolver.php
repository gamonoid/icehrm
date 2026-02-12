<?php
/**
 * Simple extension path resolver that doesn't require autoloader.
 * Used in app/index.php before the full autoloader is loaded.
 */

function getExtensionGroup($extensionName, $extensionsBasePath) {
    $dirs = scandir($extensionsBasePath);
    foreach ($dirs as $dir) {
        if ($dir === '.' || $dir === '..') {
            continue;
        }

        $groupJsonPath = $extensionsBasePath . $dir . '/group.json';
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

function resolveExtensionPath($extensionName, $extensionsBasePath) {
    $group = getExtensionGroup($extensionName, $extensionsBasePath);
    if ($group) {
        return $extensionsBasePath . $group . '/' . $extensionName . '/';
    }
    return $extensionsBasePath . $extensionName . '/';
}

function getExtensionRelativePath($extensionName, $extensionsBasePath) {
    $group = getExtensionGroup($extensionName, $extensionsBasePath);
    if ($group) {
        return $group . '/' . $extensionName;
    }
    return $extensionName;
}
