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

$moduleName = 'qualifications';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">
			  
	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabEmployeeSkill" href="#tabPageEmployeeSkill">Skills</a></li>
		<li><a id="tabEmployeeEducation" href="#tabPageEmployeeEducation">Education</a></li>
		<li><a id="tabEmployeeCertification" href="#tabPageEmployeeCertification">Certifications</a></li>
		<li><a id="tabEmployeeLanguage" href="#tabPageEmployeeLanguage">Languages</a></li>
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