<?php

namespace MarketplaceAdmin;

use Utils\LogManager;

class ExtensionUpdateService
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
     * Check if the extensions directory is writable
     *
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function checkWritePermissions()
    {
        $extensionsPath = APP_BASE_PATH . '../extensions/';

        if (!is_dir($extensionsPath)) {
            return [
                'success' => false,
                'error' => "Extensions directory does not exist: {$extensionsPath}"
            ];
        }

        if (!is_writable($extensionsPath)) {
            $currentUser = get_current_user();
            $processUser = posix_getpwuid(posix_geteuid())['name'] ?? 'unknown';
            $permissions = substr(sprintf('%o', fileperms($extensionsPath)), -4);
            $owner = posix_getpwuid(fileowner($extensionsPath))['name'] ?? 'unknown';
            $group = posix_getgrgid(filegroup($extensionsPath))['name'] ?? 'unknown';

            return [
                'success' => false,
                'error' => "Cannot write to extensions directory. " .
                    "Path: {$extensionsPath}, " .
                    "Current process user: {$processUser}, " .
                    "Directory owner: {$owner}, " .
                    "Directory group: {$group}, " .
                    "Permissions: {$permissions}. " .
                    "Please ensure the web server user has write permissions to this directory."
            ];
        }

        return ['success' => true, 'error' => null];
    }

    /**
     * Ensure the updates directory exists
     *
     * @return array ['success' => bool, 'path' => string|null, 'error' => string|null]
     */
    public function ensureUpdatesDirectory()
    {
        $updatesPath = CLIENT_BASE_PATH . 'data/updates/';

        if (!is_dir($updatesPath)) {
            if (!mkdir($updatesPath, 0755, true)) {
                return [
                    'success' => false,
                    'path' => null,
                    'error' => "Failed to create updates directory: {$updatesPath}"
                ];
            }
        }

        if (!is_writable($updatesPath)) {
            return [
                'success' => false,
                'path' => null,
                'error' => "Updates directory is not writable: {$updatesPath}"
            ];
        }

        return ['success' => true, 'path' => $updatesPath, 'error' => null];
    }

    /**
     * Download the extension zip file
     *
     * @param string $licenseKey The license key for the extension
     * @param string $updatesPath The path to save the downloaded file
     * @return array ['success' => bool, 'zipPath' => string|null, 'error' => string|null]
     */
    public function downloadExtension($licenseKey, $updatesPath)
    {
        $downloadUrl = APP_WEB_URL . '/download-extension?id=' . urlencode($licenseKey);
        $zipPath = $updatesPath . 'extension_' . time() . '.zip';

        $ch = curl_init();
        $fp = fopen($zipPath, 'w+');

        if (!$fp) {
            return [
                'success' => false,
                'zipPath' => null,
                'error' => "Failed to create file for download: {$zipPath}"
            ];
        }

        // Capture an advertised archive checksum, if the server sends one, so the
        // downloaded bytes can be verified before any of them are extracted.
        $advertisedSha256 = null;
        $headerCallback = function ($curlHandle, $header) use (&$advertisedSha256) {
            $parts = explode(':', $header, 2);
            if (count($parts) === 2
                && in_array(strtolower(trim($parts[0])), ['x-checksum-sha256', 'x-content-sha256'], true)
            ) {
                $advertisedSha256 = trim($parts[1]);
            }
            return strlen($header);
        };

        curl_setopt_array($ch, [
            CURLOPT_URL => $downloadUrl,
            CURLOPT_FILE => $fp,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => \Classes\BaseService::shouldVerifyOutboundTls(),
            CURLOPT_SSL_VERIFYHOST => \Classes\BaseService::shouldVerifyOutboundTls() ? 2 : 0,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_DNS_CACHE_TIMEOUT => 0,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_HEADERFUNCTION => $headerCallback,
            CURLOPT_HTTPHEADER => [
                'Accept: application/zip, application/octet-stream',
            ],
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);

        curl_close($ch);
        fclose($fp);

        if ($result === false) {
            @unlink($zipPath);
            return [
                'success' => false,
                'zipPath' => null,
                'error' => "Download failed: {$error}"
            ];
        }

        if ($httpCode >= 400) {
            // Read the error response from the file
            $errorContent = file_get_contents($zipPath);
            @unlink($zipPath);

            $errorMessage = "Download failed with HTTP {$httpCode}";

            // Try to parse JSON error response
            $errorData = json_decode($errorContent, true);
            if ($errorData && isset($errorData['error'])) {
                $errorMessage = is_string($errorData['error'])
                    ? $errorData['error']
                    : ($errorData['error'][0][0]['message'] ?? $errorMessage);
            } elseif (!empty($errorContent) && strlen($errorContent) < 500) {
                $errorMessage = $errorContent;
            }

            return [
                'success' => false,
                'zipPath' => null,
                'error' => $errorMessage
            ];
        }

        if (!$this->verifyArchiveChecksum($zipPath, $advertisedSha256)) {
            @unlink($zipPath);
            return [
                'success' => false,
                'zipPath' => null,
                'error' => 'Downloaded extension archive failed its integrity check'
            ];
        }

        return ['success' => true, 'zipPath' => $zipPath, 'error' => null];
    }

    /**
     * Backup existing extension
     *
     * @param string $extensionName The name of the extension to backup
     * @return array ['success' => bool, 'backupPath' => string|null, 'error' => string|null]
     */
    public function backupExtension($extensionName)
    {
        $extensionsPath = APP_BASE_PATH . '../extensions/';
        $extensionPath = $extensionsPath . $extensionName;

        if (!is_dir($extensionPath)) {
            // No existing extension to backup
            return ['success' => true, 'backupPath' => null, 'error' => null];
        }

        $backupsPath = CLIENT_BASE_PATH . 'data/updates/backups/';
        if (!is_dir($backupsPath)) {
            if (!mkdir($backupsPath, 0755, true)) {
                return [
                    'success' => false,
                    'backupPath' => null,
                    'error' => "Failed to create backups directory: {$backupsPath}"
                ];
            }
        }

        $timestamp = date('Y-m-d_H-i-s');
        $backupPath = $backupsPath . $extensionName . '_' . $timestamp;

        if (!$this->recursiveCopy($extensionPath, $backupPath)) {
            return [
                'success' => false,
                'backupPath' => null,
                'error' => "Failed to backup extension to: {$backupPath}"
            ];
        }

        return ['success' => true, 'backupPath' => $backupPath, 'error' => null];
    }

    /**
     * Extract and install the extension
     *
     * @param string $zipPath Path to the downloaded zip file
     * @param string $extensionName Name of the extension
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function extractAndInstall($zipPath, $extensionName)
    {
        $extensionsPath = APP_BASE_PATH . '../extensions/';
        $extractPath = CLIENT_BASE_PATH . 'data/updates/extracted_' . time() . '/';

        // Create extraction directory
        if (!mkdir($extractPath, 0755, true)) {
            return [
                'success' => false,
                'error' => "Failed to create extraction directory: {$extractPath}"
            ];
        }

        // Extract zip
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            $this->recursiveDelete($extractPath);
            return [
                'success' => false,
                'error' => "Failed to open ZIP file: {$zipPath}"
            ];
        }

        // Reject "zip slip" archives before extracting anything: ZipArchive::extractTo()
        // will happily follow "../" or an absolute path in an entry name and write
        // outside $extractPath — which, for an archive fetched over the network, means
        // arbitrary file overwrite in the application tree.
        $unsafeEntry = $this->findUnsafeZipEntry($zip);
        if ($unsafeEntry !== null) {
            $zip->close();
            $this->recursiveDelete($extractPath);
            return [
                'success' => false,
                'error' => "ZIP contains an unsafe entry path: {$unsafeEntry}"
            ];
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // Find the extension directory in extracted content
        $extractedDirs = array_filter(scandir($extractPath), function($item) use ($extractPath) {
            return $item !== '.' && $item !== '..' && is_dir($extractPath . $item);
        });

        if (empty($extractedDirs)) {
            $this->recursiveDelete($extractPath);
            return [
                'success' => false,
                'error' => "No directory found in extracted ZIP"
            ];
        }

        // Get the first directory (should be the extension)
        $extractedDir = reset($extractedDirs);
        $sourcePath = $extractPath . $extractedDir;
        $targetPath = $extensionsPath . $extensionName;

        // Remove old extension if exists
        if (is_dir($targetPath)) {
            if (!$this->recursiveDelete($targetPath)) {
                $this->recursiveDelete($extractPath);
                return [
                    'success' => false,
                    'error' => "Failed to remove old extension: {$targetPath}"
                ];
            }
        }

        // Move new extension
        if (!rename($sourcePath, $targetPath)) {
            // Try copy instead of rename (across filesystems)
            if (!$this->recursiveCopy($sourcePath, $targetPath)) {
                $this->recursiveDelete($extractPath);
                return [
                    'success' => false,
                    'error' => "Failed to install extension to: {$targetPath}"
                ];
            }
        }

        // Cleanup
        $this->recursiveDelete($extractPath);
        @unlink($zipPath);

        return ['success' => true, 'error' => null];
    }

    /**
     * Recursively copy a directory
     */
    private function recursiveCopy($src, $dst)
    {
        if (!is_dir($src)) {
            return copy($src, $dst);
        }

        if (!is_dir($dst) && !mkdir($dst, 0755, true)) {
            return false;
        }

        $dir = opendir($src);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;

            if (is_dir($srcPath)) {
                if (!$this->recursiveCopy($srcPath, $dstPath)) {
                    closedir($dir);
                    return false;
                }
            } else {
                if (!copy($srcPath, $dstPath)) {
                    closedir($dir);
                    return false;
                }
            }
        }
        closedir($dir);
        return true;
    }

    /**
     * Recursively delete a directory
     */
    private function recursiveDelete($path)
    {
        if (!is_dir($path)) {
            return @unlink($path);
        }

        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $path . '/' . $file;
            if (is_dir($filePath)) {
                $this->recursiveDelete($filePath);
            } else {
                @unlink($filePath);
            }
        }
        return @rmdir($path);
    }

    /**
     * Scan a ZIP for entries that would escape the extraction directory
     * ("zip slip"). Returns the offending entry name, or null when every entry is
     * safe to extract.
     *
     * ZipArchive::extractTo() does not do this for us: an entry named
     * "../../core/service.php" or "/etc/passwd" is written where it points.
     *
     * @param \ZipArchive $zip An already-opened archive
     * @return string|null
     */
    private function findUnsafeZipEntry($zip)
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === false || $name === '') {
                return '(unreadable entry name)';
            }

            // Normalise Windows separators before inspecting the path segments.
            $normalised = str_replace('\\', '/', $name);

            if (strpos($normalised, "\0") !== false) {
                return $name;
            }
            // Absolute paths (POSIX and drive-letter) escape the target directory.
            if (substr($normalised, 0, 1) === '/' || preg_match('#^[A-Za-z]:#', $normalised) === 1) {
                return $name;
            }
            // Any ".." segment can walk out, wherever it appears in the path.
            $segments = explode('/', $normalised);
            if (in_array('..', $segments, true)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Verify a downloaded archive against an expected SHA-256, when the server
     * supplied one. TLS alone is not sufficient for a channel that installs code,
     * so this is the second half of that guarantee.
     *
     * Returns true when the archive matches, or when no checksum was advertised
     * (older marketplace responses carry none — those keep working, protected by
     * certificate verification alone).
     *
     * @param string $zipPath
     * @param string|null $expectedSha256
     * @return bool
     */
    private function verifyArchiveChecksum($zipPath, $expectedSha256)
    {
        if (empty($expectedSha256) || !is_string($expectedSha256)) {
            return true;
        }

        $actual = @hash_file('sha256', $zipPath);
        if ($actual === false) {
            return false;
        }

        return hash_equals(strtolower($expectedSha256), strtolower($actual));
    }

    /**
     * Perform full update process
     *
     * @param string $extensionName Name of the extension
     * @param string $licenseKey License key for download
     * @return array ['success' => bool, 'error' => string|null, 'backupPath' => string|null]
     */
    public function updateExtension($extensionName, $licenseKey)
    {
        // Step 1: Check write permissions
        $permCheck = $this->checkWritePermissions();
        if (!$permCheck['success']) {
            return ['success' => false, 'error' => $permCheck['error'], 'backupPath' => null];
        }

        // Step 2: Ensure updates directory
        $updatesDir = $this->ensureUpdatesDirectory();
        if (!$updatesDir['success']) {
            return ['success' => false, 'error' => $updatesDir['error'], 'backupPath' => null];
        }

        // Step 3: Download extension
        $download = $this->downloadExtension($licenseKey, $updatesDir['path']);
        if (!$download['success']) {
            return ['success' => false, 'error' => $download['error'], 'backupPath' => null];
        }

        // Step 4: Backup existing extension
        $backup = $this->backupExtension($extensionName);
        if (!$backup['success']) {
            @unlink($download['zipPath']);
            return ['success' => false, 'error' => $backup['error'], 'backupPath' => null];
        }

        // Step 5: Extract and install
        $install = $this->extractAndInstall($download['zipPath'], $extensionName);
        if (!$install['success']) {
            return [
                'success' => false,
                'error' => $install['error'],
                'backupPath' => $backup['backupPath']
            ];
        }

        return [
            'success' => true,
            'error' => null,
            'backupPath' => $backup['backupPath']
        ];
    }

    /**
     * Check if multiple directories are writable for core update
     *
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function checkCoreWritePermissions()
    {
        $basePath = APP_BASE_PATH . '../';
        $dirsToCheck = ['app', 'core', 'docs', 'extensions', 'release-notes', 'web'];

        foreach ($dirsToCheck as $dir) {
            $path = $basePath . $dir;
            if (is_dir($path) && !is_writable($path)) {
                $processUser = posix_getpwuid(posix_geteuid())['name'] ?? 'unknown';
                return [
                    'success' => false,
                    'error' => "Cannot write to directory: {$path}. Process user: {$processUser}"
                ];
            }
        }

        // Check if base path is writable for root files
        if (!is_writable($basePath)) {
            $processUser = posix_getpwuid(posix_geteuid())['name'] ?? 'unknown';
            return [
                'success' => false,
                'error' => "Cannot write to base directory: {$basePath}. Process user: {$processUser}"
            ];
        }

        return ['success' => true, 'error' => null];
    }

    /**
     * Download IceHrm core from S3 URL
     *
     * @param string $downloadUrl The S3 download URL
     * @param string $updatesPath The path to save the downloaded file
     * @return array ['success' => bool, 'zipPath' => string|null, 'error' => string|null]
     */
    public function downloadCore($downloadUrl, $updatesPath)
    {
        $zipPath = $updatesPath . 'icehrm_core_' . time() . '.zip';

        $ch = curl_init();
        $fp = fopen($zipPath, 'w+');

        if (!$fp) {
            return [
                'success' => false,
                'zipPath' => null,
                'error' => "Failed to create file for download: {$zipPath}"
            ];
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $downloadUrl,
            CURLOPT_FILE => $fp,
            CURLOPT_TIMEOUT => 600,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => \Classes\BaseService::shouldVerifyOutboundTls(),
            CURLOPT_SSL_VERIFYHOST => \Classes\BaseService::shouldVerifyOutboundTls() ? 2 : 0,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_HTTPHEADER => [
                'Accept: application/zip, application/octet-stream',
            ],
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);
        fclose($fp);

        if ($result === false) {
            @unlink($zipPath);
            return [
                'success' => false,
                'zipPath' => null,
                'error' => "Download failed: {$error}"
            ];
        }

        if ($httpCode >= 400) {
            $errorContent = file_get_contents($zipPath);
            @unlink($zipPath);
            return [
                'success' => false,
                'zipPath' => null,
                'error' => "Download failed with HTTP {$httpCode}: " . substr($errorContent, 0, 200)
            ];
        }

        // Verify file size
        if (filesize($zipPath) < 1000) {
            $content = file_get_contents($zipPath);
            @unlink($zipPath);
            return [
                'success' => false,
                'zipPath' => null,
                'error' => "Downloaded file too small. Content: " . substr($content, 0, 200)
            ];
        }

        return ['success' => true, 'zipPath' => $zipPath, 'error' => null];
    }

    /**
     * Backup core files before update
     *
     * @return array ['success' => bool, 'backupPath' => string|null, 'error' => string|null]
     */
    public function backupCore()
    {
        $basePath = APP_BASE_PATH . '../';
        $backupsPath = CLIENT_BASE_PATH . 'data/updates/backups/';

        if (!is_dir($backupsPath)) {
            if (!mkdir($backupsPath, 0755, true)) {
                return [
                    'success' => false,
                    'backupPath' => null,
                    'error' => "Failed to create backups directory: {$backupsPath}"
                ];
            }
        }

        $timestamp = date('Y-m-d_H-i-s');
        $backupPath = $backupsPath . 'icehrm_core_' . $timestamp . '/';

        if (!mkdir($backupPath, 0755, true)) {
            return [
                'success' => false,
                'backupPath' => null,
                'error' => "Failed to create backup directory: {$backupPath}"
            ];
        }

        // Backup directories
        $dirsToBackup = ['core', 'docs', 'release-notes'];
        foreach ($dirsToBackup as $dir) {
            $srcPath = $basePath . $dir;
            if (is_dir($srcPath)) {
                if (!$this->recursiveCopy($srcPath, $backupPath . $dir)) {
                    return [
                        'success' => false,
                        'backupPath' => null,
                        'error' => "Failed to backup directory: {$dir}"
                    ];
                }
            }
        }

        // Backup extensions (editor and marketplace only)
        $extensionsToBackup = ['editor', 'marketplace'];
        if (!mkdir($backupPath . 'extensions', 0755, true)) {
            return [
                'success' => false,
                'backupPath' => null,
                'error' => "Failed to create extensions backup directory"
            ];
        }
        foreach ($extensionsToBackup as $ext) {
            $srcPath = $basePath . 'extensions/' . $ext;
            if (is_dir($srcPath)) {
                if (!$this->recursiveCopy($srcPath, $backupPath . 'extensions/' . $ext)) {
                    return [
                        'success' => false,
                        'backupPath' => null,
                        'error' => "Failed to backup extension: {$ext}"
                    ];
                }
            }
        }

        return ['success' => true, 'backupPath' => $backupPath, 'error' => null];
    }

    /**
     * Extract and install core update
     *
     * @param string $zipPath Path to the downloaded zip file
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function extractAndInstallCore($zipPath)
    {
        $basePath = APP_BASE_PATH . '../';
        $extractPath = CLIENT_BASE_PATH . 'data/updates/extracted_core_' . time() . '/';

        // Create extraction directory
        if (!mkdir($extractPath, 0755, true)) {
            return [
                'success' => false,
                'error' => "Failed to create extraction directory: {$extractPath}"
            ];
        }

        // Extract zip
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            $this->recursiveDelete($extractPath);
            return [
                'success' => false,
                'error' => "Failed to open ZIP file: {$zipPath}"
            ];
        }

        // Reject "zip slip" archives before extracting anything — see extractAndInstall().
        $unsafeEntry = $this->findUnsafeZipEntry($zip);
        if ($unsafeEntry !== null) {
            $zip->close();
            $this->recursiveDelete($extractPath);
            return [
                'success' => false,
                'error' => "ZIP contains an unsafe entry path: {$unsafeEntry}"
            ];
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // Find the icehrm directory in extracted content
        $extractedDirs = array_filter(scandir($extractPath), function($item) use ($extractPath) {
            return $item !== '.' && $item !== '..' && is_dir($extractPath . $item);
        });

        if (empty($extractedDirs)) {
            $this->recursiveDelete($extractPath);
            return [
                'success' => false,
                'error' => "No directory found in extracted ZIP"
            ];
        }

        // Get the source path (icehrm folder inside the zip)
        $extractedDir = reset($extractedDirs);
        $sourcePath = $extractPath . $extractedDir . '/';

        // Directories to copy
        $dirsToCopy = ['app', 'core', 'docs', 'release-notes', 'web'];
        foreach ($dirsToCopy as $dir) {
            $src = $sourcePath . $dir;
            $dst = $basePath . $dir;

            if (is_dir($src)) {
                // For these directories, merge contents (don't delete existing)
                if (!$this->recursiveMerge($src, $dst)) {
                    $this->recursiveDelete($extractPath);
                    return [
                        'success' => false,
                        'error' => "Failed to update directory: {$dir}"
                    ];
                }
            }
        }

        // Copy extensions (editor and marketplace)
        $extensionsToCopy = ['editor', 'marketplace'];
        foreach ($extensionsToCopy as $ext) {
            $src = $sourcePath . 'extensions/' . $ext;
            $dst = $basePath . 'extensions/' . $ext;

            if (is_dir($src)) {
                // Remove old extension and copy new one
                if (is_dir($dst)) {
                    $this->recursiveDelete($dst);
                }
                if (!$this->recursiveCopy($src, $dst)) {
                    $this->recursiveDelete($extractPath);
                    return [
                        'success' => false,
                        'error' => "Failed to update extension: {$ext}"
                    ];
                }
            }
        }

        // Copy root files
        $rootFiles = [
            'Dockerfile', 'Dockerfile-prod', 'Dockerfile-testing', 'Dockerfile-worker',
            'docker-compose.yaml', 'docker-compose-prod.yaml', 'docker-compose-testing.yaml',
            'readme.md', 'release.md', 'RoboFile.php', 'version.json'
        ];
        foreach ($rootFiles as $file) {
            $src = $sourcePath . $file;
            $dst = $basePath . $file;

            if (file_exists($src)) {
                if (!copy($src, $dst)) {
                    // Log but don't fail for root files
                    LogManager::getInstance()->info("Warning: Failed to copy root file: {$file}");
                }
            }
        }

        // Cleanup
        $this->recursiveDelete($extractPath);
        @unlink($zipPath);

        return ['success' => true, 'error' => null];
    }

    /**
     * Recursively merge source directory into destination (copy new/updated files)
     */
    private function recursiveMerge($src, $dst)
    {
        if (!is_dir($src)) {
            return copy($src, $dst);
        }

        if (!is_dir($dst) && !mkdir($dst, 0755, true)) {
            return false;
        }

        $dir = opendir($src);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;

            if (is_dir($srcPath)) {
                if (!$this->recursiveMerge($srcPath, $dstPath)) {
                    closedir($dir);
                    return false;
                }
            } else {
                if (!copy($srcPath, $dstPath)) {
                    closedir($dir);
                    return false;
                }
            }
        }
        closedir($dir);
        return true;
    }

    /**
     * Perform full core update process
     *
     * @param string $downloadUrl The S3 download URL from modules response
     * @return array ['success' => bool, 'error' => string|null, 'backupPath' => string|null]
     */
    public function updateCore($downloadUrl)
    {
        // Step 1: Check write permissions for all directories
        $permCheck = $this->checkCoreWritePermissions();
        if (!$permCheck['success']) {
            return ['success' => false, 'error' => $permCheck['error'], 'backupPath' => null];
        }

        // Step 2: Ensure updates directory
        $updatesDir = $this->ensureUpdatesDirectory();
        if (!$updatesDir['success']) {
            return ['success' => false, 'error' => $updatesDir['error'], 'backupPath' => null];
        }

        // Step 3: Download core update from S3
        $download = $this->downloadCore($downloadUrl, $updatesDir['path']);
        if (!$download['success']) {
            return ['success' => false, 'error' => $download['error'], 'backupPath' => null];
        }

        // Step 4: Backup existing core files
        $backup = $this->backupCore();
        if (!$backup['success']) {
            @unlink($download['zipPath']);
            return ['success' => false, 'error' => $backup['error'], 'backupPath' => null];
        }

        // Step 5: Extract and install core update
        $install = $this->extractAndInstallCore($download['zipPath']);
        if (!$install['success']) {
            return [
                'success' => false,
                'error' => $install['error'],
                'backupPath' => $backup['backupPath']
            ];
        }

        return [
            'success' => true,
            'error' => null,
            'backupPath' => $backup['backupPath']
        ];
    }
}
