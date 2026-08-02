<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'training';
$moduleGroup = 'modules';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
        <li class="active"><a id="tabTrainingSession" href="#tabPageTrainingSession"><?=t('All Training Sessions')?></a></li>
        <li class=""><a id="tabEmployeeTrainingSession" href="#tabPageEmployeeTrainingSession"><?=t('My Training Sessions')?></a></li>
		<li class=""><a id="tabSubEmployeeTraining" href="#tabPageSubEmployeeTraining"><?=t('Training Sessions of Direct Reports')?></a></li>
        <li class=""><a id="tabCoordinatedTrainingSession" href="#tabPageCoordinatedTrainingSession"><?=t('Training Sessions of Coordinated by Me')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageTrainingSession">
			<div id="TrainingSession" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="TrainingSessionForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
        <div class="tab-pane" id="tabPageEmployeeTrainingSession">
            <div id="EmployeeTrainingSession" class="reviewBlock" data-content="List" style="padding-left:5px;">

            </div>
            <div id="EmployeeTrainingSessionForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

            </div>
        </div>
		<div class="tab-pane" id="tabPageSubEmployeeTraining">
			<div id="SubEmployeeTraining" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="SubEmployeeTrainingForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
		<div class="tab-pane" id="tabPageCoordinatedTrainingSession">
			<div id="CoordinatedTrainingSession" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="CoordinatedTrainingSessionForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>

	</div>

</div>
<?php
use Classes\PermissionManager;
use Training\Common\Model\CoordinatedTrainingSession;
use Training\Common\Model\EmployeeTrainingSession;
use Training\Common\Model\TrainingSessionWithCourse;

$moduleData = [
    'user_level' => $user->user_level,
    'permissions' => [
        'TrainingSessionWithCourse' => PermissionManager::checkGeneralAccess(new TrainingSessionWithCourse()),
        'EmployeeTrainingSession' => PermissionManager::checkGeneralAccess(new EmployeeTrainingSession()),
        'CoordinatedTrainingSession' => PermissionManager::checkGeneralAccess(new CoordinatedTrainingSession()),
    ]
];
?>
<script>
initUserTraining(<?=json_encode($moduleData)?>);
</script>
<?php include APP_BASE_PATH.'footer.php';?>
