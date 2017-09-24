<?php
namespace Model;

use Classes\IceResponse;

class BaseModel extends \ADOdb_Active_Record
{

    public $keysToIgnore = array(
        "_table",
        "_dbat",
        "_tableat",
        "_where",
        "_saved",
        "_lasterr",
        "_original",
        "foreignName",
        "a",
        "t"
    );

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getOtherAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }

    public function getEmployeeAccess()
    {
        return $this->getUserAccess();
    }

    public function getAnonymousAccess()
    {
        return array();
    }

    public function getUserOnlyMeAccess()
    {
        return array("get","element");
    }

    public function getUserOnlyMeAccessField()
    {
        return "employee";
    }

    public function getUserOnlyMeAccessRequestField()
    {
        return "employee";
    }

    public function validateSave($obj)
    {
        return new IceResponse(IceResponse::SUCCESS, "");
    }

    public function executePreSaveActions($obj)
    {
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function executePreUpdateActions($obj)
    {
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function executePostSaveActions($obj)
    {
    }

    public function executePostUpdateActions($obj)
    {
    }

    public function postProcessGetData($obj)
    {
        return $obj;
    }

    public function postProcessGetElement($obj)
    {
        return $obj;
    }

    public function getDefaultAccessLevel()
    {
        return array("get","element","save","delete");
    }

    public function getVirtualFields()
    {
        return array(
        );
    }

    public function allowIndirectMapping()
    {
        return false;
    }

    public function getDisplayName()
    {
        return get_called_class();
    }

    public function getObjectKeys()
    {
        $keys = array();

        foreach ($this as $k => $v) {
            if (in_array($k, $this->keysToIgnore)) {
                continue;
            }

            if (is_array($v) || is_object($v)) {
                continue;
            }

            $keys[$k] = $k;
        }

        return $keys;
    }

    public function getCustomFields($obj)
    {
        $keys = array();
        $objKeys = $this->getObjectKeys();
        foreach ($obj as $k => $v) {
            if (isset($objKeys[$k])) {
                continue;
            }

            if (is_array($v) || is_object($v)) {
                continue;
            }

            if (in_array($k, $this->keysToIgnore)) {
                continue;
            }

            $keys[$k] = $k;
        }

        return $keys;
    }
}
