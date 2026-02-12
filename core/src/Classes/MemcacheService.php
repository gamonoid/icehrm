<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:29 AM
 */

namespace Classes;

class MemcacheService
{

    private $connection             = null;
    public static $openConnections  = array();
    private static $me  = null;

    protected $inMemoryStore = [];
    protected $cacheDir = null;

    private function __construct()
    {
        // Set up file cache directory
        $this->cacheDir = CLIENT_BASE_PATH . 'cache';
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }

    public static function getInstance()
    {
        if (self::$me == null) {
            self::$me = new MemcacheService();
        }

        return self::$me;
    }

    private function connect()
    {

        if ($this->connection == null) {
            $this->connection = new \Memcached();
            $this->connection->addServer(MEMCACHE_HOST, MEMCACHE_PORT);

            if (!$this->isConnected()) {
                $this->connection = null;
            } else {
                self::$openConnections[] = $this->connection;
            }
        }
        return $this->connection;
    }

    private function isConnected()
    {
        $statuses = $this->connection->getStats();
        return isset($statuses[MEMCACHE_HOST.":".MEMCACHE_PORT]);
    }

    private function compressKey($key)
    {
        return crc32(APP_DB.$key).md5(CLIENT_NAME);
    }

    public function set($key, $value, $expiry = 3600)
    {
        // Try to set in memcached server first
        if (!$this->setInServer($key, $value, $expiry)) {
            // If memcached fails, try file cache
            if (!$this->setInFileCache($key, $value, $expiry)) {
                // If file cache fails, fall back to in-memory
                $this->inMemoryStore[$this->compressKey($key)] = $value;
            }
        }

        return true;
    }

    public function setInServer($key, $value, $expiry = 3600)
    {
        if (!class_exists('\\Memcached')) {
            return false;
        }
        $key = $this->compressKey($key);
        $memcache = $this->connect();

        if (!empty($memcache) && $this->isConnected()) {
            $ok = $memcache->set($key, $value, time() + $expiry);
            if (!$ok) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function get($key)
    {
        // Try memcached server first
        $data = $this->getFromServer($key);
        if ($data !== false) {
            return $data;
        }

        // Try file cache
        $data = $this->getFromFileCache($key);
        if ($data !== false) {
            return $data;
        }

        // Fall back to in-memory store
        if (isset($this->inMemoryStore[$this->compressKey($key)])) {
            return $this->inMemoryStore[$this->compressKey($key)];
        }

        return false;
    }

    public function getFromServer($key)
    {
        if (!class_exists('\\Memcached')) {
            return false;
        }
        $key = $this->compressKey($key);
        $memcache = $this->connect();
        if (!empty($memcache) && $this->isConnected()) {
            return $memcache->get($key);
        } else {
            return false;
        }
    }

    public function close()
    {
        if ($this->connection != null) {
            if ($this->isConnected()) {
                $this->connection->quit();
            }
            $this->connection = null;
        }
    }

    /**
     * Set value in file-based cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $expiry Expiry time in seconds
     * @return bool Success status
     */
    protected function setInFileCache($key, $value, $expiry = 3600)
    {
        if (!is_dir($this->cacheDir) || !is_writable($this->cacheDir)) {
            return false;
        }

        try {
            $compressedKey = $this->compressKey($key);
            $cacheFile = $this->cacheDir . '/' . $compressedKey . '.cache';

            $cacheData = [
                'value' => $value,
                'expiry' => time() + $expiry,
            ];

            $serialized = serialize($cacheData);

            // Write to temp file first, then rename for atomic operation
            $tempFile = $cacheFile . '.tmp';
            if (file_put_contents($tempFile, $serialized, LOCK_EX) !== false) {
                return rename($tempFile, $cacheFile);
            }

            return false;
        } catch (\Exception $e) {
            error_log("File cache write error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get value from file-based cache
     *
     * @param string $key Cache key
     * @return mixed|false Cached value or false if not found/expired
     */
    protected function getFromFileCache($key)
    {
        if (!is_dir($this->cacheDir)) {
            return false;
        }

        try {
            $compressedKey = $this->compressKey($key);
            $cacheFile = $this->cacheDir . '/' . $compressedKey . '.cache';

            if (!file_exists($cacheFile)) {
                return false;
            }

            $serialized = @file_get_contents($cacheFile);
            if ($serialized === false) {
                return false;
            }

            $cacheData = @unserialize($serialized);
            if ($cacheData === false || !is_array($cacheData)) {
                // Invalid cache file, delete it
                @unlink($cacheFile);
                return false;
            }

            // Check expiry
            if (isset($cacheData['expiry']) && $cacheData['expiry'] < time()) {
                // Cache expired, delete file
                @unlink($cacheFile);
                return false;
            }

            return $cacheData['value'] ?? false;
        } catch (\Exception $e) {
            error_log("File cache read error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clean up expired cache files (can be called periodically)
     *
     * @return int Number of files cleaned up
     */
    public function cleanupExpiredFileCache()
    {
        if (!is_dir($this->cacheDir)) {
            return 0;
        }

        $cleaned = 0;
        $files = glob($this->cacheDir . '/*.cache');

        if (!$files) {
            return 0;
        }

        foreach ($files as $file) {
            try {
                $serialized = @file_get_contents($file);
                if ($serialized === false) {
                    continue;
                }

                $cacheData = @unserialize($serialized);
                if ($cacheData === false || !is_array($cacheData)) {
                    // Invalid cache file
                    @unlink($file);
                    $cleaned++;
                    continue;
                }

                if (isset($cacheData['expiry']) && $cacheData['expiry'] < time()) {
                    // Expired
                    @unlink($file);
                    $cleaned++;
                }
            } catch (\Exception $e) {
                error_log("Error cleaning cache file {$file}: " . $e->getMessage());
            }
        }

        return $cleaned;
    }
}
