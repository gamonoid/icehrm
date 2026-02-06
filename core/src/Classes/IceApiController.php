<?php

namespace Classes;

use Classes\Data\DataReader;
use Classes\Data\Query\DataQuery;
use Exception;
use Model\BaseModel;

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
            Macaw::delete(REST_API_PATH.$path, $callback);
        } elseif ($httpMethod === self::OPTIONS) {
            Macaw::options(REST_API_PATH.$path, $callback);
        } elseif ($httpMethod === self::HEAD) {
            Macaw::head(REST_API_PATH.$path, $callback);
        } else {
            throw new Exception('HTTP method '.$httpMethod.' is not a supported!');
        }
    }

    public function listByQuery(DataQuery $query, $page = 1, $limit = 15)
    {
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
}
