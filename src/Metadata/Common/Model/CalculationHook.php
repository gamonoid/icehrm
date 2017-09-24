<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 3:08 PM
 */

namespace Metadata\Common\Model;

use Classes\BaseService;
use Model\BaseModel;

class CalculationHook extends BaseModel
{
    public $table = 'CalculationHooks';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getAnonymousAccess()
    {
        return array("get","element");
    }
    // @codingStandardsIgnoreStart
    function Find($whereOrderBy, $bindarr = false, $pkeysArr = false, $extra = array())
    {
        return BaseService::getInstance()->getCalculationHooks();
    }

    function Load($where = null, $bindarr = false)
    {
        return BaseService::getInstance()->getCalculationHook($bindarr[0]);
    }
    // @codingStandardsIgnoreEnd
}
