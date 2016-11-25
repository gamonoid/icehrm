<?php
if (!class_exists('EmployeesAdminManager')) {
	class EmployeesAdminManager extends AbstractModuleManager{

		public function initializeUserClasses(){
				
		}

		public function initializeFieldMappings(){
			
		}

        public function setupRestEndPoints(){
            \NoahBuscher\Macaw\Macaw::get(REST_API_PATH.'employee/(:any)', function($pathParams) {
                $empRestEndPoint = new EmployeeRestEndPoint();
                $empRestEndPoint->process('get',$pathParams);
            });

        }

		public function initializeDatabaseErrorMappings(){
			$this->addDatabaseErrorMapping('CONSTRAINT `Fk_User_Employee` FOREIGN KEY',"Can not delete Employee, please delete the User for this employee first.");
			$this->addDatabaseErrorMapping("Duplicate entry|for key 'employee'","A duplicate entry found");
		}

		public function setupModuleClassDefinitions(){
			$this->addModelClass('Employee');
			$this->addModelClass('EmploymentStatus');
			$this->addModelClass('EmployeeApproval');
		}

        public function getDashboardItemData(){
            $data = array();
            $emp = new Employee();
            $data['numberOfEmployees'] = $emp->Count("1 = 1");

            return $data;

        }

        public function initQuickAccessMenu(){
            UIManager::getInstance()->addQuickAccessMenuItem("View Employees","fa-users",CLIENT_BASE_URL."?g=admin&n=employees&m=admin_Employees",array("Admin","Manager"));
            UIManager::getInstance()->addQuickAccessMenuItem("Add a New Employee","fa-edit",CLIENT_BASE_URL."?g=admin&n=employees&m=admin_Employees&action=new",array("Admin"));

        }

	}
}

if (!class_exists('Employee')) {
	class Employee extends ICEHRM_Record {

        var $oldObj = null;
        var $oldObjOrig = null;
        var $historyUpdateList = array();
        var $historyFieldsToTrack = array(
            "employee_id"=>"employee_id",
            "first_name"=>"first_name",
            "middle_name"=>"middle_name",
            "last_name"=>"last_name",
            "nationality"=>"nationality_Name",
            "birthday"=>"birthday",
            "gender"=>"gender",
            "marital_status"=>"marital_status",
            "ssn_num"=>"ssn_num",
            "nic_num"=>"nic_num",
            "other_id"=>"other_id",
            "employment_status"=>"employment_status_Name",
            "job_title"=>"job_title_Name",
            "pay_grade"=>"pay_grade_Name",
            "work_station_id"=>"work_station_id",
            "address1"=>"address1",
            "address2"=>"address2",
            "city"=>"city_Name",
            "country"=>"country_Name",
            "province"=>"province_Name",
            "postal_code"=>"postal_code",
            "home_phone"=>"home_phone",
            "mobile_phone"=>"mobile_phone",
            "work_phone"=>"work_phone",
            "work_email"=>"work_email",
            "private_email"=>"private_email",
            "joined_date"=>"joined_date",
            "confirmation_date"=>"confirmation_date",
            "supervisor"=>"supervisor_Name",
            "indirect_supervisors"=>"indirect_supervisors",
            "department"=>"department_Name"
        );

		public function getAdminAccess(){
			return array("get","element","save","delete");
		}

		public function getManagerAccess(){
			return array("get","element","save");
		}

		public function getUserAccess(){
			return array("get");
		}

		public function getUserOnlyMeAccess(){
			return array("element","save");
		}

		public function getUserOnlyMeAccessField(){
			return "id";
		}


        private function initHistory($obj){

            $oldObjOrig = new Employee();
            $oldObjOrig->Load("id = ?",array($obj->id));
            $this->oldObjOrig = $oldObjOrig;

            $mapping = '{"nationality":["Nationality","id","name"],"employment_status":["EmploymentStatus","id","name"],"job_title":["JobTitle","id","name"],"pay_grade":["PayGrade","id","name"],"country":["Country","code","name"],"province":["Province","id","name"],"department":["CompanyStructure","id","title"],"supervisor":["Employee","id","first_name+last_name"]}';

            $this->oldObj = BaseService::getInstance()->getElement('Employee',$obj->id,$mapping,true);

        }

        private function saveHistory($obj){

            $oldObj = $this->oldObj;
            $oldObjOrig = $this->oldObjOrig;

            $mapping = '{"nationality":["Nationality","id","name"],"employment_status":["EmploymentStatus","id","name"],"job_title":["JobTitle","id","name"],"pay_grade":["PayGrade","id","name"],"country":["Country","code","name"],"province":["Province","id","name"],"department":["CompanyStructure","id","title"],"supervisor":["Employee","id","first_name+last_name"]}';

            $objEnriched = BaseService::getInstance()->getElement('Employee',$obj->id,$mapping,true);

            foreach($this->historyFieldsToTrack as $k => $v){

                if(empty($oldObjOrig->$k) && $obj->$k = '[]'){
                    continue;
                }

                if(empty($obj->$k) && $oldObjOrig->$k = '0000-00-00'){
                    continue;
                }

                if($oldObjOrig->$k != $obj->$k){

                    $enrichNewVal = '';
                    $enrichOldVal = '';

                    if($k == 'indirect_supervisors'){
                        if(!empty($obj->$k) && $obj->$k != '[]'){
                            $newIndeirectSupervisorIds = json_decode($obj->$k);
                            foreach($newIndeirectSupervisorIds as $id){
                                $item = BaseService::getInstance()->getItemFromCache("Employee", $id);
                                if($enrichNewVal != ""){
                                    $enrichNewVal .= ", ";
                                }
                                $enrichNewVal .= $item->first_name." ".$item->last_name;
                            }
                        }

                        if(!empty($oldObjOrig->$k) && $oldObjOrig->$k != '[]'){
                            $oldIndeirectSupervisorIds = json_decode($oldObjOrig->$k);
                            foreach($oldIndeirectSupervisorIds as $id){
                                $item = BaseService::getInstance()->getItemFromCache("Employee", $id);
                                if($enrichOldVal != ""){
                                    $enrichOldVal .= ", ";
                                }
                                $enrichOldVal .= $item->first_name." ".$item->last_name;
                            }
                        }
                    }else{
                        $enrichOldVal = $oldObj->$v;
                        $enrichNewVal = $objEnriched->$v;
                    }

                    $this->historyUpdateList[] = array($obj->id,$k,$enrichOldVal,$enrichNewVal);
                }
            }

            while(count($this->historyUpdateList)){
                $ele = array_pop($this->historyUpdateList);
                BaseService::getInstance()->addHistoryItem("Employee","Employee",$ele[0],$ele[1],$ele[2],$ele[3]);
            }
        }
		
		public function executePreSaveActions($obj){
			if(empty($obj->status)){
				$obj->status = 'Active';
			}
			return new IceResponse(IceResponse::SUCCESS,$obj);
		}

        public function executePreUpdateActions($obj){
            $this->initHistory($obj);
            return new IceResponse(IceResponse::SUCCESS,$obj);
        }

        public function executePostUpdateActions($obj){
            $this->saveHistory($obj);
        }

        public function postProcessGetData($obj){
            $obj = FileService::getInstance()->updateSmallProfileImage($obj);
            return $obj;
        }

        public function getVirtualFields(){
            return array(
                "image"
            );
        }

        public function getActiveEmployees(){
            $employee = new Employee();
            $list = $employee->Find("status = ?",array('Active'));
            return $list;
        }

        public function getActiveSubordinateEmployees(){

            $employee = new Employee();
            if(BaseService::getInstance()->currentUser->user_level != 'Admin'){
                $cemp = BaseService::getInstance()->getCurrentProfileId();
                $list = $employee->Find("status = ? and supervisor = ?",array('Active', $cemp));
            }else{
                $list = $employee->Find("status = ?",array('Active'));
            }
            return $list;
        }

        public static function cleanEmployeeData($obj){
            unset($obj->keysToIgnore);
            unset($obj->historyFieldsToTrack);
            unset($obj->historyUpdateList);
            unset($obj->oldObjOrig);
            unset($obj->oldObj);
            return $obj;
        }

		var $_table = 'Employees';
	}
}

if (!class_exists('ArchivedEmployee')) {
	class ArchivedEmployee extends ICEHRM_Record {

		public function getAdminAccess(){
			return array("get","element","save","delete");
		}

		public function getManagerAccess(){
			return array("get","element","save");
		}

		public function getUserAccess(){
			return array("get");
		}

		public function getUserOnlyMeAccess(){
			return array("element","save");
		}

		public function getUserOnlyMeAccessField(){
			return "id";
		}

		var $_table = 'ArchivedEmployees';
	}
}

if (!class_exists('EmploymentStatus')) {
	class EmploymentStatus extends ICEHRM_Record {

		var $_table = 'EmploymentStatus';

		public function getAdminAccess(){
			return array("get","element","save","delete");
		}

		public function getManagerAccess(){
			return array("get","element","save");
		}

		public function getUserAccess(){
			return array();
		}
	}
}

if (!class_exists('EmployeeApproval')) {
	class EmployeeApproval extends ICEHRM_Record {

		var $_table = 'EmployeeApprovals';

		public function getAdminAccess(){
			return array("get","element","save","delete");
		}

		public function getManagerAccess(){
			return array("get","element","save");
		}

		public function getUserAccess(){
			return array();
		}
	}
}


if (!class_exists('EmployeeRestEndPoint')) {
    class EmployeeRestEndPoint extends RestEndPoint{
        public function get($parameter){

            if(empty($parameter)){
                return new IceResponse(IceResponse::ERROR, "Employee ID not provided");
            }

            if($parameter === 'list'){
                $emp = new Employee();
                $emps = $emp->Find("1=1");
                $newEmps = array();
                foreach($emps as $emp){
                    $employee = new stdClass();
                    $employee->id = $emp->id;
                    $employee->employee_id = $emp->employee_id;
                    $employee->first_name = $emp->first_name;
                    $employee->middle_name = $emp->middle_name;
                    $employee->last_name = $emp->last_name;
                    $newEmps[] = $employee;

                }
                return new IceResponse(IceResponse::SUCCESS, $newEmps);
            }else{
                $mapping = '{"nationality":["Nationality","id","name"],"ethnicity":["Ethnicity","id","name"],"immigration_status":["ImmigrationStatus","id","name"],"employment_status":["EmploymentStatus","id","name"],"job_title":["JobTitle","id","name"],"pay_grade":["PayGrade","id","name"],"country":["Country","code","name"],"province":["Province","id","name"],"department":["CompanyStructure","id","title"],"supervisor":["Employee","id","first_name+last_name"]}';
                $emp = BaseService::getInstance()->getElement('Employee',$parameter,$mapping,true);
                if(!empty($emp)){
                    $emp = Employee::cleanEmployeeData($emp);
                    return new IceResponse(IceResponse::SUCCESS,$emp);
                }
                return new IceResponse(IceResponse::ERROR, "Employee not found" ,404);
            }


        }


    }
}
