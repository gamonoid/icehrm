<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'loans';
$moduleGroup = 'modules';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabEmployeeCompanyLoan" href="#tabPageEmployeeCompanyLoan"><?=t('Loans Taken')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageEmployeeCompanyLoan">
			<div id="EmployeeCompanyLoan" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="EmployeeCompanyLoanForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabEmployeeCompanyLoan'] = new EmployeeCompanyLoanAdapter('EmployeeCompanyLoan','EmployeeCompanyLoan');
modJsList['tabEmployeeCompanyLoan'].setShowAddNew(false);
modJsList['tabEmployeeCompanyLoan'].setShowSave(false);
modJsList['tabEmployeeCompanyLoan'].setShowDelete(false);
modJsList['tabEmployeeCompanyLoan'].setShowEdit(true);

var modJs = modJsList['tabEmployeeCompanyLoan'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
