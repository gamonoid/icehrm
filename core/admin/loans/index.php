<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
use Classes\PermissionManager;
use CompanyLoans\Common\Model\CompanyLoan;
use CompanyLoans\Common\Model\EmployeeCompanyLoan;

$moduleName = 'loans';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabCompanyLoan" href="#tabPageCompanyLoan"><?=t('Loan Types')?></a></li>
		<li><a id="tabEmployeeCompanyLoan" href="#tabPageEmployeeCompanyLoan"><?=t('Employee Loans')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageCompanyLoan">
			<div id="CompanyLoan" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="CompanyLoanForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
		<div class="tab-pane" id="tabPageEmployeeCompanyLoan">
			<div id="EmployeeCompanyLoan" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="EmployeeCompanyLoanForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
	</div>

</div>

<?php
$moduleData = [
    'user_level' => $user->user_level,
    'permissions' => [
        'CompanyLoan' => PermissionManager::checkGeneralAccess(new CompanyLoan()),
        'EmployeeCompanyLoan' => PermissionManager::checkGeneralAccess(new EmployeeCompanyLoan()),
    ]
];
?>

<script>
var modJsList = [];
  var data = <?= json_encode($moduleData) ?>;
  modJsList['tabCompanyLoan'] = new ProjectAdapter('Company Loan', 'Company Loan');
  modJsList.tabCompanyLoan.setObjectTypeName('CompanyLoan');
  modJsList.tabCompanyLoan.setAccess(data.permissions.CompanyLoan);
  modJsList.tabCompanyLoan.setDataPipe(new IceDataPipe(modJsList.tabCompanyLoan));
  modJsList.tabCompanyLoan.setRemoteTable(true);

  modJsList['tabEmployeeCompanyLoan'] = new EmployeeCompanyLoanAdapter('EmployeeCompanyLoan', 'EmployeeCompanyLoan');


  modJsList.tabEmployeeCompanyLoan.setObjectTypeName('Employee Company Loan');
  modJsList.tabEmployeeCompanyLoan.setAccess(data.permissions.EmployeeCompanyLoan);
  modJsList.tabEmployeeCompanyLoan.setDataPipe(new IceDataPipe(modJsList.tabEmployeeCompanyLoan));
  modJsList.tabEmployeeCompanyLoan.setRemoteTable(true);

  var modJs = modJsList['tabCompanyLoan'];


</script>
<?php include APP_BASE_PATH.'footer.php';?>
