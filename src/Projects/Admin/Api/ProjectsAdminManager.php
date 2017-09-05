<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 5:56 PM
 */

namespace Projects\Admin\Api;

use Classes\AbstractModuleManager;
use Classes\UIManager;
use Projects\Common\Model\Project;

class ProjectsAdminManager extends AbstractModuleManager
{

    public function initializeUserClasses()
    {
    }

    public function initializeFieldMappings()
    {
    }

    public function initializeDatabaseErrorMappings()
    {
        $this->addDatabaseErrorMapping("key 'EmployeeProjectsKey'", "Employee already added to this project");
    }

    public function setupModuleClassDefinitions()
    {

        $this->addModelClass('Client');
        $this->addModelClass('Project');
    }

    public function getDashboardItemData()
    {
        $data = array();
        $project = new Project();
        $data['numberOfProjects'] = $project->Count("status = 'Active'");
        return $data;
    }

    public function initQuickAccessMenu()
    {
        UIManager::getInstance()->addQuickAccessMenuItem(
            "Manage Client/Projects",
            "fa-list-alt",
            CLIENT_BASE_URL."?g=admin&n=projects&m=admin_Admin",
            array("Admin","Manager")
        );
    }
}
