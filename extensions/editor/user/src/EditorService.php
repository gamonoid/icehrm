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

	/**
	 * Single source of truth for resolving a document for rendering — the access
	 * checks + data assembly that used to live inline in web/index.php. Both the
	 * legacy page and the native SPA (GET editor/document) call this, so they can
	 * never diverge.
	 *
	 * @param array $params keys: object, id, field, hash, view, checks, title
	 * @return array {
	 *   allowed: bool, error?: string,
	 *   hash, contentId, objectType, objectId, objectField,
	 *   data (decoded editor JSON), readOnly: bool, canSelectChecks: bool,
	 *   employees: array, sideBarObject: mixed
	 * }
	 */
	public static function resolveDocument(array $params) {
		$readOnlyTypes = [
			'LmsEmployeeCourse',
			'LmsEmployeeLesson',
		];

		$content = null;

		// New-content path: object + id + field given (no hash yet).
		if (!empty($params['object']) && isset($params['id']) && !empty($params['field'])) {
			$object = self::getRelatedObject($params['object'], $params['id']);
			$access = self::getObjectAccess($object);
			if (!in_array('element', $access)) {
				$content = self::getContent($params['object'], $params['id'], $params['field']);
			}
			if ($content === null && !in_array('save', $access)) {
				return ['allowed' => false, 'error' => 'You are not allowed to create this document'];
			}
			if ($content === null && in_array('save', $access)) {
				$content = self::createContent(
					$params['object'],
					$params['id'],
					$params['field'],
					isset($params['title']) ? $params['title'] : null
				);
			}
		}

		// Hash path (the canonical case once a document exists).
		if ($content === null) {
			if (empty($params['hash'])) {
				return ['allowed' => false, 'error' => 'Not found'];
			}
			$content = self::getContentByHash($params['hash']);
		}

		if (empty($content) || empty($content->hash)) {
			return ['allowed' => false, 'error' => 'Not found'];
		}

		/** @var \Model\BaseModel $object */
		$object = self::getRelatedObject($content->object_type, $content->object_id);
		if (!$object || empty($object->id)) {
			return ['allowed' => false, 'error' => 'No object for the document'];
		}

		$access = self::getObjectAccess($object);
		if (!in_array('element', $access)) {
			return ['allowed' => false, 'error' => 'Not allowed to view the document'];
		}

		$readOnly = (!empty($params['view']) || !in_array('save', $access));
		if (in_array($content->object_type, $readOnlyTypes)) {
			$readOnly = true;
		}
		$canSelectChecks = (!empty($params['checks']) && in_array('element', $access));

		$editorPermissions = $object->getEditorPermissions();
		if (!in_array('default', $editorPermissions)) {
			if (!$readOnly && !in_array('edit', $editorPermissions)) {
				$readOnly = true;
			}
			if (in_array('view', $editorPermissions)) {
				$readOnly = true;
			}
			if (in_array('check', $editorPermissions)) {
				$canSelectChecks = true;
			}
		}

		$sideBarObject = $object->getEditorSideBarObject($readOnly ? 'view' : 'edit');

		return [
			'allowed' => true,
			'hash' => $content->hash,
			'contentId' => $content->id,
			'objectType' => $content->object_type,
			'objectId' => $content->object_id,
			'objectField' => $content->object_field,
			'data' => json_decode($content->content),
			'readOnly' => (bool) $readOnly,
			'canSelectChecks' => (bool) $canSelectChecks,
			'employees' => self::getEmployeeNamesAndImages(),
			'sideBarObject' => $sideBarObject,
		];
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
