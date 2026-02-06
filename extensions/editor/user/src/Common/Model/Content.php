<?php
namespace EditorUser\Common\Model;

use Classes\Editor\DeleteEditorContent;
use Classes\ModuleAccess;
use Model\BaseModel;

class Content extends BaseModel
{
	use DeleteEditorContent;
	public const STATUS_PRIVATE = 'private';
	public const STATUS_PUBLIC = 'public';

	public $table = 'Content';

	public function getAdminAccess()
	{
		return array("get","element","save","delete");
	}

	public function getManagerAccess()
	{
		return array("get","element","save","delete");
	}

	public function getUserAccess()
	{
		return array("get","element");
	}

	public function getAnonymousAccess()
	{
		return array();
	}

	public function getUserOnlyMeAccess()
	{
		return array("get","element","save","delete");
	}

	public function getModuleAccess()
	{
		return [
			new ModuleAccess('editor', 'user'),
		];
	}
}
