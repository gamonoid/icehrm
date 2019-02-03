<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
$moduleName = 'jobs';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabJobTitles" href="#tabPageJobTitles"><?=t('Job Titles')?></a></li>
		<li><a id="tabPayGrades" href="#tabPagePayGrades"><?=t('Pay Grades')?></a></li>
		<li><a id="tabEmploymentStatus" href="#tabPageEmploymentStatus"><?=t('Employment Status')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageJobTitles">
			<div id="JobTitle" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="JobTitleForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
		<div class="tab-pane" id="tabPagePayGrades">
			<div id="PayGrade" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="PayGradeForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
		<div class="tab-pane" id="tabPageEmploymentStatus">
			<div id="EmploymentStatus" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="EmploymentStatusForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabJobTitles'] = new JobTitleAdapter('JobTitle');
modJsList['tabPayGrades'] = new PayGradeAdapter('PayGrade');
modJsList['tabEmploymentStatus'] = new EmploymentStatusAdapter('EmploymentStatus');

var modJs = modJsList['tabJobTitles'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
