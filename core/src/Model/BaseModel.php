<?php
namespace Model;

use Classes\BaseService;
use Classes\FinderProxy;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Classes\ModuleAccessService;
use Documents\Common\Model\CompanyDocumentFinderProxy;
use Modules\Common\Model\Module;
use MyORM\MySqlActiveRecord;
use ReflectionClass;
use Users\Common\Model\UserRole;
use Utils\LogManager;

//class BaseModel extends \ADOdb_Active_Record
class BaseModel extends MySqlActiveRecord implements FinderProxy
{
    public $objectName = null;
    protected $allowCustomFields = false;
    protected $isSubordinateQuery = false;
    public $isJoinFind = false;

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

	public function getEmployee() {
		return $this->employee;
	}
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
        /**
 * @var ModuleAccess $moduleAccess
*/
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

		if (!empty($this->getEmployee())) {
			$userId = BaseService::getInstance()->getCurrentUser()->id;
			$employee = BaseService::getInstance()->getEmployeeByUserId($userId);
			if (!empty($employee) && $this->getEmployee() === $employee->id) {
				$permissionMethod = "getUserOnlyMeAccess";
				$allowedAccessMatrixMeOnly = $this->$permissionMethod();
				$allowedAccessMatrix = array_unique(array_merge($allowedAccessMatrixMeOnly,$allowedAccessMatrix), SORT_REGULAR);
			}
		}

        $userRoles = $this->getMatchingUserRoles($userRoles);
        if ($userRoles === false) {
            return empty($allowedAccessMatrix) ? $this->getDefaultAccessLevel() : $allowedAccessMatrix;
        }

        $permissions = empty($allowedAccessMatrix)  ? $this->getDefaultAccessLevel() : $allowedAccessMatrix;
        $className = '';
        try {
            $className = (new ReflectionClass($this))->getShortName();
        } catch (\ReflectionException $e) {
        }
        foreach ($userRoles as $role) {
            $userRole = new UserRole();
            $userRole->Load('id = ?', [$role]);
            try {
                $userRolePermissions = json_decode($userRole->additional_permissions);
                foreach ($userRolePermissions as $tablePermissions) {
                    if ($tablePermissions->table === $className) {
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

	public function preDeleteChecks()
	{
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
     * @param  $obj
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

    /**
     * If a user was given permissions to a module via a user role,
     * The function `getModuleAccess` on models will define having access to which modules
     * give the right to access a specific model object.
     *
     * When user has this access, the `getDefaultAccessLevel` will define what the user can do on that module,
     * if user level access function such as `getEmployeeAccess` returns empty
     *
     * @return array
     */
    public function getDefaultAccessLevel()
    {
        return array();
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
        $whereOrderBy = $this->refineWhereClause($whereOrderBy);

        return parent::Find($whereOrderBy, $bindarr, $pkeysArr, $extra);
    }

    private function refineWhereClause($whereOrderBy) {
        $whereOrderBy = str_replace('(', ' ( ', $whereOrderBy);
        $whereOrderBy = str_replace(')', ' ) ', $whereOrderBy);
        $whereOrderBy = preg_replace('/where * and/', 'where', $whereOrderBy);
        $whereOrderBy = preg_replace('/where * AND/', 'where', $whereOrderBy);
        $whereOrderBy = preg_replace('/WHERE * and/', 'where', $whereOrderBy);
        $whereOrderBy = preg_replace('/WHERE * AND/', 'where', $whereOrderBy);
        //make sure $whereOrderBy is not starting with AND
        $whereOrderBy = preg_replace('/^ *and/', '', $whereOrderBy);
        $whereOrderBy = preg_replace('/^ *AND/', '', $whereOrderBy);
        // Fix AND before order by
        $whereOrderBy = preg_replace('/AND * ORDER BY/', 'ORDER BY', $whereOrderBy);
        $whereOrderBy = preg_replace('/and * ORDER BY/', 'ORDER BY', $whereOrderBy);
        $whereOrderBy = preg_replace('/and * order by/', 'ORDER BY', $whereOrderBy);
        $whereOrderBy = preg_replace('/AND * order by/', 'ORDER BY', $whereOrderBy);

        return $whereOrderBy;
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
		$this->preDeleteChecks();
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

    public function getTotalCount($query, $data)
    {
        // An admin loading all user data
        $sql = "Select count(id) as count from " . $this->table;

        if ($this->startsWith(trim(strtolower($query)), 'order by') ||
            $this->startsWith(trim(strtolower($query)), 'limit') ||
            $this->startsWith(trim(strtolower($query)), 'and')) {
            $sql .= " where 1=1 " . $query;
        } else if (empty($query))  {
            $sql .= " where 1=1";
        } else {
            $sql .= " where 1=1 and " . $query;
        }
        return $this->countRows($sql, $data);
    }

	public function getEditorDraftContent() {
		return null;
	}

    public function setIsSubOrdinateQuery($val)
    {
        $this->isSubordinateQuery = $val;
    }

	public function getCustomFieldTable( $table ) {
		return $table;
	}

	public function getEditorSideBarObject($mode) {
		return $this;
	}

	public function editorObjectUpdated() {

	}

	public function getEditorPermissions() {
		return ['default'];
	}

    private function startsWith($string, $query){
        return substr($string, 0, strlen($query)) === $query;
    }
}
