<?php

use Classes\SettingsManager;

$moduleName = 'leaves';
$moduleGroup = 'modules';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
$leavePeriod = \Leaves\Common\Model\LeavePeriod::getCurrentLeavePeriod()->getData();
if(!empty($leavePeriod)){
	$leavePeriodId = $leavePeriod->id;
}
$isMultiLevelApprovalsEnabled = SettingsManager::getInstance()->getSetting('Leave: Enable Multi Level Approvals') == '1';
?><div class="span9">
	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabEmployeeLeaveAll" href="#tabPageEmployeeLeaveAll"><?=t('All My Leaves')?></a></li>
		<li class=""><a id="tabEmployeeLeaveEntitlement" href="#tabPageEmployeeLeaveEntitlement"><?=t('Leave Entitlement')?></a></li>
		<li class=""><a id="tabEmployeeLeaveApproved" href="#tabPageEmployeeLeaveApproved"><?=t('Approved Leave')?></a></li>
		<li class=""><a id="tabEmployeeLeavePending" href="#tabPageEmployeeLeavePending"><?=t('Pending Leave')?></a></li>
		<li class=""><a id="tabSubEmployeeLeaveAll" href="#tabPageSubEmployeeLeaveAll"><?=t('Leave Requests (Direct Reports)')?></a></li>
		<li class=""><a id="tabSubEmployeeLeaveCancel" href="#tabPageSubEmployeeLeaveCancel"><?=t('Leave Cancellation Requests')?></a></li>
        <?php if ($isMultiLevelApprovalsEnabled) {?>
		<li class=""><a id="tabEmployeeLeaveApproval" href="#tabPageEmployeeLeaveApproval"><?=t('Approval Requests')?></a></li>
        <?php } ?>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageEmployeeLeaveAll">
			<div id="EmployeeLeaveAllTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="EmployeeLeaveAllForm" data-content="Form"></div>
			<div id="EmployeeLeaveAllFilterForm"></div>
		</div>
        <div class="tab-pane active" id="tabPageEmployeeLeaveEntitlement">
            <div id="EmployeeLeaveEntitlement" data-content="List" style="padding-left:5px;padding:20px;" class="reviewBlock">
                <div class="row search-controls" style="padding-bottom:25px;display:none;">
                    <div class="col-lg-4 col-md-4"></div>
                    <div class="col-lg-5 col-md-5">

                    </div>
                    <div class="col-lg-3 col-md-3">
                        <input id="EmployeeLeaveEntitlement_search" type="text" class="form-control" placeholder="Search for...">
                    </div>

                </div>
                <div id="EmployeeLeaveEntitlement_error" class="alert alert-warning" role="alert" style="display: none;">

                </div>
                <div class="row objectList flex-container">
                </div>
                <nav aria-label="">
                    <ul class="pager">
                        <li id="loadMoreEmployeeLeaveEntitlement" style="display:none;"><a href="#" style="font-size:14px;">Load More <span aria-hidden="true">&rarr;</span></a></li>
                    </ul>
                </nav>
            </div>
        </div>
		<div class="tab-pane" id="tabPageEmployeeLeaveApproved">
			<div id="EmployeeLeaveApprovedTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="EmployeeLeaveApprovedForm" data-content="Form"></div>
			<div id="EmployeeLeaveApprovedFilterForm"></div>
		</div>
		<div class="tab-pane" id="tabPageEmployeeLeavePending">
			<div id="EmployeeLeavePendingTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="EmployeeLeavePendingForm" data-content="Form"></div>
			<div id="EmployeeLeavePendingFilterForm"></div>
		</div>
		<div class="tab-pane" id="tabPageSubEmployeeLeaveAll">
			<div id="SubEmployeeLeaveAllTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="SubEmployeeLeaveAllForm" data-content="Form"></div>
			<div id="SubEmployeeLeaveAllFilterForm"></div>
		</div>
		<div class="tab-pane" id="tabPageSubEmployeeLeaveCancel">
			<div id="SubEmployeeLeaveCancelTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="SubEmployeeLeaveCancelForm" data-content="Form"></div>
			<div id="SubEmployeeLeaveCancelFilterForm"></div>
		</div>
        <?php if ($isMultiLevelApprovalsEnabled) {?>
		<div class="tab-pane" id="tabPageEmployeeLeaveApproval">
			<div id="EmployeeLeaveApprovalTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="EmployeeLeaveApprovalForm" data-content="Form"></div>
			<div id="EmployeeLeaveApprovalFilterForm"></div>
		</div>
        <?php } ?>
	</div>

</div>
<?php
$moduleData = [
	'user_level' => $user->user_level,
	'permissions' => [
		'EmployeeLeave' => ['get', 'element', 'save', 'delete'],
	],
	'leavePeriodId' => isset($leavePeriodId) ? $leavePeriodId : null,
	'isMultiLevelApprovalsEnabled' => $isMultiLevelApprovalsEnabled,
];
?>
<script>
var modJsList = [];

// All My Leaves
modJsList['tabEmployeeLeaveAll'] = new EmployeeLeaveAdapter('EmployeeLeave', 'EmployeeLeaveAll', '', 'date_start desc');
modJsList['tabEmployeeLeaveAll'].setObjectTypeName('Leave Request');
modJsList['tabEmployeeLeaveAll'].setDataPipe(new IceDataPipe(modJsList['tabEmployeeLeaveAll']));
modJsList['tabEmployeeLeaveAll'].setAccess(<?=json_encode($moduleData['permissions']['EmployeeLeave'])?>);

// Approved Leave
modJsList['tabEmployeeLeaveApproved'] = new EmployeeApprovedLeaveAdapter('EmployeeLeave', 'EmployeeLeaveApproved', {"status":"Approved"}, 'date_start desc');
modJsList['tabEmployeeLeaveApproved'].setObjectTypeName('Approved Leave');
modJsList['tabEmployeeLeaveApproved'].setDataPipe(new IceDataPipe(modJsList['tabEmployeeLeaveApproved']));
modJsList['tabEmployeeLeaveApproved'].setAccess(<?=json_encode($moduleData['permissions']['EmployeeLeave'])?>);
modJsList['tabEmployeeLeaveApproved'].setShowAddNew(false);

// Pending Leave
modJsList['tabEmployeeLeavePending'] = new EmployeeLeaveAdapter('EmployeeLeave', 'EmployeeLeavePending', {"status":["Pending","Processing"]}, 'date_start desc');
modJsList['tabEmployeeLeavePending'].setObjectTypeName('Pending Leave');
modJsList['tabEmployeeLeavePending'].setDataPipe(new IceDataPipe(modJsList['tabEmployeeLeavePending']));
modJsList['tabEmployeeLeavePending'].setAccess(<?=json_encode($moduleData['permissions']['EmployeeLeave'])?>);
modJsList['tabEmployeeLeavePending'].setShowAddNew(false);

// Leave Requests (Direct Reports)
modJsList['tabSubEmployeeLeaveAll'] = new SubEmployeeLeaveAdapter('EmployeeLeave', 'SubEmployeeLeaveAll', '', 'date_start desc');
modJsList['tabSubEmployeeLeaveAll'].setObjectTypeName('Subordinate Leave Request');
modJsList['tabSubEmployeeLeaveAll'].setDataPipe(new IceDataPipe(modJsList['tabSubEmployeeLeaveAll']));
modJsList['tabSubEmployeeLeaveAll'].setAccess(<?=json_encode($moduleData['permissions']['EmployeeLeave'])?>);
modJsList['tabSubEmployeeLeaveAll'].setShowAddNew(false);
<?php if(!empty($leavePeriodId)){?>
modJsList['tabSubEmployeeLeaveAll'].preSetFilterExternal(<?='{"status":"Pending","leave_period":"'.$leavePeriodId.'"}'?>);
<?php }?>

// Leave Cancellation Requests
modJsList['tabSubEmployeeLeaveCancel'] = new SubEmployeeLeaveAdapter('EmployeeLeave', 'SubEmployeeLeaveCancel', {"status":"Cancellation Requested"}, 'date_start desc');
modJsList['tabSubEmployeeLeaveCancel'].setObjectTypeName('Leave Cancellation Request');
modJsList['tabSubEmployeeLeaveCancel'].setDataPipe(new IceDataPipe(modJsList['tabSubEmployeeLeaveCancel']));
modJsList['tabSubEmployeeLeaveCancel'].setAccess(<?=json_encode($moduleData['permissions']['EmployeeLeave'])?>);
modJsList['tabSubEmployeeLeaveCancel'].setShowAddNew(false);

// Leave Entitlement (keeps ObjectAdapter - no changes needed)
modJsList['tabEmployeeLeaveEntitlement'] = new EmployeeLeaveEntitlementAdapter('EmployeeLeaveEntitlement', 'EmployeeLeaveEntitlement');
modJsList['tabEmployeeLeaveEntitlement'].setShowAddNew(false);
modJsList['tabEmployeeLeaveEntitlement'].setSearchBox($("#EmployeeLeaveEntitlement_search"));
modJsList['tabEmployeeLeaveEntitlement'].setShowEdit(false);
modJsList['tabEmployeeLeaveEntitlement'].setShowDelete(false);
modJsList['tabEmployeeLeaveEntitlement'].setPageSize(100);

<?php if ($isMultiLevelApprovalsEnabled) {?>
// Approval Requests
modJsList['tabEmployeeLeaveApproval'] = new EmployeeLeaveApprovalAdapter('EmployeeLeaveApprove', 'EmployeeLeaveApproval', '', '');
modJsList['tabEmployeeLeaveApproval'].setObjectTypeName('Leave Approval Request');
modJsList['tabEmployeeLeaveApproval'].setDataPipe(new IceDataPipe(modJsList['tabEmployeeLeaveApproval']));
modJsList['tabEmployeeLeaveApproval'].setAccess(<?=json_encode($moduleData['permissions']['EmployeeLeave'])?>);
modJsList['tabEmployeeLeaveApproval'].setShowAddNew(false);
<?php } ?>

var modJs = modJsList['tabEmployeeLeaveAll'];

</script>
<div class="modal" id="leaveStatusModel" tabindex="-1" role="dialog" aria-labelledby="messageModelLabel" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><li class="fa fa-times"/></button>
		<h3 style="font-size: 17px;">Change Leave Status</h3>
	</div>
	<div class="modal-body">
		<form id="leaveStatusForm">
		<div class="control-group">
			<label class="control-label" for="leave_status">Leave Status</label>
			<div class="controls">
			  	<select class="form-control" type="text" id="leave_status" name="leave_status" value="">
				  	<option value="Approved">Approved</option>
				  	<option value="Pending">Pending</option>
				  	<option value="Rejected">Rejected</option>
				  	<option value="Cancelled">Cancelled</option>
			  	</select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="leave_status">Status Change Note</label>
			<div class="controls">
			  	<textarea id="leave_reason" class="form-control" name="leave_reason" maxlength="500"></textarea>
			</div>
		</div>
		</form>
	</div>
	<div class="modal-footer">
 		<button class="btn btn-primary" onclick="modJs.changeLeaveStatus();">Change Leave Status</button>
 		<button class="btn" onclick="modJs.closeLeaveStatus();">Not Now</button>
	</div>
</div>
</div>
</div>
<?php include APP_BASE_PATH.'footer.php';?>
