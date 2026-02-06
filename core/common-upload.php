<?php
/**
 * Handle file uploads via XMLHttpRequest
 */

use Classes\BaseService;
use Classes\FileService;
use Classes\SettingsManager;
use Utils\LogManager;

include("config.base.php");
include("include.common.php");
include_once('server.includes.inc.php');

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class IceFileUploader
{
	/**
	 * Save the file to the specified path
	 * @return boolean TRUE on success
	 */
	function save($path)
	{
		if (!move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
			return false;
		}
		return true;
	}
	function getName()
	{
		return $_FILES['file']['name'];
	}
	function getSize()
	{
		return $_FILES['file']['size'];
	}
}

class qqFileUploader
{
	var $log = null;
	private $allowedExtensions = array();
	private $sizeLimit = 10485760;
	private $file;

	function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760)
	{
		$allowedExtensions = array_map("strtolower", $allowedExtensions);
		$this->allowedExtensions = $allowedExtensions;
		$this->sizeLimit = $sizeLimit;
		$this->file = new IceFileUploader();
	}

	private function toBytes($str)
	{
		$val = trim($str);
		$last = strtolower($str[strlen($str)-1]);
		switch ($last) {
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
		return $val;
	}

	/**
	 * Returns array('success'=>1) or array('error'=>'error message')
	 */
	function handleUpload($uploadDirectory, $saveFileName, $replaceOldFile = false)
	{
		if (!is_writable($uploadDirectory)) {
			LogManager::getInstance()->debug("Server error. Upload directory is not writable");
			return array('success'=>0,'error' => "Server error. Upload directory is not writable");
		}

		if (!$this->file->getName()) {
			LogManager::getInstance()->debug('No files were uploaded.');
			return array('success'=>0,'error' => 'No files were uploaded.');
		}

		$size = $this->file->getSize();

		if ($size == 0) {
			LogManager::getInstance()->debug('Could not calculate the file size');
			return array('success'=>0,'error' => 'Could not upload the file. ' .
				'The upload was cancelled, or the server rejected the file');
		}

		if ($size > $this->sizeLimit) {
			LogManager::getInstance()->debug('File size is larger than allowed max limit');
			LogManager::getInstance()->debug('file size ='.$size);
			LogManager::getInstance()->debug('file size limit ='.$this->sizeLimit);
			return array('success'=>0,'error' => 'File is too large');
		}

		$pathinfo = pathinfo($this->file->getName());
		$ext = $pathinfo['extension'];

		if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)) {
			$these = implode(', ', $this->allowedExtensions);
			LogManager::getInstance()->debug('File has an invalid extension, it should be one of '. $these . '.');
			return array('success'=>0,'error' => 'File has an invalid extension, it should be one of '. $these . '.');
		}
		$filename = $saveFileName; // file with only name
		$saveFileName = $saveFileName.'.'.strtolower($ext); // file with extention

		$final_img_location = $uploadDirectory . $saveFileName;

		if ($this->file->save($final_img_location)) {
			$arr = explode("/", $final_img_location);
			return array('success'=>1,'filename'=>$arr[count($arr)-1],'error'=>'');
		} else {
			LogManager::getInstance()->debug('Error occurred while saving the file');
			return array('success'=>0,'error'=> 'Could not save uploaded file.' .
				'The upload was cancelled, or server error encountered');
		}
	}
}
//Generate File Name
$saveFileName = $_REQUEST['file_name'];
$saveFileName = str_replace("..", "", $saveFileName);
$saveFileName = str_replace("/", "", $saveFileName);

if (stristr($saveFileName, ".php")) {
	$saveFileName = str_replace(".php", "", $saveFileName);
}

if (empty($saveFileName) || $saveFileName == "_NEW_") {
	$saveFileName = microtime();
	$saveFileName = str_replace(".", "-", $saveFileName);
}

$file = new \Model\File();
$file->Load("name = ?", array($saveFileName));

// list of valid extensions, ex. array("jpeg", "xml", "bmp")

$allowedExtensions = explode(',', "csv,doc,xls,docx,xlsx,txt,ppt,pptx,rtf,pdf,xml,jpg,bmp,gif,png,jpeg");
// max file size in bytes
$sizeLimit =MAX_FILE_SIZE_KB * 1024;
$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
$result = $uploader->handleUpload(BaseService::getInstance()->getDataDirectory(), $saveFileName);
// to pass data through iframe you will need to encode all html tags

$uploadFilesToS3 = SettingsManager::getInstance()->getSetting("Files: Upload Files to S3");
$uploadFilesToS3Key = SettingsManager::getInstance()->getSetting("Files: Amazon S3 Key for File Upload");
$uploadFilesToS3Secret = SettingsManager::getInstance()->getSetting(
	"Files: Amazon S3 Secret for File Upload"
);
$s3Bucket = SettingsManager::getInstance()->getSetting("Files: S3 Bucket");
$s3WebUrl = SettingsManager::getInstance()->getSetting("Files: S3 Web Url");

$uploadedToS3 = false;

$localFile = BaseService::getInstance()->getDataDirectory().$result['filename'];
$f_size = filesize($localFile);
if ($uploadFilesToS3.'' == '1' && !empty($uploadFilesToS3Key) && !empty($uploadFilesToS3Secret) &&
	!empty($s3Bucket) && !empty($s3WebUrl)) {
	$uploadname = CLIENT_NAME."/".$result['filename'];
	LogManager::getInstance()->debug("Upload file to s3:".$uploadname);
	LogManager::getInstance()->debug("Local file:".$localFile);
	LogManager::getInstance()->debug("Local file size:".$f_size);


	$s3FileSys = new \Classes\S3FileSystem($uploadFilesToS3Key, $uploadFilesToS3Secret);
	$res = $s3FileSys->putObject($s3Bucket, $uploadname, $localFile, 'authenticated-read');

	$file_url = $s3WebUrl.$uploadname;
	$file_url = $s3FileSys->generateExpiringURL($file_url);
	LogManager::getInstance()->info("Response from s3 file sys:".print_r($res, true));
	unlink($localFile);

	$uploadedToS3 = true;
}

if ($result['success'] == 1) {
	$file->name = $saveFileName;
	$file->filename = $result['filename'];
	$signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
	$file->$signInMappingField = $_REQUEST['user']=="_NONE_"?null:$_REQUEST['user'];
	$file->file_group = $_REQUEST['file_group'];
	$file->size = $f_size;
	$file->size_text = FileService::getInstance()->getReadableSize($f_size);
	$file->Save();
	if ($uploadedToS3) {
		$url = $file_url;
	} else {
		$url = \Classes\FileService::getInstance()->getLocalSecureUrl($result['filename']);
	}

	echo json_encode([
		'name' => $file->name,
		'status' => 'success',
		'url' => $url,
	]);
} else {
	echo json_encode([
		'status' => 'error',
		'message' => $result['error'],
	]);
}
