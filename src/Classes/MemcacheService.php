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

    private function __construct()
    {
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
}
