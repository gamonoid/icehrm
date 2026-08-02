<?php

namespace MarketplaceAdmin;

use Utils\LogManager;

class InstalledExtensionsService
{
    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get all installed extensions with their versions
     * Extension groups are shown as a single entry instead of individual extensions
     *
     * @return array Array of installed extensions
     */
    public function getInstalledExtensions()
    {
        $extensions = [];
        $extensionsPath = APP_BASE_PATH . '../extensions/';

        if (!is_dir($extensionsPath)) {
            return $extensions;
        }

        $dirs = scandir($extensionsPath);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $fullPath = $extensionsPath . $dir;
            if (!is_dir($fullPath)) {
                continue;
            }

            // Check if this is an extension group (has group.json)
            $groupJsonPath = $fullPath . '/group.json';
            if (file_exists($groupJsonPath)) {
                // This is a group - add the group itself, not individual extensions
                $groupData = $this->getGroupData($fullPath, $dir);
                if ($groupData) {
                    $extensions[] = $groupData;
                }
            } else {
                // Regular extension - check if it has admin or user subdirectory
                if (is_dir($fullPath . '/admin') || is_dir($fullPath . '/user') || is_dir($fullPath . '/core') ) {
                    $extData = $this->getExtensionData($fullPath, $dir);
                    if ($extData) {
                        $extensions[] = $extData;
                    }
                }
            }
        }

        return $extensions;
    }

    /**
     * Get group data including version and list of extensions
     *
     * @param string $groupPath Full path to the group directory
     * @param string $groupName Name of the group
     * @return array|null Group data or null if not valid
     */
    private function getGroupData($groupPath, $groupName)
    {
        $version = null;
        $releaseDate = null;
        $label = $groupName;
        $description = null;
        $extensionsList = [];

        // Read group.json for metadata
        $groupJsonPath = $groupPath . '/group.json';
        if (file_exists($groupJsonPath)) {
            $groupData = json_decode(file_get_contents($groupJsonPath), true);
            if ($groupData) {
                $label = $groupData['label'] ?? $groupName;
                $description = $groupData['description'] ?? null;
                $extensionsList = $groupData['extensions'] ?? [];
            }
        }

        // Read version.json for version info
        $versionPath = $groupPath . '/version.json';
        if (file_exists($versionPath)) {
            $versionData = json_decode(file_get_contents($versionPath), true);
            if ($versionData) {
                $version = $versionData['version'] ?? null;
                $releaseDate = $versionData['releaseDate'] ?? null;
            }
        }

        // Get license status
        $licenseStatus = $this->getLicenseStatus($groupName);

        return [
            'name' => $label,
            'folder' => $groupName,
            'version' => $version,
            'releaseDate' => $releaseDate,
            'group' => null,
            'path' => $groupName,
            'isGroup' => true,
            'description' => $description,
            'extensions' => $extensionsList,
            'licenseStatus' => $licenseStatus['status'],
            'licenseExpiry' => $licenseStatus['expiry'],
            'licenseKey' => $licenseStatus['licenseKey'],
        ];
    }

    /**
     * Get extension data including version
     *
     * @param string $extPath Full path to the extension
     * @param string $extName Extension name (folder name)
     * @return array|null Extension data or null if not valid
     */
    private function getExtensionData($extPath, $extName)
    {
        $version = null;
        $releaseDate = null;

        // Try to read version.json from extension
        $versionPath = $extPath . '/version.json';
        if (file_exists($versionPath)) {
            $versionData = json_decode(file_get_contents($versionPath), true);
            if ($versionData) {
                $version = $versionData['version'] ?? null;
                $releaseDate = $versionData['releaseDate'] ?? null;
            }
        }

        // Get license status
        $licenseStatus = $this->getLicenseStatus($extName);

        return [
            'name' => $extName,
            'folder' => $extName,
            'version' => $version,
            'releaseDate' => $releaseDate,
            'group' => null,
            'path' => $extName,
            'isGroup' => false,
            'licenseStatus' => $licenseStatus['status'],
            'licenseExpiry' => $licenseStatus['expiry'],
            'licenseKey' => $licenseStatus['licenseKey'],
        ];
    }

    /**
     * Get license status for an extension
     * Returns 'Active', 'Expired', or 'No License'
     * Prioritizes active licenses over expired ones
     *
     * @param string $code Extension directory/code
     * @return array ['status' => string, 'expiry' => string|null, 'licenseKey' => string|null]
     */
    private function getLicenseStatus($code)
    {
        $extensions = \Classes\BaseService::getInstance()->getSystemData(ExtensionData::SYSTEM_DATA_KEY_MY_EXTENSIONS);

        if (empty($extensions) || !is_array($extensions)) {
            return ['status' => 'No License', 'expiry' => null, 'licenseKey' => null];
        }

        $activeLicense = null;
        $expiredLicense = null;

        // Look through all licenses for this code
        foreach ($extensions as $extension) {
            if (!is_array($extension)) {
                continue;
            }

            $directory = $extension['directory'] ?? '';
            if ($directory !== $code) {
                continue;
            }

            $isExpired = $extension['is_expired'] ?? true;
            $expiryDate = $extension['expiry_date'] ?? null;
            $licenseKey = $extension['license_key'] ?? null;

            if ($isExpired === false) {
                // Found an active license - prioritize this
                $activeLicense = [
                    'status' => 'Active',
                    'expiry' => $expiryDate,
                    'licenseKey' => $licenseKey,
                ];
                break; // No need to continue, active license takes priority
            } else {
                // Keep track of expired license (in case no active found)
                if ($expiredLicense === null) {
                    $expiredLicense = [
                        'status' => 'Expired',
                        'expiry' => $expiryDate,
                        'licenseKey' => $licenseKey,
                    ];
                }
            }
        }

        // Return active if found, otherwise expired if found, otherwise no license
        if ($activeLicense !== null) {
            return $activeLicense;
        }

        if ($expiredLicense !== null) {
            return $expiredLicense;
        }

        return ['status' => 'No License', 'expiry' => null, 'licenseKey' => null];
    }
}
