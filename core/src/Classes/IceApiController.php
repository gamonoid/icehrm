<?php

namespace Classes;

use Exception;

class IceApiController extends RestEndPoint
{
    const GET       = 'get';
    const POST      = 'post';
    const PUT       = 'put';
    const DELETE    = 'delete';
    const OPTIONS   = 'options';
    const HEAD      = 'head';

    /**
     * @param $path
     * @param $httpMethod
     * @param $callback
     * @throws Exception
     */
    public static function register($path, $httpMethod, $callback)
    {

        if ($httpMethod === self::GET) {
            Macaw::get(REST_API_PATH.$path, $callback);
        } elseif ($httpMethod === self::POST) {
            Macaw::post(REST_API_PATH.$path, $callback);
        } elseif ($httpMethod === self::PUT) {
            Macaw::put(REST_API_PATH.$path, $callback);
        } elseif ($httpMethod === self::DELETE) {
            Macaw::options(REST_API_PATH.$path, $callback);
        } elseif ($httpMethod === self::OPTIONS) {
            Macaw::options(REST_API_PATH.$path, $callback);
        } elseif ($httpMethod === self::HEAD) {
            Macaw::head(REST_API_PATH.$path, $callback);
        } else {
            throw new Exception('HTTP method '.$httpMethod.' is not a supported!');
        }
    }
}
