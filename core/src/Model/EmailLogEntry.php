<?php

namespace Model;

class EmailLogEntry extends BaseModel
{
    public $table = 'EmailLog';

	public function postProcessGetElement($obj)
	{
		$obj->body = base64_encode($obj->body);
		return $obj;
	}
}
