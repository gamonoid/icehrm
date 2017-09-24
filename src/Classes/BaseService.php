<?php

/*
This file is part of Ice Framework.

Ice Framework is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Ice Framework is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Ice Framework. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */

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
use Company\Common\Model\CompanyStructure;
use Employees\Common\Model\Employee;
use Employees\Common\Model\EmployeeApproval;
use FieldNames\Common\Model\CustomField;
use FieldNames\Common\Model\FieldNameMapping;
use Metadata\Common\Model\CalculationHook;
use Model\DataEntryBackup;
use Model\Setting;
use Modules\Common\Model\Module;
use Permissions\Common\Model\Permission;
use Users\Common\Model\User;
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
    public $modelClassMap = array();

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

        $this->checkSecureAccess("get", $obj);

        $query = "";
        $queryData = array();
        if (!empty($filterStr)) {
            $filter = json_decode($filterStr, true);

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
        }

        if (empty($orderBy)) {
            $orderBy = "";
        } else {
            $orderBy = " ORDER BY ".$orderBy;
        }

        if (in_array($table, $this->userTables)) {
            $cemp = $this->getCurrentProfileId();
            if (!empty($cemp)) {
                $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
                LogManager::getInstance()->debug("Query: ".$signInMappingField." = ?".$query.$orderBy);
                LogManager::getInstance()->debug("Query Data: ".print_r(array_merge(array($cemp), $queryData), true));
                $list = $obj->Find($signInMappingField." = ?".$query.$orderBy, array_merge(array($cemp), $queryData));
            } else {
                $list = array();
            }
        } else {
            LogManager::getInstance()->debug("Query: "."1=1".$query.$orderBy);
            LogManager::getInstance()->debug("Query Data: ".print_r($queryData, true));
            $list = $obj->Find("1=1".$query.$orderBy, $queryData);
        }

        $newList = array();
        foreach ($list as $listObj) {
            $newList[] = $this->cleanUpAdoDB($listObj);
        }

        if (!empty($mappingStr) && count($map)>0) {
            $list = $this->populateMapping($newList, $map);
        }

        return $list;
    }

    public function addModelClass($modelClass, $fullQualifiedName)
    {
        $this->modelClassMap[$modelClass] = $fullQualifiedName;
    }

    public function getModelClassName($name)
    {
        return $this->getFullQualifiedModelClassName($name);
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

                    $query.=$k." like ?";

                    if ($i < $length -1) {
                        $query.=" or ";
                    } else {
                        $query.=")";
                    }
                    $queryData[] = "%".$v[$i]."%";
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
        if (!empty($mappingStr)) {
            $map = json_decode($mappingStr);
        }
        $nsTable = $this->getFullQualifiedModelClassName($table);
        $obj = new $nsTable();
        $this->checkSecureAccess("get", $obj);
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
                    $list = $obj->Find($signInMappingField." = ?".$query.$orderBy.$limit, $queryData);
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
                        $list = $obj->Find(
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
                $list = $obj->Find(
                    $signInMappingField." in (".$subordinatesIds.") ".$query.$orderBy.$limit,
                    $queryData
                );
            } else {
                $list = $obj->Find("1=1".$query.$orderBy.$limit, $queryData);
            }
        } else {
            $list = $obj->Find("1=1".$query.$orderBy.$limit, $queryData);
        }

        if (!$list) {
            LogManager::getInstance()->debug("Get Data Error:".$obj->ErrorMsg());
        }

        LogManager::getInstance()->debug("Data Load Query:"."1=1".$query.$orderBy.$limit);
        LogManager::getInstance()->debug("Data Load Query Data:".json_encode($queryData));

        $processedList = array();
        foreach ($list as $obj) {
            $processedList[] = $this->cleanUpAdoDB($obj->postProcessGetData($obj));
        }

        $list = $processedList;

        if (!empty($mappingStr) && count($map)>0) {
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
            $tObj->Load($v[1]."= ?", array($item->$k));

            if ($tObj->{$v[1]} == $item->$k) {
                $v[2] = str_replace("+", " ", $v[2]);
                $values = explode(" ", $v[2]);
                if (count($values) == 1) {
                    $idField = $k."_id";
                    $item->$idField = $item->$k;
                    $item->$k = $tObj->{$v[2]};
                } else {
                    $objVal = "";
                    foreach ($values as $v) {
                        if ($objVal != "") {
                            $objVal .= " ";
                        }
                        $objVal .= $tObj->$v;
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
            $this->checkSecureAccess("element", $obj);
        }

        if (!empty($mappingStr)) {
            $map = json_decode($mappingStr);
        }
        if ($obj->id == $id) {
            if (!empty($mappingStr)) {
                foreach ($map as $k => $v) {
                    $fTable = $this->getFullQualifiedModelClassName($v[0]);
                    $tObj = new $fTable();
                    $tObj->Load($v[1]."= ?", array($obj->$k));
                    if ($tObj->{$v[1]} == $obj->$k) {
                        $name = $k."_Name";
                        $values = explode("+", $v[2]);
                        if (count($values) == 1) {
                            $obj->$name = $tObj->{$v[2]};
                        } else {
                            $objVal = "";
                            foreach ($values as $v) {
                                if ($objVal != "") {
                                    $objVal .= " ";
                                }
                                $objVal .= $tObj->$v;
                            }
                            $obj->$name = $objVal;
                        }
                    }
                }
            }

            //Add custom fields
            $customFields = $this->customFieldManager->getCustomFields($table, $obj->id);
            foreach ($customFields as $cf) {
                $obj->{$cf->name} = $cf->value;
            }

            $obj = $obj->postProcessGetElement($obj);
            return  $this->cleanUpAdoDB($obj->postProcessGetData($obj));
        }
        return null;
    }

    /**
     * Add an element to a given table
     * @method addElement
     * @param $table {String} model class name of the table to add data (e.g for Users table model class name is User)
     * @param $obj {Array} an associative array with field names and values for the new object.
     * If the object id is not empty an existing object will be updated
     * @return {Object} newly added or updated element of type $table
     */

    public function addElement($table, $obj)
    {
        $customFields = array();
        $isAdd = true;
        $nsTable = $this->getFullQualifiedModelClassName($table);
        $ele = new $nsTable();
        //LogManager::getInstance()->error("Obj:".json_encode($obj));

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

        $this->checkSecureAccess("save", $ele);

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
            $ele = $ele->executePreSaveActions($ele)->getData();
        } else {
            $ele = $ele->executePreUpdateActions($ele)->getData();
        }

        $ok = $ele->Save();

        if (!$ok) {
            $error = $ele->ErrorMsg();

            LogManager::getInstance()->info($error);

            if ($isAdd) {
                $this->audit(
                    IceConstants::AUDIT_ERROR,
                    "Error occured while adding an object to ".$table." \ Error: ".$error
                );
            } else {
                $this->audit(
                    IceConstants::AUDIT_ERROR,
                    "Error occured while editing an object in ".$table." [id:".$ele->id."] \ Error: ".$error
                );
            }
            return new IceResponse(IceResponse::ERROR, $this->findError($error));
        }
        LogManager::getInstance()->error("Element:".json_encode($ele));
        LogManager::getInstance()->error("Obj:".json_encode($obj));
        LogManager::getInstance()->error("Obj Keys:".json_encode($objectKeys));
        $customFields = $ele->getCustomFields($obj);
        LogManager::getInstance()->error("Custom:".json_encode($customFields));
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
        $ele = new $nsTable();

        $ele->Load('id = ?', array($id));

        $this->checkSecureAccess("delete", $ele);

        if (isset($this->nonDeletables[$table])) {
            $nonDeletableTable = $this->nonDeletables[$table];
            if (!empty($nonDeletableTable)) {
                foreach ($nonDeletableTable as $field => $value) {
                    if ($ele->$field == $value) {
                        return "This item can not be deleted";
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
            return $this->findError($error);
        } else {
            //Backup
            if ($table == ucfirst(SIGN_IN_ELEMENT_MAPPING_FIELD_NAME)) {
                $newObj = $this->cleanUpAdoDB($ele);
                $dataEntryBackup = new DataEntryBackup();
                $dataEntryBackup->tableType = $table;
                $dataEntryBackup->data = json_encode($newObj);
                $dataEntryBackup->Save();
            }

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
        foreach ($cfs as $cf) {
            $cf->Delete();
        }

        return null;
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
        if (!empty($method)) {
            LogManager::getInstance()->debug("Call method for getFieldValues:".$method);
            LogManager::getInstance()->debug("Call method params for getFieldValues:".json_decode($methodParams));
            if (method_exists($ele, $method)) {
                if (!empty($methodParams)) {
                    $list = $ele->$method(json_decode($methodParams));
                } else {
                    $list = $ele->$method(array());
                }
            } else {
                LogManager::getInstance()->debug("Could not find method:".$method." in Class:".$table);
                $list = $ele->Find('1 = 1', array());
            }
        } else {
            $list = $ele->Find('1 = 1', array());
        }

        foreach ($list as $obj) {
            $obj = $this->cleanUpAdoDB($obj);
            if (count($values) == 1) {
                $ret[$obj->$key] = $obj->$value;
            } else {
                $objVal = "";
                foreach ($values as $v) {
                    if ($objVal != "") {
                        $objVal .= " ";
                    }
                    $objVal .= $obj->$v;
                }
                $ret[$obj->$key] = $objVal;
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
        $adminEmpId = SessionUtils::getSessionObject('admin_current_profile');
        $user = SessionUtils::getSessionObject('user');
        if (empty($adminEmpId) && !empty($user)) {
            $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
            return $user->$signInMappingField;
        }
        return $adminEmpId;
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
        } elseif ($this->currentUser->user_level == 'Manager') {
            $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
            $signInMappingFieldTable = ucfirst($signInMappingField);
            $subordinate = new $signInMappingFieldTable();
            $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
            $subordinates = $subordinate->Find("supervisor = ?", array($this->currentUser->$signInMappingField));
            $subFound = false;
            foreach ($subordinates as $sub) {
                if ($sub->id == $profileId) {
                    $subFound = true;
                    break;
                }
            }

            if (!$subFound) {
                return;
            }

            SessionUtils::saveSessionObject('admin_current_profile', $profileId);
        }
    }

    public function cleanUpAdoDB($obj)
    {
        unset($obj->_table);
        unset($obj->_dbat);
        unset($obj->_tableat);
        unset($obj->_where);
        unset($obj->_saved);
        unset($obj->_lasterr);
        unset($obj->_original);
        unset($obj->foreignName);

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

    public function checkSecureAccessOld($type, $object)
    {

        $accessMatrix = array();
        if ($this->currentUser->user_level == 'Admin') {
            $accessMatrix = $object->getAdminAccess();
            if (in_array($type, $accessMatrix)) {
                return true;
            }
        } elseif ($this->currentUser->user_level == 'Manager') {
            $accessMatrix = $object->getManagerAccess();
            if (in_array($type, $accessMatrix)) {
                return true;
            } else {
                $accessMatrix = $object->getUserOnlyMeAccess();
                $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
                if (in_array($type, $accessMatrix) && $_REQUEST[$object->getUserOnlyMeAccessField()]
                    == $this->currentUser->$signInMappingField) {
                    return true;
                }

                if (in_array($type, $accessMatrix)) {
                    $field = $object->getUserOnlyMeAccessField();
                    $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
                    if ($this->currentUser->$signInMappingField."" == $object->$field) {
                        return true;
                    }
                }
            }
        } else {
            $accessMatrix = $object->getUserAccess();
            if (in_array($type, $accessMatrix)) {
                return true;
            } else {
                $accessMatrix = $object->getUserOnlyMeAccess();
                $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
                if (in_array($type, $accessMatrix) && $_REQUEST[$object->getUserOnlyMeAccessField()]
                    == $this->currentUser->$signInMappingField) {
                    return true;
                }

                if (in_array($type, $accessMatrix)) {
                    $field = $object->getUserOnlyMeAccessField();
                    $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
                    if ($this->currentUser->$signInMappingField."" == $object->$field) {
                        return true;
                    }
                }
            }
        }

        $ret['status'] = "ERROR";
        $ret['message'] = "Access violation";
        echo json_encode($ret);
        exit();
    }

    /**
     * Use user level security functions defined in model classes to check whether a given action
     * type is allowed to be executed by the current user on a given object
     * @method checkSecureAccess
     * @param $type {String} Action type
     * @param $object {Object} object to test access
     * @return {Boolen} true or exit
     */

    public function checkSecureAccess($type, $object)
    {

        if (!empty($this->currentUser->user_roles)) {
            return true;
        }

        $accessMatrix = array();

        //Construct permission method
        $permMethod = "get".$this->currentUser->user_level."Access";
        if (method_exists($object, $permMethod)) {
            $accessMatrix = $object->$permMethod();
        } else {
            $accessMatrix = $object->getDefaultAccessLevel();
        }

        if (in_array($type, $accessMatrix)) {
            //The user has required permission, so return true
            return true;
        } else {
            //Now we need to check whther the user has access to his own records
            $accessMatrix = $object->getUserOnlyMeAccess();

            $userOnlyMeAccessRequestField = $object->getUserOnlyMeAccessRequestField();

            //This will check whether user can access his own records using a value in request
            if (isset($_REQUEST[$object->getUserOnlyMeAccessField()])
                && isset($this->currentUser->$userOnlyMeAccessRequestField)) {
                if (in_array($type, $accessMatrix) && $_REQUEST[$object->getUserOnlyMeAccessField()]
                    == $this->currentUser->$userOnlyMeAccessRequestField) {
                    return true;
                }
            }

            //This will check whether user can access his own records using a value in requested object
            if (in_array($type, $accessMatrix)) {
                $field = $object->getUserOnlyMeAccessField();
                if ($this->currentUser->$userOnlyMeAccessRequestField == $object->$field) {
                    return true;
                }
            }
        }

        $ret['status'] = "ERROR";
        $ret['message'] = "Access violation";
        echo json_encode($ret);
        exit();
    }

    public function getInstanceId()
    {
        $settings = new Setting();
        $settings->Load("name = ?", array("Instance : ID"));

        if ($settings->name != "Instance : ID" || empty($settings->value)) {
            $settings->value = md5(time());
            $settings->name = "Instance : ID";
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

        $data = AesCtr::decrypt($key, $instanceId, 256);
        $arr = explode("|", $data);
        if ($arr[0] == KEY_PREFIX && $arr[1] == $instanceId) {
            return true;
        }

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
            $json = str_replace("|", '"', $json);
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
        return $this->moduleManagers[$type."_".$name];
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

    public function executeCalculationHook($parameters, $code = null)
    {
        $ch = BaseService::getInstance()->getCalculationHook($code);

        if (empty($ch->code)) {
            return null;
        }
        $class = $ch->class;
        return call_user_func_array(array(new $class(), $ch->method), $parameters);
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
        if ($this->modelClassMap[$class]) {
            return $this->modelClassMap[$class];
        }
        return '\\Model\\'.$class;
    }
}
