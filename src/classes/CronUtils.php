<?php
class CronUtils{
	var $clientBasePath;
	var $cronFile;
	
	private static $me = null;
	
	private function __construct($clientBasePath, $cronFile){
		$this->clientBasePath = $clientBasePath."/";
		$this->cronFile = $cronFile;
	}
	
	public static function getInstance($clientBasePath, $cronFile){
		if(empty(self::$me)){
			self::$me = new CronUtils($clientBasePath, $cronFile);
		}
		return self::$me;
	}
	
	
	public function run(){
		$ams = scandir($this->clientBasePath);
		$count = 0;
		foreach($ams as $am){
			if(is_dir($this->clientBasePath.$am) && $am != '.' && $am != '..'){
                $command = 	"php ".$this->clientBasePath.$am."/".$this->cronFile;
                if(file_exists($this->clientBasePath.$am."/".$this->cronFile)){

                    echo "Run:".$command."\r\n";
                    error_log("Run:".$command);
                    passthru($command, $res);
                    echo "Result :".$res."\r\n";
                    error_log("Result :".$res);

                    $count++;
                    if($count > 25){
                        sleep(1);
                        $count = 0;
                    }
                }else{
                    echo "Error (File Not Found):".$command."\r\n";
                    error_log("Error (File Not Found):".$command);
                }

			}
		}
	}
}


class IceCron{

    const MINUTELY = "Minutely";
    const HOURLY = "Hourly";
    const DAILY = "Daily";
    const WEEKLY = "Weekly";
    const MONTHLY = "Monthly";
    const YEARLY = "Yearly";

    private $cron;

    public function __construct($cron){
        $this->cron = $cron;
    }

    public function isRunNow(){
        LogManager::getInstance()->debug("Cron ".print_r($this->cron,true));
        $lastRunTime = $this->cron->lastrun;
        if(empty($lastRunTime)){
            LogManager::getInstance()->debug("Cron ".$this->cron->name." is running since last run time is empty");
            return true;
        }

        $type = $this->cron->type;
        $frequency = intval($this->cron->frequency);
        $time = intval($this->cron->time);

        if(empty($frequency) || !is_int($frequency)){
            LogManager::getInstance()->debug("Cron ".$this->cron->name." is not running since frequency is not an integer");
            return false;
        }


        if($type == self::MINUTELY){

            $diff = (strtotime("now") - strtotime($lastRunTime));
            if(empty($time) || !is_int($time)){
                if($diff > 60){
                    return true;
                }
            }else{
                if($diff > 60 * $time){
                    return true;
                }
            }


        }else if($type == self::HOURLY){
            if(empty($time) || !is_int($time)){

                if(date('H') != date('H',strtotime($lastRunTime))){
                    return true;
                }
            }else{
                if(intval(date('i')) >= intval($time) && date('H') != date('H',strtotime($lastRunTime))){
                    return true;
                }
            }
        }else if($type == self::DAILY){
            if(empty($time) || !is_int($time)){

                if(date('d') != date('d',strtotime($lastRunTime))){
                    return true;
                }
            }else{
                if(intval(date('H')) >= intval($time) && date('d') != date('d',strtotime($lastRunTime))){
                    return true;
                }
            }
        }else if($type == self::MONTHLY){
            if(empty($time) || !is_int($time)){

                if(date('m') != date('m',strtotime($lastRunTime))){
                    return true;
                }
            }else{
                if(intval(date('d')) >= intval($time) && date('m') != date('m',strtotime($lastRunTime))){
                    return true;
                }
            }
        }else if($type == self::YEARLY){
            if(empty($time) || !is_int($time)){
                if(date('Y') != date('Y',strtotime($lastRunTime))){
                    return true;
                }
            }else{
                if(intval(date('m')) >= intval($time) && date('Y') != date('Y',strtotime($lastRunTime))){
                    return true;
                }
            }
        }

        return false;
    }

    public function execute(){
        $class = $this->cron->class;
        $obj = new $class();
        $obj->execute($this->cron);
        $this->cronCompleted();
    }


    private function cronCompleted(){
        $this->cron->lastrun = date("Y-m-d H:i:s");
        $ok = $this->cron->Save();
        if(!$ok){
            LogManager::getInstance()->error("Error saving cron due to :".$this->cron->ErrorMsg());
        }
    }

}

interface IceTask{
    public function execute($cron);
}

abstract class EmailIceTask implements IceTask{
    public abstract function execute($cron);

    public function sendEmployeeEmails($emailList, $subject){


        foreach($emailList as $employeeId => $emailData){
            $ccList = array();
            if(SettingsManager::getInstance()->getSetting('Notifications: Copy Document Expiry Emails to Manager') == '1'){
                $employee = new Employee();
                $employee->Load("id = ?",array($employeeId));
                if(!empty($employee->supervisor)){
                    $supperuser = BaseService::getInstance()->getUserFromProfileId($employee->supervisor);
                    if(!empty($supperuser)){
                        $ccList[] = $supperuser->email;
                    }
                }
            }
            $user = BaseService::getInstance()->getUserFromProfileId($employeeId);
            if(!empty($user) && !empty($user->email)){
                $email = new IceEmail();
                $email->subject = $subject;
                $email->toEmail = $user->email;
                $email->template = $emailData;
                $email->params = '[]';
                $email->cclist = json_encode($ccList);
                $email->bcclist = '[]';
                $email->status = 'Pending';
                $email->created = date('Y-m-d H:i:s');
                $email->updated = date('Y-m-d H:i:s');
                $ok = $email->Save();
                if(!$ok){
                    LogManager::getInstance()->error("Error Saving Email: ".$email->ErrorMsg());
                }
            }
        }
    }
}
