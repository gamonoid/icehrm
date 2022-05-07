<?php
define('CLIENT_PATH', dirname(__FILE__));
include("config.base.php");
include("include.common.php");
$modulePath = \Utils\SessionUtils::getSessionObject("modulePath");
if (!defined('MODULE_PATH')) {
    define('MODULE_PATH', $modulePath);
}
include("server.includes.inc.php");
if (empty($user)) {
    $ret['status'] = "ERROR";
    echo json_encode($ret);
    exit();
}

$_REQUEST['sm'] = \Classes\BaseService::getInstance()->fixJSON($_REQUEST['sm']);
$_REQUEST['cl'] = \Classes\BaseService::getInstance()->fixJSON($_REQUEST['cl']);
$_REQUEST['ft'] = \Classes\BaseService::getInstance()->fixJSON($_REQUEST['ft']);

// Domain aware input cleanup
$cleaner = new \Classes\DomainAwareInputCleaner();
$_REQUEST['t'] = $cleaner->cleanTableColumn($_REQUEST['t']);
$_REQUEST['ft'] = $cleaner->cleanFilters($_REQUEST['ft']);
$_REQUEST['ob'] = $cleaner->cleanOrderBy($_REQUEST['ob']);
$_REQUEST['sSearch'] = $cleaner->cleanSearch($_REQUEST['sSearch']);
$_REQUEST['cl'] = $cleaner->cleanColumns($_REQUEST['cl']);

$columns = json_decode($_REQUEST['cl'], true);
$columns[] = "id";
$table = $_REQUEST['t'];
$nsTable = \Classes\BaseService::getInstance()->getFullQualifiedModelClassName($table);
$obj = new $nsTable();

$sLimit = "";
if (!isset($_REQUEST['objects'])) {
    if (isset($_REQUEST['iDisplayStart']) && $_REQUEST['iDisplayLength'] != '-1') {
        $sLimit = " LIMIT " . intval($_REQUEST['iDisplayStart']) . ", " . intval($_REQUEST['iDisplayLength']);
    }
} else {
    if (isset($_REQUEST['iDisplayStart']) && $_REQUEST['iDisplayLength'] != '-1') {
        $sLimit = " LIMIT " . intval($_REQUEST['iDisplayStart']) . ", " . (intval($_REQUEST['iDisplayLength'])+1);
    }
}

$isSubOrdinates = false;
if (isset($_REQUEST['type']) && $_REQUEST['type'] = "sub") {
    $isSubOrdinates = true;
}

$skipProfileRestriction = false;
if (isset($_REQUEST['skip']) && $_REQUEST['type'] = "1") {
    $skipProfileRestriction = true;
}

$sortData = \Classes\BaseService::getInstance()->getSortingData($_REQUEST);
$data = \Classes\BaseService::getInstance()->getData(
    $_REQUEST['t'],
    $_REQUEST['sm'],
    $_REQUEST['ft'],
    $_REQUEST['ob'],
    $sLimit,
    $_REQUEST['cl'],
    $_REQUEST['sSearch'],
    $isSubOrdinates,
    $skipProfileRestriction,
    $sortData
);

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
                $defaultFilterResp = \Classes\BaseService::getInstance()->buildDefaultFilterQuery($filter);
                $countFilterQuery = $defaultFilterResp[0];
                $countFilterQueryData = $defaultFilterResp[1];
            }
        }
    }

    $searchTerm = $_REQUEST['sSearch'];
    $searchColumns = $_REQUEST['cl'];
    $searchQuery = '';
    $searchQueryData = [];
    $totalRows = 0;
    if (!empty($searchTerm) && !empty($searchColumns)) {
        $searchColumnList = json_decode($searchColumns);
        $searchColumnList = array_diff($searchColumnList, $obj->getVirtualFields());
        if (!empty($searchColumnList)) {
            $searchQuery = " and (";
            foreach ($searchColumnList as $col) {
                if ($searchQuery != " and (") {
                    $searchQuery.=" or ";
                }
                $searchQuery.=$col." like ?";
                $searchQueryData[] = "%".$searchTerm."%";
            }
            $searchQuery.=")";
        }
    }


    if (in_array($table, \Classes\BaseService::getInstance()->userTables)
        && !$skipProfileRestriction && !$isSubOrdinates) {
        //Get data for user table.
        // E.g: an employee loading attendnace data
        $cemp = \Classes\BaseService::getInstance()->getCurrentProfileId();
        $sql = "Select count(id) as count from "
            . $obj->table . " where " . SIGN_IN_ELEMENT_MAPPING_FIELD_NAME . " = ? " . $countFilterQuery;
        array_unshift($countFilterQueryData, $cemp);

        $totalRows = $obj->countRows($sql, $countFilterQueryData);
    } else {
        // Not a user table, means an admin or a manager loading employee data
        if ($isSubOrdinates) {
            // Loading subordinates
            $cemp = \Classes\BaseService::getInstance()->getCurrentProfileId();
            $profileClass = \Classes\BaseService::getInstance()->getFullQualifiedModelClassName(
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
	            . $countFilterQuery.$searchQuery;
            $totalRows = $obj->countRows($sql, array_merge($countFilterQueryData, $searchQueryData));
        } else {
            // An admin loading all user data
            $sql = "Select count(id) as count from " . $obj->table;
            $sql .= " where 1=1 " . $countFilterQuery.$searchQuery;
            $totalRows = $obj->countRows($sql, array_merge($countFilterQueryData, $searchQueryData));
        }
    }
}


/*
 * Output
 */
if (isset($_REQUEST['version']) && $_REQUEST['version'] === 'v2') {
    $output = [
        "totalRecords" => $totalRows,
        "totalDisplayRecords" => $totalRows,
        "objects" => []
    ];

    foreach ($data as $item) {
        $row = new stdClass();
        $colCount = count($columns);
        for ($i = 0; $i < $colCount; $i++) {
            $row->{$columns[$i]} = $item->{$columns[$i]};
        }
        $output['objects'][] = $row;
    }

    try {
        echo \Classes\BaseService::getInstance()->safeJsonEncode($output);
    } catch (Exception $e) {
        \Utils\LogManager::getInstance()->error($e->getMessage());
        \Utils\LogManager::getInstance()->notifyException($e);
        echo json_encode(['status' => 'Error']);
    }
}else if (!isset($_REQUEST['objects'])) {
    $output = array(
        "sEcho" => intval($_REQUEST['sEcho']),
        "iTotalRecords" => $totalRows,
        "iTotalDisplayRecords" => $totalRows,
        "aaData" => array()
    );

    foreach ($data as $item) {
        $row = array();
        $colCount = count($columns);
        for ($i = 0; $i < $colCount; $i++) {
            $row[] = $item->{$columns[$i]};
        }
        $row["_org"] = \Classes\BaseService::getInstance()->cleanUpAdoDB($item);
        $output['aaData'][] = $row;
    }

    try {
        echo \Classes\BaseService::getInstance()->safeJsonEncode($output);
    } catch (Exception $e) {
        \Utils\LogManager::getInstance()->error($e->getMessage());
        \Utils\LogManager::getInstance()->notifyException($e);
        echo json_encode(['status' => 'Error']);
    }
} else {
    $output = array();
    foreach ($data as $item) {
        unset($item->keysToIgnore);
        $output[] = \Classes\BaseService::getInstance()->cleanUpAdoDB($item);
    }
    try {
        echo \Classes\BaseService::getInstance()->safeJsonEncode($output);
    } catch (Exception $e) {
        \Utils\LogManager::getInstance()->error($e->getMessage());
        echo json_encode(['status' => 'Error']);
    }
}
