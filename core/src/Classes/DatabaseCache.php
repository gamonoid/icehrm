<?php

namespace Classes;

class DatabaseCache
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
     * Get a cached value by key
     *
     * @param string $key The cache key
     * @return mixed|null Returns the cached value or null if not found/expired
     */
    public function get($key)
    {
        $sql = "SELECT cache_value, expires_at FROM `Cache` WHERE cache_key = ?";
        $result = $this->executeQuery($sql, [$key]);

        if (empty($result)) {
            return null;
        }

        $row = $result[0];
        $expiresAt = strtotime($row['expires_at']);

        // Check if expired
        if ($expiresAt < time()) {
            // Clean up expired entry
            $this->delete($key);
            return null;
        }

        $value = $row['cache_value'];
        if ($value === null) {
            return null;
        }

        // Try to unserialize, return raw value if fails
        $unserialized = @unserialize($value);
        if ($unserialized === false && $value !== serialize(false)) {
            return $value;
        }

        return $unserialized;
    }

    /**
     * Set a cached value with expiry
     *
     * @param string $key The cache key
     * @param mixed $value The value to cache (will be serialized)
     * @param int $ttl Time to live in seconds (default: 3600 = 1 hour)
     * @return bool Returns true on success
     */
    public function set($key, $value, $ttl = 3600)
    {
        $serializedValue = serialize($value);
        $expiresAt = date('Y-m-d H:i:s', time() + $ttl);
        $createdAt = date('Y-m-d H:i:s');

        // Use REPLACE to handle both insert and update
        $sql = "REPLACE INTO `Cache` (cache_key, cache_value, expires_at, created_at) VALUES (?, ?, ?, ?)";
        $this->executeQuery($sql, [$key, $serializedValue, $expiresAt, $createdAt]);

        return true;
    }

    /**
     * Delete a cached value by key
     *
     * @param string $key The cache key
     * @return bool Returns true on success
     */
    public function delete($key)
    {
        $sql = "DELETE FROM `Cache` WHERE cache_key = ?";
        $this->executeQuery($sql, [$key]);
        return true;
    }

    /**
     * Delete all expired cache entries
     *
     * @return bool Returns true on success
     */
    public function deleteExpired()
    {
        $sql = "DELETE FROM `Cache` WHERE expires_at < ?";
        $this->executeQuery($sql, [date('Y-m-d H:i:s')]);
        return true;
    }

    /**
     * Clear all cache entries
     *
     * @return bool Returns true on success
     */
    public function clear()
    {
        $sql = "DELETE FROM `Cache`";
        $this->executeQuery($sql);
        return true;
    }

    /**
     * Check if a cache key exists and is not expired
     *
     * @param string $key The cache key
     * @return bool Returns true if key exists and is not expired
     */
    public function has($key)
    {
        $sql = "SELECT 1 FROM `Cache` WHERE cache_key = ? AND expires_at >= ?";
        $result = $this->executeQuery($sql, [$key, date('Y-m-d H:i:s')]);
        return !empty($result);
    }

    /**
     * Execute a database query
     *
     * @param string $sql The SQL query
     * @param array $params Query parameters
     * @return array Query results
     */
    private function executeQuery($sql, $params = [])
    {
        $connection = BaseService::getInstance()->getDB();

        if (empty($params)) {
            $result = $connection->Execute($sql);
        } else {
            $result = $connection->Execute($sql, $params);
        }

        if ($result === false) {
            return [];
        }

        // Execute returns array directly for SELECT queries
        if (is_array($result)) {
            return $result;
        }

        return [];
    }
}
