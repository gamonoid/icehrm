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
			<div id="EmployeeSkill" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="EmployeeSkillForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
		<div class="tab-pane" id="tabPageEmployeeEducation">
			<div id="EmployeeEducation" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="EmployeeEducationForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
		<div class="tab-pane" id="tabPageEmployeeCertification">
			<div id="EmployeeCertification" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="EmployeeCertificationForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
		<div class="tab-pane" id="tabPageEmployeeLanguage">
			<div id="EmployeeLanguage" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="EmployeeLanguageForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabEmployeeSkill'] = new EmployeeSkillAdapter('EmployeeSkill');
modJsList['tabEmployeeEducation'] = new EmployeeEducationAdapter('EmployeeEducation');
modJsList['tabEmployeeCertification'] = new EmployeeCertificationAdapter('EmployeeCertification');
modJsList['tabEmployeeLanguage'] = new EmployeeLanguageAdapter('EmployeeLanguage');

var modJs = modJsList['tabEmployeeSkill'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
