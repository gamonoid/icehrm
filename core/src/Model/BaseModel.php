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
        "t",
        // SPA data-scope params (added to every request by withSpaScope /
        // IceDataPipe). Like 'a' and 't' they are framework request params,
        // not model data — never persist them as custom fields.
        "mg",
        "mn"
    );

	public function getEmployee() {
		return $this->employee;
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

	public function getAdminAccess()
	{
		return array("get","element","add","save","delete");
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

	public function getEmployeeOnlyMeAccess()
    {
        return $this->getUserOnlyMeAccess();
    }

    public function getUserOnlyMeSwitchedAccess()
    {
        return $this->getUserOnlyMeAccess();
    }

    /**
     * Columns this viewer may see on THIS record, or null for "no projection".
     *
     * Opt-in: the default returns null, so every model keeps returning every column
     * exactly as before. A model overrides this only when some of its columns must be
     * withheld from users who are allowed to LIST it but not to see all of it
     * (finding 2.7 — Employee is the only such model today).
     *
     * Applied by BaseService::projectForViewer() at the point rows are returned to a
     * user-facing request, per record, so a manager can get the full record for their
     * own subordinates and the reduced one for everybody else in the same list.
     *
     * @param  mixed $user               the requesting user (may be null)
     * @param  bool  $isSelf             the record belongs to the requester
     * @param  bool  $isPrivilegedViewer Admin, or a Manager over this record's owner
     * @return array|null                allowlist of column names, or null for all
     */
    public function getFieldsVisibleTo($user, $isSelf, $isPrivilegedViewer)
    {
        return null;
    }

    /**
     * May a LIST of this model be scoped to the caller's direct reports?
     *
     * data.php turns `type=sub` into $isSubOrdinates, and BaseService::getData()
     * then filters rows to `<ownerField> IN (<the caller's reports>)`. That branch
     * is chosen by a CLIENT-SUPPLIED parameter, while authorization is decided
     * separately by checkSecureAccess("get", ...), which never sees it — so without
     * this opt-in any caller with direct reports could flip any employee-owned list
     * from "my rows" to "my reports' rows" on a model that was never meant to have a
     * team view.
     *
     * Default DENY. Override to true only where a screen genuinely shows a team
     * list (the adapter returns true from isSubProfileTable()). The query still
     * constrains rows to actual direct reports, so this governs WHICH MODELS may be
     * viewed that way, not who the reports are.
     *
     * @return bool
     */
    public function allowsSubordinateList()
    {
        return false;
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

    /**
     * Columns a SELECT BOX may ask for through service.php?a=getFieldValues.
     *
     * getFieldValues() projects rows as `$obj->$key => $obj->$value`, and both names
     * come straight from the request — so without this list any caller holding "get"
     * on a model can read ANY column of EVERY row, bypassing the row scoping in
     * get()/getData() entirely (it runs Find('1 = 1')). That is how a plain employee
     * could read every colleague's address, phone number and date of birth.
     *
     * Deny by default: a model must publish the columns its pickers legitimately
     * need — normally just the id and a display label. Anything else fails closed
     * (an empty dropdown), which surfaces in the module's e2e spec rather than
     * leaking.
     *
     * This is the third axis of picker control, alongside the two that already
     * existed: getFieldMappingFinder() restricts which ROWS are returned, and
     * fieldValueMethods() restricts which METHOD may be invoked.
     */
    public function fieldValueFields()
    {
        return [];
    }

    /**
     * Fields the generic save/add path (BaseService::addElement) must NOT copy from the
     * client for the CURRENT actor — a per-model, privilege-aware mass-assignment guard.
     * addElement authorises the *verb* and *row*, but blindly assigns every request key
     * that matches a column; a model that grants an owner `save`/`add` therefore lets
     * that owner over-post sensitive columns (approval status, supervisor, pay grade, ...).
     * Override to return those columns for actors who must not set them; the loaded DB
     * value (update) or model default (add) is kept instead. Default: no restriction.
     *
     * @param object|null $user the current user (BaseService::getCurrentUser())
     * @return string[]
     */
    public function getProtectedFields($user)
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
        // Check if this is an insert (no id before save)
        $isInsert = empty($this->id);

        $ok = parent::Save();
        if (!$ok) {
            $message = sprintf('%s: (%s) %s', 'Error saving :', $this->ErrorMsg(), json_encode($this));
            LogManager::getInstance()->error($message);
            LogManager::getInstance()->notifyException(new \Exception($message));
        }

        // Track insert for demo mode if enabled
        if ($ok && $isInsert && !empty($this->id) && class_exists('DemoModeAdmin\DemoModeTracker')) {
            \DemoModeAdmin\DemoModeTracker::trackInsert($this->getTable(), $this->id);
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
