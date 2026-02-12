<?php
use Classes\BaseService;
use Classes\PermissionManager;
use Training\Common\Model\Course;
use Training\Common\Model\EmployeeTrainingSession;
use Training\Common\Model\TrainingSession;

$moduleName = 'training';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
$user = BaseService::getInstance()->getCurrentUser();
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabCourse" href="#tabPageCourse"><?=t('Training Courses')?></a></li>
		<li class=""><a id="tabTrainingSession" href="#tabPageTrainingSession"><?=t('Training Sessions')?></a></li>
        <li class=""><a id="tabEmployeeTrainingSession" href="#tabPageEmployeeTrainingSession"><?=t('Employee Training Sessions')?></a></li>
    </ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageCourse">
			<div id="CourseTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="CourseForm"></div>
            <div id="CourseFilterForm"></div>
		</div>
		<div class="tab-pane" id="tabPageTrainingSession">
			<div id="TrainingSessionTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="TrainingSessionForm"></div>
            <div id="TrainingSessionFilterForm"></div>
		</div>
		<div class="tab-pane" id="tabPageEmployeeTrainingSession">
			<div id="EmployeeTrainingSessionTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="EmployeeTrainingSessionForm"></div>
            <div id="EmployeeTrainingSessionFilterForm"></div>
		</div>
	</div>

</div>
<div id="dataGroup"></div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'permissions' => [
        'Course' => PermissionManager::checkGeneralAccess(new Course()),
        'TrainingSession' => PermissionManager::checkGeneralAccess(new TrainingSession()),
        'EmployeeTrainingSession' => PermissionManager::checkGeneralAccess(new EmployeeTrainingSession()),
    ]
];
?>
<script>
  initAdminTraining(<?=json_encode($moduleData)?>);
</script>
<?php include APP_BASE_PATH.'footer.php';?>
