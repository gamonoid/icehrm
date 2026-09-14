<?php

namespace Model;

class EmailLogEntry extends BaseModel
{
    public $table = 'EmailLog';

    public function getUserAccess()
    {
        return array();
    }

    public function getUserOnlyMeAccess()
    {
        return array();
    }

	public function postProcessGetElement($obj)
	{
		$obj->body = base64_encode($obj->body);
		return $obj;
	}

    /**

     * No module grants Manager access to this model (module meta.json user_levels),

     * so no manager-facing screen reads it. The inherited BaseModel default

     * would expose the whole table on the generic service.php path.

     */

    public function getManagerAccess()

    {

        return array();

    }

}
