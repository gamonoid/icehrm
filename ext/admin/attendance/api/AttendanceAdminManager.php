<?php
if (!class_exists('AttendanceAdminManager')) {

    class AttendanceAdminManager extends AbstractModuleManager{

        public function initializeUserClasses(){

        }

        public function initializeFieldMappings(){

        }

        public function initializeDatabaseErrorMappings(){

        }

        public function setupModuleClassDefinitions(){
            $this->addModelClass('Attendance');
        }

        public function getDashboardItemData(){
            $data = array();
            $attendance = new Attendance();
            $data['numberOfAttendanceLastWeek'] = $attendance->Count("in_time > '".date("Y-m-d H:i:s",strtotime("-1 week"))."'");
            if(empty($data['numberOfAttendanceLastWeek'])){
                $data['numberOfAttendanceLastWeek'] = 0;
            }
            return $data;

        }

        public function initQuickAccessMenu(){
            UIManager::getInstance()->addQuickAccessMenuItem("Clocked In Employees","fa-clock-o",CLIENT_BASE_URL."?g=admin&n=attendance&m=admin_Employees#tabAttendanceStatus",array("Admin","Manager"));
        }

    }
}

if (!class_exists('AttendanceDashboardManager')) {

    class AttendanceDashboardManager extends AbstractModuleManager{

        public function initializeUserClasses(){

        }

        public function initializeFieldMappings(){

        }

        public function initializeDatabaseErrorMappings(){

        }

        public function setupModuleClassDefinitions(){
            $this->addModelClass('Attendance');
        }

    }
}


//Model Classes

if (!class_exists('Attendance')) {
    class Attendance extends ICEHRM_Record {
        var $_table = 'Attendance';

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



if (!class_exists('AttendanceStatus')) {
    class AttendanceStatus extends ICEHRM_Record {
        var $_table = 'Attendance';


        public function getRecentAttendanceEntries($limit){
            $shift = intval(SettingsManager::getInstance()->getSetting("Attendance: Shift (Minutes)"));
            $attendance = new Attendance();
            $attendanceToday = $attendance->Find("1 = 1 order by in_time desc limit ".$limit,array());
            $attendanceData = array();
            $employees = array();
            foreach($attendanceToday as $atEntry){
                $entry = new stdClass();
                $entry->id = $atEntry->employee;
                $dayArr = explode(" ",$atEntry->in_time);
                $day = $dayArr[0];
                if($atEntry->out_time == "0000-00-00 00:00:00" || empty($atEntry->out_time)){
                    if(strtotime($atEntry->in_time) < (time() + $shift * 60) && $day == date("Y-m-d")){
                        $entry->status = "Clocked In";
                        $entry->statusId = 0;
                        $entry->color = 'green';

                        $employee = new Employee();
                        $employee->Load("id = ?",array($entry->id));
                        $entry->employee = $employee->first_name." ".$employee->last_name;
                        $employees[$entry->id] = $entry;
                    }
                }

                if(!isset($employees[$entry->id])){
                    $employee = new Employee();
                    $employee->Load("id = ?",array($entry->id));
                    if($day == date("Y-m-d")){
                        $entry->status = "Clocked Out";
                        $entry->statusId = 1;
                        $entry->color = 'yellow';
                    }else{
                        $entry->status = "Not Clocked In";
                        $entry->statusId = 2;
                        $entry->color = 'gray';
                    }
                    $entry->employee = $employee->first_name." ".$employee->last_name;
                    $employees[$entry->id] = $entry;
                }

            }

            return array_values($employees);
        }

        public function Find($whereOrderBy,$bindarr=false,$pkeysArr=false,$extra=array()){
            $shift = intval(SettingsManager::getInstance()->getSetting("Attendance: Shift (Minutes)"));
            $employee = new Employee();
            $data = array();
            $employees = $employee->Find("1=1");

            $attendance = new Attendance();
            $attendanceToday = $attendance->Find("date(in_time) = ?",array(date("Y-m-d")));
            $attendanceData = array();
            //Group by employee
            foreach($attendanceToday as $attendance){
                if(isset($attendanceData[$attendance->employee])){
                    $attendanceData[$attendance->employee][] = $attendance;
                }else{
                    $attendanceData[$attendance->employee] = array($attendance);
                }
            }


            foreach($employees as $employee){

                $entry = new stdClass();
                $entry->id = $employee->id;
                $entry->employee = $employee->id;



                if(isset($attendanceData[$employee->id])){
                    $attendanceEntries = $attendanceData[$employee->id];
                    foreach($attendanceEntries as $atEntry){
                        if($atEntry->out_time == "0000-00-00 00:00:00" || empty($atEntry->out_time)){
                            if(strtotime($atEntry->in_time) < time() + $shift * 60){
                                $entry->status = "Clocked In";
                                $entry->statusId = 0;
                            }
                        }
                    }

                    if(empty($entry->status)){
                        $entry->status = "Clocked Out";
                        $entry->statusId = 1;
                    }
                }else{
                    $entry->status = "Not Clocked In";
                    $entry->statusId = 2;
                }

                $data[] = $entry;
            }

            function cmp($a, $b) {
                return $a->statusId - $b->statusId;
            }
            usort($data, "cmp");

            return $data;
        }

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


if (!class_exists('BasicOvertimeCalculator')) {

    class BasicOvertimeCalculator{

        public function createAttendanceSummary($atts){

            $atTimeByDay = array();

            foreach($atts as $atEntry){
                if($atEntry->out_time == "0000-00-00 00:00:00" || empty($atEntry->out_time)){
                    continue;
                }

                $atDate = date("Y-m-d",strtotime($atEntry->in_time));

                if(!isset($atTimeByDay[$atDate])){
                    $atTimeByDay[$atDate]   = 0;
                }

                $diff = strtotime($atEntry->out_time) - strtotime($atEntry->in_time);
                if($diff < 0){
                    $diff = 0;
                }

                $atTimeByDay[$atDate] += $diff;

            }

            return $atTimeByDay;
        }

        public function calculateOvertime($atTimeByDay){
            $overtimeStarts = SettingsManager::getInstance()->getSetting('Attendance: Overtime Start Hour');
            $doubletimeStarts = SettingsManager::getInstance()->getSetting('Attendance: Double time Start Hour');

            $overtimeStarts = (is_numeric($overtimeStarts))?floatval($overtimeStarts)*60*60:0;
            $doubletimeStarts = (is_numeric($doubletimeStarts))?floatval($doubletimeStarts)*60*60:0;

            $atTimeByDayNew = array();
            foreach($atTimeByDay as $k=>$v){
                $atTimeByDayNewEntry = array("t"=>$v,"r"=>0,"o"=>0,"d"=>0);
                if($overtimeStarts > 0 && $v > $overtimeStarts){
                    $atTimeByDayNewEntry["r"] = $overtimeStarts;
                    if($doubletimeStarts > 0 && $doubletimeStarts > $overtimeStarts){
                        //calculate double time
                        if($v > $doubletimeStarts){
                            $atTimeByDayNewEntry['d'] =  $v - $doubletimeStarts;
                            $atTimeByDayNewEntry['o'] = $doubletimeStarts - $overtimeStarts;
                        }else{
                            $atTimeByDayNewEntry['d'] = 0 ;
                            $atTimeByDayNewEntry['o'] = $v - $overtimeStarts;
                        }

                    }else{
                        //ignore double time
                        $atTimeByDayNewEntry['o'] = $v - $overtimeStarts;
                    }
                }else{
                    //ignore overtime
                    $atTimeByDayNewEntry['r'] = $v;
                }

                $atTimeByDayNew[$k] = $atTimeByDayNewEntry;
            }

            return $atTimeByDayNew;
        }

        protected function removeAdditionalDays($atSummary, $actualStartDate){
            $newAtSummary = array();
            foreach($atSummary as $k => $v){
                if(strtotime($k) >= strtotime($actualStartDate)){
                    $newAtSummary[$k] = $v;
                }
            }

            return $newAtSummary;
        }

        public function getData($atts, $actualStartDate, $aggregate = false){
            $atSummary = $this->createAttendanceSummary($atts);
            $overtime = $this->calculateOvertime($this->removeAdditionalDays($atSummary, $actualStartDate));
            if($aggregate){
                $overtime = $this->aggregateData($overtime);
                return $this->convertToHoursAggregated($overtime);
            }else{
                return $this->convertToHours($overtime);
            }

        }



        public function convertToHours($overtime){
            foreach($overtime as $k=>$v){
                $overtime[$k]['t'] =  $this->convertToHoursAndMinutes($overtime[$k]['t']);
                $overtime[$k]['r'] =  $this->convertToHoursAndMinutes($overtime[$k]['r']);
                $overtime[$k]['o'] =  $this->convertToHoursAndMinutes($overtime[$k]['o']);
                $overtime[$k]['d'] =  $this->convertToHoursAndMinutes($overtime[$k]['d']);
            }

            return $overtime;
        }

        public function convertToHoursAggregated($overtime){
            $overtime['t'] =  $this->convertToHoursAndMinutes($overtime['t']);
            $overtime['r'] =  $this->convertToHoursAndMinutes($overtime['r']);
            $overtime['o'] =  $this->convertToHoursAndMinutes($overtime['o']);
            $overtime['d'] =  $this->convertToHoursAndMinutes($overtime['d']);

            return $overtime;
        }

        protected function aggregateData($overtime){
            $ag = array("t"=>0,"r"=>0,"o"=>0,"d"=>0);
            foreach($overtime as $k=>$v){
                $ag['t'] += $v['t'];
                $ag['r'] += $v['r'];
                $ag['o'] += $v['o'];
                $ag['d'] += $v['d'];
            }

            return $ag;
        }

        public function convertToHoursAndMinutes($val){
            $sec = $val % 60;
            $minutesTot = ($val - $sec)/60;

            $minutes = $minutesTot % 60;
            $hours = ($minutesTot - $minutes)/60;

            if($hours < 10){
                $hours = "0".$hours;
            }
            if($minutes < 10){
                $minutes = "0".$minutes;
            }

            return $hours.":".$minutes;

        }

    }

}


if (!class_exists('CaliforniaOvertimeCalculator')) {

    class CaliforniaOvertimeCalculator extends BasicOvertimeCalculator{

        public function getData($atts, $actualStartDate, $aggregate = false){

            if(count($atts) == 0){
                return array();
            }

            $atSummary = $this->createAttendanceSummary($atts);
            $overtime = $this->calculateOvertime($atSummary);

            $workWeekStartDate = SettingsManager::getInstance()->getSetting('Attendance: Work Week Start Day');

            //TODO - just assume a work week from Sunday to Saturday

            //Find first Sunday in array
            $firstDate = null;
            //Find double time days (7th work day of a week without a break)
            $doubleTimeDates = array();
            $prvDate = null;
            $consecutiveWorkDays = 1;
            foreach($overtime as $k=>$v){
                if($firstDate == null) {
                    $dw = date("w", strtotime($k));
                    if ($dw == $workWeekStartDate) {
                        $firstDate = $k;
                    }
                }

                if($firstDate != null){
                    if($prvDate != null && date('Y-m-d', strtotime('-1 day',strtotime($k))) == $prvDate){

                        $consecutiveWorkDays++;
                        if($consecutiveWorkDays == 7){
                            //This is a double time day
                            $overtime[$k]['d'] = $overtime[$k]['d'] + $overtime[$k]['o'];
                            $overtime[$k]['o'] = 0;
                        }
                    }

                    //Resetting $consecutiveWorkDays at the start of the work week
                    if($prvDate != null && date( "w", strtotime($k)) == $workWeekStartDate){
                        $consecutiveWorkDays = 1;
                        $prvDate = null;
                    }

                    $prvDate = $k;
                }
            }

            $overtime = $this->removeAdditionalDays($overtime, $actualStartDate);
            if($aggregate){
                $overtime = $this->aggregateData($overtime);
                return $this->convertToHoursAggregated($overtime);
            }else{
                return $this->convertToHours($overtime);
            }


        }

    }

}
