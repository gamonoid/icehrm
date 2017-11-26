<?php
namespace Employees\Rest;

use Classes\BaseService;
use Classes\Data\Query\DataQuery;
use Classes\IceResponse;
use Classes\PermissionManager;
use Classes\RestEndPoint;
use Employees\Common\Model\Employee;
use Users\Common\Model\User;

class EmployeeRestEndPoint extends RestEndPoint
{
    const ELEMENT_NAME = 'Employee';

    public function getModelObject($id)
    {
        $obj = new Employee();
        $obj->Load("id = ?", array($id));
        return $obj;
    }

    public function listAll(User $user)
    {
        $query = new DataQuery('Employee');

        $limit = self::DEFAULT_LIMIT;
        if (isset($_GET['limit']) && intval($_GET['limit']) > 0) {
            $limit = intval($_GET['limit']);
        }
        $query->setLength($limit);

        if ($user->user_level !== 'Admin') {
            $query->setIsSubOrdinates(true);
        }

        return $this->listByQuery($query);
    }

    public function get(User $user, $parameter)
    {
        if (empty($parameter)) {
            return new IceResponse(IceResponse::ERROR, "Employee not found", 404);
        }

        if ($parameter === 'me') {
            $parameter = BaseService::getInstance()->getCurrentProfileId();
        }

        if ($user->user_level !== 'Admin' && !PermissionManager::manipulationAllowed(
            BaseService::getInstance()->getCurrentProfileId(),
            $this->getModelObject($parameter)
        )
        ) {
            return new IceResponse(IceResponse::ERROR, "Permission denied", 403);
        }

        $mapping = [
            "nationality" => ["Nationality","id","name"],
            "ethnicity" => ["Ethnicity","id","name"],
            "immigration_status" => ["ImmigrationStatus","id","name"],
            "employment_status" => ["EmploymentStatus","id","name"],
            "job_title" => ["JobTitle","id","name"],
            "pay_grade" => ["PayGrade","id","name"],
            "country" => ["Country","code","name"],
            "province" => ["Province","id","name"],
            "department" => ["CompanyStructure","id","title"],
            "supervisor" => [self::ELEMENT_NAME,"id","first_name+last_name"],
        ];

        $emp = BaseService::getInstance()->getElement(
            self::ELEMENT_NAME,
            $parameter,
            json_encode($mapping),
            true
        );

        $emp = $this->enrichElement($emp, $mapping);
        if (!empty($emp)) {
            $emp = $this->cleanObject($emp);
            $emp = $this->removeNullFields($emp);
            return new IceResponse(IceResponse::SUCCESS, $emp);
        }
        return new IceResponse(IceResponse::ERROR, "Employee not found", 404);
    }

    public function post(User $user)
    {
        if ($user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, "Permission denied", 403);
        }
        $body = $this->getRequestBody();
        $response = BaseService::getInstance()->addElement(self::ELEMENT_NAME, $body);
        if ($response->getStatus() === IceResponse::SUCCESS) {
            $response = $this->get($user, $response->getData()->id);
            $response->setCode(201);
            return $response;
        }

        return new IceResponse(IceResponse::ERROR, $response->getData(), 400);
    }

    public function put(User $user, $parameter)
    {

        if ($user->user_level !== 'Admin' &&
            !PermissionManager::manipulationAllowed(
                BaseService::getInstance()->getCurrentProfileId(),
                $this->getModelObject($parameter)
            )
        ) {
            return new IceResponse(IceResponse::ERROR, "Permission denied", 403);
        }

        $body = $this->getRequestBody();
        $body['id'] = $parameter;
        $response = BaseService::getInstance()->addElement(self::ELEMENT_NAME, $body);
        if ($response->getStatus() === IceResponse::SUCCESS) {
            return $this->get($user, $response->getData()->id);
        }

        return new IceResponse(IceResponse::ERROR, 'Error modifying employee', 400);
    }

    public function delete(User $user, $parameter)
    {
        if ($user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, "Permission denied", 403);
        }

        $response = BaseService::getInstance()->deleteElement(
            self::ELEMENT_NAME,
            $parameter
        );
        if ($response->getStatus() === IceResponse::SUCCESS) {
            return new IceResponse(IceResponse::SUCCESS, ['id' => $parameter], 200);
        }
        return new IceResponse(IceResponse::ERROR, $response->getData(), 400);
    }
}
