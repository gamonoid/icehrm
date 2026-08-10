<?php

use Employees\Common\Model\EmployeeAccess;

define('CLIENT_PATH', dirname(__FILE__));
include("config.base.php");
include("include.common.php");
$modulePath = \Utils\SessionUtils::getSessionObject("modulePath");
// New SPA UI: a data request can declare the module it belongs to explicitly
// (mg/mn), so data scope (admin all-rows vs user own-rows) is derived per-request
// instead of from the shared session modulePath. See docs/DATA_SCOPE_ISSUE.md.
// Authorization is validated AFTER server.includes (below) before any data is
// returned. Legacy requests omit mg/mn and keep using the session value.
$reqModGroup = isset($_REQUEST['mg']) ? $_REQUEST['mg'] : null;
$reqModName = isset($_REQUEST['mn']) ? $_REQUEST['mn'] : null;
if (!empty($reqModGroup) && !empty($reqModName)) {
    $resolvedModulePath = \Classes\ModuleScopeResolver::pathFor($reqModGroup, $reqModName);
    if ($resolvedModulePath !== null) {
        $modulePath = $resolvedModulePath;
    }
}
if (!defined('MODULE_PATH')) {
    define('MODULE_PATH', $modulePath);
}
include("server.includes.inc.php");
if (empty($user)) {
    $ret['status'] = "ERROR";
    $ret['code'] = "NO_USER_FOUND";
    echo json_encode($ret);
    exit();
}

// Forced password reset (legacy MD5 account): serve no data at all until the password
// has been upgraded. Mirrors the gate in service.php; the reset itself never comes here.
if (\Classes\PasswordManager::userNeedsPasswordReset($user)) {
    http_response_code(403);
    $ret['status'] = "ERROR";
    $ret['code'] = "PASSWORD_RESET_REQUIRED";
    echo json_encode($ret);
    exit();
}

// Reject a forged/unauthorized explicit module before returning any data — a
// client must not gain admin scope by naming a module it can't access.
if (!empty($reqModGroup) && !empty($reqModName)
    && !\Classes\ModuleScopeResolver::isAuthorized($reqModGroup, $reqModName, $user)
) {
    http_response_code(403);
    $ret['status'] = "ERROR";
    $ret['code'] = "MODULE_ACCESS_DENIED";
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
if (isset($_REQUEST['sSearch'])) {
    $_REQUEST['sSearch'] = $cleaner->cleanSearch($_REQUEST['sSearch']);
}
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

// Whether this list is scoped to the caller's direct reports. The request may ASK
// for that scope, but the model decides: resolveSubordinateListScope() narrows the
// request to what allowsSubordinateList() permits, so a client cannot flip an
// arbitrary employee-owned list from "my rows" to "my reports' rows". Resolved here,
// at the entry point, so the row query and the row COUNT below agree — narrowing
// only inside getData() would have left this file counting subordinate rows for a
// list that returned own rows.
$isSubOrdinates = \Classes\BaseService::getInstance()->resolveSubordinateListScope(
    isset($_REQUEST['type']) && $_REQUEST['type'] === "sub",
    $obj,
    $table
);

// Whether the own-records restriction on user tables is lifted. This is decided
// by the server alone and is NEVER taken from the request.
//
// A `skip` request parameter used to set this. Its guard was written with an
// assignment instead of a comparison — `$_REQUEST['type'] = "1"` — which is
// always truthy, so the condition collapsed to "is skip present". Any
// authenticated user could append skip=1 and read every employee's rows in any
// user table (leaves, expenses, timesheets, salaries, documents, payslips...).
// The parameter was never sent by the application either: nothing overrides
// AdapterBase::remoteTableSkipProfileRestriction(), which returns false. So the
// handling is removed outright rather than repaired.
//
// If an all-employee view is ever needed, gate it on a privilege check here
// (e.g. EmployeeAccess::hasAccessToAllEmployeeData()) — never on a client-supplied
// parameter.
$skipProfileRestriction = false;

$sortData = \Classes\BaseService::getInstance()->getSortingData($_REQUEST);
$data = \Classes\BaseService::getInstance()->getData(
    $_REQUEST['t'],
    $_REQUEST['sm'],
    $_REQUEST['ft'],
    $_REQUEST['ob'],
    $sLimit,
    $_REQUEST['cl'],
    isset($_REQUEST['sSearch']) ? $_REQUEST['sSearch'] : '',
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

    $searchTerm = isset($_REQUEST['sSearch']) ? $_REQUEST['sSearch'] : '';
    $searchColumns = $_REQUEST['cl'];
    $searchQuery = '';
    $searchQueryData = [];
    $totalRows = 0;
    if (!empty($searchTerm) && !empty($searchColumns)) {
        $searchColumnList = json_decode($searchColumns);
        // Search only real table columns (same rule as BaseService::getData):
        // the client's column list may contain computed fields (image,
        // document_link, total_time, …) that would break the SQL.
        $searchColumnList = array_intersect($searchColumnList, $obj->getColumns());
        $searchColumnList = array_diff($searchColumnList, $obj->getVirtualFields());

        $searchConditions = array();
        foreach ($searchColumnList as $col) {
            $searchConditions[] = $col . " like ?";
            $searchQueryData[] = "%".$searchTerm."%";
        }

        // Mirror BaseService::getData: when the list resolves an `employee`
        // column to a name (source mapping), searching by employee name must
        // also be counted — otherwise the total undercounts a name search and
        // pagination collapses to one page. A subquery keeps getTotalCount
        // (which has no JOIN) valid.
        $sourceMap = !empty($_REQUEST['sm']) ? json_decode($_REQUEST['sm']) : null;
        if (!empty($sourceMap) && !empty($sourceMap->employee) && $table !== 'Employee') {
            $searchConditions[] = "employee in (select id from Employees where first_name like ? or last_name like ?)";
            $searchQueryData[] = "%".$searchTerm."%";
            $searchQueryData[] = "%".$searchTerm."%";
        }

        if (!empty($searchConditions)) {
            $searchQuery = " and (" . implode(" or ", $searchConditions) . ")";
        }
    }


    if (in_array($table, \Classes\BaseService::getInstance()->userTables)
        && !$skipProfileRestriction && !$isSubOrdinates) {
        //Get data for user table.
        // E.g: an employee loading attendnace data
        $cemp = \Classes\BaseService::getInstance()->getCurrentProfileId();
        $countQuery = ' AND '.SIGN_IN_ELEMENT_MAPPING_FIELD_NAME . " = ? " . $countFilterQuery.$searchQuery;
        array_unshift($countFilterQueryData, $cemp);
        $queryParams = array_merge($countFilterQueryData, $searchQueryData);
        $totalRows = $obj->getTotalCount($countQuery, $queryParams);
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
            if (empty($subordinatesIds)) {
                $subordinatesIds = '0';
            }
            $countQuery = ' AND '.$obj->getUserOnlyMeAccessField() . " in (" . $subordinatesIds . ") " . $countFilterQuery.$searchQuery;
            $totalRows = $obj->getTotalCount($countQuery, array_merge($countFilterQueryData, $searchQueryData));
        } else {
            // Mirror the row query (BaseService::getData): a Manager's unscoped list is
            // restricted to their own team, so the paging total must be too — otherwise
            // iTotalRecords still discloses the company-wide row count.
            list($mgrClause, $mgrData) = \Classes\BaseService::getInstance()
                ->getManagerListScopeClause($obj);
            $totalRows = $obj->getTotalCount(
                $mgrClause.$countFilterQuery.$searchQuery,
                array_merge($mgrData, $countFilterQueryData, $searchQueryData)
            );
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
} elseif (!isset($_REQUEST['objects'])) {
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
