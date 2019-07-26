<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

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
<script>
var modJsList = new Array();

modJsList['tabCompanyLoan'] = new CompanyLoanAdapter('CompanyLoan','CompanyLoan');
modJsList['tabEmployeeCompanyLoan'] = new EmployeeCompanyLoanAdapter('EmployeeCompanyLoan','EmployeeCompanyLoan');

var modJs = modJsList['tabCompanyLoan'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
