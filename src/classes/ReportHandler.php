<?php
class ReportHandler{
	public function handleReport($request){
		if(!empty($request['id'])){
			$report = new Report();
			$report->Load("id = ?",array($request['id']));
			if($report->id."" == $request['id']){
				
				if($report->type == 'Query'){
					$where = $this->buildQueryOmmit(json_decode($report->paramOrder,true), $request);
					$query = str_replace("_where_", $where[0], $report->query);
					return $this->executeReport($report,$query,$where[1]);
				}else if($report->type == 'Class'){
					$className = $report->query;
					include MODULE_PATH.'/reportClasses/ReportBuilder.php';
					include MODULE_PATH.'/reportClasses/'.$className.".php";
					$cls = new $className();
					$data = $cls->getData($report,$request);
					return $this->generateReport($report,$data);
				}
			}else{
				return array("ERROR","Report id not found");
			}
		}		
	}
	
	
	private function executeReport($report,$query,$parameters){
		
		$report->DB()->SetFetchMode(ADODB_FETCH_ASSOC);
		$rs = $report->DB()->Execute($query,$parameters);
		if(!$rs){
			LogManager::getInstance()->info($report->DB()->ErrorMsg());
			return array("ERROR","Error generating report");
		}
		
		$reportNamesFilled = false;
		$columnNames = array();
		$reportData = array();
		foreach ($rs as $rowId => $row) {
			$reportData[] = array();
			if(!$reportNamesFilled){
				foreach ($row as $name=> $value){
					$columnNames[] = $name;
					$reportData[count($reportData)-1][] = $value;
				}
				$reportNamesFilled = true;
			}else{
				foreach ($row as $name=> $value){
					$reportData[count($reportData)-1][] = $value;
				}
			}
		}
		
		
		array_unshift($reportData,$columnNames);
		
		return $this->generateReport($report, $reportData);
		
		
	}
	
	private function generateReport($report, $data){
		
		$fileFirst = "Report_".str_replace(" ", "_", $report->name)."-".date("Y-m-d_H-i-s");
		$file = $fileFirst.".csv";
		
		$fileName = CLIENT_BASE_PATH.'data/'.$file;
		$fp = fopen($fileName, 'w');

		foreach ($data as $fields) {
			fputcsv($fp, $fields);
		}
			
		fclose($fp);
		
		$uploadedToS3 = false;
		
		$uploadFilesToS3 = SettingsManager::getInstance()->getSetting("Files: Upload Files to S3");
		$uploadFilesToS3Key = SettingsManager::getInstance()->getSetting("Files: Amazon S3 Key for File Upload");
		$uploadFilesToS3Secret = SettingsManager::getInstance()->getSetting("Files: Amazone S3 Secret for File Upload");
		$s3Bucket = SettingsManager::getInstance()->getSetting("Files: S3 Bucket");
		$s3WebUrl = SettingsManager::getInstance()->getSetting("Files: S3 Web Url");
		
		if($uploadFilesToS3.'' == '1' && !empty($uploadFilesToS3Key) 
			&& !empty($uploadFilesToS3Secret) && !empty($s3Bucket) && !empty($s3WebUrl)){
			
			$uploadname = CLIENT_NAME."/".$file;
			$s3FileSys = new S3FileSystem($uploadFilesToS3Key, $uploadFilesToS3Secret);
			$res = $s3FileSys->putObject($s3Bucket, $uploadname, $fileName, 'authenticated-read');
			
			if(empty($res)){
				return array("ERROR",$file);
			}
			
			unlink($fileName);
			$file_url = $s3WebUrl.$uploadname;
			$file_url = $s3FileSys->generateExpiringURL($file_url);
			$uploadedToS3 = true;
		}
		
		
		
		$fileObj = new File();
		$fileObj->name = $fileFirst;
		$fileObj->filename = $file;
		$fileObj->file_group = "Report";
		$ok = $fileObj->Save();
		
		if(!$ok){
			LogManager::getInstance()->info($fileObj->ErrorMsg());
			return array("ERROR","Error generating report");
		}
		
		$headers = array_shift($data);
		if($uploadedToS3){
			return array("SUCCESS",array($file_url,$headers,$data));
		}else{
			return array("SUCCESS",array($file,$headers,$data));
		}
		
	}
	
	private function buildQueryOmmit($names, $params){
		$parameters = array();
		$query = "";
		foreach($names as $name){
			if($params[$name] != "NULL"){
				if($query != ""){
					$query.=" AND ";
				}
				$query.=$name." = ?";
				$parameters[] = $params[$name];
			}	
		}
		
		if($query != ""){
			$query = "where ".$query;
		}
		
		return array($query, $parameters);
	}
}