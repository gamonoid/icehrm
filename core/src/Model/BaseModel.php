<?php
namespace Model;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Classes\ModuleAccessService;
use Documents\Common\Model\CompanyDocumentFinderProxy;
use Modules\Common\Model\Module;
use MyORM\MySqlActiveRecord;
use Users\Common\Model\UserRole;
use Utils\LogManager;

//class BaseModel extends \ADOdb_Active_Record
class BaseModel extends MySqlActiveRecord
{
    public $objectName = null;
    protected $allowCustomFields = false;

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

    public function getMatchingUserRoles($userRoles)
    {
        if (empty($userRoles)) {
            return false;
        }

        $userRoles = json_decode($userRoles, true);

        if (empty($userRoles)) {
            return false;
        }

        $moduleAccessData = $this->getModuleAccess();
        if (empty($moduleAccessData)) {
            return false;
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
            return false;
        }

        foreach ($modules as $module) {
            if (empty($module->user_roles) || $module->user_roles == '[]') {
                continue;
            }
            $matchingUserRoles = array_intersect($userRoles, json_decode($module->user_roles, true));
            if (count($matchingUserRoles) > 0) {
                return $matchingUserRoles;
            }
        }

        return false;
    }

    public function getRoleBasedAccess($userLevel, $userRoles)
    {
        $permissionMethod = "get".str_replace(' ', '', $userLevel)."Access";
        $allowedAccessMatrix = $this->$permissionMethod();

        $userRoles = $this->getMatchingUserRoles($userRoles);
        if ($userRoles === false) {
            return $allowedAccessMatrix === null ? $this->getDefaultAccessLevel() : $allowedAccessMatrix;
        }

        $permissions = $allowedAccessMatrix === null ? $this->getDefaultAccessLevel() : $allowedAccessMatrix;

        foreach ($userRoles as $role) {
            $userRole = new UserRole();
            $userRole->Load('id = ?', [$role]);
            try {
                $userRolePermissions = json_decode($userRole->additional_permissions);
                foreach ($userRolePermissions as $tablePermissions) {
                    if ($tablePermissions->table === $this->table) {
                        $permissions = array_unique(
                            array_merge(
                                $permissions,
                                json_decode($tablePermissions->permissions, true)
                            )
                        );
                    }
                }
            } catch (\Exception $e) {
            }
        }

        return $permissions;
    }

    public function getRestrictedAdminAccess()
    {
        return $this->getAdminAccess();
    }

    public function getRestrictedManagerAccess()
    {
        return $this->getManagerAccess();
    }

    public function getRestrictedEmployeeAccess()
    {
        return $this->getEmployeeAccess();
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

    /**
     * @param $obj
     * @return IceResponse
     */
    public function executePreSaveActions($obj)
    {
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    /**
     * @param $obj
     * @return IceResponse
     */
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

    public function executePostDeleteActions($obj)
    {
    }

    /**
     * If null is returned the object wont be included in the response
     *
     * @param $obj
     * @return mixed
     */
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

//    public function getObjectKeys()
//    {
//        $keys = array();
//
//        foreach ($this as $k => $v) {
//            if (in_array($k, $this->keysToIgnore)) {
//                continue;
//            }
//
//            if (is_array($v) || is_object($v)) {
//                continue;
//            }
//
//            $keys[$k] = $k;
//        }
//
//        return $keys;
//    }

    public function getObjectKeys()
    {
        return $this->getColumnKeys();
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

    protected function getEntity()
    {
        $data = explode('\\', get_called_class());
        return end($data);
    }

    public function Load($where = null, $bindarr = false)
    {
        return parent::Load($where, $bindarr); // TODO: Change the autogenerated stub
    }

    // @codingStandardsIgnoreStart

    public function Find($whereOrderBy, $bindarr = false, $cache = false, $pkeysArr = false, $extra = array())
    {
        return parent::Find($whereOrderBy, $bindarr, $pkeysArr, $extra);
    }

    public function Save()
    {
        $ok = parent::Save();
        if (!$ok) {
            $message = sprintf('%s: (%s) %s', 'Error saving :', $this->ErrorMsg(), json_encode($this));
            LogManager::getInstance()->error($message);
            LogManager::getInstance()->notifyException(new \Exception($message));
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

        return $ok;
    }
    // @codingStandardsIgnoreEnd

    public function countRows($query, $data)
    {
        $rowCount = $this->DB()->Execute($query, $data);
        if (isset($rowCount) && !empty($rowCount)) {
            foreach ($rowCount as $cnt) {
                return intval($cnt['count']);
            }
        }

        return 0;
    }

    public function getObjectName()
    {
        return null;
    }

    public function isCustomFieldsEnabled()
    {
        return false;
    }

    public function getFinder()
    {
        return null;
    }

    public function getFieldMappingFinder()
    {
        return null;
    }
}
