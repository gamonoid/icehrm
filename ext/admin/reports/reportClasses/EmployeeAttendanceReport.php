<?php
if(!class_exists('ReportBuilder')){
	include_once MODULE_PATH.'/reportClasses/ReportBuilder.php';
}
class EmployeeAttendanceReport extends ReportBuilder{
	
	public function getMainQuery(){
		$query = "SELECT 
(SELECT `employee_id` from Employees where id = at.employee) as 'Employee',
(SELECT concat(`first_name`,' ',`middle_name`,' ', `last_name`) from Employees where id = at.employee) as 'Employee',
in_time as 'Time In',
out_time as 'Time Out',
note as 'Note'
FROM Attendance at";	
		
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
		
		if(!empty($employeeList)){
			$query = "where employee in (".implode(",", $employeeList).") and in_time >= ? and out_time <= ? order by in_time desc;";
			$params = array(	
					$request['date_start']." 00:00:00",	
					$request['date_end']." 23:59:59",	
			);
		}else{
			$query = "where in_time >= ? and out_time <= ? order by in_time desc;";
			$params = array(
					$request['date_start']." 00:00:00",	
					$request['date_end']." 23:59:59",	
			);
		}
		
		LogManager::getInstance()->info("Query:".$query);
		LogManager::getInstance()->info("Params:".json_encode($params));
		
		return array($query, $params);
	}
}