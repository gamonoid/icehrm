<?php

namespace Classes\Editor;

use Classes\BaseService;
use Classes\FileService;
use EditorUser\EditorService;

trait DeleteEditorContent
{
	public function preDeleteChecks()
	{
		$classPaths = explode('\\',self::class);
		$class = end($classPaths);
		EditorService::deleteContent($class, $this->id);
		FileService::getInstance()->deleteFilesForObject($class, $this->id);
	}
}
