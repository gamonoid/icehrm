<?php
define('CLIENT_PATH',dirname(__FILE__));
include ("config.base.php");
include ("include.common.php");
$modulePath = SessionUtils::getSessionObject("modulePath");
if(!defined('MODULE_PATH')){
	define('MODULE_PATH',$modulePath);
}
include("server.includes.inc.php");
if(empty($user)){
	$ret['status'] = "ERROR";
	echo json_encode($ret);
	exit();
}

$_REQUEST['sm'] = BaseService::getInstance()->fixJSON($_REQUEST['sm']);
$_REQUEST['cl'] = BaseService::getInstance()->fixJSON($_REQUEST['cl']);
$_REQUEST['ft'] = BaseService::getInstance()->fixJSON($_REQUEST['ft']);


$columns = json_decode($_REQUEST['cl'],true);
$columns[]="id";
$table = $_REQUEST['t'];
$obj = new $table();


$sLimit = "";
if ( isset( $_REQUEST['iDisplayStart'] ) && $_REQUEST['iDisplayLength'] != '-1' ){
	$sLimit = " LIMIT ".intval( $_REQUEST['iDisplayStart'] ).", ".intval( $_REQUEST['iDisplayLength'] );
}

$isSubOrdinates = false;
if(isset($_REQUEST['type']) && $_REQUEST['type']="sub"){
	$isSubOrdinates = true;
}

$skipProfileRestriction = false;
if(isset($_REQUEST['skip']) && $_REQUEST['type']="1"){
	$skipProfileRestriction = true;
}

$sortData = BaseService::getInstance()->getSortingData($_REQUEST);
$data = BaseService::getInstance()->getData($_REQUEST['t'],$_REQUEST['sm'],$_REQUEST['ft'],$_REQUEST['ob'],$sLimit, $_REQUEST['cl'], $_REQUEST['sSearch'],$isSubOrdinates,$skipProfileRestriction,$sortData);

//Get Total row count
$totalRows = 0;

$countFilterQuery = "";
$countFilterQueryData = array();
if(!empty($_REQUEST['ft'])){
	$filter = json_decode($_REQUEST['ft']);
	if(!empty($filter)){
		LogManager::getInstance()->debug("Filter:".print_r($filter,true));
		if(method_exists($obj,'getCustomFilterQuery')){
			$response = $obj->getCustomFilterQuery($filter);
			$countFilterQuery = $response[0];
			$countFilterQueryData = $response[1];
		}else{
			
			$defaultFilterResp = BaseService::getInstance()->buildDefaultFilterQuery($filter);
			$countFilterQuery = $defaultFilterResp[0];
			$countFilterQueryData = $defaultFilterResp[1];
		}
	}
}

LogManager::getInstance()->debug("Row Count Filter Query:".$countFilterQuery);
LogManager::getInstance()->debug("Row Count Filter Query Data:".json_encode($countFilterQueryData));


if(in_array($table, BaseService::getInstance()->userTables) && !$skipProfileRestriction && !$isSubOrdinates){
	$cemp = BaseService::getInstance()->getCurrentProfileId();
	$sql = "Select count(id) as count from ".$obj->_table." where ".SIGN_IN_ELEMENT_MAPPING_FIELD_NAME." = ? ".$countFilterQuery;
	array_unshift($countFilterQueryData,$cemp);
	LogManager::getInstance()->debug("Count Filter Query 1:".$sql);
	LogManager::getInstance()->debug("Count Filter Query Data 1:".json_encode($countFilterQueryData));
	
	$rowCount = $obj->DB()->Execute($sql, $countFilterQueryData);
			
}else{
	if($isSubOrdinates){
		$cemp = BaseService::getInstance()->getCurrentProfileId();
		$profileClass = ucfirst(SIGN_IN_ELEMENT_MAPPING_FIELD_NAME);
		$subordinate = new $profileClass();
		$subordinates = $subordinate->Find("supervisor = ?",array($cemp));
		$subordinatesIds = "";
		foreach($subordinates as $sub){
			if($subordinatesIds != ""){
				$subordinatesIds.=",";
			}
			$subordinatesIds.=$sub->id;
		}
		$subordinatesIds.="";
		$sql = "Select count(id) as count from ".$obj->_table." where ".SIGN_IN_ELEMENT_MAPPING_FIELD_NAME." in (".$subordinatesIds.") ".$countFilterQuery;
		LogManager::getInstance()->debug("Count Filter Query 2:".$sql);
		LogManager::getInstance()->debug("Count Filter Query Data 2:".json_encode($countFilterQueryData));
		$rowCount = $obj->DB()->Execute($sql,$countFilterQueryData);
	}else{
		$sql = "Select count(id) as count from ".$obj->_table;
		if(!empty($countFilterQuery)){
			$sql.=" where 1=1 ".$countFilterQuery;
		}
		LogManager::getInstance()->debug("Count Filter Query 3:".$sql);
		LogManager::getInstance()->debug("Count Filter Query Data 3:".json_encode($countFilterQueryData));
		$rowCount = $obj->DB()->Execute($sql,$countFilterQueryData);
	}
	
}

if(!empty($rowCount)){
	foreach ($rowCount as $cnt) {
		$totalRows = $cnt['count'];
	}	
}else{
	$totalRows = 0;
}
	

/*
 * Output
 */

$output = array(
	"sEcho" => intval($_REQUEST['sEcho']),
	"iTotalRecords" => $totalRows,
	"iTotalDisplayRecords" => $totalRows,
	"aaData" => array()
);

/*
$output['debug_data'] = print_r($data,true);
$output['debug_col'] = print_r($columns,true);
$output['debug_col_plain'] = $_REQUEST['cl'];
$output['get_magic_quotes_gpc'] = get_magic_quotes_gpc();
*/

foreach($data as $item){
	$row = array();
	$colCount = count($columns);
	for ($i=0 ; $i<$colCount;$i++){
		$row[] = $item->$columns[$i];
	}
	$row["_org"] = BaseService::getInstance()->cleanUpAdoDB($item);
	$output['aaData'][] = $row;
}
echo json_encode($output);
