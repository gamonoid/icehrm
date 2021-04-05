<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Classes\PermissionManager;
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
        <li><a id="tabEmployeeProject" href="#tabPageEmployeeProject"><?= t('Employee Projects') ?></a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="tabPageProject">
            <div id="ProjectTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="ProjectForm"></div>
            <div id="ProjectFilterForm"></div>
        </div>
        <div class="tab-pane" id="tabPageEmployeeProject">
            <div id="EmployeeProjectTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="EmployeeProjectForm"></div>
            <div id="EmployeeProjectFilterForm"></div>
        </div>
    </div>

</div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'permissions' => [
        'Project' => PermissionManager::checkGeneralAccess(new Project()),
        'EmployeeProject' => PermissionManager::checkGeneralAccess(new EmployeeProject()),
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

  modJsList['tabEmployeeProject'] = new EmployeeProjectAdapter('EmployeeProject', 'EmployeeProject');


  modJsList.tabEmployeeProject.setObjectTypeName('Employee Project');
  modJsList.tabEmployeeProject.setAccess(data.permissions.EmployeeProject);
  modJsList.tabEmployeeProject.setDataPipe(new IceDataPipe(modJsList.tabEmployeeProject));
  modJsList.tabEmployeeProject.setRemoteTable(true);

  var modJs = modJsList['tabProject'];

</script>
<?php include APP_BASE_PATH . 'footer.php'; ?>
