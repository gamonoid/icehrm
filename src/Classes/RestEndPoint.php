<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:49 AM
 */

namespace Classes;

class RestEndPoint
{

    public function process($type, $parameter = null)
    {

        $accessTokenValidation = $this->validateAccessToken();
        if (!empty($accessTokenValidation) && $accessTokenValidation->getStatus() == IceResponse::ERROR) {
            $resp = $accessTokenValidation;
        } else {
            $resp = $this->$type($parameter);
        }

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
            $this->printResponse(array("error",[$messages]));
        }
    }

    public function get($parameter)
    {
        return new IceResponse(IceResponse::ERROR, "Method not Implemented", 404);
    }

    public function post($parameter)
    {
        return new IceResponse(IceResponse::ERROR, "Method not Implemented", 404);
    }

    public function put($parameter)
    {
        return new IceResponse(IceResponse::ERROR, "Method not Implemented", 404);
    }

    public function delete($parameter)
    {
        return new IceResponse(IceResponse::ERROR, "Method not Implemented", 404);
    }

    public function clearObject($obj)
    {
        return BaseService::getInstance()->cleanUpAdoDB($obj);
    }

    public function validateAccessToken()
    {
        $accessTokenValidation = RestApiManager::getInstance()->validateAccessToken($this->getBearerToken());

        return $accessTokenValidation;
    }

    public function cleanDBObject($obj)
    {
        unset($obj->keysToIgnore);
        return $obj;
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
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
