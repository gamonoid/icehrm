<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 7:44 AM
 */

namespace Training\Common\Model;

use Classes\IceResponse;
use Classes\ModuleAccess;
use Model\BaseModel;
use MarketplaceAdmin\ExtensionData;

class Course extends BaseModel
{
    public $table = 'Courses';

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getUserAccess()
    {
        return array("get", "element");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('training', 'admin'),
        ];
    }

	public function executePreSaveActions($obj)
	{
		return new IceResponse(IceResponse::SUCCESS, $obj);
	}

    /**
     * Columns this model's select boxes may request (see
     * BaseModel::fieldValueFields). Derived from the pickers that actually exist,
     * so this allows today's usage and nothing more.
     */
    public function fieldValueFields()
    {
        return array('code', 'id', 'name');
    }

}
