<?php
namespace Model;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Classes\ModuleAccessService;
use Modules\Common\Model\Module;
use Utils\LogManager;

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

    private function getRestrictedAccess($userRoles, $allowedAccessMatrix)
    {
        if (empty($userRoles)) {
            return $this->getDefaultAccessLevel();
        }

        $userRoles = json_decode($userRoles, true);

        if (empty($userRoles)) {
            return $this->getDefaultAccessLevel();
        }

        $moduleAccessData = $this->getModuleAccess();
        if (empty($moduleAccessData)) {
            return $this->getDefaultAccessLevel();
        }

        $modules = [];
        /** @var ModuleAccess $moduleAccess */
        foreach ($moduleAccessData as $moduleAccess) {
            $modules[] = ModuleAccessService::getInstance()->getModule(
                $moduleAccess->getName(),
                $moduleAccess->getGroup()
            );
        }

        if (empty($modules)) {
            return $this->getDefaultAccessLevel();
        }

        foreach ($modules as $module) {
            if (empty($module->user_roles) || $module->user_roles == '[]') {
                continue;
            }

            if (count(array_intersect($userRoles, json_decode($module->user_roles, true))) > 0) {
                return $allowedAccessMatrix;
            }
        }

        return $this->getDefaultAccessLevel();
    }

    public function getRestrictedAdminAccess($userRoles)
    {
        return $this->getRestrictedAccess($userRoles, $this->getAdminAccess());
    }

    public function getRestrictedManagerAccess($userRoles)
    {
        return $this->getRestrictedAccess($userRoles, $this->getAdminAccess());
    }

    public function getRestrictedEmployeeAccess($userRoles)
    {
        return $this->getRestrictedAccess($userRoles, $this->getAdminAccess());
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

    public function getUserOnlyMeSwitchedAccess()
    {
        return $this->getUserOnlyMeAccess();
    }

    public function getUserOnlyMeAccessField()
    {
        return "employee";
    }

    public function getUserOnlyMeAccessRequestField()
    {
        return "employee";
    }

    public function getModuleAccess()
    {
        return [];
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

    public function executePreDeleteActions($obj)
    {
        return new IceResponse(IceResponse::SUCCESS, null);
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
        return $this->getAnonymousAccess();
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
        return $this->getEntity();
    }

    public function fieldValueMethods()
    {
        return [];
    }

    public function validateCSRF()
    {
        return false;
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

            $keys[$k] = $v;
        }

        return $keys;
    }

    // @codingStandardsIgnoreStart

    public function Find($whereOrderBy, $bindarr = false, $cache = false, $pkeysArr = false, $extra = array())
    {
        if ($cache && BaseService::getInstance()->queryCacheEnabled()) {
            $data = BaseService::getInstance()->getCacheService()->getDBQuery($this->getEntity(),$whereOrderBy, $bindarr);
            if ($data !== null) {
                return $data;
            }
        }

        $data = parent::Find($whereOrderBy, $bindarr, $pkeysArr, $extra);

        if (empty($data)) {
            return $data;
        }

        if ($cache && BaseService::getInstance()->queryCacheEnabled()) {
            BaseService::getInstance()->getCacheService()->setDBQuery($this->getEntity(),$whereOrderBy, $bindarr, $data);
        }

        return $data;
    }

    protected function getEntity()
    {
        $data = explode('\\', get_called_class());
        return end($data);
    }

    public function Save()
    {
        $ok = parent::Save();
        if (!$ok) {
            $message = sprintf('%s: (%s) %s', 'Error saving :', $this->ErrorMsg(), json_encode($this));
            LogManager::getInstance()->error($message);
            LogManager::getInstance()->notifyException(new \Exception($message));
        }
        if (BaseService::getInstance()->queryCacheEnabled()) {
            BaseService::getInstance()->getCacheService()->deleteByEntity($this->getEntity());
        }


        return $ok;
    }

    public function Delete()
    {
        $ok = parent::Delete();
        if (!$ok) {
            $message = sprintf('%s: (%s) %s', 'Error deleting', $this->ErrorMsg(), json_encode($this));
            LogManager::getInstance()->error($message);
            LogManager::getInstance()->notifyException(new \Exception($message));
        }

        if (BaseService::getInstance()->queryCacheEnabled()) {
            BaseService::getInstance()->getCacheService()->deleteByEntity($this->getEntity());
        }

        return $ok;
    }
    // @codingStandardsIgnoreEnd
}
