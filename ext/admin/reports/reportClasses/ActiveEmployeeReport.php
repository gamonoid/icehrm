<?php
if(!class_exists('ReportBuilder')){
	include_once MODULE_PATH.'/reportClasses/ReportBuilder.php';
}
class ActiveEmployeeReport extends ReportBuilder{
	
	public function getMainQuery(){
		$query = "Select id, employee_id as 'Employee ID',
concat(`first_name`,' ',`middle_name`,' ', `last_name`) as 'Name',
(SELECT name from Nationality where id = nationality) as 'Nationality',
birthday as 'Birthday',
gender as 'Gender',
marital_status as 'Marital Status',
ssn_num as 'SSN Number',
nic_num as 'NIC Number',
other_id as 'Other IDs',
driving_license as 'Driving License Number',
(SELECT name from EmploymentStatus where id = employment_status) as 'Employment Status',
(SELECT name from JobTitles where id = job_title) as 'Job Title',
(SELECT name from PayGrades where id = pay_grade) as 'Pay Grade',
work_station_id as 'Work Station ID',
address1 as 'Address 1',
address2 as 'Address 2',
city as 'City',
(SELECT name from Country where code = country) as 'Country',
(SELECT name from Province where id = province) as 'Province',
postal_code as 'Postal Code',
home_phone as 'Home Phone',
mobile_phone as 'Mobile Phone',
work_phone as 'Work Phone',
work_email as 'Work Email',
private_email as 'Private Email',
joined_date as 'Joined Date',
confirmation_date as 'Confirmation Date',
(SELECT title from CompanyStructures where id = department) as 'Department',
(SELECT concat(`first_name`,' ',`middle_name`,' ', `last_name`,' [Employee ID:',`employee_id`,']') from Employees e1 where e1.id = e.supervisor) as 'Supervisor', notes as 'Notes'
FROM Employees e";	
		 
		return $query;

	}
	
	public function getWhereQuery($request){
		$query = "";
		$params = array();
		
		if(empty($request['department']) || $request['department'] == "NULL"){
			$params = array();
			$query = "where ((termination_date = '0001-01-01 00:00:00' or termination_date = '0000-00-00 00:00:00') and joined_date < NOW()) or (termination_date > NOW() and joined_date < NOW())";
		}else{
			$depts = $this->getChildCompanyStuctures($request['department']);
			$query = "where department in (".implode(",",$depts).") and (((termination_date = '0001-01-01 00:00:00' or termination_date = '0000-00-00 00:00:00') and joined_date < NOW()) or (termination_date > NOW() and joined_date < NOW()))";
		}
		
		
		return array($query, $params);
	}
	
	public function getChildCompanyStuctures($companyStructId){
		$childIds = array();
		$childIds[] = $companyStructId;
		$nodeIdsAtLastLevel = $childIds;
		$count = 0;
		do{
			$count++;
			$companyStructTemp = new CompanyStructure();
			if(empty($nodeIdsAtLastLevel) || empty($childIds)){
				break;	
			}
			$idQuery = "parent in (".implode(",",$nodeIdsAtLastLevel).") and id not in(".implode(",",$childIds).")";
			LogManager::getInstance()->debug($idQuery);
			$list = $companyStructTemp->Find($idQuery, array());
			if(!$list){
				LogManager::getInstance()->debug($companyStructTemp->ErrorMsg());	
			}
			$nodeIdsAtLastLevel = array();
			foreach($list as $item){
				$childIds[] = $item->id;
				$nodeIdsAtLastLevel[] = $item->id;
			}
		}while(count($list) > 0 && $count < 10);
		
		return $childIds;
	}
	
	
	
}