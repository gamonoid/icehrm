<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Classes\PermissionManager;
use Qualifications\Common\Model\Certification;
use Qualifications\Common\Model\Education;
use Qualifications\Common\Model\Language;
use Qualifications\Common\Model\Skill;

$moduleName = 'qualifications';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabSkill" href="#tabPageSkill"><?=t('Skills')?></a></li>
		<li><a id="tabEducation" href="#tabPageEducation"><?=t('Education')?></a></li>
		<li><a id="tabCertification" href="#tabPageCertification"><?=t('Certifications')?></a></li>
		<li><a id="tabLanguage" href="#tabPageLanguage"><?=t('Languages')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageSkill">
            <div id="SkillTableTop" class="reviewBlock"></div>
			<div id="SkillTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="SkillForm"></div>
			<div id="SkillFilterForm"></div>
		</div>
		<div class="tab-pane" id="tabPageEducation">
            <div id="EducationTableTop" class="reviewBlock"></div>
			<div id="EducationTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="EducationForm"></div>
            <div id="EducationFilterForm"></div>
		</div>
		<div class="tab-pane" id="tabPageCertification">
            <div id="CertificationTableTop" class="reviewBlock"></div>
			<div id="CertificationTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="CertificationForm"></div>
            <div id="CertificationFilterForm"></div>
		</div>
		<div class="tab-pane" id="tabPageLanguage">
            <div id="LanguageTableTop" class="reviewBlock"></div>
			<div id="LanguageTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="LanguageForm"></div>
            <div id="LanguageFilterForm"></div>
		</div>
	</div>
</div>
<div id="dataGroup"></div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'permissions' => [
        'Skill' => PermissionManager::checkGeneralAccess(new Skill()),
        'Education' => PermissionManager::checkGeneralAccess(new Education()),
        'Certification' => PermissionManager::checkGeneralAccess(new Certification()),
        'Language' => PermissionManager::checkGeneralAccess(new Language()),
    ]
];
?>
<script>
  initAdminQualifications(<?=json_encode($moduleData)?>);
</script>
<?php include APP_BASE_PATH.'footer.php';?>
