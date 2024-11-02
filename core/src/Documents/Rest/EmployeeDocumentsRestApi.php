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
}
