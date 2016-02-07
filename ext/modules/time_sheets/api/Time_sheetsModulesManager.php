<?php
if (!class_exists('Time_sheetsModulesManager')) {
	
	class Time_sheetsModulesManager extends AbstractModuleManager{

		public function initializeUserClasses(){
			$this->addUserClass("EmployeeTimeSheet");
			$this->addUserClass("EmployeeTimeEntry");
		}

		public function initializeFieldMappings(){
				
		}

		public function initializeDatabaseErrorMappings(){

		}

		public function setupModuleClassDefinitions(){
			
			$this->addModelClass('EmployeeTimeSheet');
			$this->addModelClass('EmployeeTimeEntry');
			
		}

        public function getDashboardItemData(){
            $data = array();
            $data['timeSheetHoursWorked'] = $this->getLastTimeSheetHours()->getData();
            return $data;

        }

        private function getLastTimeSheetHours(){
            $timeSheet = new EmployeeTimeSheet();
            $timeSheet->Load("employee = ? order by date_end desc limit 1",array(BaseService::getInstance()->getCurrentProfileId()));

            if(empty($timeSheet->employee)){
                return new IceResponse(IceResponse::SUCCESS,"0:00");
            }

            $timeSheetEntry = new EmployeeTimeEntry();
            $list = $timeSheetEntry->Find("timesheet = ?",array($timeSheet->id));

            $seconds = 0;
            foreach($list as $entry){
                $seconds += (strtotime($entry->date_end) - strtotime($entry->date_start));
            }

            $minutes = (int)($seconds/60);
            $rem = $minutes % 60;
            $hours = ($minutes - $rem)/60;
            if($rem < 10){
                $rem ="0".$rem;
            }
            return new IceResponse(IceResponse::SUCCESS,$hours.":".$rem);

        }

	}
}

if (!class_exists('EmployeeTimeSheet')) {

	class EmployeeTimeSheet extends ICEHRM_Record {
		var $_table = 'EmployeeTimeSheets';

		public function getAdminAccess(){
			return array("get","element","save","delete");
		}

		public function getManagerAccess(){
			return array("get","element","save","delete");
		}

		public function getUserAccess(){
			return array("get","element");
		}

		public function getUserOnlyMeAccess(){
			return array("element","save","delete");
		}

        public function getTotalTime()
        {

            $start = $this->date_start . " 00:00:00";
            $end = $this->date_end . " 23:59:59";

            $timeEntry = new EmployeeTimeEntry();
            $list = $timeEntry->Find("employee = ? and ((date_start >= ? and date_start <= ?) or (date_end >= ? and date_end <= ?))", array($this->employee, $start, $end, $start, $end));


            $seconds = 0;

            foreach ($list as $entry) {

                $secondsTemp = (strtotime($entry->date_end) - strtotime($entry->date_start));
                if ($secondsTemp < 0) {
                    $secondsTemp = 0;
                }


                $seconds += $secondsTemp;
            }

            $totMinutes = round($seconds / 60);
            $minutes = $totMinutes % 60;
            $hours = ($totMinutes - $minutes) / 60;

            return CalendarTools::addLeadingZero($hours) . ":" . CalendarTools::addLeadingZero($minutes);
        }

        public function postProcessGetData($entry){
            $entry->total_time = $this->getTotalTime();
            return $entry;
        }
	}

	class EmployeeTimeEntry extends ICEHRM_Record {
		var $_table = 'EmployeeTimeEntry';

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
		
		public function validateSave($obj){
			if(SettingsManager::getInstance()->getSetting("Attendance: Time-sheet Cross Check") == "1"){
				$attendance = new Attendance();
				$list = $attendance->Find("employee = ? and in_time <= ? and out_time >= ?",array($obj->employee,$obj->date_start,$obj->date_end));
				if(empty($list) || count($list) == 0){
					return new IceResponse(IceResponse::ERROR,"The time entry can not be added since you have not marked attendance for selected period");
				}	
			}
			return new IceResponse(IceResponse::SUCCESS,"");
		}
	}

}