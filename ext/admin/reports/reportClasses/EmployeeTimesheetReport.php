<?php
if(!class_exists('ReportBuilder')){
	include_once MODULE_PATH.'/reportClasses/ReportBuilder.php';
}
class EmployeeTimesheetReport extends ReportBuilder{
	
	public function getMainQuery(){
		$query = "SELECT 
(SELECT concat(`first_name`,' ',`middle_name`,' ', `last_name`) from Employees where id = te.employee) as 'Employee',
(SELECT name from Projects where id = te.project) as 'Project',
details as 'Details',
date_start as 'Start Time',
date_end as 'End Time',
SEC_TO_TIME(TIMESTAMPDIFF(SECOND,te.date_start,te.date_end)) as 'Duration'
FROM EmployeeTimeEntry te";	
		
		return $query;

	}
	
	public function getWhereQuery($request){
		
		$employeeList = array();
		if(!empty($request['employee'])){
			$employeeList = json_decode($request['employee'],true);
		}
		
		if(in_array("NULL", $employeeList) ){
			$employeeList = array();
		}
		
		
		if(!empty($employeeList) && ($request['project'] != "NULL" && !empty($request['project']))){
			$query = "where employee in (".implode(",", $employeeList).") and date_start >= ? and date_end <= ? and project = ?;";
			$params = array(	
					$request['date_start'],	
					$request['date_end'],	
					$request['project']
			);
		}else if(!empty($employeeList)){
			$query = "where employee in (".implode(",", $employeeList).") and date_start >= ? and date_end <= ?;";
			$params = array(
					$request['date_start'],
					$request['date_end']
			);
		}else if(($request['project'] != "NULL" && !empty($request['project']))){
			$query = "where project = ? and date_start >= ? and date_end <= ?;";
			$params = array(
					$request['project'],
					$request['date_start'],
					$request['date_end']
			);
		}else{
			$query = "where date_start >= ? and date_end <= ?;";
			$params = array(
					$request['date_start'],
					$request['date_end']
			);
		}
		
		LogManager::getInstance()->info("Query:".$query);
		LogManager::getInstance()->info("Params:".json_encode($params));
		
		return array($query, $params);
	}
}