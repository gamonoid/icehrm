<?php
if (!class_exists('OvertimeAdminManager')) {
	
	class OvertimeAdminManager extends AbstractModuleManager{

		public function initializeUserClasses(){
			if(defined('MODULE_TYPE') && MODULE_TYPE != 'admin'){
				$this->addUserClass("EmployeeOvertime");
			}
		}

		public function initializeFieldMappings(){

		}

		public function initializeDatabaseErrorMappings(){

		}

		public function setupModuleClassDefinitions(){
			
			$this->addModelClass('OvertimeCategory');
			$this->addModelClass('EmployeeOvertime');
			$this->addModelClass('EmployeeOvertimeApproval');
			
		}

	}
}


if (!class_exists('OvertimeCategory')) {
	class OvertimeCategory extends ICEHRM_Record {
		var $_table = 'OvertimeCategories';

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



if (!class_exists('EmployeeOvertime')) {
	class EmployeeOvertime extends ApproveModel
    {
        var $_table = 'EmployeeOvertime';

        var $notificationModuleName = "Overtime Management";
        var $notificationUnitName = "OvertimeRequest";
        var $notificationUnitPrefix = "An";
        var $notificationUnitAdminUrl = "g=modules&n=overtime&m=module_Time_Management#tabSubordinateEmployeeOvertime";
        var $preApproveSettingName = "Attendance: Pre-Approve Overtime Request";

		public function isMultiLevelApprovalsEnabled(){
			return (SettingsManager::getInstance()->getSetting('Overtime: Enable Multi Level Approvals') == '1');
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
                "start_time",
                "end_time"
            );
        }

		public function getType(){
			return 'EmployeeOvertime';
		}

		public function allowIndirectMapping(){
			if(SettingsManager::getInstance()->getSetting('Overtime: Allow Indirect Admins to Approve') == '1'){
				return true;
			}
			return false;
		}

    }
}


if (!class_exists('EmployeeOvertimeApproval')) {

	class EmployeeOvertimeApproval extends EmployeeOvertime
	{

		public function Find($whereOrderBy,$bindarr=false,$pkeysArr=false,$extra=array()){
			return $this->findApprovals(new EmployeeOvertime(), $whereOrderBy,$bindarr,$pkeysArr,$extra);
		}
	}
}
