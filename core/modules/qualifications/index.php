<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'qualifications';
$moduleGroup = 'modules';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabEmployeeSkill" href="#tabPageEmployeeSkill"><?=t('Skills')?></a></li>
		<li><a id="tabEmployeeEducation" href="#tabPageEmployeeEducation"><?=t('Education')?></a></li>
		<li><a id="tabEmployeeCertification" href="#tabPageEmployeeCertification"><?=t('Certifications')?></a></li>
		<li><a id="tabEmployeeLanguage" href="#tabPageEmployeeLanguage"><?=t('Languages')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageEmployeeSkill">
			<div id="EmployeeSkillTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="EmployeeSkillForm" data-content="Form"></div>
		</div>
		<div class="tab-pane" id="tabPageEmployeeEducation">
			<div id="EmployeeEducationTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="EmployeeEducationForm" data-content="Form"></div>
		</div>
		<div class="tab-pane" id="tabPageEmployeeCertification">
			<div id="EmployeeCertificationTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="EmployeeCertificationForm" data-content="Form"></div>
		</div>
		<div class="tab-pane" id="tabPageEmployeeLanguage">
			<div id="EmployeeLanguageTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="EmployeeLanguageForm" data-content="Form"></div>
		</div>
	</div>

</div>
<?php
$moduleData = [
	'user_level' => $user->user_level,
	'permissions' => [
		'EmployeeSkill' => ['get', 'element', 'save', 'delete'],
		'EmployeeEducation' => ['get', 'element', 'save', 'delete'],
		'EmployeeCertification' => ['get', 'element', 'save', 'delete'],
		'EmployeeLanguage' => ['get', 'element', 'save', 'delete'],
	],
];
?>
<script>
var modJsList = [];

modJsList['tabEmployeeSkill'] = new EmployeeSkillAdapter('EmployeeSkill', 'EmployeeSkill', '', '');
modJsList['tabEmployeeSkill'].setObjectTypeName('Employee Skill');
modJsList['tabEmployeeSkill'].setDataPipe(new IceDataPipe(modJsList['tabEmployeeSkill']));
modJsList['tabEmployeeSkill'].setAccess(<?=json_encode($moduleData['permissions']['EmployeeSkill'])?>);

modJsList['tabEmployeeEducation'] = new EmployeeEducationAdapter('EmployeeEducation', 'EmployeeEducation', '', '');
modJsList['tabEmployeeEducation'].setObjectTypeName('Employee Education');
modJsList['tabEmployeeEducation'].setDataPipe(new IceDataPipe(modJsList['tabEmployeeEducation']));
modJsList['tabEmployeeEducation'].setAccess(<?=json_encode($moduleData['permissions']['EmployeeEducation'])?>);

modJsList['tabEmployeeCertification'] = new EmployeeCertificationAdapter('EmployeeCertification', 'EmployeeCertification', '', '');
modJsList['tabEmployeeCertification'].setObjectTypeName('Employee Certification');
modJsList['tabEmployeeCertification'].setDataPipe(new IceDataPipe(modJsList['tabEmployeeCertification']));
modJsList['tabEmployeeCertification'].setAccess(<?=json_encode($moduleData['permissions']['EmployeeCertification'])?>);

modJsList['tabEmployeeLanguage'] = new EmployeeLanguageAdapter('EmployeeLanguage', 'EmployeeLanguage', '', '');
modJsList['tabEmployeeLanguage'].setObjectTypeName('Employee Language');
modJsList['tabEmployeeLanguage'].setDataPipe(new IceDataPipe(modJsList['tabEmployeeLanguage']));
modJsList['tabEmployeeLanguage'].setAccess(<?=json_encode($moduleData['permissions']['EmployeeLanguage'])?>);

var modJs = modJsList['tabEmployeeSkill'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
