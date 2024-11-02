<?php

namespace Classes;

use Predis\Client;

class RedisCacheService implements CacheService
{
    protected $client = null;
    protected $appName = null;

    public function __construct($uri, $appName)
    {
        $this->uri = $uri;
        $this->appName = $appName;
    }

    protected function getClient()
    {
        if ($this->client === null && !empty($this->uri)) {
            $this->client = new Client($this->uri);
        }

        return $this->client;
    }

    protected function getAppKey($keyData)
    {
        return sprintf('%s-%s', $this->appName, implode('-', $keyData));
    }


    public function setDBQuery($entity, $query, $params, $result, $ttl = 600)
    {
        /**
 * @var Client $client
*/
        $client = $this->getClient();
        if ($client == null) {
            return null;
        }

        $client->set($this->getAppKey([$entity, $query, implode('-', $params)]), base64_encode(serialize($result)));
        $client->expire($this->getAppKey([$query, $params]), $ttl);
    }

    public function getDBQuery($entity, $query, $params)
    {
        /**
 * @var Client $client
*/
        $client = $this->getClient();
        if ($client == null) {
            return null;
        }

        $base64 = $client->get($this->getAppKey([$entity, $query, implode('-', $params)]));
        if (empty($base64)) {
            return null;
        }

        $data = unserialize(base64_decode($base64));

        if (empty($data)) {
            return null;
        }

        return $data;
    }

    public function deleteDatabaseEntity($table, $id)
    {
        /**
 * @var Client $client
*/
        $client = $this->getClient();
        if ($client == null) {
            return null;
        }

        $client->del([$this->getAppKey([$table, $id])]);
    }

    public function deleteQuery($query)
    {
        /**
 * @var Client $client
*/
        $client = $this->getClient();
        if ($client == null) {
            return null;
        }

        $client->del([$this->getAppKey([$query])]);
    }

    protected function deleteByPrefix($prefix)
    {
        /**
 * @var Client $client
*/
        $client = $this->getClient();
        if ($client == null) {
            return null;
        }
        $list = $client->keys($this->getAppKey([$prefix]).'*');
        if (!empty($list)) {
            $client->del($list);
        }
    }

    public function deleteByEntity($entity)
    {
        $this->deleteByPrefix($entity.'-');
    }
}
