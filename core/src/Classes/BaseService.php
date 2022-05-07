<?php

/**
 * BaseService class serves as the core logic for managing the application and for handling most
 * of the tasks related to retriving and saving data. This can be referred within any module using
 * BaseService::getInstance()
 *
@class BaseService
 */

namespace Classes;

use Classes\Crypt\AesCtr;
use Classes\Email\EmailSender;
use Classes\Exception\IceHttpException;
use Classes\Migration\MigrationInterface;
use Company\Common\Model\CompanyStructure;
use Employees\Common\Model\Employee;
use Employees\Common\Model\EmployeeApproval;
use FieldNames\Common\Model\CustomField;
use FieldNames\Common\Model\FieldNameMapping;
use Metadata\Common\Model\CalculationHook;
use Model\BaseModel;
use Model\DataEntryBackup;
use Model\Setting;
use Model\SystemData;
use Modules\Common\Model\Module;
use Permissions\Common\Model\Permission;
use Users\Common\Model\User;
use Users\Common\Model\UserRole;
use Utils\LogManager;
use Utils\SessionUtils;

class BaseService
{

    public $nonDeletables = array();
    public $errros = array();
    public $userTables = array();
    /* @var User $currentUser */
    public $currentUser = null;
    public $db = null;
    public $auditManager = null;
    /* @var NotificationManager $notificationManager */
    public $notificationManager = null;
    /* @var SettingsManager $settingsManager*/
    public $settingsManager = null;
    public $fileFields = null;
    public $moduleManagers = null;
    /* @var EmailSender $emailSender */
    public $emailSender = null;
    public $user = null;
    public $historyManagers = array();
    public $calculationHooks = array();
    public $customFieldManager = null;
    public $migrationManager = null;
    public $modelClassMap = [];
    public $customFieldsClassMap = [];
    public $currentProfileId = false;

    protected $cacheService = null;
    protected $extensionMigrations = [];

    protected $pro = null;

    private static $me = null;

    private function __construct()
    {
    }

    /**
     * Get the only instance created for BaseService
     * @method getInstance
     * @return {BaseService} BaseService object
     */

    public static function getInstance()
    {
        if (empty(self::$me)) {
            self::$me = new BaseService();
        }

        return self::$me;
    }

    /**
     * Get an array of objects from database
     * @method get
     * @param $table {String} model class name of the table to retive data
     * (e.g for Users table model class name is User)
     * @param $mappingStr {String} a JSON string to specify fields of the $table should be mapped
     * to other tables (e.g {"profile":["Profile","id","first_name+last_name"]} : this is how the
     * profile field in Users table is mapped to Profile table. In this case users profile field
     * will get filled by Profile first name and last name. The original value in User->profile
     * field will get moved to User->profile_id)
     * @param $filterStr {String} a JSON string to specify the ordering of the items
     * (e.g {"job_title":"2","department":"2"}  - this will select only items having
     * job_title = 2 and department = 2)
     * @param $orderBy {String} a string to specify the ordering (e.g in_time desc)
     * @param string $limit {String} a string to specify the limit (e.g limit 2)
     * @return {Array} an array of objects of type $table
     */
    public function get($table, $mappingStr = null, $filterStr = null, $orderBy = null, $limit = null)
    {

        if (!empty($mappingStr)) {
            $map = json_decode($mappingStr);
        }
        $nsTable = $this->getFullQualifiedModelClassName($table);
        $obj = new $nsTable();

        $this->checkSecureAccess("get", $obj, $table, $_REQUEST);

        $query = "";
        $queryData = array();
        if (!empty($filterStr)) {
            $filter = json_decode($filterStr, true);

            if (!empty($filter)) {
                if (method_exists($obj, 'getCustomFilterQuery')) {
                    $response = $obj->getCustomFilterQuery($filter);
                    $query = $response[0];
                    $queryData = $response[1];
                } else {
                    $defaultFilterResp = $this->buildDefaultFilterQuery($filter);
                    $query = $defaultFilterResp[0];
                    $queryData = $defaultFilterResp[1];
                }
            }
        }

        if (empty($orderBy)) {
            $orderBy = "";
        } else {
            $orderBy = " ORDER BY ".$orderBy;
        }

        if ($obj->getFinder() !== null) {
            $finder = $obj->getFinder();
        } else {
            $finder = $obj;
        }
        if (in_array($table, $this->userTables)) {
            $cemp = $this->getCurrentProfileId();
            if (!empty($cemp)) {
                $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
                $list = $finder->Find(
                    $signInMappingField." = ?".$query.$orderBy,
                    array_merge(array($cemp), $queryData)
                );
            } else {
                $list = array();
            }
        } else {
            LogManager::getInstance()->debug("Query: "."1=1".$query.$orderBy);
            LogManager::getInstance()->debug("Query Data: ".print_r($queryData, true));
            $list = $finder->Find("1=1".$query.$orderBy, $queryData);
        }

        $newList = array();
        foreach ($list as $listObj) {
            $newList[] = $this->cleanUpAdoDB($listObj);
        }

        if (!empty($mappingStr) && is_object($map)) {
            $list = $this->populateMapping($newList, $map);
        }

        return $list;
    }

    public function getModelClassMap()
    {
        return $this->modelClassMap;
    }

    public function addModelClass($modelClass, $fullQualifiedName)
    {
        $this->modelClassMap[$modelClass] = $fullQualifiedName;
    }

    public function getCustomFieldClassMap()
    {
        $map = [];
        foreach ($this->customFieldsClassMap as $key => $val) {
            $map[] = [$key, $val];
        }
        return $map;
    }

    public function addCustomFieldClass($customFieldsClass, $objectName)
    {
        $this->customFieldsClassMap[$customFieldsClass] = $objectName;
    }

    public function getModelClassName($name)
    {
        return $this->getFullQualifiedModelClassName($name);
    }

    /**
     * @param boolean $currentProfileId
     */
    public function setCurrentProfileId($currentProfileId)
    {
        $this->currentProfileId = $currentProfileId;
    }

    public function buildDefaultFilterQuery($filter)
    {
        $query = "";
        $queryData = array();
        foreach ($filter as $k => $v) {
            if (empty($v)) {
                continue;
            }
            if (is_array($v)) {
                if (empty($v)) {
                    continue;
                }
                $length = count($v);
                for ($i = 0; $i<$length; $i++) {
                    if ($i == 0) {
                        $query.=" and (";
                    }

                    $query.=$k." = ?";

                    if ($i < $length -1) {
                        $query.=" or ";
                    } else {
                        $query.=")";
                    }
                    $queryData[] = $v[$i];
                }
            } else {
                if (!empty($v) && $v != 'NULL') {
                    $query.=" and ".$k."=?";
                    if ($v == '__myid__') {
                        $v = $this->getCurrentProfileId();
                    }
                    $queryData[] = $v;
                }
            }
        }

        return array($query, $queryData);
    }

    public function getSortingData($req)
    {
        $data = array();
        $data['sorting'] = $req['sorting'];

        $columns = json_decode($req['cl'], true);

        $data['column'] = $columns[$req['iSortCol_0']];

        $data['order'] = $req['sSortDir_0'];

        return $data;
    }

    public function getDataCount()
    {
        //Get Total row count
        $totalRows = 0;

        if (!isset($_REQUEST['objects'])) {
            $countFilterQuery = "";
            $countFilterQueryData = array();
            if (!empty($_REQUEST['ft'])) {
                $filter = json_decode($_REQUEST['ft']);
                if (!empty($filter)) {
                    \Utils\LogManager::getInstance()->debug("Filter:" . print_r($filter, true));
                    if (method_exists($obj, 'getCustomFilterQuery')) {
                        $response = $obj->getCustomFilterQuery($filter);
                        $countFilterQuery = $response[0];
                        $countFilterQueryData = $response[1];
                    } else {
                        $defaultFilterResp = BaseService::getInstance()->buildDefaultFilterQuery($filter);
                        $countFilterQuery = $defaultFilterResp[0];
                        $countFilterQueryData = $defaultFilterResp[1];
                    }
                }
            }


            if (in_array($table, BaseService::getInstance()->userTables)
                && !$skipProfileRestriction && !$isSubOrdinates) {
                $cemp = BaseService::getInstance()->getCurrentProfileId();
                $sql = "Select count(id) as count from "
                    . $obj->table . " where " . SIGN_IN_ELEMENT_MAPPING_FIELD_NAME . " = ? " . $countFilterQuery;
                array_unshift($countFilterQueryData, $cemp);

                $rowCount = $obj->DB()->Execute($sql, $countFilterQueryData);
            } else {
                if ($isSubOrdinates) {
                    $cemp = BaseService::getInstance()->getCurrentProfileId();
                    $profileClass = BaseService::getInstance()->getFullQualifiedModelClassName(
                        ucfirst(SIGN_IN_ELEMENT_MAPPING_FIELD_NAME)
                    );
                    $subordinate = new $profileClass();
                    $subordinates = $subordinate->Find("supervisor = ?", array($cemp));

                    $cempObj = new \Employees\Common\Model\Employee();
                    $cempObj->Load("id = ?", array($cemp));

                    if ($obj->getUserOnlyMeAccessField() == 'id'
                        && \Classes\SettingsManager::getInstance()->getSetting(
                            'System: Company Structure Managers Enabled'
                        ) == 1
                        && \Company\Common\Model\CompanyStructure::isHeadOfCompanyStructure($cempObj->department, $cemp)
                    ) {
                        if (empty($subordinates)) {
                            $subordinates = array();
                        }

                        $childCompaniesIds = array();
                        if (\Classes\SettingsManager::getInstance()->getSetting(
                            'System: Child Company Structure Managers Enabled'
                        ) == '1'
                        ) {
                            $childCompaniesResp = \Company\Common\Model\CompanyStructure::getAllChildCompanyStructures(
                                $cempObj->department
                            );
                            $childCompanies = $childCompaniesResp->getObject();

                            foreach ($childCompanies as $cc) {
                                $childCompaniesIds[] = $cc->id;
                            }
                        } else {
                            $childCompaniesIds[] = $cempObj->department;
                        }

                        if (!empty($childCompaniesIds)) {
                            $childStructureSubordinates = $subordinate->Find(
                                "department in (" . implode(',', $childCompaniesIds) . ") and id != ?",
                                array($cemp)
                            );
                            $subordinates = array_merge($subordinates, $childStructureSubordinates);
                        }
                    }

                    $subordinatesIds = "";
                    foreach ($subordinates as $sub) {
                        if ($subordinatesIds != "") {
                            $subordinatesIds .= ",";
                        }
                        $subordinatesIds .= $sub->id;
                    }
                    if ($obj->allowIndirectMapping()) {
                        $indeirectEmployees = $subordinate->Find(
                            "indirect_supervisors IS NOT NULL and indirect_supervisors <> '' and status = 'Active'",
                            array()
                        );
                        foreach ($indeirectEmployees as $ie) {
                            $indirectSupervisors = json_decode($ie->indirect_supervisors, true);
                            if (in_array($cemp, $indirectSupervisors)) {
                                if ($subordinatesIds != "") {
                                    $subordinatesIds .= ",";
                                }
                                $subordinatesIds .= $ie->id;
                            }
                        }
                    }
                    $sql = "Select count(id) as count from " . $obj->table .
                        " where " . $obj->getUserOnlyMeAccessField() . " in (" . $subordinatesIds . ") "
                        . $countFilterQuery;
                    $rowCount = $obj->DB()->Execute($sql, $countFilterQueryData);
                } else {
                    $sql = "Select count(id) as count from " . $obj->table;
                    if (!empty($countFilterQuery)) {
                        $sql .= " where 1=1 " . $countFilterQuery;
                    }
                    $rowCount = $obj->DB()->Execute($sql, $countFilterQueryData);
                }
            }
        }

        if (isset($rowCount) && !empty($rowCount)) {
            foreach ($rowCount as $cnt) {
                $totalRows = $cnt['count'];
            }
        }

        return $totalRows;
    }

    /**
     * An extention of get method for the use of data tables with ability to search
     * @method getData
     * @param $table {String} model class name of the table to retive data
     * (e.g for Users table model class name is User)
     * @param $mappingStr {String} a JSON string to specify fields of the $table should
     * be mapped to other tables (e.g {"profile":["Profile","id","first_name+last_name"]}
     * : this is how the profile field in Users table is mapped to Profile table.
     * In this case users profile field will get filled by Profile first name and last name.
     * The original value in User->profile field will get moved to User->profile_id)
     * @param $filterStr {String} a JSON string to specify the ordering of the items
     * (e.g {"job_title":"2","department":"2"}  - this will select only items having
     * job_title = 2 and department = 2)
     * @param $orderBy {String} a string to specify the ordering (e.g in_time desc)
     * @param string $limit {String} a string to specify the limit (e.g limit 2)
     * @param string $searchColumns {String} a JSON string to specify names of searchable
     * fields (e.g ["id","employee_id","first_name","last_name","mobile_phone","department","gender","supervisor"])
     * @param string $searchTerm {String} a string to specify term to search
     * @param string $isSubOrdinates {Boolean} a Boolean to specify if we only need to retive
     * subordinates. Any item is a subordinate item if the item has "profile" field defined
     * and the value of "profile" field is equal to id of one of the subordinates of currenly
     * logged in profile id. (Any Profile is a subordinate of curently logged in Profile if the
     * supervisor field of a Profile is set to the id of currently logged in Profile)
     * @param string $skipProfileRestriction {Boolean} default if false - TODO - I'll explain this later
     * @return {Array} an array of objects of type $table
     */
    public function getData(
        $table,
        $mappingStr = null,
        $filterStr = null,
        $orderBy = null,
        $limit = null,
        $searchColumns = null,
        $searchTerm = null,
        $isSubOrdinates = false,
        $skipProfileRestriction = false,
        $sortData = array()
    ) {
        $map = [];
        if (!empty($mappingStr)) {
            $map = json_decode($mappingStr);
        }
        $nsTable = $this->getFullQualifiedModelClassName($table);
        $obj = new $nsTable();
        $this->checkSecureAccess("get", $obj, $table, $_REQUEST);
        $query = "";
        $queryData = array();
        if (!empty($filterStr)) {
            $filter = json_decode($filterStr);
            if (!empty($filter)) {
                LogManager::getInstance()->debug("Building filter query");
                if (method_exists($obj, 'getCustomFilterQuery')) {
                    LogManager::getInstance()->debug("Method: getCustomFilterQuery exists");
                    $response = $obj->getCustomFilterQuery($filter);
                    $query = $response[0];
                    $queryData = $response[1];
                } else {
                    LogManager::getInstance()->debug("Method: getCustomFilterQuery not found");
                    $defaultFilterResp = $this->buildDefaultFilterQuery($filter);
                    $query = $defaultFilterResp[0];
                    $queryData = $defaultFilterResp[1];
                }
            }

            LogManager::getInstance()->debug("Filter Query:".$query);
            LogManager::getInstance()->debug("Filter Query Data:".json_encode($queryData));
        }

        if (!empty($searchTerm) && !empty($searchColumns)) {
            $searchColumnList = json_decode($searchColumns);
            $searchColumnList = array_diff($searchColumnList, $obj->getVirtualFields());
            if (!empty($searchColumnList)) {
                $tempQuery = " and (";
                foreach ($searchColumnList as $col) {
                    if ($tempQuery != " and (") {
                        $tempQuery.=" or ";
                    }
                    $tempQuery.=$col." like ?";
                    $queryData[] = "%".$searchTerm."%";
                }
                $query.= $tempQuery.")";
            }
        }

        if (!empty($sortData) && $sortData['sorting']."" == "1" && isset($sortData['column'])) {
            $orderBy = " ORDER BY ".$sortData['column']." ".$sortData['order'];
        } else {
            if (empty($orderBy)) {
                $orderBy = "";
            } else {
                $orderBy = " ORDER BY ".$orderBy;
            }
        }

        if (empty($limit)) {
            $limit = "";
        }

        if ($obj->getFinder() !== null) {
            $finder = $obj->getFinder();
        } else {
            $finder = $obj;
        }

        if (in_array($table, $this->userTables) && !$skipProfileRestriction) {
            $cemp = $this->getCurrentProfileId();
            if (!empty($cemp)) {
                if (!$isSubOrdinates) {
                    array_unshift($queryData, $cemp);
                    //$signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
                    $signInMappingField = $obj->getUserOnlyMeAccessField();
                    LogManager::getInstance()->debug(
                        "Data Load Query (x1):"."1=1".$signInMappingField." = ?".$query.$orderBy.$limit
                    );
                    LogManager::getInstance()->debug("Data Load Query Data (x1):".json_encode($queryData));
                    $list = $finder->Find($signInMappingField." = ?".$query.$orderBy.$limit, $queryData);
                } else {
                    $profileClass = $this->getFullQualifiedModelClassName(ucfirst(SIGN_IN_ELEMENT_MAPPING_FIELD_NAME));
                    $subordinate = new $profileClass();
                    $subordinates = $subordinate->Find("supervisor = ?", array($cemp));
                    $cempObj = new Employee();
                    $cempObj->Load("id = ?", array($cemp));

                    if ($obj->getUserOnlyMeAccessField() == 'id' &&
                        SettingsManager::getInstance()->getSetting('System: Company Structure Managers Enabled') == 1 &&
                        CompanyStructure::isHeadOfCompanyStructure($cempObj->department, $cemp)) {
                        if (empty($subordinates)) {
                            $subordinates = array();
                        }

                        $childCompaniesIds = array();
                        if (SettingsManager::getInstance()->getSetting(
                            'System: Child Company Structure Managers Enabled'
                        ) == '1'
                        ) {
                            $childCompaniesResp = CompanyStructure::getAllChildCompanyStructures($cempObj->department);
                            $childCompanies = $childCompaniesResp->getObject();

                            foreach ($childCompanies as $cc) {
                                $childCompaniesIds[] = $cc->id;
                            }
                        } else {
                            $childCompaniesIds[] = $cempObj->department;
                        }

                        if (!empty($childCompaniesIds)) {
                            $childStructureSubordinates = $subordinate->Find(
                                "department in (" . implode(',', $childCompaniesIds) . ") and id != ?",
                                array($cemp)
                            );
                            $subordinates = array_merge($subordinates, $childStructureSubordinates);
                        }
                    }

                    $subordinatesIds = "";
                    foreach ($subordinates as $sub) {
                        if ($subordinatesIds != "") {
                            $subordinatesIds.=",";
                        }
                        $subordinatesIds.=$sub->id;
                    }

                    if ($obj->allowIndirectMapping()) {
                        $indeirectEmployees = $subordinate->Find(
                            "indirect_supervisors IS NOT NULL and indirect_supervisors <> '' and status = 'Active'",
                            array()
                        );
                        foreach ($indeirectEmployees as $ie) {
                            $indirectSupervisors = json_decode($ie->indirect_supervisors, true);
                            if (in_array($cemp, $indirectSupervisors)) {
                                if ($subordinatesIds != "") {
                                    $subordinatesIds.=",";
                                }
                                $subordinatesIds.=$ie->id;
                            }
                        }
                    }

                    $signInMappingField = $obj->getUserOnlyMeAccessField();
                    LogManager::getInstance()->debug(
                        "Data Load Query (x2):"."1=1".$signInMappingField." in (".$subordinatesIds.") "
                        .$query.$orderBy.$limit
                    );
                    LogManager::getInstance()->debug("Data Load Query Data (x2):".json_encode($queryData));
                    if (!empty($subordinatesIds)) {
                        $list = $finder->Find(
                            $signInMappingField . " in (" . $subordinatesIds . ") " . $query . $orderBy . $limit,
                            $queryData
                        );
                    } else {
                        $list = array();
                    }
                }
            } else {
                $list = array();
            }
        } elseif ($isSubOrdinates) {
            $cemp = $this->getCurrentProfileId();
            if (!empty($cemp)) {
                $profileClass = $this->getFullQualifiedModelClassName(ucfirst(SIGN_IN_ELEMENT_MAPPING_FIELD_NAME));
                $subordinate = new $profileClass();
                $subordinates = $subordinate->Find("supervisor = ?", array($cemp));
                $cempObj = new Employee();
                $cempObj->Load("id = ?", array($cemp));
                if ($obj->getUserOnlyMeAccessField() == 'id' &&
                    SettingsManager::getInstance()->getSetting('System: Company Structure Managers Enabled') == 1 &&
                    CompanyStructure::isHeadOfCompanyStructure($cempObj->department, $cemp)) {
                    if (empty($subordinates)) {
                        $subordinates = array();
                    }

                    $childCompaniesIds = array();
                    if (SettingsManager::getInstance()->getSetting(
                        'System: Child Company Structure Managers Enabled'
                    ) == '1'
                    ) {
                        $childCompaniesResp = CompanyStructure::getAllChildCompanyStructures($cempObj->department);
                        $childCompanies = $childCompaniesResp->getObject();

                        foreach ($childCompanies as $cc) {
                            $childCompaniesIds[] = $cc->id;
                        }
                    } else {
                        $childCompaniesIds[] = $cempObj->department;
                    }

                    if (!empty($childCompaniesIds)) {
                        $childStructureSubordinates = $subordinate->Find(
                            "department in (" . implode(',', $childCompaniesIds) . ") and id != ?",
                            array($cemp)
                        );
                        $subordinates = array_merge($subordinates, $childStructureSubordinates);
                    }
                }

                $subordinatesIds = "";
                foreach ($subordinates as $sub) {
                    if ($subordinatesIds != "") {
                        $subordinatesIds.=",";
                    }
                    $subordinatesIds.=$sub->id;
                }

                if ($obj->allowIndirectMapping()) {
                    $indeirectEmployees = $subordinate->Find(
                        "indirect_supervisors IS NOT NULL and indirect_supervisors <> '' and status = 'Active'",
                        array()
                    );
                    foreach ($indeirectEmployees as $ie) {
                        $indirectSupervisors = json_decode($ie->indirect_supervisors, true);
                        if (in_array($cemp, $indirectSupervisors)) {
                            if ($subordinatesIds != "") {
                                $subordinatesIds.=",";
                            }
                            $subordinatesIds.=$ie->id;
                        }
                    }
                }

                $signInMappingField = $obj->getUserOnlyMeAccessField();
                LogManager::getInstance()->debug(
                    "Data Load Query (a1):".$signInMappingField." in (".$subordinatesIds.") ".$query.$orderBy.$limit
                );
                $list = $finder->Find(
                    $signInMappingField." in (".$subordinatesIds.") ".$query.$orderBy.$limit,
                    $queryData
                );
            } else {
                $list = $finder->Find("1=1".$query.$orderBy.$limit, $queryData);
            }
        } else {
            $list = $finder->Find("1=1".$query.$orderBy.$limit, $queryData);
        }

        if (!$list) {
            LogManager::getInstance()->debug("Get Data Error:".$obj->ErrorMsg());
        }

        LogManager::getInstance()->debug("Data Load Query:"."1=1".$query.$orderBy.$limit);
        LogManager::getInstance()->debug("Data Load Query Data:".json_encode($queryData));

        $processedList = array();
        foreach ($list as $obj) {
            $processedObj = $this->cleanUpAdoDB($obj->postProcessGetData($obj));
            if (null !== $processedObj) {
                $processedList[] = $processedObj;
            }
        }

        $list = $processedList;

        if (!empty($mappingStr) && is_object($map)) {
            $list = $this->populateMapping($list, $map);
        }

        return $list;
    }

    /**
     * Propulate field mappings for a given set of objects
     * @method populateMapping
     * @param $list {Array} array of model objects
     * @param $map {Array} an associative array of Mappings (e.g {"profile":["Profile","id","first_name+last_name"]})
     * @return {Array} array of populated objects
     */

    public function populateMapping($list, $map)
    {
        $listNew = array();
        if (empty($list)) {
            return $listNew;
        }
        foreach ($list as $item) {
            $item = $this->populateMappingItem($item, $map);
            $listNew[] = $item;
        }
        return  $listNew;
    }

    public function populateMappingItem($item, $map)
    {
        foreach ($map as $k => $v) {
            $fTable = $this->getFullQualifiedModelClassName($v[0]);
            $tObj = new $fTable();
            $tObj = $tObj->Find($v[1]."= ?", array($item->$k));

            if (is_array($tObj)) {
                $tObj = $tObj[0];
            } else {
                continue;
            }

            if ($tObj->{$v[1]} == $item->$k) {
                $v[2] = str_replace("+", " ", $v[2]);
                $values = explode(" ", $v[2]);
                if (count($values) == 1) {
                    $idField = $k."_id";
                    $item->$idField = $item->$k;
                    $item->$k = $tObj->{$v[2]};
                } else {
                    $objVal = "";
                    foreach ($values as $val2) {
                        if ($objVal != "") {
                            $objVal .= " ";
                        }
                        $objVal .= $tObj->$val2;
                    }
                    $idField = $k."_id";
                    $item->$idField = $item->$k;
                    $item->$k = $objVal;
                }
            }
        }
        return  $item;
    }

    /**
     * Retive one element from db
     * @method getElement
     * @param $table {String} model class name of the table to get data (e.g for Users table model class name is User)
     * @param $table {Integer} id of the item to get from $table
     * @param $mappingStr {String} a JSON string to specify fields of the $table should be mapped to other
     * tables (e.g {"profile":["Profile","id","first_name+last_name"]} : this is how the profile field in
     * Users table is mapped to Profile table. In this case users profile field will get filled by Profile
     * first name and last name. The original value in User->profile field will get moved to User->profile_id)
     * @param $skipSecurityCheck {Boolean} if true won't check whether the user has access to that object
     * @return {Object} an object of type $table
     */

    public function getElement($table, $id, $mappingStr = null, $skipSecurityCheck = false)
    {
        $nsTable = $this->getFullQualifiedModelClassName($table);
        $obj = new $nsTable();

        if (in_array($table, $this->userTables)) {
            $cemp = $this->getCurrentProfileId();
            if (!empty($cemp)) {
                $obj->Load("id = ?", array($id));
            } else {
            }
        } else {
            $obj->Load("id = ?", array($id));
        }

        if (!$skipSecurityCheck) {
            $this->checkSecureAccess("element", $obj, $table, $_POST);
        }

        if ($obj->id == $id) {
            if (!empty($mappingStr)) {
                $map = json_decode($mappingStr);
                $obj = $this->enrichObjectMappings($map, $obj);
            }

            //Add custom fields
            $obj = $this->enrichObjectCustomFields($table, $obj);

            $obj = $obj->postProcessGetElement($obj);
            return  $this->cleanUpAdoDB($obj);
        }
        return null;
    }

    /**
     * @param $nameField
     * @param $targetObject
     * @return string
     */
    private function getCombinedValue($nameField, $targetObject)
    {
        $values = explode("+", $nameField);
        if (count($values) == 1) {
            return $targetObject->{$nameField};
        }
        $objVal = '';
        foreach ($values as $value) {
            if ($objVal != "") {
                $objVal .= " ";
            }
            if (substr($value, 0, 1) !== ':') {
                $objVal .= $targetObject->{$value};
            } else {
                $objVal .= substr($value, 1);
            }
        }

        return $objVal;
    }

    /**
     * Add an element to a given table
     * @method addElement
     * @param $table {String} model class name of the table to add data (e.g for Users table model class name is User)
     * @param $obj {Array} an associative array with field names and values for the new object.
     * If the object id is not empty an existing object will be updated
     * @param null $postObject
     * @return IceResponse {Object} newly added or updated element of type $table newly added or updated
     * element of type $table
     */

    public function addElement($table, $obj, $postObject = null)
    {

        $customFields = array();
        $isAdd = true;
        $nsTable = $this->getFullQualifiedModelClassName($table);
        $ele = new $nsTable();


        if ($ele->validateCSRF()
            && (empty($obj->csrf) || $obj->csrf !== SessionUtils::getSessionObject('csrf-'.$table))) {
            return new IceResponse(
                IceResponse::ERROR,
                "CSRF Error"
            );
        }

        if (class_exists("\\Classes\\ProVersion")) {
            $pro = new ProVersion();
            $subscriptionTables = $pro->getSubscriptionTables();
            if (in_array($table, $subscriptionTables)) {
                $resp = $pro->subscriptionCheck($obj);
                if ($resp->getStatus() != IceResponse::SUCCESS) {
                    return $resp;
                }
            }
        }

        if (!empty($obj['id'])) {
            $isAdd = false;
            $ele->Load('id = ?', array($obj['id']));
        }

        $objectKeys = $ele->getObjectKeys();

        foreach ($obj as $k => $v) {
            if ($k == 'id' || $k == 't' || $k == 'a') {
                continue;
            }
            if ($v == "NULL") {
                $v = null;
            }
            if (isset($objectKeys[$k])) {
                $ele->$k = $v;
            }
        }

        if (empty($obj['id'])) {
            if (in_array($table, $this->userTables)) {
                $cemp = $this->getCurrentProfileId();
                if (!empty($cemp)) {
                    $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
                    $ele->$signInMappingField = $cemp;
                } else {
                    return new IceResponse(IceResponse::ERROR, "Profile id is not set");
                }
            }
        }

        if ($postObject === null) {
            $this->checkSecureAccess("save", $ele, $table, $_POST);
        } else {
            $this->checkSecureAccess("save", $ele, $table, $postObject);
        }


        $resp = $ele->validateSave($ele);
        if ($resp->getStatus() != IceResponse::SUCCESS) {
            return $resp;
        }

        if ($isAdd) {
            if (empty($ele->created)) {
                $ele->created = date("Y-m-d H:i:s");
            }
        }

        if (empty($ele->updated)) {
            $ele->updated = date("Y-m-d H:i:s");
        }
        if ($isAdd) {
            $preResponse = $ele->executePreSaveActions($ele);
        } else {
            $preResponse = $ele->executePreUpdateActions($ele);
        }

        if ($preResponse->getStatus() === IceResponse::ERROR) {
            return $preResponse;
        }

        $ele = $preResponse->getData();

        $ok = $ele->Save();

        if (!$ok) {
            $error = $ele->ErrorMsg();

            LogManager::getInstance()->info($error);

            if ($isAdd) {
                $this->audit(
                    IceConstants::AUDIT_ERROR,
                    "Error occurred while adding an object to ".$table." \ Error: ".$error
                );
            } else {
                $this->audit(
                    IceConstants::AUDIT_ERROR,
                    "Error occurred while editing an object in ".$table." [id:".$ele->id."] \ Error: ".$error
                );
            }
            return new IceResponse(IceResponse::ERROR, $this->findError($error));
        }

        $customFields = $ele->getCustomFields($obj);
        foreach ($obj as $k => $v) {
            if (isset($customFields[$k])) {
                $this->customFieldManager->addCustomField($table, $ele->id, $k, $v);
            }
        }

        if ($isAdd) {
            $ele->executePostSaveActions($ele);
            $this->audit(IceConstants::AUDIT_ADD, "Added an object to ".$table." [id:".$ele->id."]");
        } else {
            $ele->executePostUpdateActions($ele);
            $this->audit(IceConstants::AUDIT_EDIT, "Edited an object in ".$table." [id:".$ele->id."]");
        }

        return new IceResponse(IceResponse::SUCCESS, $ele);
    }

    /**
     * Delete an element if not the $table and $id is defined as a non deletable
     * @method deleteElement
     * @param $table {String} model class name of the table to delete data
     * (e.g for Users table model class name is User)
     * @param $id {Integer} id of the item to delete
     * @return NULL
     */
    public function deleteElement($table, $id)
    {
        $fileFields = $this->fileFields;
        $nsTable = $this->getFullQualifiedModelClassName($table);
        /** @var BaseModel $ele */
        $ele = new $nsTable();

        $ele->Load('id = ?', array($id));

        if (empty($ele->id) || $ele->id !== intval($id)) {
            return new IceResponse(
                IceResponse::ERROR,
                "Item not found"
            );
        }

        $preDeleteResponse = $ele->executePreDeleteActions($ele);
        if ($preDeleteResponse->getStatus() !== IceResponse::SUCCESS) {
            return $preDeleteResponse;
        }

        $this->checkSecureAccess("delete", $ele, $table, $_POST);

        if (isset($this->nonDeletables[$table])) {
            $nonDeletableTable = $this->nonDeletables[$table];
            if (!empty($nonDeletableTable)) {
                foreach ($nonDeletableTable as $field => $value) {
                    if ($ele->$field == $value) {
                        return new IceResponse(
                            IceResponse::ERROR,
                            "This item can not be deleted"
                        );
                    }
                }
            }
        }
        //Delete approval requests
        if (class_exists("\\Employees\\Common\\ModelEmployeeApproval")) {
            $approvalRequest = new EmployeeApproval();
            $approvalRequests = $approvalRequest->Find("type = ? and element = ?", array($table, $id));
            foreach ($approvalRequests as $approvalRequest) {
                $approvalRequest->Delete();
            }
        }

        $ok = $ele->Delete();
        if (!$ok) {
            $error = $ele->ErrorMsg();
            LogManager::getInstance()->info($error);
            return new IceResponse(
                IceResponse::ERROR,
                $this->findError($error)
            );
        } else {
            //Backup
            if ($table == ucfirst(SIGN_IN_ELEMENT_MAPPING_FIELD_NAME)) {
                $newObj = $this->cleanUpAdoDB($ele);
                $dataEntryBackup = new DataEntryBackup();
                $dataEntryBackup->tableType = $table;
                $dataEntryBackup->data = json_encode($newObj);
                $dataEntryBackup->Save();
            }
            $ele->executePostDeleteActions($ele);
            $this->audit(IceConstants::AUDIT_DELETE, "Deleted an object in ".$table." [id:".$ele->id."]");
        }

        if (isset($fileFields[$table])) {
            foreach ($fileFields[$table] as $k => $v) {
                if (!empty($ele->$k)) {
                    FileService::getInstance()->deleteFileByField($ele->$k, $v);
                }
            }
        }

        $cfs = $this->customFieldManager->getCustomFields($table, $id);
        /** @var CustomField $cf */
        foreach ($cfs as $cf) {
            $cf->Delete();
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            null
        );
    }

    /**
     * Get associative array of by retriving data from $table using $key field ans key and
     * $value field as value. Mainly used for getting data for populating option lists of select
     * boxes when adding and editing items
     * @method getFieldValues
     * @param $table {String} model class name of the table to get data (e.g for Users table model class name is User)
     * @param $key {String} key field name
     * @param $value {String} value field name (multiple fileds cam be concatinated using +) - e.g first_name+last_name
     * @param $method {String} if not empty, use this menthod to get only a selected set of objects
     * from db instead of retriving all objects. This method should be defined in class $table
     * and should return an array of objects of type $table
     * @return {Array} associative array
     */

    public function getFieldValues($table, $key, $value, $method, $methodParams = null)
    {

        $values = explode("+", $value);

        $ret = array();
        $nsTable = $this->getFullQualifiedModelClassName($table);
        $ele = new $nsTable();

        $finder = $ele->getFieldMappingFinder();
        if ($finder !== null) {
            $ele = $finder;
        }

        $this->checkSecureAccess("get", $ele, $table, $_POST);
        if (!empty($method)) {
            if (method_exists($ele, $method) && in_array($method, $ele->fieldValueMethods())) {
                if (!empty($methodParams)) {
                    $list = $ele->$method(json_decode($methodParams));
                } else {
                    $list = $ele->$method(array());
                }
            } else {
                LogManager::getInstance()->error("Could not find method:".$method." in Class:".$table);
                $list = $ele->Find('1 = 1', array());
            }
        } else {
            $list = $ele->Find('1 = 1', array());
        }

        foreach ($list as $obj) {
            $obj = $this->cleanUpAdoDB($obj);
            if (count($values) == 1) {
                $ret[(string)$obj->$key] = $obj->$value;
            } else {
                $objVal = "";
                foreach ($values as $v) {
                    if ($objVal != "") {
                        $objVal .= " ";
                    }
                    $objVal .= $obj->$v;
                }
                $ret[(string)$obj->$key] = $objVal;
            }
        }
        return $ret;
    }

    public function setNonDeletables($table, $field, $value)
    {
        if (!isset($this->nonDeletables[$table])) {
            $this->nonDeletables[$table] = array();
        }
        $this->nonDeletables[$table][$field] = $value;
    }

    public function setSqlErrors($errros)
    {
        $this->errros = $errros;
    }

    public function setUserTables($userTables)
    {
        $this->userTables = $userTables;
    }

    /**
     * Set the current logged in user
     * @method setCurrentUser
     * @param $currentUser {User} the current logged in user
     */

    public function setCurrentUser($currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function findError($error)
    {
        foreach ($this->errros as $k => $v) {
            if (strstr($error, $k)) {
                return $v;
            } else {
                $keyParts = explode("|", $k);
                if (count($keyParts) >= 2) {
                    if (strstr($error, $keyParts[0]) && strstr($error, $keyParts[1])) {
                        return $v;
                    }
                }
            }
        }
        return $error;
    }

    /**
     * Get the currently logged in user from session
     * @method getCurrentUser
     * @return {User} currently logged in user from session
     */

    public function getCurrentUser()
    {
        if (!empty($this->currentUser)) {
            return $this->currentUser;
        }
        $user = SessionUtils::getSessionObject('user');
        return $user;
    }

    /**
     * Get the Profile id attached to currently logged in user. if the user is switched,
     * this will return the id of switched Profile instead of currently logged in users Prifile id
     * @method getCurrentProfileId
     * @return {Integer}
     */
    public function getCurrentProfileId()
    {
        if ($this->currentProfileId) {
            return $this->currentProfileId;
        }
        $adminEmpId = SessionUtils::getSessionObject('admin_current_profile');
        $user = SessionUtils::getSessionObject('user');
        if (empty($adminEmpId) && !empty($user)) {
            $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
            return $user->$signInMappingField;
        }
        return $adminEmpId;
    }

    /**
     * Get the Profile id attached to currently logged in user
     * @method getCurrentProfileId
     * @return {Integer}
     */
    public function getCurrentUserProfileId()
    {
        $user = SessionUtils::getSessionObject('user');
        $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
        return $user->$signInMappingField;
    }

    /**
     * Check if the current user has switched into another user
     * @method isEmployeeSwitched
     * @return {Boolean}
     */
    public function isEmployeeSwitched()
    {
        $adminEmpId = SessionUtils::getSessionObject('admin_current_profile');
        return !empty($adminEmpId);
    }

    /**
     * Get User by profile id
     * @method getUserFromProfileId
     * @param $profileId {Integer} profile id
     * @return {User} user object
     */

    public function getUserFromProfileId($profileId)
    {
        $user = new User();
        $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
        $user->load($signInMappingField." = ?", array($profileId));
        if ($user->$signInMappingField == $profileId) {
            return $user;
        }
        return null;
    }

    public function setCurrentAdminProfile($profileId)
    {
        if ($profileId == "-1") {
            SessionUtils::saveSessionObject('admin_current_profile', null);
            return;
        }

        if ($this->currentUser->user_level == 'Admin') {
            SessionUtils::saveSessionObject('admin_current_profile', $profileId);
        } elseif ($this->currentUser->user_level == 'Manager'
            && $this->canManageEmployee($profileId)
        ) {
            SessionUtils::saveSessionObject('admin_current_profile', $profileId);
        } else {
            SessionUtils::saveSessionObject('admin_current_profile', null);
        }
    }

    public function cleanUpAdoDB($obj)
    {
        unset($obj->table);

        return $obj;
    }

    public function cleanUpIgnoreKeys($obj)
    {
        unset($obj->keysToIgnore);

        return $obj;
    }

    public function cleanUpApprovalModelParameters($obj)
    {
        unset($obj->notificationModuleName);
        unset($obj->notificationUnitName);
        unset($obj->notificationUnitPrefix);
        unset($obj->notificationUnitAdminUrl);
        unset($obj->preApproveSettingName);

        return $obj;
    }

    public function cleanUpAll($obj)
    {
        $obj = $this->cleanUpAdoDB($obj);
        $obj = $this->cleanUpIgnoreKeys($obj);

        return $obj;
    }

    public function cleanUpUser($obj)
    {
        $obj = $this->cleanUpAll($obj);
        unset($obj->password);
        unset($obj->login_hash);
        unset($obj->googleUserData);
        unset($obj->wrong_password_count);
        unset($obj->last_wrong_attempt_at);

        return $obj;
    }

    public function setDB($db)
    {
        $this->db = $db;
    }

    public function getDB()
    {
        return $this->db;
    }

    /**
     * Use user level security functions defined in model classes to check whether a given action
     * type is allowed to be executed by the current user on a given object
     * @method checkSecureAccess
     * @param $type {String} Action type
     * @param $object {Object} object to test access
     * @param $table
     * @param $request
     * @return bool {Boolen} true or exit true or exit
     */

    public function checkSecureAccess($type, $object, $table, $request)
    {
        $userOnlyMeAccessRequestField = $object->getUserOnlyMeAccessRequestField();
        $userOnlyMeAccessField = $object->getUserOnlyMeAccessField();

        $accessMatrix = $object->getRoleBasedAccess($this->currentUser->user_level, $this->currentUser->user_roles);

        if (in_array($type, $accessMatrix)) {
            //The user has required permission, so return true
            return true;
        } elseif (!empty($this->currentUser->$userOnlyMeAccessField)) {
            //Now we need to check whther the user has access to his own records
            if ($this->isEmployeeSwitched()) {
                $accessMatrix = $object->getUserOnlyMeSwitchedAccess();
            } else {
                $accessMatrix = $object->getUserOnlyMeAccess();
            }


            //This will check whether user can access his own records using a value in request
            if (isset($request[$userOnlyMeAccessField])
                && isset($this->currentUser->$userOnlyMeAccessField)) {
                if (in_array($type, $accessMatrix) && $request[$userOnlyMeAccessField]
                    === $this->currentUser->$userOnlyMeAccessRequestField) {
                    return true;
                }
            }

            // This will check if can query his own records
            // Employees should be able to update their own records
            if (!empty($table) && in_array($type, $accessMatrix)) {
                if (!empty($this->currentUser->$userOnlyMeAccessRequestField)
                    && in_array($table, $this->userTables)) {
                    return true;
                }
            }
        }

        $action = PermissionManager::ACCESS_LIST_DESCRIPTION[$type];
        $message = "You are not allowed to $action object type ".$object->table.'.';
        $exception = new IceHttpException(
            $message,
            403
        );
        LogManager::getInstance()->notifyException($exception);
        throw $exception;
    }

    public function getInstanceId()
    {
        $settings = new Setting();
        $settings->Load("name = ?", array("Instance : ID"));

        if ($settings->name != "Instance : ID" || empty($settings->value)) {
            $settings->value = md5(time());
            $settings->name = "Instance : ID";
            $settings->category = "Instance";
            $settings->Save();
        }

        return $settings->value;
    }

    public function setInstanceKey($key)
    {
        $settings = new Setting();
        $settings->Load("name = ?", array("Instance: Key"));
        if ($settings->name != "Instance: Key") {
            $settings->name = "Instance: Key";
            $settings->category = "Instance";
        }
        $settings->value = $key;
        $settings->Save();
    }

    public function getInstanceKey()
    {
        $settings = new Setting();
        $settings->Load("name = ?", array("Instance: Key"));
        if ($settings->name != "Instance: Key") {
            return null;
        }
        return $settings->value;
    }

    public function validateInstance()
    {
        $instanceId = $this->getInstanceId();
        if (empty($instanceId)) {
            return true;
        }

        $key = $this->getInstanceKey();

        if (empty($key)) {
            return false;
        }

        if (strlen($key) > 7) {
            return true;
        }

//        $data = AesCtr::decrypt($key, $instanceId, 256);
//        $arr = explode("|", $data);
//        if ($arr[0] == KEY_PREFIX && $arr[1] == $instanceId) {
//            return true;
//        }

        return false;
    }

    public function loadModulePermissions($group, $name, $userLevel)
    {
        $module = new Module();
        $module->Load("update_path = ?", array($group.">".$name));

        $arr = array();
        $arr['user'] = json_decode($module->user_levels, true);
        $arr['user_roles'] = !empty($module->user_roles)?json_decode($module->user_roles, true):array();

        $permission = new Permission();
        $modulePerms = $permission->Find("module_id = ? and user_level = ?", array($module->id,$userLevel));

        $perms = array();
        foreach ($modulePerms as $p) {
            $perms[$p->permission] = $p->value;
        }

        $arr['perm'] = $perms;

        return $arr;
    }

    public function isModuleAllowedForUser($moduleManagerObj)
    {
        $moduleObject = $moduleManagerObj->getModuleObject();

        //Check if the module is disabled
        if ($moduleObject['status'] == 'Disabled') {
            return false;
        }

        //Check if user has permissions to this module
        //Check Module Permissions
        $modulePermissions = BaseService::getInstance()->loadModulePermissions(
            $moduleManagerObj->getModuleType(),
            $moduleObject['name'],
            BaseService::getInstance()->getCurrentUser()->user_level
        );

        if (!in_array(BaseService::getInstance()->getCurrentUser()->user_level, $modulePermissions['user'])) {
            if (!empty(BaseService::getInstance()->getCurrentUser()->user_roles)) {
                $userRoles = json_decode(BaseService::getInstance()->getCurrentUser()->user_roles, true);
            } else {
                $userRoles = array();
            }
            $commonRoles = array_intersect($modulePermissions['user_roles'], $userRoles);
            if (empty($commonRoles)) {
                return false;
            }
        }

        return true;
    }

    public function isModuleAllowedForGivenUser($moduleManagerObj, $user)
    {
        $moduleObject = $moduleManagerObj->getModuleObject();

        //Check if the module is disabled
        if ($moduleObject['status'] == 'Disabled') {
            return false;
        }

        //Check if user has permissions to this module
        //Check Module Permissions
        $modulePermissions = BaseService::getInstance()->loadModulePermissions(
            $moduleManagerObj->getModuleType(),
            $moduleObject['name'],
            $user->user_level
        );

        if (!in_array($user->user_level, $modulePermissions['user'])) {
            if (!empty($user->user_roles)) {
                $userRoles = json_decode($user->user_roles, true);
            } else {
                $userRoles = array();
            }
            $commonRoles = array_intersect($modulePermissions['user_roles'], $userRoles);
            if (empty($commonRoles)) {
                return false;
            }
        }

        return true;
    }

    public function getGAKey()
    {
        return SettingsManager::getInstance()->getSetting('Analytics: Google Key');
    }

    public function setMigrationManager($migrationManager)
    {
        $this->migrationManager = $migrationManager;
    }

    public function getMigrationManager()
    {
        return $this->migrationManager;
    }

    /**
     * Set the audit manager
     * @method setAuditManager
     * @param $auditManager {AuditManager}
     */

    public function setAuditManager($auditManager)
    {
        $this->auditManager = $auditManager;
    }

    /**
     * Set the NotificationManager
     * @method setNotificationManager
     * @param $notificationManager {NotificationManager}
     */

    public function setNotificationManager($notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    /**
     * Set the SettingsManager
     * @method setSettingsManager
     * @param $settingsManager {SettingsManager}
     */

    public function setSettingsManager($settingsManager)
    {
        $this->settingsManager = $settingsManager;
    }

    public function setFileFields($fileFields)
    {
        $this->fileFields = $fileFields;
    }

    public function audit($type, $data)
    {
        if (!empty($this->auditManager)) {
            $this->auditManager->addAudit($type, $data);
        }
    }

    public function fixJSON($json)
    {
        $noJSONRequests = SettingsManager::getInstance()->getSetting("System: Do not pass JSON in request");
        if ($noJSONRequests."" == "1") {
            $json = base64_decode($json);
        }
        return $json;
    }

    public function addModuleManager($moduleManager)
    {
        if (empty($this->moduleManagers)) {
            $this->moduleManagers = array();
        }
        $moduleObject = $moduleManager->getModuleObject();
        $this->moduleManagers[$moduleManager->getModuleType()."_".$moduleObject['name']] = $moduleManager;
    }

    public function getModuleManagers()
    {
        return array_values($this->moduleManagers);
    }

    public function getModuleManagerNames()
    {
        $keys =  array_keys($this->moduleManagers);
        $arr = array();
        foreach ($keys as $key) {
            $arr[$key] = 1;
        }

        return $arr;
    }

    public function getModuleManager($type, $name)
    {
        return isset($this->moduleManagers[$type."_".$name]) ? $this->moduleManagers[$type."_".$name] : null;
    }

    public function setEmailSender($emailSender)
    {
        $this->emailSender = $emailSender;
    }

    public function getEmailSender()
    {
        return $this->emailSender;
    }

    public function getFieldNameMappings($type)
    {
        $fieldNameMap = new FieldNameMapping();
        $data = $fieldNameMap->Find("type = ?", array($type));
        return $data;
    }

    public function getCustomFields($type)
    {
        $customField = new CustomField();
        $data = $customField->Find("type = ? and display = ?", array($type,'Form'));
        return $data;
    }

    public function getAllAdmins()
    {
        $user = new User();
        $admins = $user->Find('user_level = ?', array('Admin'));
        return $admins;
    }

    public function getCurrentEmployeeTimeZone()
    {
        $cemp = $this->getCurrentProfileId();
        if (empty($cemp)) {
            return null;
        }
        $emp = new Employee();
        $emp->Load("id = ?", array($cemp));
        if (empty($emp->id) || empty($emp->department)) {
            return null;
        }

        $dept = new CompanyStructure();
        $dept->Load("id = ?", array($emp->department));

        return $dept->timezone;
    }

    public function setupHistoryManager($type, $historyManager)
    {
        $this->historyManagers[$type] = $historyManager;
    }

    public function addHistoryItem($historyManagerType, $type, $refId, $field, $oldVal, $newVal)
    {
        if (isset($this->historyManagers[$historyManagerType])) {
            return $this->historyManagers[$historyManagerType]->addHistory($type, $refId, $field, $oldVal, $newVal);
        }
        return false;
    }

    public function getItemFromCache($class, $id)
    {
        $class = $this->getFullQualifiedModelClassName($class);
        $data = MemcacheService::getInstance()->get($class."-".$id);
        if ($data !== false) {
            return unserialize($data);
        }

        $obj = new $class();
        $obj->Load("id = ?", array($id));
        if ($obj->id != $id) {
            return null;
        }

        MemcacheService::getInstance()->set($class."-".$id, serialize($obj), 10 * 60);

        return $obj;
    }

    public function addCalculationHook($code, $name, $class, $method)
    {
        $calcualtionHook = new CalculationHook();
        $calcualtionHook->code = $code;
        $calcualtionHook->name = $name;
        $calcualtionHook->class = $class;
        $calcualtionHook->method = $method;
        $this->calculationHooks[$code] = $calcualtionHook;
    }

    public function getCalculationHooks()
    {
        return array_values($this->calculationHooks);
    }

    public function getCalculationHook($code)
    {
        return $this->calculationHooks[$code];
    }

    public function executeCalculationHook($parameters, $code, $additionalData = null)
    {
        $ch = BaseService::getInstance()->getCalculationHook($code);

        if (empty($ch->code)) {
            return null;
        }

        if (!empty($additionalData)) {
            $parameters[] = $additionalData;
        }

        $class = $ch->class;
        return call_user_func_array(array(new $class(), $ch->method), $parameters);
    }

    public function initializePro()
    {
        $this->pro = null;
        if (class_exists('\\Classes\\ProVersion')) {
            $pro = new ProVersion();
            if (method_exists($pro, 'isModuleEnabled')) {
                $this->pro = $pro;
            }
        }
    }

    public function isModuleEnabled($type, $name)
    {
        if ($this->pro === null) {
            return true;
        }

        return $this->pro->isModuleEnabled($type, $name);
    }

    public function cleanNonUTFChar($obj)
    {
        $regex = <<<'END'
/
  (
    (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
    |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
    |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
    |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3
    ){1,100}                        # ...one or more times
  )
| .                                 # anything else
/x
END;
        if (is_string($obj)) {
            return preg_replace($regex, '$1', $obj);
        } else {
            foreach ($obj as $key => $val) {
                $obj->$key = preg_replace($regex, '$1', $val);
            }
            return $obj;
        }
    }

    public function setCustomFieldManager($customFieldManager)
    {
        $this->customFieldManager = $customFieldManager;
    }

    public function getCustomFieldManager()
    {
        return $this->customFieldManager;
    }

    public function getFullQualifiedModelClassName($class)
    {
        if (isset($this->modelClassMap[$class])) {
            return $this->modelClassMap[$class];
        }
        return '\\Model\\'.$class;
    }

    /**
     * @param $profileId
     * @return bool
     */
    protected function canManageEmployee($profileId)
    {
        $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
        $signInMappingFieldTable = $this->getFullQualifiedModelClassName(ucfirst($signInMappingField));
        $subordinate = new $signInMappingFieldTable();

        $subordinates = $subordinate->Find("supervisor = ?", array($this->currentUser->$signInMappingField));
        $subFound = false;
        foreach ($subordinates as $sub) {
            if ($sub->id == $profileId) {
                $subFound = true;
                break;
            }
        }

        $departmentHeadFound = false;
        $subordinate = new $signInMappingFieldTable();
        $subordinate->Load('id = ?', array($profileId));
        if (SettingsManager::getInstance()->getSetting('System: Company Structure Managers Enabled') == 1
            && CompanyStructure::isHeadOfCompanyStructure(
                $subordinate->department,
                $this->currentUser->$signInMappingField
            )
        ) {
            $departmentHeadFound = true;
        } elseif (SettingsManager::getInstance()->getSetting(
            'System: Child Company Structure Managers Enabled'
        ) == '1'
        ) {
            $companyStructure = new CompanyStructure();
            $companyStructure->Load('id = ?', array($subordinate->department));
            do {
                if (CompanyStructure::isHeadOfCompanyStructure(
                    $companyStructure->id,
                    $this->currentUser->$signInMappingField
                )
                ) {
                    $departmentHeadFound = true;
                    break;
                }

                $parentCompanyStructure = $companyStructure->parent;
                if (!empty($parentCompanyStructure)) {
                    $companyStructure = new CompanyStructure();
                    $companyStructure->Load('id = ?', array($parentCompanyStructure));
                }
            } while (!empty($companyStructure->id)
                && !empty($parentCompanyStructure)
            );
        }


        return $subFound || $departmentHeadFound;
    }

    /**
     * @param $value
     * @param int $options
     * @param int $depth
     * @return string
     * @throws \Exception
     */
    public function safeJsonEncode($value, $options = 0, $depth = 512)
    {
        $encoded = json_encode($value, $options, $depth);
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $encoded;
            case JSON_ERROR_DEPTH:
                throw new \Exception('Maximum stack depth exceeded');
            case JSON_ERROR_STATE_MISMATCH:
                throw new \Exception('Underflow or the modes mismatch');
            case JSON_ERROR_CTRL_CHAR:
                throw new \Exception('Unexpected control character found');
            case JSON_ERROR_SYNTAX:
                throw new \Exception('Syntax error, malformed JSON');
            case JSON_ERROR_UTF8:
                $clean = $this->utf8ize($value);
                return $this->safeJsonEncode($clean, $options, $depth);
            default:
                throw new \Exception('Unknown Json parsing error');
        }
    }

    protected function utf8ize($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->utf8ize($value);
            }
        } elseif (is_object($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed->$key = $this->utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return utf8_encode($mixed);
        }
        return $mixed;
    }

    public function generateCsrf($formId)
    {
        $csrfToken = sha1(rand(4500, 100000) . time(). CLIENT_BASE_URL. $this->currentUser->id);
        SessionUtils::saveSessionObject('csrf-'.$formId, $csrfToken);
        return $csrfToken;
    }

    /**
     * @param $map
     * @param $obj
     * @return mixed
     */
    public function enrichObjectMappings($map, $obj)
    {
        if (!empty($map)) {
            foreach ($map as $k => $v) {
                if (in_array($v[0], array('User', 'Setting'))) {
                    continue;
                }
                $fTable = $this->getFullQualifiedModelClassName($v[0]);
                $tObj = new $fTable();
                $name = $k . "_Name";
                $obj->$name = '';
                if (isset($v[3]) && $v[3] === true) {
                    if (!empty($obj->{$k}) && !empty(json_decode($obj->{$k}, true))) {
                        foreach (json_decode($obj->{$k}, true) as $partialId) {
                            if ($obj->$name != '') {
                                $obj->$name .= ', ';
                            }
                            $tObjArr = $tObj->Find($v[1] . "= ?", [$partialId]);
                            if (!is_array($tObjArr) || empty($tObjArr[0])) {
                                continue;
                            }
                            $obj->$name .= $this->getCombinedValue($v[2], $tObjArr[0]);
                        }
                    }
                } else {
                    $tObjArr = $tObj->Find($v[1] . "= ?", [$obj->$k]);
                    if (!is_array($tObjArr) || empty($tObjArr[0])) {
                        continue;
                    }
                    $obj->$name = $this->getCombinedValue($v[2], $tObjArr[0]);
                }
            }
        }
        return $obj;
    }

    /**
     * @param $table
     * @param $obj
     * @return mixed
     */
    public function enrichObjectCustomFields($table, $obj)
    {
        /** @var CustomFieldManager $customFields */
        $customFields = $this->customFieldManager->getCustomFields($table, $obj->id);
        foreach ($customFields as $cf) {
            $obj->{$cf->name} = $cf->value;
        }
        return $obj;
    }

    /**
     * @return RedisCacheService
     */
    public function getCacheService()
    {
        return $this->cacheService;
    }

    /**
     * @param CacheService $redisCacheService
     */
    public function setCacheService($redisCacheService)
    {
        $this->cacheService = $redisCacheService;
    }

    public function queryCacheEnabled()
    {
        return defined('QUERY_CACHE') && QUERY_CACHE === true;
    }

    public function getCurrentDBUser()
    {
        $user = BaseService::getInstance()->getCurrentUser();
        if (empty($user)) {
            return new IceResponse(IceResponse::ERROR);
        }
        $dbUser = new User();
        $dbUser->Load("id = ?", array($user->id));
        return $dbUser;
    }

    public function getAccessToken()
    {
        $dbUser = $this->getCurrentDBUser();
        return RestApiManager::getInstance()->getAccessTokenForUser($dbUser);
    }

    public function isSubordinateEmployee($supervisorId, $subordinateId)
    {
        $employee = new Employee();
        $employee->Load('id = ? and supervisor = ?', [$subordinateId, $supervisorId]);

        return ($supervisorId == $employee->supervisor && $subordinateId == $employee->id);
    }

    public function setSystemData($name, $value)
    {
        $sysData = new SystemData();
        $sysData->Load('name = ?', [$name]);

        if (!empty($sysData->id)) {
            $sysData->value = $value;
        } else {
            $sysData->name = $name;
            $sysData->value = $value;
        }

        return $sysData->Save();
    }

    public function getSystemData($name)
    {
        $sysData = new SystemData();
        $sysData->Load('name = ?', [$name]);
        if (!empty($sysData->id) && $sysData->name === $name) {
            return $sysData->value;
        }

        return null;
    }

    public function getDataDirectory()
    {
        $dataDir = SettingsManager::getInstance()->getSetting('System: Data Directory');
        if (!empty($dataDir) && is_dir($dataDir)) {
            return $dataDir;
        }

        return CLIENT_BASE_PATH.'data/';
    }

    public function getExtensionMigrations()
    {
        return $this->extensionMigrations;
    }

    public function registerExtensionMigration(MigrationInterface $migration)
    {
        $this->extensionMigrations[$migration->getName()] = $migration;
    }
}
