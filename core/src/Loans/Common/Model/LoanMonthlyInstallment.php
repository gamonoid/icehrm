<?php

namespace Loans\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class LoanMonthlyInstallment extends BaseModel
{
    public $table = 'LoanMonthlyInstallments';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('loans', 'admin'),
        ];
    }
}
