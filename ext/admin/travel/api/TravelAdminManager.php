<?php
if (!class_exists('TravelAdminManager')) {
	
	class TravelAdminManager extends AbstractModuleManager{

		public function initializeUserClasses(){
			if(defined('MODULE_TYPE') && MODULE_TYPE != 'admin'){
				$this->addUserClass("EmployeeImmigration");
				$this->addUserClass("EmployeeTravelRecord");
			}
		}

		public function initializeFieldMappings(){
			$this->addFileFieldMapping('EmployeeImmigration', 'attachment1', 'name');
			$this->addFileFieldMapping('EmployeeImmigration', 'attachment2', 'name');
			$this->addFileFieldMapping('EmployeeImmigration', 'attachment3', 'name');
			
			$this->addFileFieldMapping('EmployeeTravelRecord', 'attachment1', 'name');
			$this->addFileFieldMapping('EmployeeTravelRecord', 'attachment2', 'name');
			$this->addFileFieldMapping('EmployeeTravelRecord', 'attachment3', 'name');
		}

		public function initializeDatabaseErrorMappings(){

		}

		public function setupModuleClassDefinitions(){
			
			$this->addModelClass('ImmigrationDocument');
			$this->addModelClass('EmployeeImmigration');
			$this->addModelClass('EmployeeTravelRecord');
			
		}

	}
}


if (!class_exists('ImmigrationDocument')) {
	class ImmigrationDocument extends ICEHRM_Record {
		var $_table = 'ImmigrationDocuments';

		public function getAdminAccess(){
			return array("get","element","save","delete");
		}

		public function getManagerAccess(){
			return array("get","element","save","delete");
		}

		public function getUserAccess(){
			return array("get");
		}

		public function getUserOnlyMeAccess(){
			return array("get","element");
		}
	}
}

if (!class_exists('EmployeeImmigration')) {
	class EmployeeImmigration extends ICEHRM_Record {
		var $_table = 'EmployeeImmigrations';

		public function getAdminAccess(){
			return array("get","element","save","delete");
		}

		public function getManagerAccess(){
			return array("get","element","save","delete");
		}

		public function getUserAccess(){
			return array("get");
		}

		public function getUserOnlyMeAccess(){
			return array("element","save","delete");
		}
	}
}


if (!class_exists('EmployeeTravelRecord')) {
	class EmployeeTravelRecord extends ApproveModel
    {
        var $_table = 'EmployeeTravelRecords';

        var $notificationModuleName = "Travel Management";
        var $notificationUnitName = "TravelRequest";
        var $notificationUnitPrefix = "A";
        var $notificationUnitAdminUrl = "g=modules&n=travel&m=module_Travel_Management#tabSubordinateEmployeeTravelRecord";
        var $preApproveSettingName = "Travel: Pre-Approve Travel Request";

		public function isMultiLevelApprovalsEnabled(){
			return (SettingsManager::getInstance()->getSetting('Travel: Enable Multi Level Approvals') == '1');
		}

        public function getAdminAccess()
        {
            return array("get", "element", "save", "delete");
        }

        public function getManagerAccess()
        {
            return array("get", "element", "save", "delete");
        }

        public function getUserAccess()
        {
            return array("get");
        }

        public function getUserOnlyMeAccess()
        {
            return array("element", "save", "delete");
        }

        public function fieldsNeedToBeApproved()
        {
            return array(
                "travel_from",
                "travel_to",
                "travel_date",
                "return_date",
                "funding",
                "currency"
            );
        }

		public function getType(){
			return 'EmployeeTravelRecord';
		}

		public function allowIndirectMapping(){
			if(SettingsManager::getInstance()->getSetting('Travel: Allow Indirect Admins to Approve') == '1'){
				return true;
			}
			return false;
		}

    }
}


if (!class_exists('EmployeeTravelRecordApproval')) {

	class EmployeeTravelRecordApproval extends EmployeeTravelRecord
	{

		public function Find($whereOrderBy,$bindarr=false,$pkeysArr=false,$extra=array()){
			return $this->findApprovals(new EmployeeTravelRecord(), $whereOrderBy,$bindarr,$pkeysArr,$extra);
		}
	}
}
