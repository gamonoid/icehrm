<?php
if(!interface_exists('ReportBuilderInterface')){
	include_once MODULE_PATH.'/reportClasses/ReportBuilderInterface.php';
}
class EmployeeTimeTrackReport implements ReportBuilderInterface{
	public function getData($report,$req){
		
		LogManager::getInstance()->info(json_encode($report));
		LogManager::getInstance()->info(json_encode($req));
		
		$employeeTimeEntry = new EmployeeTimeEntry();
		
		$timeEntryList = $employeeTimeEntry->Find("employee = ? and date(date_start) >= ? and  date(date_end) <= ?",array($req['employee'], $req['date_start'], $req['date_end']));

			
		$seconds = 0;
		$graphTimeArray = array();
		foreach($timeEntryList as $entry){
			$seconds = (strtotime($entry->date_end) - strtotime($entry->date_start));
			$key = date("Y-m-d",strtotime($entry->date_end));
			if(isset($graphTimeArray[$key])){
				$graphTimeArray[$key] += $seconds;
			}else{
				$graphTimeArray[$key] = $seconds;
			}
		}
			
		//$minutes = (int)($seconds/60);
			
			
		//Find Attendance Entries
			
		$attendance = new Attendance();
		$atteandanceList = 	$attendance->Find("employee = ? and date(in_time) >= ? and  date(out_time) <= ? and in_time < out_time",array($req['employee'], $req['date_start'], $req['date_end']));
			
		$seconds = 0;
		$graphAttendanceArray = array();
		$firstTimeInArray = array();
		$lastTimeOutArray = array();
		foreach($atteandanceList as $entry){
			$seconds = (strtotime($entry->out_time) - strtotime($entry->in_time));
			$key = date("Y-m-d",strtotime($entry->in_time));
			if(isset($graphAttendanceArray[$key])){
				$graphAttendanceArray[$key] += $seconds;
				$lastTimeOutArray[$key] = $entry->out_time;
			}else{
				$graphAttendanceArray[$key] = $seconds;
				$firstTimeInArray[$key] = $entry->in_time;
				$lastTimeOutArray[$key] = $entry->out_time;
			}
		}
			

		/////////////////////////////////////////
		
		$employeeObject = new Employee();
		$employeeObject->Load("id = ?",array($req['employee']));
		
		
		$reportData = array();
		//$reportData[] = array($employeeObject->first_name." ".$employeeObject->last_name,"","","","");
		$reportData[] = array("Date","First Punch-In Time","Last Punch-Out Time","Time in Office","Time in Timesheets");
		
			
		//Iterate date range
			
		$interval = DateInterval::createFromDateString('1 day');
		$period = new DatePeriod(new DateTime($req['date_start']), $interval, new DateTime($req['date_end']));
			
		foreach ( $period as $dt ){
			$dataRow = array();
			$key = $dt->format("Y-m-d");
			
			$dataRow[] = $key;
			
			if(isset($firstTimeInArray[$key])){
				$dataRow[] = $firstTimeInArray[$key];
			}else{
				$dataRow[] = "Not Found";
			}
			
			if(isset($lastTimeOutArray[$key])){
				$dataRow[] = $lastTimeOutArray[$key];
			}else{
				$dataRow[] = "Not Found";
			}
			
			if(isset($graphAttendanceArray[$key])){
				$dataRow[] = round(($graphAttendanceArray[$key]/3600),2);
			}else{
				$dataRow[] = 0;
			}
		
			if(isset($graphTimeArray[$key])){
				$dataRow[] = round(($graphTimeArray[$key]/3600),2);
			}else{
				$dataRow[] = 0;
			}
			
			$reportData[] = $dataRow;
		}
		return $reportData;
	}
}