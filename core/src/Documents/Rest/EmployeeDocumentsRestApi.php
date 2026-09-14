<?php
namespace Documents\Rest;

use Classes\BaseService;
use Classes\Data\Query\DataQuery;
use Classes\Data\Query\Filter;
use Classes\FileService;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Documents\Common\Model\EmployeeDocument;
use Qualifications\Common\Model\EmployeeSkill;
use Users\Common\Model\User;
use Utils\LogManager;

class EmployeeDocumentsRestApi extends RestEndPoint
{
    const ELEMENT_NAME = 'EmployeeDocument';
    public function getModelObject($id)
    {
        $obj = new EmployeeDocument();
        $obj->Load("id = ?", array($id));
        return $obj;
    }

    public function listAll(User $user, $parameter = null)
    {
        if ($user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, "Permission denied - only Admin users are allowed to use this endpoint", 403);
        }

        $query = new DataQuery('EmployeeDocument');
        $query->addFilter(new Filter('employee', $parameter));
        $mapping = <<<JSON
{
  "employee": [ "Employee", "id", "first_name+last_name" ],
  "document": [ "Document", "id", "name" ]
}
JSON;
        $query->setFieldMapping($mapping);

        $limit = self::DEFAULT_LIMIT;
        if (isset($_GET['limit']) && intval($_GET['limit']) > 0) {
            $limit = intval($_GET['limit']);
        }
        $query->setLength($limit);

        if (!empty($_GET['filters'])) {
            $query->setFilters($_GET['filters']);
        }

        if (isset($_GET['sortField']) && !empty($_GET['sortField'])) {
            $query->setSortColumn($_GET['sortField']);
            $query->setSortingEnabled(true);
            $query->setSortOrder(
                empty($_GET['sortOrder']) || $_GET['sortOrder'] === 'ascend' ? 'ASC' : 'DESC'
            );
        }

        $response = $this->listByQuery($query);
        $data = $response->getData();
        $filteredData = [];
        foreach ($data['data'] as $value) {
            unset($value->details);
            unset($value->expire_notification_last);
            unset($value->signature);
            $filteredData[] = $value;
        }
        $data['data'] = $filteredData;
        $response->setData($data);

        return $response;
    }

    public function get(User $user, $parameter)
    {
        if ($user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, "Permission denied - only Admin users are allowed to use this endpoint", 403);
        }

        if (empty($parameter)) {
            return new IceResponse(IceResponse::ERROR, "Document ID not found", 400);
        }

        $mapping = <<<JSON
{
  "employee": [ "Employee", "id", "first_name+last_name" ],
  "document": [ "Document", "id", "name" ]
}
JSON;
        $document = BaseService::getInstance()->getElement(
            self::ELEMENT_NAME,
            $parameter,
            null,
            true
        );

        $document = $this->enrichElement($document, json_decode($mapping, true));

        if (!empty($document)) {
            $document = $this->cleanObject($document);
            $document = $this->removeNullFields($document);
            return new IceResponse(IceResponse::SUCCESS, $document);
        }

        return new IceResponse(IceResponse::ERROR, "Document not found", 404);
    }

    public function getDocumentFile(User $user, $parameter)
    {
        if ($user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, "Permission denied - only Admin users are allowed to use this endpoint", 403);
        }

        if (empty($parameter)) {
            return new IceResponse(IceResponse::ERROR, "Document ID not found", 400);
        }

        $document = BaseService::getInstance()->getElement(
            self::ELEMENT_NAME,
            $parameter,
            null,
            true
        );

        if (empty($document)) {
            return new IceResponse(IceResponse::ERROR, "Document not found", 404);
        }

        if (!empty($document->attachment)) {
            $url = FileService::getInstance()->getFileUrl($document->attachment, true);
        } else {
            return new IceResponse(IceResponse::ERROR, "Document has no attachment");
        }



        return new IceResponse(IceResponse::SUCCESS, ['document_id' => $document->id, 'url' => $url]);
    }

    /**
     * Admin: create an employee document.
     * Path: the target employee id. Body:
     *   { document?: <Document type id>, attachment?: <File name from uploadFile>,
     *     valid_until?: "YYYY-MM-DD", details?: "...", visible_to?: "Owner|Owner Only|Manager|Admin",
     *     status?: "Active|Inactive|Draft" }
     * Upload the file first via POST employees/documents/file-upload (multipart field `file`);
     * that returns the File name — pass it as `attachment`.
     */
    public function create(User $user, $employeeId)
    {
        if ($user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, 'Permission denied - admin only', 403);
        }
        if (is_array($employeeId)) {
            $employeeId = $employeeId[0] ?? null;
        }

        $employee = new \Employees\Common\Model\Employee();
        $employee->Load('id = ?', [$employeeId]);
        if (empty($employee->id) || $employee->id != $employeeId) {
            return new IceResponse(IceResponse::ERROR, 'Employee not found', 404);
        }

        $body = $this->getRequestBody();
        if (!is_array($body)) {
            $body = [];
        }

        $doc = new EmployeeDocument();
        $doc->employee = $employeeId;
        if (!empty($body['document'])) {
            $doc->document = $body['document'];
        }
        if (array_key_exists('attachment', $body)) {
            $doc->attachment = $body['attachment'] ?: null;
        }
        if (array_key_exists('details', $body)) {
            $doc->details = $body['details'];
        }
        if (!empty($body['valid_until'])) {
            $doc->valid_until = $body['valid_until'];
        }
        $doc->visible_to = $body['visible_to'] ?? 'Owner';
        $doc->status = $body['status'] ?? 'Active';

        if (!$doc->Save()) {
            LogManager::getInstance()->error('Error creating employee document: '.$doc->ErrorMsg());
            return new IceResponse(IceResponse::ERROR, 'Could not create document', 500);
        }

        return new IceResponse(IceResponse::SUCCESS, [
            'id' => $doc->id,
            'employee' => $doc->employee,
            'document' => $doc->document,
            'attachment' => $doc->attachment,
        ], 201);
    }

    /**
     * Admin: delete an employee document. Routed through BaseService::deleteElement so the
     * backing file (Files row + disk/S3 object) is cleaned up via the file-field mapping.
     */
    public function deleteDocument(User $user, $id)
    {
        if ($user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, 'Permission denied - admin only', 403);
        }
        if (is_array($id)) {
            $id = $id[0] ?? null;
        }

        $doc = new EmployeeDocument();
        $doc->Load('id = ?', [$id]);
        if (empty($doc->id) || $doc->id != $id) {
            return new IceResponse(IceResponse::ERROR, 'Document not found', 404);
        }

        $response = BaseService::getInstance()->deleteElement('EmployeeDocument', $id);
        if ($response->getStatus() !== IceResponse::SUCCESS) {
            return new IceResponse(IceResponse::ERROR, $response->getData(), 500);
        }
        return new IceResponse(IceResponse::SUCCESS, 'Document deleted');
    }
}
