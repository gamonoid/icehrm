<?php
/*
This file is part of Ice Framework.

Ice Framework is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Ice Framework is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Ice Framework. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */
namespace Classes;

use Model\File;
use Utils\LogManager;

class FileService
{

    private static $me = null;

    private $memcache;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$me)) {
            self::$me = new FileService();
        }

        return self::$me;
    }

    public function getFromCache($key)
    {
        try {
            $data = MemcacheService::getInstance()->get($key);

            if (!empty($data)) {
                return $data;
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function saveInCache($key, $data, $expire)
    {
        if (!class_exists('\\Memcached')) {
            return;
        }
        try {
            if (empty($this->memcache)) {
                $this->memcache = new \Memcached();
                $this->memcache->addServer(GLOB_MEMCACHE_SERVER, 11211);
            }
            $this->memcache->set($key, $data, $expire);
        } catch (\Exception $e) {
        }
    }

    public function checkAddSmallProfileImage($profileImage)
    {
        $file = new File();
        $file->Load('name = ?', array($profileImage->name."_small"));

        if (empty($file->id)) {
            LogManager::getInstance()->info("Small profile image ".$profileImage->name."_small not found");

            $largeFileUrl = $this->getFileUrl($profileImage->name);

            $file->name = $profileImage->name."_small";
            $signInMappingField = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
            $file->$signInMappingField = $profileImage->$signInMappingField;
            $file->filename = $file->name.str_replace($profileImage->name, "", $profileImage->filename);
            $file->file_group = $profileImage->file_group;

            file_put_contents("/tmp/".$file->filename."_orig", file_get_contents($largeFileUrl));

            if (file_exists("/tmp/".$file->filename."_orig")) {
                //Resize image to 100

                $img = new \Classes\SimpleImage("/tmp/".$file->filename."_orig");
                $img->fitToWidth(100);
                $img->save("/tmp/".$file->filename);

                $uploadFilesToS3Key = SettingsManager::getInstance()->getSetting(
                    "Files: Amazon S3 Key for File Upload"
                );
                $uploadFilesToS3Secret = SettingsManager::getInstance()->getSetting(
                    "Files: Amazone S3 Secret for File Upload"
                );
                $s3Bucket = SettingsManager::getInstance()->getSetting("Files: S3 Bucket");

                $uploadname = CLIENT_NAME."/".$file->filename;
                $localFile = "/tmp/".$file->filename;

                $s3FileSys = new S3FileSystem($uploadFilesToS3Key, $uploadFilesToS3Secret);
                $result = $s3FileSys->putObject($s3Bucket, $uploadname, $localFile, 'authenticated-read');

                unlink("/tmp/".$file->filename);
                unlink("/tmp/".$file->filename."_orig");

                LogManager::getInstance()->info("Upload Result:".print_r($result, true));

                if (!empty($result)) {
                    $file->Save();
                }

                return $file;
            }

            return null;
        }

        return $file;
    }

    public function updateSmallProfileImage($profile)
    {
        $file = new File();
        $file->Load('name = ?', array('profile_image_'.$profile->id));

        if ($file->name == 'profile_image_'.$profile->id) {
            $uploadFilesToS3 = SettingsManager::getInstance()->getSetting("Files: Upload Files to S3");
            if ($uploadFilesToS3 == "1") {
                try {
                    $fileNew = $this->checkAddSmallProfileImage($file);
                    if (!empty($fileNew)) {
                        $file = $fileNew;
                    }

                    $uploadFilesToS3Key = SettingsManager::getInstance()->getSetting(
                        "Files: Amazon S3 Key for File Upload"
                    );
                    $uploadFilesToS3Secret = SettingsManager::getInstance()->getSetting(
                        "Files: Amazone S3 Secret for File Upload"
                    );
                    $s3FileSys = new S3FileSystem($uploadFilesToS3Key, $uploadFilesToS3Secret);
                    $s3WebUrl = SettingsManager::getInstance()->getSetting("Files: S3 Web Url");
                    $fileUrl = $s3WebUrl.CLIENT_NAME."/".$file->filename;

                    $expireUrl = $this->getFromCache($fileUrl);
                    if (empty($expireUrl)) {
                        $expireUrl = $s3FileSys->generateExpiringURL($fileUrl, 600);
                        $this->saveInCache($fileUrl, $expireUrl, 500);
                    }

                    $profile->image = $expireUrl;
                } catch (\Exception $e) {
                    LogManager::getInstance()->error("Error generating profile image: ".$e->getMessage());
                    if ($profile->gender == 'Female') {
                        $profile->image = BASE_URL."images/user_female.png";
                    } else {
                        $profile->image = BASE_URL."images/user_male.png";
                    }
                }
            } else {
                $profile->image = CLIENT_BASE_URL.'data/'.$file->filename;
            }
        } else {
            if ($profile->gender == 'Female') {
                $profile->image = BASE_URL."images/user_female.png";
            } else {
                $profile->image = BASE_URL."images/user_male.png";
            }
        }

        return $profile;
    }

    public function updateProfileImage($profile)
    {
        $file = new File();
        $file->Load('name = ?', array('profile_image_'.$profile->id));

        if ($file->name == 'profile_image_'.$profile->id) {
            $uploadFilesToS3 = SettingsManager::getInstance()->getSetting("Files: Upload Files to S3");
            if ($uploadFilesToS3 == "1") {
                $uploadFilesToS3Key = SettingsManager::getInstance()->getSetting(
                    "Files: Amazon S3 Key for File Upload"
                );
                $uploadFilesToS3Secret = SettingsManager::getInstance()->getSetting(
                    "Files: Amazone S3 Secret for File Upload"
                );
                $s3FileSys = new S3FileSystem($uploadFilesToS3Key, $uploadFilesToS3Secret);
                $s3WebUrl = SettingsManager::getInstance()->getSetting("Files: S3 Web Url");
                $fileUrl = $s3WebUrl.CLIENT_NAME."/".$file->filename;

                $expireUrl = $this->getFromCache($fileUrl);
                if (empty($expireUrl)) {
                    $expireUrl = $s3FileSys->generateExpiringURL($fileUrl, 600);
                    $this->saveInCache($fileUrl, $expireUrl, 500);
                }

                $profile->image = $expireUrl;
            } else {
                $profile->image = CLIENT_BASE_URL.'data/'.$file->filename;
            }
        } else {
            if ($profile->gender == 'Female') {
                $profile->image = BASE_URL."images/user_female.png";
            } else {
                $profile->image = BASE_URL."images/user_male.png";
            }
        }

        return $profile;
    }

    public function getFileUrl($fileName)
    {
        $file = new File();
        $file->Load('name = ?', array($fileName));

        $uploadFilesToS3 = SettingsManager::getInstance()->getSetting("Files: Upload Files to S3");

        if ($uploadFilesToS3 == "1") {
            $uploadFilesToS3Key = SettingsManager::getInstance()->getSetting(
                "Files: Amazon S3 Key for File Upload"
            );
            $uploadFilesToS3Secret = SettingsManager::getInstance()->getSetting(
                "Files: Amazone S3 Secret for File Upload"
            );
            $s3FileSys = new S3FileSystem($uploadFilesToS3Key, $uploadFilesToS3Secret);
            $s3WebUrl = SettingsManager::getInstance()->getSetting("Files: S3 Web Url");
            $fileUrl = $s3WebUrl.CLIENT_NAME."/".$file->filename;

            $expireUrl = $this->getFromCache($fileUrl);
            if (empty($expireUrl)) {
                $expireUrl = $s3FileSys->generateExpiringURL($fileUrl, 600);
                $this->saveInCache($fileUrl, $expireUrl, 500);
            }

            return $expireUrl;
        } else {
            return  CLIENT_BASE_URL.'data/'.$file->filename;
        }
    }

    public function deleteProfileImage($profileId)
    {
        $file = new File();
        $file->Load('name = ?', array('profile_image_'.$profileId));
        if ($file->name == 'profile_image_'.$profileId) {
            $ok = $file->Delete();
            if ($ok) {
                LogManager::getInstance()->info("Delete File:".CLIENT_BASE_PATH.$file->filename);
                unlink(CLIENT_BASE_PATH.'data/'.$file->filename);
            } else {
                return false;
            }
        }

        $file = new File();
        $file->Load('name = ?', array('profile_image_'.$profileId."_small"));
        if ($file->name == 'profile_image_'.$profileId."_small") {
            $ok = $file->Delete();
            if ($ok) {
                LogManager::getInstance()->info("Delete File:".CLIENT_BASE_PATH.$file->filename);
                unlink(CLIENT_BASE_PATH.'data/'.$file->filename);
            } else {
                return false;
            }
        }

        return true;
    }

    public function deleteFileByField($value, $field)
    {
        LogManager::getInstance()->info("Delete file by field: $field / value: $value");
        $file = new File();
        $file->Load("$field = ?", array($value));
        if ($file->$field == $value) {
            $ok = $file->Delete();
            if ($ok) {
                $uploadFilesToS3 = SettingsManager::getInstance()->getSetting("Files: Upload Files to S3");

                if ($uploadFilesToS3 == "1") {
                    $uploadFilesToS3Key = SettingsManager::getInstance()->getSetting(
                        "Files: Amazon S3 Key for File Upload"
                    );
                    $uploadFilesToS3Secret = SettingsManager::getInstance()->getSetting(
                        "Files: Amazone S3 Secret for File Upload"
                    );
                    $s3Bucket = SettingsManager::getInstance()->getSetting("Files: S3 Bucket");

                    $uploadname = CLIENT_NAME."/".$file->filename;
                    LogManager::getInstance()->info("Delete from S3:".$uploadname);

                    $s3FileSys = new S3FileSystem($uploadFilesToS3Key, $uploadFilesToS3Secret);
                    $s3FileSys->deleteObject($s3Bucket, $uploadname);
                } else {
                    LogManager::getInstance()->info("Delete:".CLIENT_BASE_PATH.'data/'.$file->filename);
                    unlink(CLIENT_BASE_PATH.'data/'.$file->filename);
                }
            } else {
                return false;
            }
        }
        return true;
    }

    public function getFileData($name)
    {
        $file = new File();
        $file->Load("name = ?", array($name));
        if (!empty($file->id)) {
            $arr = explode(".", $file->filename);
            $file->type = $arr[count($arr) - 1];
        } else {
            return null;
        }
        return $file;
    }

    public function getReadableSize($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }
}
