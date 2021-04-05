<?php
namespace Documents\Common\Model;

use Classes\BaseService;
use Classes\ModuleAccess;
use Model\BaseModel;

class Document extends BaseModel
{
    public $table = 'Documents';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('documents', 'admin'),
            new ModuleAccess('documents', 'user'),
        ];
    }

    public function fieldValueMethods()
    {
        return ['getDocumentTypesForUser'];
    }

    public function getDocumentTypesForUser()
    {
        $documents = new Document();
        if (BaseService::getInstance()->currentUser->user_level === 'Employee'
            || BaseService::getInstance()->currentUser->user_level === 'Restricted Employee'
        ) {
            $documents = $documents->Find('share_with_employee = ?', ['Yes']);
        } else {
            $documents = $documents->Find('1 = 1');
        }

        return $documents;
    }
}
