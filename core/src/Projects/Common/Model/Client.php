<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 5:53 PM
 */

namespace Projects\Common\Model;

use Classes\ModuleAccess;
use EditorUser\EditorService;
use Model\BaseModel;

class Client extends BaseModel
{
    public $table = 'Clients';
    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('clients', 'admin'),
        ];
    }

    public function isCustomFieldsEnabled()
    {
        return true;
    }

	public function postProcessGetData($entry)
	{
		if (!class_exists('\EditorUser\EditorService')) {
			return '';
		}
		$entry->document_link = EditorService::getDocumentLink($entry->id, 'Client', 'client_file', $entry, 'admin_Admin');
		return $entry;
	}

	public function getEditorDraftContent() {
		return sprintf('{
		   "blocks":[
			  {
				 "type":"header",
				 "data":{
					"text":"%s",
					"level":1
				 }
			  },
			  {
				 "type":"paragraph",
				 "data":{
					"text":"This document will store all the details related to the client"
				 }
			  }
		   ]
		}', $this->name);
	}
}
