<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 8:25 AM
 */

namespace Documents\Common\Model;

use Classes\BaseService;
use Classes\Editor\DeleteEditorContent;
use Classes\FileService;
use Classes\IceResponse;
use Classes\ModuleAccess;
use EditorUser\EditorService;
use Employees\Common\Model\Employee;
use Model\BaseModel;

class CompanyDocument extends BaseModel
{
    use DeleteEditorContent;

    public $table = 'CompanyDocuments';

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element");
    }

    public function getUserAccess()
    {
        // Employees keep the "Company Documents" tab (list + attachment download),
        // but may no longer element-read arbitrary rows by id.
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array();
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
        $obj = $this->attachDocumentLink($obj);

        return $obj;
    }

    public function postProcessGetData($obj)
    {
        return $this->attachDocumentLink($obj);
    }

    /**
     * Attach the editor-extension "content" document link so the SPA card can
     * open the rich content. The link is view-only for anyone without 'save'
     * access (see getEditorPermissions) — i.e. only admins can edit.
     */
    protected function attachDocumentLink($obj)
    {
        if (!class_exists('\EditorUser\EditorService')) {
            return $obj;
        }
        $obj->document_link = EditorService::getDocumentLink(
            $obj->id,
            'CompanyDocument',
            'document_link',
            $obj,
            'admin_Manage'
        );
        return $obj;
    }

    /**
     * Editor content permissions: only admins may edit; everyone else who can
     * reach the document (eligible managers/employees) views it read-only.
     */
    public function getEditorPermissions()
    {
        $user = BaseService::getInstance()->getCurrentUser();
        if (!empty($user) && $user->user_level === 'Admin') {
            return ['edit'];
        }
        return ['view'];
    }

    /**
     * Sidebar data for the editor "content" document — the company document's
     * name and description (shown instead of the generic Entity/Identifier/Field).
     */
    public function getEditorSideBarObject($mode)
    {
        $obj = new \stdClass();
        $obj->name = $this->name;
        $obj->details = $this->details;
        return $obj;
    }

    public function getEditorDraftContent()
    {
        return sprintf(
            '{"blocks":[{"type":"header","data":{"text":%s,"level":1}},'
            . '{"type":"paragraph","data":{"text":"Add the document content here."}}]}',
            json_encode((string) $this->name)
        );
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
