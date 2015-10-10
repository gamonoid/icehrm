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

$moduleName = 'company_structure';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">
			  
	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabSkill" href="#tabPageSkill">Skills</a></li>
		<li><a id="tabEducation" href="#tabPageEducation">Education</a></li>
		<li><a id="tabCertification" href="#tabPageCertification">Certifications</a></li>
		<li><a id="tabLanguage" href="#tabPageLanguage">Languages</a></li>
	</ul>
	 
	<div class="tab-content">
		<div class="tab-pane active" id="tabPageSkill">
			<div id="Skill" class="reviewBlock" data-content="List" style="padding-left:5px;">
		
			</div>
			<div id="SkillForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">
		
			</div>
		</div>
		<div class="tab-pane" id="tabPageEducation">
			<div id="Education" class="reviewBlock" data-content="List" style="padding-left:5px;">
		
			</div>
			<div id="EducationForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">
		
			</div>
		</div>
		<div class="tab-pane" id="tabPageCertification">
			<div id="Certification" class="reviewBlock" data-content="List" style="padding-left:5px;">
		
			</div>
			<div id="CertificationForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">
		
			</div>
		</div>
		<div class="tab-pane" id="tabPageLanguage">
			<div id="Language" class="reviewBlock" data-content="List" style="padding-left:5px;">
		
			</div>
			<div id="LanguageForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">
		
			</div>
		</div>
	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabSkill'] = new SkillAdapter('Skill');
<?php if(isset($modulePermissions['perm']['Add Skills']) && $modulePermissions['perm']['Add Skills'] == "No"){?>
modJsList['tabSkill'].setShowAddNew(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Delete Skills']) && $modulePermissions['perm']['Delete Skills'] == "No"){?>
modJsList['tabSkill'].setShowDelete(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Edit Skills']) && $modulePermissions['perm']['Edit Skills'] == "No"){?>
modJsList['tabSkill'].setShowEdit(false);
<?php }?>


modJsList['tabEducation'] = new EducationAdapter('Education');
<?php if(isset($modulePermissions['perm']['Add Education']) && $modulePermissions['perm']['Add Education'] == "No"){?>
modJsList['tabEducation'].setShowAddNew(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Delete Education']) && $modulePermissions['perm']['Delete Education'] == "No"){?>
modJsList['tabEducation'].setShowDelete(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Edit Education']) && $modulePermissions['perm']['Edit Education'] == "No"){?>
modJsList['tabEducation'].setShowEdit(false);
<?php }?>



modJsList['tabCertification'] = new CertificationAdapter('Certification');
<?php if(isset($modulePermissions['perm']['Add Certifications']) && $modulePermissions['perm']['Add Certifications'] == "No"){?>
modJsList['tabCertification'].setShowAddNew(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Delete Certifications']) && $modulePermissions['perm']['Delete Certifications'] == "No"){?>
modJsList['tabCertification'].setShowDelete(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Edit Certifications']) && $modulePermissions['perm']['Edit Certifications'] == "No"){?>
modJsList['tabCertification'].setShowEdit(false);
<?php }?>


modJsList['tabLanguage'] = new LanguageAdapter('Language');
<?php if(isset($modulePermissions['perm']['Add Languages']) && $modulePermissions['perm']['Add Languages'] == "No"){?>
modJsList['tabLanguage'].setShowAddNew(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Delete Languages']) && $modulePermissions['perm']['Delete Languages'] == "No"){?>
modJsList['tabLanguage'].setShowDelete(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Edit Languages']) && $modulePermissions['perm']['Edit Languages'] == "No"){?>
modJsList['tabLanguage'].setShowEdit(false);
<?php }?>

var modJs = modJsList['tabSkill'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>      