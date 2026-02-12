<?php

namespace EditorUser;

use Classes\BaseService;
use Classes\FileService;
use Classes\PermissionManager;
use EditorUser\Common\Model\Content;
use Employees\Common\Model\Employee;
use Utils\StringUtils;

class EditorService
{
	public static function getRelatedObject($objectName, $objectId) {
		$class = BaseService::getInstance()->getModelClassName($objectName);
		$object = new $class();
		$object->Load('id = ?', [$objectId]);
		if (empty($object->id)) {
			return null;
		}

		return $object;
	}
	public static function getObjectAccess($object, $user = null) {
		if (empty($object)) {
			return [];
		}
		return PermissionManager::checkGeneralAccess($object, $user);
	}

	public static function getContent($objectName, $objectId, $field) {
		$content = new Content();
		$content->Load('object_type = ? and object_field = ? and object_id = ?', [$objectName, $field, $objectId]);
		if (empty($content->id)) {
			return null;
		}

		return $content;
	}

	public static function deleteContent($objectName, $objectId) {
		$content = new Content();
		$contents = $content->Find('object_type = ? and object_id = ?', [$objectName, $objectId]);
		foreach ($contents as $content) {
			$content->Delete();
		}
	}

	public static function createContent($objectName, $objectId, $field, $title) {
		$content = new Content();
		$content->Load('object_type = ? and object_field = ? and object_id = ?', [$objectName, $field, $objectId]);
		if (!empty($content->id)) {
			return $content;
		}

		$content->title = $title ?? null;
		$content->hash = md5($objectName.$objectId.$field.time()).substr(md5(StringUtils::randomString(10)), 0, 16);
		$object = self::getRelatedObject($objectName, $objectId);
		$draftContent = $object ? $object->getEditorDraftContent() : null;
		$content->content = $draftContent ?? '{
		   "blocks":[
			  {
				 "type":"header",
				 "data":{
					"text":"Example title",
					"level":1
				 }
			  },
			  {
				 "type":"paragraph",
				 "data":{
					"text":"Start writing here .."
				 }
			  }
		   ]
		}';
		$content->object_type = $objectName;
		$content->object_field = $field;
		$content->object_id = $objectId;
		$content->url = null;
		$content->status = Content::STATUS_PRIVATE;
		$content->created = date('Y-m-d H:i:s');
		$content->updated = date('Y-m-d H:i:s');

		$ok = $content->Save();
		if (!$ok) {
			return null;
		}

		return $content;
	}

	public static function updateContent($hash, $data) {
		$content = self::getContentByHash($hash);
		if (empty($content)) {
			return false;
		}

		$content->content = $data;
		$ok = $content->Save();
		if (!$ok) {
			return false;
		}

		return true;
	}

	public static function getContentByHash($hash) {
		$content = new Content();
		$content->Load('hash = ?', [$hash]);
		if (empty($content->id)) {
			return null;
		}

		return $content;
	}

	public static function getEmployeeNamesAndImages() {
		$employee = new Employee();
		$employees = $employee->Find('status = ?', ['Active']);
		$list = [];
		foreach ($employees as $employee) {
			$employee = FileService::getInstance()->updateSmallProfileImage($employee);
			$list[] = [
				'label' => $employee->first_name.' '.$employee->last_name,
				'value' => $employee->first_name.' '.$employee->last_name.' ( id:'.$employee->id.' )',
				'image' => $employee->image,
			];
		}

		return $list;
	}

	public static function getDocumentLink($entityId, $type, $field, $object, $menu, $viewOnly = false, $addBaseUrl = true) {
		$content = self::getContent($type, $entityId, $field);

		if (!empty($content->hash)) {
			$link = sprintf(
				'%sg=extension&n=editor|user&m=%s&hash=%s',
				$addBaseUrl ? CLIENT_BASE_URL.'?' : '',
				$menu,
				$content->hash
			);
		} else {
			$link = sprintf(
				'%sg=extension&n=editor|user&m=%s&object=%s&id=%s&field=%s',
				$addBaseUrl ? CLIENT_BASE_URL.'?' : '',
				$menu,
				$type,
				$entityId,
				$field
			);
		}

		$access = self::getObjectAccess($object, BaseService::getInstance()->getCurrentUser());
		if (!in_array('save', $access) || $viewOnly) {
			$link = sprintf('%s&view=1', $link);
		}

		return $link;
	}

	public static function copyDocumentContent($sourceType, $sourceField, $sourceId, $targetType, $targetField, $targetId) {
		$target = self::getContent($targetType, $targetId, $targetField);
		if (!empty($target)) {
			return false;
		}

		$source = self::getContent($sourceType, $sourceId, $sourceField);
		if (empty($source)) {
			return false;
		}

		$target = self::createContent($targetType, $targetId, $targetField, $source->title);

		if (empty($target)) {
			return false;
		}

		$target->status = $source->status;
		$target->category = $source->category;
		$target->tags = $source->tags;
		$target->share_with_all = $source->share_with_all;
		$target->share_departments = $source->share_departments;
		$target->share_teams = $source->share_teams;
		$target->share_employees = $source->share_employees;
		$target->content = $source->content;

		$ok = $target->Save();
		if (!$ok) {
			return false;
		}

		return true;
	}
}
