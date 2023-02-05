<?php
namespace Employees\Rest;

use Classes\BaseService;
use Classes\Data\Query\DataQuery;
use Classes\Data\Query\Filter;
use Classes\IceResponse;
use Classes\PermissionManager;
use Classes\RestEndPoint;
use Employees\Common\Model\Employee;
use Employees\Common\Model\EmployeeStatus;
use Users\Common\Model\User;
use Utils\CalendarTools;
use Utils\LogManager;
use Utils\NetworkUtils;

class EmployeeRestEndPoint extends RestEndPoint
{
    const ELEMENT_NAME = 'Employee';

    public function getModelObject($id)
    {
        $obj = new Employee();
        $obj->Load("id = ?", array($id));
        return $obj;
    }

    public function listAll(User $user, $parameter = null)
    {
        $query = new DataQuery('Employee');
        $query->addFilter(new Filter('employee', $parameter));
        $mapping = <<<JSON
{
  "job_title": [ "JobTitle", "id", "name" ],
  "country": [ "Country", "code", "name" ],
  "province": [ "Province", "id", "name" ],
  "department": [ "CompanyStructure", "id", "title" ],
  "supervisor": [ "Employee", "id", "first_name+last_name" ]
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

        // https://csvjson.com/json_beautifier


        $mapping = <<<JSON
{
  "nationality": [ "Nationality", "id", "name" ],
  "ethnicity": [ "Ethnicity", "id", "name" ],
  "immigration_status": [ "ImmigrationStatus", "id", "name" ],
  "employment_status": [ "EmploymentStatus", "id", "name" ],
  "job_title": [ "JobTitle", "id", "name" ],
  "pay_grade": [ "PayGrade", "id", "name" ],
  "country": [ "Country", "code", "name" ],
  "province": [ "Province", "id", "name" ],
  "department": [ "CompanyStructure", "id", "title" ],
  "supervisor": [ "Employee", "id", "first_name+last_name" ],
  "indirect_supervisors": [ "Employee", "id", "first_name+last_name" ],
  "approver1": [ "Employee", "id", "first_name+last_name" ],
  "approver2": [ "Employee", "id", "first_name+last_name" ],
  "approver3": [ "Employee", "id", "first_name+last_name" ]
}
JSON;

        $emp = BaseService::getInstance()->getElement(
            self::ELEMENT_NAME,
            $parameter,
            null,
            true
        );

        $emp = $this->enrichElement($emp, json_decode($mapping, true));
        //Get User for the employee
        $user = new User();
        $user->Load('employee = ?', [$emp->id]);

        if (!empty($user->id)) {
            $emp->user_name = $user->username;
            $emp->user_email = $user->email;
            $emp->user_level = $user->user_level;
        }

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

        if ($user->user_level !== 'Admin' 
            && !PermissionManager::manipulationAllowed(
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

    public function getEmployeeStatusMessage(User $user, $parameter)
    {
        $date = CalendarTools::getServerDate();

        $employeeId = (int)$parameter;

        $employeeState = new EmployeeStatus();
        $employeeState->Load('employee = ? and status_date = ?', [ $employeeId, $date]);

        $data = $this->cleanObject($employeeState);
        unset($data->objectName);
        unset($data->id);
        unset($data->status_date);

        return new IceResponse(IceResponse::SUCCESS, $data, 200);
    }

    public function setEmployeeStatusMessage(User $user, $parameter)
    {
        $body = $this->getRequestBody();

        $employeeId = (int)$parameter;

        $permissionResponse = $this->checkBasicPermissions($user, $employeeId);
        if ($permissionResponse->getStatus() !== IceResponse::SUCCESS) {
            return $permissionResponse;
        }

        $date = CalendarTools::getServerDate();

        $employeeState = new EmployeeStatus();
        $employeeState->Load('employee = ? and status_date = ?', [ $employeeId, $date]);

        $employeeState->employee = $employeeId;
        $employeeState->status = $body['status'];
        $employeeState->feeling = $body['feeling'];
        $employeeState->message = $body['message'];
        $employeeState->status_date = $date;

        $employeeState->Save();

        $data = $this->cleanObject($employeeState);
        unset($data->objectName);
        unset($data->id);

        return new IceResponse(IceResponse::SUCCESS, $data, 200);
    }
}
