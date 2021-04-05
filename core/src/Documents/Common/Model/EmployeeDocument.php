<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 8:26 AM
 */

namespace Documents\Common\Model;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Employees\Common\Model\Employee;
use Model\BaseModel;

class EmployeeDocument extends BaseModel
{
    public $table = 'EmployeeDocuments';

    private function getHiddenDocumentTypeIds()
    {
    }

    // @codingStandardsIgnoreStart
    public function Find($whereOrderBy, $bindarr = false, $cache = false, $pkeysArr = false, $extra = array())
    {
        $find = '';
        $user = BaseService::getInstance()->getCurrentUser();

        if ($user->user_level == 'Employee') {
            $find = ' visible_to = \'Owner\' AND ';
            $document = new Document();
            $hiddenDocumentTypes = $document->Find(
                "share_with_employee = ?",
                ['No']
            );

            $hiddenTypeIds = [];
            foreach ($hiddenDocumentTypes as $hiddenDocumentType) {
                $hiddenTypeIds[] = $hiddenDocumentType->id;
            }

            if(count($hiddenTypeIds) > 0) {
                $find .= ' document NOT IN (\''.implode('\',\'', $hiddenTypeIds).'\') AND ';
            }

            return parent::Find($find.$whereOrderBy, $bindarr, $pkeysArr, $extra);

        } else if ($user->user_level == 'Manager') {
            // Original $whereOrderBy already contain employee selection
            // So here if isSubOrdinates is true if the query coming from Employee -> Document Management
            // In that case we need to show documents from sub ordinates
            // These docs can can be owner and manager both
            if (isset($isSubOrdinates) && $isSubOrdinates) {
                $find .= ' visible_to in (\'Owner\', \'Manager\') AND ';
            } else {
                // Here we are showing the documents for the manager
                // If someone upload a document for this manager and make it visible to manager,
                // that means only the manager of this manager can see the document
                // So it should not be visible to this manager
                $find .= ' visible_to in (\'Owner\') AND ';
            }
        }

        return parent::Find($find.$whereOrderBy, $bindarr, $pkeysArr, $extra);
    }
    // @codingStandardsIgnoreEnd

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
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array("element","save","delete");
    }
    // @codingStandardsIgnoreStart
    public function Insert()
    {
        // @codingStandardsIgnoreEnd
        if (empty($this->date_added)) {
            $this->date_added = date("Y-m-d H:i:s");
        }
        return parent::Insert();
    }

    public function executePreSaveActions($obj)
    {
        $obj->expire_notification_last = -1;
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function executePreUpdateActions($obj)
    {
        $obj->expire_notification_last = -1;
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('employees', 'user'),
            new ModuleAccess('documents', 'admin'),
            new ModuleAccess('documents', 'user'),
        ];
    }
}
