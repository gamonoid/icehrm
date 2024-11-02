<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 8:25 AM
 */

namespace Documents\Common\Model;

use Classes\BaseService;
use Classes\FileService;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Employees\Common\Model\Employee;
use Model\BaseModel;

class CompanyDocument extends BaseModel
{
    public $table = 'CompanyDocuments';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('documents', 'admin'),
            new ModuleAccess('documents', 'user'),
        ];
    }

    public function getFinder()
    {
        return new CompanyDocumentFinderProxy();
    }

    public function executePreSaveActions($obj)
    {
        $obj->expire_notification_last = -1;
        if (empty($obj->visible_to)) {
            $obj->visible_to = 'Owner';
        }

        $shareDepartments = $obj->share_departments ? json_decode($obj->share_departments, true) : [];
        $shareEmployees = $obj->share_employees ? json_decode($obj->share_employees, true) : [];

        if (count($shareEmployees) > 0 && count($shareDepartments) > 0) {
            return new IceResponse(IceResponse::ERROR, "You can't share a document with both employees and departments");
        }

        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function executePreUpdateActions($obj)
    {
        $obj->expire_notification_last = -1;

        $shareDepartments = $obj->share_departments ? json_decode($obj->share_departments, true) : [];
        $shareEmployees = $obj->share_employees ? json_decode($obj->share_employees, true) : [];

        if (count($shareEmployees) > 0 && count($shareDepartments) > 0) {
            return new IceResponse(IceResponse::ERROR, "You can't share a document with both employees and departments");
        }

        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function postProcessGetElement($obj)
    {
        if ($this->isJson($obj->details)) {
            $obj->details = $this->jsonToHtml($obj->details);
        }

        return $obj;
    }

    protected function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function jsonToHtml($jsonStr)
    {
        $obj = json_decode($jsonStr);

        if (empty($obj)) {
            return $jsonStr;
        }

        if (empty($obj->blocks)) {
            return $jsonStr;
        }

        $html = '';
        foreach ($obj->blocks as $block) {
            switch ($block->type) {
                case 'paragraph':
                    $html .= '<p>' . $block->data->text . '</p>';
                    break;

                case 'header':
                    $html .= '<h' . $block->data->level . '>' . $block->data->text . '</h' . $block->data->level . '>';
                    break;

                case 'raw':
                    $html .= $block->data->html;
                    break;

                case 'list':
                    $lsType = ($block->data->style == 'ordered') ? 'ol' : 'ul';
                    $html .= '<' . $lsType . '>';
                    foreach ($block->data->items as $item) {
                        $html .= '<li>' . $item . '</li>';
                    }
                    $html .= '</' . $lsType . '>';
                    break;

                default:
                    break;
            }
        }

        return $html;
    }

}
