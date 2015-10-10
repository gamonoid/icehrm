<?php
class CronUtils{
	var $clientBasePath;
	var $cronFile;
	
	private static $me = null;
	
	private function __construct($clientBasePath, $cronFile){
		$this->clientBasePath = $clientBasePath;
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
		
		foreach($ams as $am){
			if(is_dir($this->clientBasePath.$am) && $am != '.' && $am != '..'){
				$command = 	"php ".$this->cronFile." -c".$this->clientBasePath.$am;
				echo "Run:".$command."\r\n";
				passthru($command, $res);
				echo "Result :".$res."\r\n";
			}
		}
	}
}