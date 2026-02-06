<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Classes\BaseService;
use Classes\PermissionManager;
use Projects\Common\Model\Client;
use Projects\Common\Model\Project;
use Projects\Common\Model\EmployeeProject;

$moduleName = 'projects';
$moduleGroup = 'admin';
define('MODULE_PATH', dirname(__FILE__));
include APP_BASE_PATH . 'header.php';
include APP_BASE_PATH . 'modulejslibs.inc.php';

?><div class="span9">


    <ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
        <li class="active"><a id="tabProject" href="#tabPageProject"><?= t('Projects') ?></a></li>
        <li><a id="tabClient" href="#tabPageClient"><?= t('Clients') ?></a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="tabPageProject">
            <div id="ProjectTableTop" class="reviewBlock"></div>
            <div id="ProjectTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="ProjectForm"></div>
            <div id="ProjectFilterForm"></div>
        </div>
        <div class="tab-pane" id="tabPageClient">
            <div id="ClientTableTop" class="reviewBlock"></div>
            <div id="ClientTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="ClientForm"></div>
            <div id="ClientFilterForm"></div>
        </div>
        <div class="tab-pane" id="tabPageEmployeeProject">
            <div id="EmployeeProjectTableTop" class="reviewBlock"></div>
            <div id="EmployeeProjectTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="EmployeeProjectForm"></div>
            <div id="EmployeeProjectFilterForm"></div>
        </div>
    </div>

</div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'customFields' => [
        'Project' => BaseService::getInstance()->getCustomFields("Project"),
        'EmployeeProject' => BaseService::getInstance()->getCustomFields("EmployeeProject"),
		'Client' => BaseService::getInstance()->getCustomFields("Client"),
    ],
    'permissions' => [
        'Project' => PermissionManager::checkGeneralAccess(new Project()),
        'EmployeeProject' => PermissionManager::checkGeneralAccess(new EmployeeProject()),
		'Client' => PermissionManager::checkGeneralAccess(new Client()),
    ]
];
?>
<script>
  var modJsList = [];
  var data = <?= json_encode($moduleData) ?>;
  modJsList['tabProject'] = new ProjectAdapter('Project', 'Project');
  modJsList.tabProject.setObjectTypeName('Project');
  modJsList.tabProject.setAccess(data.permissions.Project);
  modJsList.tabProject.setDataPipe(new IceDataPipe(modJsList.tabProject));
  modJsList.tabProject.setRemoteTable(true);
  modJsList.tabProject.setCustomFields(data.customFields.Project);

  modJsList['tabEmployeeProject'] = new EmployeeProjectAdapter('EmployeeProject', 'EmployeeProject');
  modJsList.tabEmployeeProject.setObjectTypeName('Employee Project');
  modJsList.tabEmployeeProject.setAccess(data.permissions.EmployeeProject);
  modJsList.tabEmployeeProject.setDataPipe(new IceDataPipe(modJsList.tabEmployeeProject));
  modJsList.tabEmployeeProject.setRemoteTable(true);

  modJsList['tabClient'] = new ClientAdapter('Client');
  modJsList.tabClient.setObjectTypeName('Client');
  modJsList.tabClient.setAccess(data.permissions.Client);
  modJsList.tabClient.setDataPipe(new IceDataPipe(modJsList.tabClient));
  modJsList.tabClient.setRemoteTable(true);
  modJsList.tabClient.setCustomFields(data.customFields.Client);

  var modJs = modJsList['tabProject'];

</script>
<?php include APP_BASE_PATH . 'footer.php'; ?>
