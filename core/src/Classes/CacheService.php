<?php

namespace Classes;

interface CacheService
{
    public function setDBQuery($entity, $query, $params, $result, $ttl = 600);

    public function getDBQuery($entity, $query, $params);

    public function deleteByEntity($entity);
}
