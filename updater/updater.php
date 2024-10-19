<?php

use Updater\Downloader;
include(APP_BASE_PATH.'../updater/Updater/Curl.php');
include(APP_BASE_PATH.'../updater/Updater/Downloader.php');
include(APP_BASE_PATH.'../updater/Updater/UpdateException.php');

try {
	$downloader = new Downloader(CLIENT_BASE_PATH . 'data/upgrades/',APP_BASE_PATH . '../');
	$downloadUrl = $downloader->getLatestDownloadUrl();
	echo 'Download url: ' . $downloadUrl . '<br/>';
	$version = $downloader->getLatestVersion();
	echo 'Downloadable version: ' . $version . '<br/>';
	//echo 'Current version:' . VERSION_NUMBER . '<br/>';

	echo 'Downloading release ....'.'<br/>';
	$filePath = $downloader->download();
	echo 'Download complete:'.$filePath.'<br/>';

	$unzipResult = $downloader->unzip();
	if ($unzipResult) {
		echo 'Archive extracted.'.'<br/>';
	} else {
		echo 'Archive already extracted.'.'<br/>';
	}

	$backupPath = $downloader->backupExisting();
	echo 'Backup stored at'.$backupPath.'<br/>';

	$downloader->replaceCurrentRelease();
	echo 'New files copied.'.'<br/>';

} catch (\Updater\UpdateException $e) {
	echo $e->getMessage().'<br/>';
}



