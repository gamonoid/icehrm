<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Classes\PermissionManager;
use Loans\Common\Model\EmployeeCompanyLoan;

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

			<div id="EmployeeCompanyLoanTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="EmployeeCompanyLoanForm"></div>
            <div id="EmployeeCompanyLoanFilterForm"></div>
		</div>
	</div>
</div>
	


<?php
$moduleData = [
    'user_level' => $user->user_level,
    'permissions' => [
        'EmployeeCompanyLoan' => PermissionManager::checkGeneralAccess(new EmployeeCompanyLoan()),
    ]
];
?>

<script>
  initAdminLoan(<?=json_encode($moduleData)?>);
</script>

<?php include APP_BASE_PATH.'footer.php';?>
