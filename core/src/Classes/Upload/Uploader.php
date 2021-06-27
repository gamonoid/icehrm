<?php
namespace Classes\Upload;

use Classes\BaseService;
use Classes\FileService;
use Classes\IceResponse;
use Classes\S3FileSystem;
use Classes\SettingsManager;
use Model\File;
use Utils\LogManager;

class Uploader
{
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;

    private $file;

    public function __construct($file, array $allowedExtensions = array(), $sizeLimit = 10485760)
    {
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
        $this->allowedExtensions = $allowedExtensions;
        $this->sizeLimit = $sizeLimit;
        $this->file = $file;
    }


    protected function handleUpload($uploadDirectory, $saveFileName, $replaceOldFile = false)
    {
        if (!is_writable($uploadDirectory)) {
            return new IceResponse(
                IceResponse::ERROR,
                "Server error. Upload directory ($uploadDirectory) is not writable"
            );
        }

        if (!$this->file) {
            return new IceResponse(
                IceResponse::ERROR,
                'No files were uploaded.'
            );
        }

        $size = $this->file->getSize();
        LogManager::getInstance()->info('file size ='.$size);
        LogManager::getInstance()->info('file size limit ='.$this->sizeLimit);
        if ($size == 0) {
            return new IceResponse(
                IceResponse::ERROR,
                'File is empty'
            );
        }

        if ($size > $this->sizeLimit) {
            return new IceResponse(
                IceResponse::ERROR,
                'File is too large'
            );
        }

        $pathinfo = pathinfo($this->file->getName());
        $ext = $pathinfo['extension'];

        if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)) {
            $these = implode(', ', $this->allowedExtensions);
            return new IceResponse(
                IceResponse::ERROR,
                'File has an invalid extension, it should be one of '. $these . '.'
            );
        }

        $saveFileName = $saveFileName.'.'.strtolower($ext);

        $finalFileLocation = $uploadDirectory . $saveFileName;

        if ($this->file->save($finalFileLocation)) {
            $arr = explode("/", $finalFileLocation);
            return new IceResponse(
                IceResponse::SUCCESS,
                $arr[count($arr)-1]
            );
            //return array('success'=>1,'filename'=>$arr[count($arr)-1],'error'=>'');
        } else {
            return new IceResponse(
                IceResponse::ERROR,
                'The upload was cancelled, or server error encountered'
            );
        }
    }

    public static function upload($postData, $fileData)
    {
        //Generate File Name
        $saveFileName = $postData['file_name'];
        $saveFileName = str_replace("..", "", $saveFileName);
        $saveFileName = str_replace("/", "", $saveFileName);

        if (stristr($saveFileName, ".php")) {
            $saveFileName = str_replace(".php", "", $saveFileName);
        }

        if (empty($saveFileName) || $saveFileName == "_NEW_") {
            $saveFileName = microtime();
            $saveFileName = str_replace(".", "", $saveFileName);
            $saveFileName = str_replace(" ", "", $saveFileName);
        }

        $file = new File();
        $file->Load("name = ?", array($saveFileName));

        $allowedExtensions = explode(',', "csv,doc,xls,docx,xlsx,txt,ppt,pptx,rtf,pdf,xml,jpg,bmp,gif,png,jpeg");
        // max file size in bytes
        $sizeLimit =MAX_FILE_SIZE_KB * 1024;
        $uploader = new Uploader(new TempFile($fileData), $allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload(CLIENT_BASE_PATH.'data/', $saveFileName);

        if ($result->getStatus() !== IceResponse::SUCCESS) {
            return $result;
        }

        $uploadFilesToS3 = SettingsManager::getInstance()->getSetting("Files: Upload Files to S3");
        $uploadFilesToS3Key = SettingsManager::getInstance()->getSetting("Files: Amazon S3 Key for File Upload");
        $uploadFilesToS3Secret = SettingsManager::getInstance()->getSetting(
            "Files: Amazon S3 Secret for File Upload"
        );
        $s3Bucket = SettingsManager::getInstance()->getSetting("Files: S3 Bucket");
        $s3WebUrl = SettingsManager::getInstance()->getSetting("Files: S3 Web Url");

        $localFile = CLIENT_BASE_PATH.'data/'.$result->getData();
        $uploadedFileSize = filesize($localFile);
        if ($uploadFilesToS3.'' == '1' && !empty($uploadFilesToS3Key) && !empty($uploadFilesToS3Secret) &&
            !empty($s3Bucket) && !empty($s3WebUrl)) {
            $uploadName = CLIENT_NAME."/".$result->getData();
            LogManager::getInstance()->info("Upload file to s3:".$uploadName);
            LogManager::getInstance()->info("Local file:".$localFile);
            LogManager::getInstance()->info("Local file size:".$uploadedFileSize);


            $s3FileSys = new S3FileSystem($uploadFilesToS3Key, $uploadFilesToS3Secret);
            $res = $s3FileSys->putObject($s3Bucket, $uploadName, $localFile, 'authenticated-read');

            LogManager::getInstance()->info("Response from s3 file sys:".print_r($res, true));
            unlink($localFile);
        }

        $file->name = $saveFileName;
        $file->filename = $result->getData();
        $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
        $file->$signInMappingField = $postData['user']=="_NONE_"?null:$postData['user'];
        $file->file_group = $postData['file_group'];
        $file->size = $uploadedFileSize;
        $file->size_text = FileService::getInstance()->getReadableSize($uploadedFileSize);
        $file->Save();
        return new IceResponse(
            IceResponse::SUCCESS,
            $saveFileName
        );
    }
}
