<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 5:54 PM
 */

namespace Projects\Common\Model;

use Classes\BaseService;
use Classes\Editor\DeleteEditorContent;
use Classes\ModuleAccess;
use Classes\SettingsManager;
use Projects\Common\Model\Client;
use EditorUser\EditorService;
use Model\BaseModel;

class Project extends BaseModel
{
	use DeleteEditorContent;
    public $table = 'Projects';
    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }

    public function getAllProjects()
    {
        $employeeProjects = [];
        $project = new Project();
        $projects = $project->Find("status = ?", 'Active');
        foreach ($projects as $project) {
            $client = new Client();
            $client->Load("id = ?", array($project->client));

            $project->name = $project->name." (".$client->name.")";
            $employeeProjects[] = $project;
        }
        return $employeeProjects;
    }

    public function getEmployeeProjects()
    {
        $allowAllProjects = SettingsManager::getInstance()->getSetting(
            "Projects: Make All Projects Available to Employees"
        );
        $employeeProjects = array();
        if ($allowAllProjects == 0) {
            $employeeProjectsTemp = new EmployeeProject();
            $employeeProjectsTemp = $employeeProjectsTemp->Find(
                "employee = ?",
                array(BaseService::getInstance()->getCurrentProfileId())
            );
            foreach ($employeeProjectsTemp as $p) {
                $project = new Project();
                $project->Load("id = ?", array($p->project));
                if ($project->status == 'Active') {
                    $client = new Client();
                    $client->Load("id = ?", array($project->client));

                    $project->name = $project->name." (".$client->name.")";
                    $employeeProjects[] = $project;
                }
            }
        } else {
            $project = new Project();
            $projects = $project->Find("status = ?", array('Active'));
            foreach ($projects as $project) {
                $client = new Client();
                $client->Load("id = ?", array($project->client));

                $project->name = $project->name." (".$client->name.")";
                $employeeProjects[] = $project;
            }
        }

        return $employeeProjects;
    }

    public function fieldValueMethods()
    {
        return ['getEmployeeProjects'];
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('projects', 'admin'),
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
		$entry->document_link = EditorService::getDocumentLink($entry->id, 'Project', 'project_document', $entry, 'admin_Admin');
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
					"text":"Start adding project details"
				 }
			  }
		   ]
		}', $this->name);
	}
}
