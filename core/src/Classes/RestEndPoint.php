<?php
namespace Classes;

use Classes\Data\DataReader;
use Classes\Data\Query\DataQuery;
use Classes\Upload\Uploader;
use Employees\Common\Model\Employee;
use Model\BaseModel;
use Users\Common\Model\User;
use Utils\SessionUtils;

class RestEndPoint
{
    /*
    200
    GET/PUT
    Response entity details or list of entities.

    201
    POST
    To create a new entity.

    400
    GET/POST/PUT/DELETE
    Request payload and query params validation error

    401
    GET/POST/PUT/DELETE
    Authentication error

    403
    GET/POST/PUT/DELETE
    Authorization issue

    404
    GET/POST/PUT/DELETE
    We don’t have the endpoint

     */
    const RESPONSE_ERR_ENTITY_NOT_FOUND = 'Entity not found';
    const RESPONSE_ERR_PERMISSION_DENIED = 'Permission denied';
    const RESPONSE_ERR_UNPROCESSABLE = 'Unprocessable Entity';
    const RESPONSE_ERR_EMPLOYEE_NOT_FOUND = 'Employee not found';

    const DEFAULT_LIMIT = 50;

    const ELEMENT_NAME = '';

    protected $cachedObjects = [];

    public function getModelObject($id)
    {
        return false;
    }

    public function checkBasicPermissions($user, $employeeId)
    {
        if (!isset($employeeId)) {
            return new IceResponse(IceResponse::ERROR, self::RESPONSE_ERR_UNPROCESSABLE, 422);
        }

        $employee = new Employee();
        $employee->Load("id = ?", array($employeeId));
        if (empty($employee->id) || $employee->id != $employeeId) {
            return new IceResponse(IceResponse::ERROR, self::RESPONSE_ERR_EMPLOYEE_NOT_FOUND, 422);
        }

        if ($user->user_level !== 'Admin') {
            if ($user->user_level === 'Manager') {
                if (!PermissionManager::manipulationAllowed(
                    BaseService::getInstance()->getCurrentProfileId(),
                    $employee
                )
                ) {
                    return new IceResponse(IceResponse::ERROR, self::RESPONSE_ERR_PERMISSION_DENIED, 403);
                }
            } elseif ($user->user_level === 'Employee'
                && $employeeId != BaseService::getInstance()->getCurrentProfileId()) {
                return new IceResponse(IceResponse::ERROR, self::RESPONSE_ERR_PERMISSION_DENIED, 403);
            } elseif ($user->user_level !== 'Employee' &&  $user->user_level !== 'Manager') {
                return new IceResponse(IceResponse::ERROR, self::RESPONSE_ERR_PERMISSION_DENIED, 403);
            }
        }

        return new IceResponse(IceResponse::SUCCESS);
    }

    public function process($type, $parameters = [], $requireAccessToken = true)
    {
        if ($parameters === null) {
            $parameters = [];
        }

        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        if ($requireAccessToken) {
            $accessTokenValidation = $this->validateAccessToken();
            if (!empty($accessTokenValidation) && $accessTokenValidation->getStatus() == IceResponse::ERROR) {
                $resp = $accessTokenValidation;

                return $this->sendResponse($resp);
            }

            BaseService::getInstance()->setCurrentUser($accessTokenValidation->getData());
            SessionUtils::saveSessionObject('user', $accessTokenValidation->getData());
            array_unshift($parameters, $accessTokenValidation->getData());
        } else {
            array_unshift($parameters, new User());
        }

        $resp = call_user_func_array(array($this, $type), $parameters);

        return $this->sendResponse($resp);
    }

    protected function sendResponse($resp)
    {
        header('Content-Type: application/json');

        if ($resp->getStatus() == IceResponse::SUCCESS && $resp->getCode() == null) {
            header('Content-Type: application/json');
            http_response_code(200);
            $this->printResponse($resp->getObject());
        } elseif ($resp->getStatus() == IceResponse::SUCCESS) {
            header('Content-Type: application/json');
            http_response_code($resp->getCode());
            $this->printResponse($resp->getObject());
        } else {
            header('Content-Type: application/json');
            http_response_code($resp->getCode());
            $messages = array();
            $messages[] = array(
                "code" => $resp->getCode(),
                "message" => $resp->getObject()
            );
            $this->printResponse(array("error" => [$messages]));
        }

        return true;
    }

    /**
     * @param BaseModel $obj
     * @param $map
     * @return mixed
     */
    protected function enrichElement($obj, $map)
    {
        if (empty($map)) {
            return $obj;
        }

        foreach ($map as $k => $v) {
            $fTable = BaseService::getInstance()->getFullQualifiedModelClassName($v[0]);
            $tObj = new $fTable();
            $tObjArr = $tObj->Find($v[1] . "= ?", [$obj->$k], true);
            if (!is_array($tObjArr) || empty($tObjArr[0])) {
                continue;
            }
            $obj->$k = [
                'type' => $v[0],
                $v[1] => $obj->$k,
                'display' => $this->getCombinedValue($v[2], $tObjArr[0])
            ];
        }

        return $obj;
    }

    protected function enrichElements($items, $map)
    {
        return array_map(function ($item) use ($map) {
            return $this->enrichElement($item, $map);
        }, $items);
    }

    protected function getCombinedValue($nameField, $targetObject)
    {
        if (is_string($nameField)) {
            $values = explode("+", $nameField);
            if (count($values) == 1) {
                return $targetObject->{$nameField};
            }
            $objVal = '';
            foreach ($values as $value) {
                if ($objVal != "") {
                    $objVal .= " ";
                }
                if (substr($value, 0, 1) !== ':') {
                    $objVal .= $targetObject->{$value};
                } else {
                    $objVal .= substr($value, 1);
                }
            }

            return $objVal;
        } elseif (is_array($nameField)) {
            $objVal = [];
            foreach ($nameField as $value) {
                $objVal[$value] = $targetObject->{$value};
            }

            return $objVal;
        }

        return null;
    }

    protected function cleanObject($obj)
    {
        $obj = BaseService::getInstance()->cleanUpAdoDB($obj);
        unset($obj->keysToIgnore);
        unset($obj->historyFieldsToTrack);
        unset($obj->historyUpdateList);
        unset($obj->oldObjOrig);
        unset($obj->oldObj);
        unset($obj->_org);

        return $obj;
    }

    protected function removeNullFields($obj)
    {
        foreach ($obj as $k => $v) {
            if ($obj->$k === null) {
                unset($obj->$k);
            }
        }

        return $obj;
    }

    public function listAll(User $user, $parameter = null)
    {
        return new IceResponse(IceResponse::ERROR, "Method not Implemented", 404);
    }

    protected function listByQuery(DataQuery $query)
    {
        $page = 1;
        if (isset($_GET['page']) && intval($_GET['page']) > 0) {
            $page = intval($_GET['page']);
        }

        if (!$query->isLengthSet()) {
            $limit = static::DEFAULT_LIMIT;
            if (isset($_GET['limit']) && intval($_GET['limit']) > 0) {
                $limit = intval($_GET['limit']);
            }
        } else {
            $limit = $query->getLength();
        }

        $query->setStartPage(($page - 1) * $limit);
        $query->setLength($limit);

        $data = DataReader::getData($query);
        $output = array();
        $columns = $query->getColumns();
        foreach ($data as $item) {
            if (!empty($query->getFieldMapping())) {
                $map = json_decode($query->getFieldMapping(), true);
                $item = $this->enrichElement($item, $map);
            }
            if (!empty($columns)) {
                $obj = new \stdClass();
                foreach ($columns as $column) {
                    $obj->$column = $item->$column;
                }
            } else {
                $obj = $this->cleanObject($item);
            }
            $output[] = $obj;
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'data' => $output,
                'nextPage' => $page + 1,
            ]
        );
    }

    protected function listData(
        $object,
        $limit,
        $page = 1,
        $fieldsToRemove = array(),
        $customQuery = null,
        $params = []
    ) {
        if (!isset($limit) || $limit <= 0) {
            $limit = self::DEFAULT_LIMIT;
        }

        if ($customQuery) {
            $query = $customQuery.' order by id limit ?,?';
            $params[] = ($page - 1) * $limit;
            $params[] = $limit + 1;
        } else {
            $query = '1=1 order by id limit ?,?';
            $params = array(($page - 1) * $limit, ($limit + 1));
        }

        $allObjects = $object->Find($query, $params);
        $count = 0;
        $hasMore = 0;
        $newObjects = array();
        foreach ($allObjects as $object) {
            $object = $this->cleanObject($object);
            $object = $this->removeNullFields($object);
            if (!empty($fieldsToRemove)) {
                foreach ($fieldsToRemove as $field) {
                    unset($object->$field);
                }
            }
            $newObjects[] = $object;
            $count++;
            if ($count === $limit) {
                $hasMore = true;
                break;
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'data' => $newObjects,
                'prevPage' => ($page > 1) ? $page - 1 : '',
                'nextPage' => $hasMore ? $page + 1 : '',
                'limit' => $limit,
            ]
        );
    }

    public function get(User $user, $parameter)
    {
        return new IceResponse(IceResponse::ERROR, "Method not Implemented", 404);
    }

    public function post(User $user)
    {
        return new IceResponse(IceResponse::ERROR, "Method not Implemented", 404);
    }

    public function put(User $user, $parameter)
    {
        return new IceResponse(IceResponse::ERROR, "Method not Implemented", 404);
    }

    public function delete(User $user, $parameter)
    {
        if ($user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, "Permission denied", 403);
        }

        $response = BaseService::getInstance()->deleteElement(
            static::ELEMENT_NAME,
            $parameter
        );
        if ($response->getStatus() === IceResponse::SUCCESS) {
            return new IceResponse(IceResponse::SUCCESS, ['id' => $parameter], 200);
        }
        return new IceResponse(IceResponse::ERROR, $response->getData(), 400);
    }

    public function validateAccessToken()
    {
        $accessTokenValidation = RestApiManager::getInstance()->validateAccessToken($this->getBearerToken());

        return $accessTokenValidation;
    }

    public function printResponse($response)
    {
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    /**
     * Get hearder Authorization
     * */
    private function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions
            // (a nice side-effect of this fix means we don't care about capitalization
            // for Authorization)
            $requestHeaders = array_combine(
                array_map(
                    'ucwords',
                    array_keys($requestHeaders)
                ),
                array_values($requestHeaders)
            );
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
    /**
     * get access token from header
     * */
    private function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();
        $token = '';
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                $token = $matches[1];
            }
        } else {
            $token = $_GET['token'];
        }

        $tokenService = new JwtTokenService();
        $token = $tokenService->getBaseToken($token);

        return $token;
    }

    protected function getRequestBody()
    {
        $inputJSON = file_get_contents('php://input');
        return json_decode($inputJSON, true);
    }

    protected function getFile()
    {
        return $_FILES;
    }

    public function uploadFile(User $user)
    {
        $fileData = $this->getFile();
        $postData = [
            'file_name' => '_NEW_',
            'user' => $user->employee,
            'file_group' => static::ELEMENT_NAME
        ];

        $fileResponse = Uploader::upload($postData, $fileData);
        if ($fileResponse->getStatus() === IceResponse::SUCCESS) {
            return new IceResponse(IceResponse::SUCCESS, ['data' => $fileResponse->getData()], 201);
        }

        return $fileResponse;
    }
}
