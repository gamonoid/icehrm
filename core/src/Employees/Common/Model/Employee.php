<?php
namespace Employees\Common\Model;

use Classes\BaseService;
use Classes\FileService;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Classes\PermissionManager;
use Company\Common\Model\CompanyStructure;
use Metadata\Common\Model\Country;
use Model\BaseModel;
use Model\CustomFieldTrait;
use Model\File;
use Utils\CalendarTools;

class Employee extends BaseModel
{

    public $oldObj = null;
    public $oldObjOrig = null;
    public $historyUpdateList = array();
    public $historyFieldsToTrack = array(
        "employee_id"=>"employee_id",
        "first_name"=>"first_name",
        "middle_name"=>"middle_name",
        "last_name"=>"last_name",
        "nationality"=>"nationality_Name",
        "birthday"=>"birthday",
        "gender"=>"gender",
        "marital_status"=>"marital_status",
        "ssn_num"=>"ssn_num",
        "nic_num"=>"nic_num",
        "other_id"=>"other_id",
        "employment_status"=>"employment_status_Name",
        "job_title"=>"job_title_Name",
        "pay_grade"=>"pay_grade_Name",
        "work_station_id"=>"work_station_id",
        "address1"=>"address1",
        "address2"=>"address2",
        "city"=>"city_Name",
        "country"=>"country_Name",
        "province"=>"province_Name",
        "postal_code"=>"postal_code",
        "home_phone"=>"home_phone",
        "mobile_phone"=>"mobile_phone",
        "work_phone"=>"work_phone",
        "work_email"=>"work_email",
        "private_email"=>"private_email",
        "joined_date"=>"joined_date",
        "confirmation_date"=>"confirmation_date",
        "supervisor"=>"supervisor_Name",
        "indirect_supervisors"=>"indirect_supervisors",
        "department"=>"department_Name"
    );

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getManagerAccess()
    {
        // A Manager may view and EDIT employee records (their subordinates, scoped by
        // managerRecordScopeAllows) but may not CREATE or DELETE employees — those are
        // Admin-only lifecycle operations. "add" is removed here so the generic
        // add path (a=add&t=Employee) denies managers; "delete" was never granted.
        // The action-manager methods deleteEmployee/activateEmployee/terminateEmployee
        // enforce the same via checkSecureAccess('delete').
        return array("get","element","save");
    }

    public function getUserAccess()
    {
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array("element","add","save");
    }

    /**
     * Columns a colleague may see (finding 2.7).
     *
     * getUserAccess() deliberately grants "get" to every authenticated user, because the
     * staff directory, the org chart and every employee picker in the SPA need the
     * company-wide name list. That is a ROW grant; it was never meant to hand over the
     * whole record. Without this projection an employee could read every colleague's
     * ssn_num, nic_num, birthday, home address, personal phone/e-mail, tax id and
     * health insurance straight off service.php.
     *
     * The allowlist is exactly what the staff directory already publishes company-wide
     * (see Directory\Common\Model\StaffDirectory::Find), so nothing that works today
     * stops working. Anything not listed here is withheld from colleagues; the employee
     * themselves, an Admin, and a Manager over that employee still get the full record.
     */
    public function getFieldsVisibleTo($user, $isSelf, $isPrivilegedViewer)
    {
        if ($isSelf || $isPrivilegedViewer) {
            return null; // full record
        }

        return array(
            'id',
            'first_name',
            'middle_name',
            'last_name',
            'job_title',
            'department',
            'work_phone',
            'work_email',
            'joined_date',
            'gender',
            'country',
            'address1',
            'address2',
            'city',
            'postal_code',
            'status',
        );
    }

    public function getUserOnlyMeAccessField()
    {
        return "id";
    }

    /**
     * Mass-assignment guard for self-service. getUserOnlyMeAccess() grants an employee
     * "save"/"add" on their OWN record (owner field = id), and BaseService::addElement
     * copies every request column — so without this a non-admin could POST
     * a=save&t=Employee&id=<own> and set their own supervisor / approvers (reroute the
     * approval chain), job_title / department / pay_grade (self-promotion) or status /
     * termination_date (self-DoS). These are HR/admin-controlled fields; keep the stored
     * value when the actor is editing their OWN record and is not an Admin. Admins, and a
     * manager editing a subordinate, are unaffected.
     */
    public function getProtectedFields($user)
    {
        if (!empty($user) && $user->user_level === 'Admin') {
            return array();
        }
        $selfId = BaseService::getInstance()->getCurrentProfileId();
        if (!empty($this->id) && !empty($selfId) && (string) $this->id === (string) $selfId) {
            return array(
                'supervisor', 'indirect_supervisors',
                'approver1', 'approver2', 'approver3',
                'job_title', 'department', 'pay_grade',
                'employment_status', 'status', 'termination_date',
            );
        }
        return array();
    }

    private function initHistory($obj)
    {

        $oldObjOrig = new Employee();
        $oldObjOrig->Load("id = ?", array($obj->id));
        $this->oldObjOrig = $oldObjOrig;

        $mapping = '{"nationality":["Nationality","id","name"],'
            .'"employment_status":["EmploymentStatus","id","name"],"job_title":["JobTitle","id","name"],'
            .'"pay_grade":["PayGrade","id","name"],"country":["Country","code","name"],'
            .'"province":["Province","id","name"],"department":["CompanyStructure","id","title"],'
            .'"supervisor":["Employee","id","first_name+last_name"]}';

        $this->oldObj = BaseService::getInstance()->getElement('Employee', $obj->id, $mapping, true);
    }

    public function isCustomFieldsEnabled()
    {
        return true;
    }

    private function saveHistory($obj)
    {

        $oldObj = $this->oldObj;
        $oldObjOrig = $this->oldObjOrig;

        $mapping = '{"nationality":["Nationality","id","name"],'
            .'"employment_status":["EmploymentStatus","id","name"],"job_title":["JobTitle","id","name"],'
            .'"pay_grade":["PayGrade","id","name"],"country":["Country","code","name"],'
            .'"province":["Province","id","name"],"department":["CompanyStructure","id","title"],'
            .'"supervisor":["Employee","id","first_name+last_name"]}';

        $objEnriched = BaseService::getInstance()->getElement('Employee', $obj->id, $mapping, true);

        foreach ($this->historyFieldsToTrack as $k => $v) {
            if (empty($oldObjOrig->$k) && $obj->$k = '[]') {
                continue;
            }

            if (empty($obj->$k) && $oldObjOrig->$k == '0000-00-00') {
                continue;
            }

            if ($oldObjOrig->$k != $obj->$k) {
                $enrichNewVal = '';
                $enrichOldVal = '';

                if ($k == 'indirect_supervisors') {
                    if (!empty($obj->$k) && $obj->$k != '[]') {
                        $newIndeirectSupervisorIds = json_decode($obj->$k);
                        foreach ($newIndeirectSupervisorIds as $id) {
                            $item = BaseService::getInstance()->getItemFromCache("Employee", $id);
                            if ($enrichNewVal != "") {
                                $enrichNewVal .= ", ";
                            }
                            $enrichNewVal .= $item->first_name." ".$item->last_name;
                        }
                    }

                    if (!empty($oldObjOrig->$k) && $oldObjOrig->$k != '[]') {
                        $oldIndeirectSupervisorIds = json_decode($oldObjOrig->$k);
                        foreach ($oldIndeirectSupervisorIds as $id) {
                            $item = BaseService::getInstance()->getItemFromCache("Employee", $id);
                            if ($enrichOldVal != "") {
                                $enrichOldVal .= ", ";
                            }
                            $enrichOldVal .= $item->first_name." ".$item->last_name;
                        }
                    }
                } else {
                    $enrichOldVal = $oldObj->$v;
                    $enrichNewVal = $objEnriched->$v;
                }

                $this->historyUpdateList[] = array($obj->id,$k,$enrichOldVal,$enrichNewVal);
            }
        }

        while (count($this->historyUpdateList)) {
            $ele = array_pop($this->historyUpdateList);
            BaseService::getInstance()->addHistoryItem("Employee", "Employee", $ele[0], $ele[1], $ele[2], $ele[3]);
        }
    }

    public function executePreSaveActions($obj)
    {
        if (empty($obj->status)) {
            $obj->status = 'Active';
        }
        // Auto-generate the employee number on create when enabled. This is
        // authoritative — the form field is read-only, and any submitted value
        // is ignored — so numbers stay unique even across concurrent adds.
        if (self::isEmployeeNumberGenerationEnabled()) {
            $obj->employee_id = self::generateEmployeeNumber();
        }
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    /** Whether "Company: Generate Employee Numbers" is turned on. */
    public static function isEmployeeNumberGenerationEnabled()
    {
        return \Classes\SettingsManager::getInstance()
            ->getSetting('Company: Generate Employee Numbers') == '1';
    }

    /**
     * Next available employee number: the "Company: Employee Number Prefix"
     * followed by the next Employees-table id, zero-padded to at least 4 digits
     * (e.g. next id 31 + prefix "IC-" => "IC-0031"). If that number is already
     * taken it advances to the next free one.
     */
    public static function generateEmployeeNumber()
    {
        $prefix = \Classes\SettingsManager::getInstance()->getSetting('Company: Employee Number Prefix');
        if ($prefix === null) {
            $prefix = '';
        }

        $db = BaseService::getInstance()->getDB();
        $next = 1;
        $ai = $db->Execute(
            "SELECT AUTO_INCREMENT AS n FROM information_schema.TABLES "
            . "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Employees'"
        );
        if (is_array($ai) && !empty($ai[0]['n'])) {
            $next = (int) $ai[0]['n'];
        }
        $mx = $db->Execute("SELECT COALESCE(MAX(id), 0) + 1 AS n FROM Employees");
        if (is_array($mx) && isset($mx[0]['n']) && (int) $mx[0]['n'] > $next) {
            $next = (int) $mx[0]['n'];
        }

        while (true) {
            $candidate = $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $existing = new Employee();
            $existing->Load('employee_id = ?', array($candidate));
            if (empty($existing->id)) {
                return $candidate;
            }
            $next++;
        }
    }

    public function executePreUpdateActions($obj)
    {
		$savedObject = new Employee();
		$savedObject->Load('id = ?', [$obj->id]);
		$obj = $this->processBasicPermissions($obj, $savedObject);
        $this->initHistory($obj);
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

	protected function processBasicPermissions( $obj, $savedObject ) {
		// The employee number can never change once set when auto-generation is on.
		// (The former per-level "Edit Employee Number" permission has been removed;
		// editing is now always allowed unless auto-generation locks it.)
		if (self::isEmployeeNumberGenerationEnabled()) {
			$obj->employee_id = $savedObject->employee_id;
		}

		return $obj;
	}

    public function executePostUpdateActions($obj)
    {
        $this->saveHistory($obj);
    }

    public function postProcessGetData($obj)
    {
        if (empty($obj->timezone)) {
            $obj->timezone = BaseService::getInstance()->getEmployeeTimeZone($obj->id);
        }
        $obj = FileService::getInstance()->updateSmallProfileImage($obj);
        return $this->updateEmployeeUserEmail($obj);
    }

    public function getVirtualFields()
    {
        return array(
            "image"
        );
    }

    public function getActiveEmployees()
    {
        $employee = new Employee();
        $list = $employee->Find("status = ?", array('Active'));
        return $list;
    }

    public function getActiveSubordinateEmployees()
    {

        $employee = new Employee();

        if (BaseService::getInstance()->currentUser->user_level != 'Admin' && !EmployeeAccess::hasAccessToAllEmployeeData()) {
            $cemp = BaseService::getInstance()->getCurrentProfileId();
            $list = $employee->Find("status = ? and supervisor = ?", array('Active', $cemp));
        } else {
            $list = $employee->Find("status = ?", array('Active'));
        }
        return $list;
    }

    public static function cleanEmployeeData($obj)
    {
        unset($obj->keysToIgnore);
        unset($obj->historyFieldsToTrack);
        unset($obj->historyUpdateList);
        unset($obj->oldObjOrig);
        unset($obj->oldObj);
        return $obj;
    }

    public function fieldValueMethods()
    {
        return ['getActiveSubordinateEmployees'];
    }

    public static function getCurrentEmployeeCompanyStructureCountry()
    {
        $cemp = BaseService::getInstance()->getCurrentProfileId();
        $employee = new Employee();
        $employee->Load('id = ?', [$cemp]);

        $companyStructure = new CompanyStructure();
        $companyStructure->Load('id = ?', [$employee->department]);

        $country = new Country();
        $country->Load('code = ?', [$companyStructure->country]);

        return $country->id;
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('employees', 'user'),
        ];
    }

    public function postProcessGetElement($obj)
    {
        if (empty($obj->timezone)) {
            $obj->timezone = BaseService::getInstance()->getEmployeeTimeZone($obj->id);
        }
        $obj->current_time = CalendarTools::getServerDate('Y-m-d H:i:s', $obj->id);
        $obj = FileService::getInstance()->updateProfileImage($obj);
        return $this->updateEmployeeUserEmail($obj);
    }

    public $table = 'Employees';

    /**
     * @param $obj
     * @return mixed
     */
    protected function updateEmployeeUserEmail($obj)
    {
        $user = BaseService::getInstance()->getEmployeeUser($obj->id);
        if (null !== $user) {
            $obj->email = $user->email;
        } else {
            $obj->email = $obj->work_email;
        }
        return $obj;
    }

    /**
     * A team list exists for this model: the adapter opts into `type=sub`
     * (isSubProfileTable), so BaseService::getData() may scope its rows to the
     * caller's direct reports. See BaseModel::allowsSubordinateList().
     */
    public function allowsSubordinateList()
    {
        return true;
    }


    /**
     * Columns this model's select boxes may request (see
     * BaseModel::fieldValueFields). Derived from the pickers that actually exist,
     * so this allows today's usage and nothing more.
     */
    public function fieldValueFields()
    {
        return array('first_name', 'id', 'last_name');
    }

}
