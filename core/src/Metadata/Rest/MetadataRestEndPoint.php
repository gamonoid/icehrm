<?php
namespace Metadata\Rest;

use Classes\Data\Query\DataQuery;
use Classes\RestEndPoint;
use Users\Common\Model\User;

class MetadataRestEndPoint extends RestEndPoint
{
    public function getCurrency(User $user)
    {
        $query = new DataQuery('CurrencyType');
        $query->setLength(500);
        return $this->listByQuery($query);
    }

    public function getCountries(User $user)
    {
        $query = new DataQuery('Country');
        $query->setLength(500);
        return $this->listByQuery($query);
    }
}
