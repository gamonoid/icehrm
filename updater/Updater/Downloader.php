<?php

namespace Updater;

class Downloader
{
	protected $basePath;
	protected $path;
	protected $downloadFileName;
	protected $zipFile;
	protected $releaseDirPath;
	protected $releaseDirName;
	protected $backupDirPath;

	protected $dirToReplace = ['core', 'web'];

	/**
	 * @param $basePath
	 */
	public function __construct( $workingDirectory, $copyIntoDir )
	{
		$this->copyIntoDir = realpath($copyIntoDir).'/';
		if (!file_exists($workingDirectory) && !mkdir($workingDirectory, 0755, true)) {
			throw new UpdateException('Error creating working directory:' . $this->releaseDirPath);
		}
		$this->path = $workingDirectory;
		$this->zipFile = $this->getLatestZip();
		if (empty($this->zipFile)) {
			return new UpdateException("No release data found" );
		}
		$this->releaseDirName = str_replace('.zip', '', $this->zipFile->name);
		$this->releaseDirPath = $this->path.$this->releaseDirName;
		$this->backupDirPath = $this->path.'backup_pre_'.$this->getLatestVersion();

	}

	public function  downloadLatestRelease() {
		$url = $this->getLatestDownloadUrl();
	}

	public function getLatestVersion() {
		if (empty($this->zipFile->name)) {
			return new UpdateException("Error getting URL to download latest zip file" );
		}
		$version = str_replace('icehrm_v', '', $this->zipFile->name);
		$version = str_replace('.OS.zip', '', $version);
		$versions = explode('.', $version);

		return (int)$versions[0] * 10000 + (int)$versions[1] * 100 + (int)$versions[2];
	}

	public function getLatestDownloadUrl() {

		if (empty($this->zipFile->browser_download_url)) {
			throw new UpdateException("Error getting URL to download latest zip file" );
		}

		return $this->zipFile->browser_download_url;
	}

	public function download() {
		$this->downloadFileName = $this->path.$this->getLatestVersion().'.zip';
		if (!file_exists($this->downloadFileName)) {
			file_put_contents($this->downloadFileName, file_get_contents($this->getLatestDownloadUrl()));
		}
		if (!file_exists($this->path)) {
			throw new UpdateException('Error downloading release zip file');
		}
		return $this->downloadFileName;
	}

	public function unzip() {

		if (file_exists($this->releaseDirPath)) {
			return false;
		}

		if (!class_exists( 'ZipArchive' )) {
			throw new UpdateException('Error extracting file: ZipArchive not found.');
		}
		$zip = new \ZipArchive;
		$res = $zip->open($this->downloadFileName);
		if ($res !== true) {
			throw new UpdateException('Error extracting file');
		}

		$zip->extractTo($this->path);
		$zip->close();

		return true;
	}

	public function backupExisting() {
		if (!file_exists($this->backupDirPath) && !mkdir($this->backupDirPath, 0755, true)) {
			throw new UpdateException('Error creating backup directory:' . $this->$this->backupDirPath);
		}
		foreach ($this->dirToReplace as $dir) {
			$source = APP_BASE_PATH.'../'.$dir;
			$destination = $this->backupDirPath.'/';
			if (file_exists($destination)) {
				continue;
			}
			system("cp -r $source $destination");
		}

		return $this->backupDirPath;
	}

	public function replaceCurrentRelease() {
		if (!file_exists($this->copyIntoDir) && !mkdir($this->copyIntoDir, 0755, true)) {
			throw new UpdateException('Error creating copy into:' . $this->$this->copyIntoDir);
		}
		foreach ($this->dirToReplace as $dir) {
			$source = $this->releaseDirPath.'/'.$dir;
			$destination = $this->copyIntoDir;
			if (strlen($destination) < 3 || strpos($destination, realpath(APP_BASE_PATH.'../')) === false) {
				throw new UpdateException('Error replacing files. Invalid destination:' . $destination);
			}

			system("rm -rf ".$destination.$dir);
			system("cp -r $source $destination");
		}

		return $this->backupDirPath;
	}

	protected function getLatestZip() {
		$content = Curl::get('https://api.github.com/repos/gamonoid/icehrm/releases/latest');
		$data = json_decode($content);
		$zipFiles = array_filter( $data->assets, function($item) {
			return strpos( $item->name, '.zip') > 0;
		});

		if (empty($zipFiles)) {
			throw new UpdateException("Couldn't find URL to download latest zip file" );
		}

		return end($zipFiles);
	}



}
