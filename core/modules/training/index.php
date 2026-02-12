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
<script>
var modJsList = new Array();

modJsList['tabEmployeeTrainingSession'] = new EmployeeTrainingSessionAdapter('EmployeeTrainingSession','EmployeeTrainingSession');
modJsList['tabEmployeeTrainingSession'].setShowAddNew(false);
modJsList['tabEmployeeTrainingSession'].setRemoteTable(true);


modJsList['tabTrainingSession'] = new TrainingSessionAdapter('TrainingSessionWithCourse','TrainingSession',{"attendanceType":"Sign Up","status":"Approved"});
modJsList['tabTrainingSession'].setShowAddNew(false);
modJsList['tabTrainingSession'].setShowFormOnPopup(true);
modJsList['tabTrainingSession'].setRemoteTable(true);
modJsList['tabTrainingSession'].setShowSave(false);


modJsList['tabSubEmployeeTraining'] = new SubEmployeeTrainingSessionAdapter('EmployeeTrainingSession','SubEmployeeTraining');
modJsList['tabSubEmployeeTraining'].setShowAddNew(false);
modJsList['tabSubEmployeeTraining'].setRemoteTable(true);

modJsList['tabCoordinatedTrainingSession'] = new CoordinatedTrainingSessionAdapter('CoordinatedTrainingSession','CoordinatedTrainingSession');
modJsList['tabCoordinatedTrainingSession'].setRemoteTable(false);
modJsList['tabCoordinatedTrainingSession'].setShowSave(true);
modJsList['tabCoordinatedTrainingSession'].setShowDelete(false);
modJsList['tabCoordinatedTrainingSession'].setShowAddNew(false);

var modJs = modJsList['tabTrainingSession'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
