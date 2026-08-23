<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 2:22 PM
 */

namespace Leaves\Common\Model;

use Classes\IceResponse;
use Classes\ModuleAccess;
use Model\BaseModel;

class HoliDay extends BaseModel
{
    public $table = 'HoliDays';
    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('leaves', 'admin'),
        ];
    }

	public function executePreSaveActions($obj)
	{
		if (!empty($obj->country) && !empty($obj->leave_group)) {
			return new IceResponse(IceResponse::ERROR, 'You are not allowed to set both country and leave group. Remove either country or the leave group.');
		}

		if (empty($obj->country) && empty($obj->leave_group) ) {
			$holiday = new HoliDay();
			$holiday->Load('dateh = ? and country IS NULL and leave_group IS NULL', [$obj->dateh]);
			if (!empty($holiday->id) && $holiday->id !== $obj->id) {
				return new IceResponse(IceResponse::ERROR, 'There is a duplicate holiday on '.$obj->dateh . '.');
			}
		} else if (empty($obj->leave_group)) {
			$holiday = new HoliDay();
			$holiday->Load('dateh = ? and country = ? and leave_group IS NULL', [$obj->dateh, $obj->country]);
			if (!empty($holiday->id) && $holiday->id !== $obj->id) {
				return new IceResponse(IceResponse::ERROR, 'There is a duplicate holiday on '.$obj->dateh . ' for the selected country.');
			}
		} else if (empty($obj->country)) {
			$holiday = new HoliDay();
			$holiday->Load('dateh = ? and country IS NULL and leave_group = ?', [$obj->dateh, $obj->leave_group]);
			if (!empty($holiday->id) && $holiday->id !== $obj->id) {
				return new IceResponse(IceResponse::ERROR, 'There is a duplicate holiday on '.$obj->dateh . ' for the selected leave group.');
			}
		}

		return new IceResponse(IceResponse::SUCCESS, $obj);
	}

	public function executePreUpdateActions($obj)
	{
		return $this->executePreSaveActions($obj);
	}
}
