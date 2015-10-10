<?php

/**
 * Handle file uploads via XMLHttpRequest
 */
include ("config.base.php");
include ("include.common.php");
include_once ('server.includes.inc.php');

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES['file']['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    function getName() {
        return $_FILES['file']['name'];
    }
    function getSize() {
        return $_FILES['file']['size'];
    }
}

class qqFileUploader {
	var $log = null;
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    //private $sizeLimit = 2485760;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760){        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        $this->checkServerSettings();       
       	$this->file = new qqUploadedFileForm();
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        /*if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
        }*/       
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>1) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory,$saveFileName, $replaceOldFile = FALSE){
        if (!is_writable($uploadDirectory)){
            return array('success'=>0,'error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->file){
            return array('success'=>0,'error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        LogManager::getInstance()->info('file size ='.$size);
        LogManager::getInstance()->info('file size limit ='.$this->sizeLimit);
        if ($size == 0) {
            return array('success'=>0,'error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('success'=>0,'error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('success'=>0,'error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        //$filename .= microtime(true);
        $filename = $saveFileName; // file with only name
        $saveFileName = $saveFileName.'.'.strtolower($ext); // file with extention
       
        $final_img_location = $uploadDirectory . $saveFileName;

        if ($this->file->save($final_img_location)){
        	$arr = explode("/", $final_img_location);
			return array('success'=>1,'filename'=>$arr[count($arr)-1],'error'=>'');
        } else {
            return array('success'=>0,'error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }    
}
//Generate File Name
$saveFileName = $_POST['file_name'];
$saveFileName = str_replace("..","",$saveFileName);
$saveFileName = str_replace("/","",$saveFileName);

if(stristr($saveFileName,".php")){
	$saveFileName = str_replace(".php","",$saveFileName);
}

if(empty($saveFileName) || $saveFileName == "_NEW_"){
	$saveFileName = microtime();
	$saveFileName = str_replace(".", "-", $saveFileName);	
}

$file = new File();
$file->Load("name = ?",array($saveFileName));

// list of valid extensions, ex. array("jpeg", "xml", "bmp")

$allowedExtensions = explode(',', "csv,doc,xls,docx,xlsx,txt,ppt,pptx,rtf,pdf,xml,jpg,bmp,gif,png,jpeg");
// max file size in bytes
$sizeLimit =MAX_FILE_SIZE_KB * 1024;
$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
$result = $uploader->handleUpload(CLIENT_BASE_PATH.'data/',$saveFileName);
// to pass data through iframe you will need to encode all html tags

$uploadFilesToS3 = SettingsManager::getInstance()->getSetting("Files: Upload Files to S3");
$uploadFilesToS3Key = SettingsManager::getInstance()->getSetting("Files: Amazon S3 Key for File Upload");
$uploadFilesToS3Secret = SettingsManager::getInstance()->getSetting("Files: Amazone S3 Secret for File Upload");
$s3Bucket = SettingsManager::getInstance()->getSetting("Files: S3 Bucket");
$s3WebUrl = SettingsManager::getInstance()->getSetting("Files: S3 Web Url");

$uploadedToS3 = false;

LogManager::getInstance()->info($uploadFilesToS3."|".$uploadFilesToS3Key."|".$uploadFilesToS3Secret."|".$s3Bucket."|".$s3WebUrl."|".CLIENT_NAME);

if($uploadFilesToS3.'' == '1' && !empty($uploadFilesToS3Key) && !empty($uploadFilesToS3Secret) &&
	!empty($s3Bucket) && !empty($s3WebUrl)){
	
	$localFile = CLIENT_BASE_PATH.'data/'.$result['filename'];
	
	$f_size = filesize($localFile);
	$uploadname = CLIENT_NAME."/".$result['filename'];
	LogManager::getInstance()->info("Upload file to s3:".$uploadname);
	LogManager::getInstance()->info("Local file:".$localFile);
	LogManager::getInstance()->info("Local file size:".$f_size);
	
	
	$s3FileSys = new S3FileSystem($uploadFilesToS3Key, $uploadFilesToS3Secret);
	$res = $s3FileSys->putObject($s3Bucket, $uploadname, $localFile, 'authenticated-read');
	
	$file_url = $s3WebUrl.$uploadname;
	$file_url = $s3FileSys->generateExpiringURL($file_url);
	LogManager::getInstance()->info("Response from s3 file sys:".print_r($res,true));
	unlink($localFile);
	
	$uploadedToS3 = true;
}

if($result['success'] == 1){
	$file->name = $saveFileName;
	$file->filename = $result['filename'];
	$signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
	$file->$signInMappingField = $_POST['user']=="_NONE_"?null:$_POST['user'];
	$file->file_group = $_POST['file_group'];
	$file->Save();
	if($uploadedToS3){
		$result['data'] = $file_url;
	}else{
		$result['data'] = CLIENT_BASE_URL.'data/'.$result['filename'];
	}
	$result['data'] .= "|".$saveFileName;
	$result['data'] .= "|".$file->id;
}


echo "<script>parent.closeUploadDialog(".$result['success'].",'".$result['error']."','".$result['data']."');</script>";	


