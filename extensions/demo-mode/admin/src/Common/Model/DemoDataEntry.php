<?php

namespace DemoModeAdmin\Common\Model;

use Model\BaseModel;

class DemoDataEntry extends BaseModel
{
    public $table = 'DemoDataEntries';

    public function getAdminAccess()
    {
        return ['get', 'element', 'add','save', 'delete'];
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getUserOnlyMeAccess()
    {
        return array();
    }

    public function getDataMapping()
    {
        return [
            'id',
            'table_name',
            'record_id',
            'data_type',
            'created',
        ];
    }
}
