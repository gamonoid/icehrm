<?php
namespace KudosAdmin;

use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;

class ApiController extends IceApiController
{
	public function registerEndPoints() {
        // REST Api get request
        self::register(
            REST_API_PATH . 'kudos/echo', self::GET, function ($pathParams = null) {
                (new RestEndPoint())->sendResponse(
                    new IceResponse(
                        IceResponse::SUCCESS,
                    'Hello from KudosAdmin extension'
                    )
                );
        });

        // REST Api get request with path parameters
        self::register(
            REST_API_PATH . 'kudos/echo/(:any)', self::GET, function ($parameter) {
                (new RestEndPoint())->sendResponse(
                    new IceResponse(
                        IceResponse::SUCCESS,
                        'Hello from KudosAdmin extension with data:'.$parameter
                    )
                );
        });

        // REST Api get request with two path parameters
        self::register(
            REST_API_PATH . 'kudos/echo/(:any)/(:num)', self::GET, function ($parameter1, $parameter2) {
                (new RestEndPoint())->sendResponse(
                    new IceResponse(
                        IceResponse::SUCCESS,
                        'Hello from KudosAdmin extension with parameter1:'.$parameter1.' and parameter2:'.$parameter2
                    )
                );
        });

        // REST Api post request
        self::register(
            REST_API_PATH . 'kudos/echo', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $restEndpoint->sendResponse(
                new IceResponse(
                    IceResponse::SUCCESS,
                    'Hello from KudosAdmin extension (POST request) with body:'.json_encode($data)
                )
            );
        });
	}
}

