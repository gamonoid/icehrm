<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Classes\PermissionManager;
use Employees\Common\Model\EmploymentStatus;
use Jobs\Common\Model\JobTitle;
use Jobs\Common\Model\PayGrade;

$moduleName = 'jobs';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabJobTitle" href="#tabPageJobTitles"><?=t('Job Titles')?></a></li>
		<li><a id="tabPayGrade" href="#tabPagePayGrades"><?=t('Pay Grades')?></a></li>
		<li><a id="tabEmploymentStatus" href="#tabPageEmploymentStatus"><?=t('Employment Status')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageJobTitles">
			<div id="JobTitleTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="JobTitleForm"></div>
            <div id="JobTitleFilterForm"></div>
		</div>

		<div class="tab-pane" id="tabPagePayGrades">
			<div id="PayGradeTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="PayGradeForm"></div>
            <div id="PayGradeFilterForm"></div>
		</div>

		<div class="tab-pane" id="tabPageEmploymentStatus">
			<div id="EmploymentStatusTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="EmploymentStatusForm"></div>
            <div id="EmploymentStatusFilterForm"></div>
		</div>
		
	</div>

</div>

<?php
$moduleData = [
    'user_level' => $user->user_level,
    'permissions' => [
        'JobTitle' => PermissionManager::checkGeneralAccess(new JobTitle()),
        'PayGrade' => PermissionManager::checkGeneralAccess(new PayGrade()),
        'EmploymentStatus' => PermissionManager::checkGeneralAccess(new EmploymentStatus()),
    ]
];
?>
<script>
  initAdminJobs(<?=json_encode($moduleData)?>);
</script>
<?php include APP_BASE_PATH.'footer.php';?>
