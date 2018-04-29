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

$moduleName = 'Modules';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
$groupsStr = \Classes\SettingsManager::getInstance()->getSetting("Modules : Group");
$groups = array();
if(!empty($groupsStr)){
	$groups = explode(",",$groupsStr);
}
if(empty($groups)){
	$groups[] = 'all';
}
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabUsage" href="#tabPageUsage"><?=t('Usage')?></a></li>
		<li class=""><a id="tabModule" href="#tabPageModule"><?=t('Modules')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageUsage">
			<div id="Usage" class="reviewBlock" data-content="List" style="padding-left:5px;">
				<div class="callout callout-info">
					<h3>How Do You Want to Use IceHrm</h3>

					<p style="font-size: 1.2em;">
						In order to make IceHrm user interface much simpler to use for you and your employees you
						can select the purpose of using IceHrm for your company. This will disable unwanted modules
						and provide you a better user experience.
					</p>
				</div>


				<hr/>


				<div class="row">
					<div class="col-lg-4 col-xs-12">
						<div class="callout callout-gray">
							<h4 class="list-group-item-heading">
								<input id="all" class="module-check" type="checkbox" value="all"/>
								&nbsp;Use All Available Modules</h4>
							<p style="font-size: 1.2em;">
								Use all the Available Modules in IceHrm. This option will enable all the modules
								including Employee Management, Leave Management, Time Sheets, Attendance, Training,
								Expenses, Document Management, Travel, Recruitment Management and Payroll
							</p>
						</div>
						<div class="callout callout-white">
							<h4 class="list-group-item-heading">
								<input id="leave" class="module-check" type="checkbox" value="leave"/>
								&nbsp;Leave Management System
							</h4>
							<p style="font-size: 1.2em;">
								Use IceHrm as a Leave / Vacation Management System, Allow Employees to Apply for leave,
								Approve leave requests and track leave balances
							</p>
						</div>
						<div class="callout callout-gray">
							<h4 class="list-group-item-heading">
								<input id="documents" class="module-check" type="checkbox" value="documents"/>
								&nbsp;Document Management System
							</h4>
							<p style="font-size: 1.2em;">
								Use IceHrm as a Document Management System, Allow Employees upload documents, Automated
								notifications for expiring documents, Add company documents and share with specific
								employees or departments.
							</p>
						</div>
					</div>
					<div class="col-lg-4 col-xs-12">
						<div class="callout callout-white">
							<h4 class="list-group-item-heading">
								<input id="attendance" class="module-check" type="checkbox" value="attendance"/>
								&nbsp;Time Tracking System
							</h4>
							<p style="font-size: 1.2em;"><br/>
								Use IceHrm as an Attendance Management and Time Tracking System. Let employees record
								attendance and fill in time sheets.
							</p>
						</div>
						<div class="callout callout-gray">
							<h4 class="list-group-item-heading">
								<input id="training" class="module-check" type="checkbox" value="training"/>
								&nbsp;Training Management System
							</h4>
							<p style="font-size: 1.2em;">
								Use IceHrm as a Training Management System. Create courses and training sessions. Let
								employees subscribe to training sessions and allow them to submit feedback with training
								certificates for auditing purposes.
							</p>
						</div>
						<div class="callout callout-white">
							<h4 class="list-group-item-heading">
								<input id="finance" class="module-check" type="checkbox" value="finance"/>
								&nbsp;Expense and Travel Management
							</h4>
							<p style="font-size: 1.2em;">
								Get your employees to submit expense claims and let managers approve. Also combine
								approved expenses with payroll module to have those added to employees salary.
								Also you can track and approve employee travel requests
							</p>
						</div>
					</div>
					<?php if(defined('RECRUITMENT_ENABLED') && RECRUITMENT_ENABLED == true){?>
					<div class="col-lg-4 col-xs-12">
						<div class="callout callout-gray">
							<h4 class="list-group-item-heading">
								<input id="recruitment" class="module-check" type="checkbox" value="recruitment"/>
								&nbsp;Applicant Tracking System
							</h4>
							<p style="font-size: 1.2em;">
								Define available vacancies in your company and track applicants. Schedule interviews
								and track progress of your candidates
							</p>
						</div>
						<div class="callout callout-white">
							<h4 class="list-group-item-heading">
								<input id="payroll" class="module-check"  type="checkbox" value="payroll"/>
								&nbsp;Salary and Payroll
							</h4>
							<p style="font-size: 1.2em;">
								Process your company payroll using IceHrm and Store employee salary
							</p>
						</div>
					</div>
					<?php }?>
				</div>

				<button onclick="modJs.saveUsage();" class="saveBtn btn btn-primary">
					<i class="fa fa-save"></i><t>Save</t>
				</button>
			</div>
		</div>
		<div class="tab-pane" id="tabPageModule">
			<div id="Module" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="ModuleForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
	</div>

</div>
<script>
var modJsList = [];

modJsList['tabModule'] = new ModuleAdapter('Module','Module');
modJsList['tabModule'].setShowAddNew(false);

modJsList['tabUsage'] = new UsageAdapter('Usage','Usage');
var modJs = modJsList['tabUsage'];

	$(document).ready(function(){


		$("#all").click(function() {
			if($(this).is(":checked")) {

				$('.module-check').each(function(){
					if($(this).val() != 'all'){
						$(this).removeAttr('checked');
					}
				});
			}
		});

		$(".module-check").click(function() {
			if($(this).val() != 'all') {
				$("#all").removeAttr('checked');
			}
		});

		$('.module-check').each(function(){
			if(jQuery.inArray($(this).val(), <?=json_encode($groups)?>) !== -1){
				$(this).attr('checked','checked');
			}

		});
	})

</script>
<?php include APP_BASE_PATH.'footer.php';?>
