<?php

namespace Invoices\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class Invoice extends BaseModel
{
    public $table = 'Invoices';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
        ];
    }
}