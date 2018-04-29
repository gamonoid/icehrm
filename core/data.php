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

    \Utils\LogManager::getInstance()->debug("Row Count Filter Query:" . $countFilterQuery);
    \Utils\LogManager::getInstance()->debug("Row Count Filter Query Data:" . json_encode($countFilterQueryData));

    if (in_array($table, \Classes\BaseService::getInstance()->userTables)
        && !$skipProfileRestriction && !$isSubOrdinates) {
        $cemp = \Classes\BaseService::getInstance()->getCurrentProfileId();
        $sql = "Select count(id) as count from "
            . $obj->_table . " where " . SIGN_IN_ELEMENT_MAPPING_FIELD_NAME . " = ? " . $countFilterQuery;
        array_unshift($countFilterQueryData, $cemp);
        \Utils\LogManager::getInstance()->debug("Count Filter Query 1:" . $sql);
        \Utils\LogManager::getInstance()->debug("Count Filter Query Data 1:" . json_encode($countFilterQueryData));

        $rowCount = $obj->DB()->Execute($sql, $countFilterQueryData);
    } else {
        if ($isSubOrdinates) {
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
            $sql = "Select count(id) as count from " . $obj->_table .
	            " where " . $obj->getUserOnlyMeAccessField() . " in (" . $subordinatesIds . ") "
	            . $countFilterQuery;
            \Utils\LogManager::getInstance()->debug("Count Filter Query 2:" . $sql);
            \Utils\LogManager::getInstance()->debug(
                "Count Filter Query Data 2:" . json_encode($countFilterQueryData)
            );
            $rowCount = $obj->DB()->Execute($sql, $countFilterQueryData);
        } else {
            $sql = "Select count(id) as count from " . $obj->_table;
            if (!empty($countFilterQuery)) {
                $sql .= " where 1=1 " . $countFilterQuery;
            }
            \Utils\LogManager::getInstance()->debug("Count Filter Query 3:" . $sql);
            \Utils\LogManager::getInstance()->debug(
                "Count Filter Query Data 3:" . json_encode($countFilterQueryData)
            );
            $rowCount = $obj->DB()->Execute($sql, $countFilterQueryData);
        }
    }
}

if (isset($rowCount) && !empty($rowCount)) {
    foreach ($rowCount as $cnt) {
        $totalRows = $cnt['count'];
    }
} else {
    $totalRows = 0;
}

/*
 * Output
 */

if (!isset($_REQUEST['objects'])) {
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
    echo json_encode($output);
} else {
    $output = array();
    foreach ($data as $item) {
        unset($item->keysToIgnore);
        $output[] = \Classes\BaseService::getInstance()->cleanUpAdoDB($item);
    }
    echo json_encode($output);
}
