<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 12:27 PM
 */

namespace FieldNames\Common\Model;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Model\BaseModel;
use Utils\LogManager;

class CustomField extends BaseModel
{
    public $table = 'CustomFields';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array();
    }

    public function validateSave($obj)
    {
        $type = BaseService::getInstance()->getFullQualifiedModelClassName($obj->type);
        $baseObject = new $type();
        $fields = $baseObject->getObjectKeys();
        if (isset($fields[$obj->name])) {
            return new IceResponse(IceResponse::ERROR, "Column name already exists by default");
        }

        $cf = new CustomField();
        if (empty($obj->id)) {
            $cf->Load("type = ? and name = ?", array($obj->type, $obj->name));
            if ($cf->name == $obj->name) {
                return new IceResponse(
                    IceResponse::ERROR,
                    "Another custom field with same name has already been added"
                );
            }
        } else {
            $cf->Load("type = ? and name = ? and id <> ?", array($obj->type, $obj->name, $obj->id));
            if ($cf->name == $obj->name) {
                return new IceResponse(
                    IceResponse::ERROR,
                    "Another custom field with same name has already been added"
                );
            }
        }

        return new IceResponse(IceResponse::SUCCESS, "");
    }

    /**
     * @param CustomField $obj
     */
    public function executePostDeleteActions($obj)
    {
        $ret = $this->DB()->Execute(
            'DELETE FROM CustomFieldValues where type = ? and name = ?',
            [$obj->type, $obj->name]
        );

        if (!$ret) {
            $this->lastError =  $this->DB()->ErrorMsg();
            LogManager::getInstance()->error('Error deleting custom field values: '.$this->DB()->ErrorMsg());
        }
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('fieldnames', 'admin'),
        ];
    }
}
