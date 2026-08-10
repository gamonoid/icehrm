<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 2:53 PM
 */

namespace Loans\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class CompanyLoan extends BaseModel
{
    public $table = 'CompanyLoans';

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getUserAccess()
    {
        // "get" only: the employee Loans view resolves loan-type names via a
        // remote-source list; element reads by id stay admin-side.
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('loans', 'admin'),
        ];
    }

    /**
     * Columns this model's select boxes may request (see
     * BaseModel::fieldValueFields). Derived from the pickers that actually exist,
     * so this allows today's usage and nothing more.
     */
    public function fieldValueFields()
    {
        return array('id', 'name');
    }

}
