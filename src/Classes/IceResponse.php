<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:50 AM
 */

namespace Classes;

class IceResponse
{

    const SUCCESS = "SUCCESS";
    const ERROR = "ERROR";

    public $status;
    public $data;
    public $code;

    public function __construct($status, $data = null, $code = null)
    {
        $this->status = $status;
        $this->data = $data;
        $this->code = $code;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getObject()
    {
        return $this->data;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getJsonArray()
    {
        return array("status"=>$this->status,"data"=>$this->data);
    }
}
