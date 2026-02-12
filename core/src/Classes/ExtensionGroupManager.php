<?php

namespace Classes;

// Include the simple path resolver functions
require_once APP_BASE_PATH . 'extensions/path-resolver.php';

/**
 * Manages extension groups and provides path resolution for grouped extensions.
 *
 * Extension groups allow multiple related extensions to be organized under a single
 * directory with shared versioning. The group.json file in each group directory
 * defines which extensions belong to the group.
 */
class ExtensionGroupManager
{
    /** @var ExtensionGroupManager|null */
    private static $instance = null;

    /** @var array<string, array> Group metadata indexed by group name */
    private $groups = [];

    /** @var bool Whether the mappings have been initialized */
    private $initialized = false;

    private function __construct()
    {
    }

    public static function getInstance(): ExtensionGroupManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the extension group mappings by scanning the extensions directory
     */
    public function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $extensionsPath = $this->getExtensionsPath();
        if (!is_dir($extensionsPath)) {
            $this->initialized = true;
            return;
        }

        $dirs = scandir($extensionsPath);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $groupJsonPath = $extensionsPath . $dir . '/group.json';
            if (file_exists($groupJsonPath)) {
                $content = file_get_contents($groupJsonPath);
                $groupData = json_decode($content, true);
                if ($groupData && isset($groupData['extensions'])) {
                    $this->groups[$dir] = $groupData;
                }
            }
        }

        $this->initialized = true;
    }

    /**
     * Get the base extensions path
     */
    public function getExtensionsPath(): string
    {
        return APP_BASE_PATH . '../extensions/';
    }

    /**
     * Check if an extension belongs to a group
     */
    public function isGroupedExtension(string $extensionName): bool
    {
        return getExtensionGroup($extensionName, $this->getExtensionsPath()) !== null;
    }

    /**
     * Get the group name for an extension, or null if not grouped
     */
    public function getGroupForExtension(string $extensionName): ?string
    {
        return getExtensionGroup($extensionName, $this->getExtensionsPath());
    }

    /**
     * Get the actual filesystem path for an extension
     */
    public function getExtensionPath(string $extensionName): string
    {
        return resolveExtensionPath($extensionName, $this->getExtensionsPath());
    }

    /**
     * Get the relative path for an extension (relative to extensions directory)
     */
    public function getExtensionRelativePath(string $extensionName): string
    {
        return getExtensionRelativePath($extensionName, $this->getExtensionsPath());
    }

    /**
     * Get all groups
     */
    public function getGroups(): array
    {
        $this->initialize();
        return $this->groups;
    }

    /**
     * Get extensions in a specific group
     */
    public function getExtensionsInGroup(string $groupName): array
    {
        $this->initialize();
        return $this->groups[$groupName]['extensions'] ?? [];
    }

    /**
     * Get group metadata (version, releaseDate, etc.)
     */
    public function getGroupMetadata(string $groupName): ?array
    {
        $this->initialize();
        return $this->groups[$groupName] ?? null;
    }

    /**
     * Get all extension directories (both grouped and ungrouped)
     * Returns an array of extension names
     */
    public function getAllExtensionNames(): array
    {
        $this->initialize();

        $extensions = [];
        $extensionsPath = $this->getExtensionsPath();

        $dirs = scandir($extensionsPath);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $fullPath = $extensionsPath . $dir;
            if (!is_dir($fullPath)) {
                continue;
            }

            // Check if this is a group
            if (isset($this->groups[$dir])) {
                // Add all extensions from this group
                foreach ($this->groups[$dir]['extensions'] as $extName) {
                    $extensions[] = $extName;
                }
            } else {
                // Regular extension (has admin or user subdirectory)
                if (is_dir($fullPath . '/admin') || is_dir($fullPath . '/user')) {
                    $extensions[] = $dir;
                }
            }
        }

        return array_unique($extensions);
    }
}
