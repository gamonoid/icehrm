<?php
namespace Consolidation\TestUtils;

use Consolidation\AnnotatedCommand\Cache\SimpleCacheInterface;

/**
 * A simple in-memory cache for testing
 */
class InMemoryCacheStore implements SimpleCacheInterface
{
    protected $cache;

    public function __construct()
    {
        $this->cache = [];
    }

    /**
     * Test for an entry from the cache
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        return array_key_exists($key, $this->cache);
    }

    /**
     * Get an entry from the cache
     * @param string $key
     * @return array
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            return [];
        }
        return $this->cache[$key];
    }

    /**
     * Store an entry in the cache
     * @param string $key
     * @param array $data
     */
    public function set($key, $data)
    {
        $this->cache[$key] = $data;
    }
}
