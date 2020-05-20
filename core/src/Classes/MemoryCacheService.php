<?php

namespace Classes;

class MemoryCacheService implements CacheService
{
    protected $appName = null;

    protected $store = [];

    public function __construct($appName)
    {
        $this->appName = $appName;
    }

    protected function getAppKey($keyData)
    {
        return sprintf('%s-%s', $this->appName, implode('-', $keyData));
    }


    public function setDBQuery($entity, $query, $params, $result, $ttl = 600)
    {
        $this->store[$this->getAppKey($entity, $query, implode('-', $params))] = serialize($result);
    }

    public function getDBQuery($entity, $query, $params)
    {

        $data = $this->store[$this->getAppKey([$entity, $query, implode('-', $params)])];
        if (empty($data)) {
            return null;
        }

        $data = unserialize($data);
        if (empty($data)) {
            return null;
        }

        return $data;
    }

    public function deleteByEntity($entity)
    {
        $this->deleteByPrefix($entity.'-');
        $newStore = [];
        foreach ($this->store as $key => $val) {
            if (substr($key, 0, strlen($entity.'-')) === $entity.'-') {
                continue;
            }

            $newStore[$key] = $val;
        }

        $this->store = $newStore;
    }
}
