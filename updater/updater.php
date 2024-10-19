<?php
if (php_sapi_name() != 'cli') {
	exit();
}
use Updater\Downloader;
include(APP_BASE_PATH.'config.base.php');
include(APP_BASE_PATH.'../updater/Updater/Curl.php');
include(APP_BASE_PATH.'../updater/Updater/Downloader.php');
include(APP_BASE_PATH.'../updater/Updater/UpdateException.php');

try {
	$downloader = new Downloader(CLIENT_BASE_PATH . 'data/upgrades/',APP_BASE_PATH . '../');
	$version = $downloader->getLatestVersion();
	echo 'Downloadable version: ' . $version . "\r\n";
	echo 'Current version:' . VERSION_NUMBER . "\r\n";

	if ((int)VERSION_NUMBER >= (int)$version) {
		echo 'Stopping update process, you already have the latest version: ' . $version . "\r\n";
	}

	$downloadUrl = $downloader->getLatestDownloadUrl();
	echo 'Download url: ' . $downloadUrl . "\r\n";

	echo 'Downloading release ....'."\r\n";
	$filePath = $downloader->download();
	echo 'Download complete:'.$filePath."\r\n";

	$unzipResult = $downloader->unzip();
	if ($unzipResult) {
		echo 'Archive extracted.'."\r\n";
	} else {
		echo 'Archive already extracted.'."\r\n";
	}

	$backupPath = $downloader->backupExisting();
	echo 'Backup stored at'.$backupPath."\r\n";

	$downloader->replaceCurrentRelease();
	echo 'New files copied.'."\r\n";

} catch (\Updater\UpdateException $e) {
	echo $e->getMessage()."\r\n";
}



