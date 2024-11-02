<?php
namespace Classes\Data;

use Classes\Data\Query\DataQuery;

class DataReader
{
    public static function getData(DataQuery $query)
    {
        $table = $query->getTable();

        $sLimit = " LIMIT " . intval($query->getStartPage()) . ", " . intval($query->getLength());

        $sortData = $query->getSortingData();
        $data = \Classes\BaseService::getInstance()->getData(
            $table,
            null,
            $query->getFilters(),
            $query->getOrderBy(),
            $sLimit,
            json_encode($query->getSearchColumns()),
            $query->getSearchTerm(),
            $query->isIsSubOrdinates(),
            $query->isSkipProfileRestriction(),
            $sortData
        );
        return $data;
    }

    public static function getDataCount(DataQuery $query)
    {
        $table = $query->getTable();

        $sortData = $query->getSortingData();
        $count = \Classes\BaseService::getInstance()->getData(
            $table,
            null,
            $query->getFilters(),
            $query->getOrderBy(),
            null,
            json_encode($query->getSearchColumns()),
            $query->getSearchTerm(),
            $query->isIsSubOrdinates(),
            $query->isSkipProfileRestriction(),
            $sortData,
            true
        );

        return $count;
    }
}
