<?php 
/*
This file is part of iCE Hrm.

iCE Hrm is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

iCE Hrm is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with iCE Hrm. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]  
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */

$moduleName = 'jobs';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">
			  
	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabJobTitles" href="#tabPageJobTitles">Job Titles</a></li>
		<li><a id="tabPayGrades" href="#tabPagePayGrades">Pay Grades</a></li>
		<li><a id="tabEmploymentStatus" href="#tabPageEmploymentStatus">Employment Status</a></li>
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