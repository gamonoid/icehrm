<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'documents';
$moduleGroup = 'modules';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabEmployeeCompanyDocument" href="#tabPageEmployeeCompanyDocument"><?=t('Company Documents')?></a></li>
		<li class=""><a id="tabEmployeeDocument" href="#tabPageEmployeeDocument"><?=t('Personal Documents')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageEmployeeCompanyDocument">
			<div id="EmployeeCompanyDocument" data-content="List" style="padding-left:5px;padding:20px;" class="reviewBlock">
				<div class="row search-controls" style="padding-bottom:25px;display:none;">
					<div class="col-lg-4 col-md-4"></div>
					<div class="col-lg-5 col-md-5">

					</div>
					<div class="col-lg-3 col-md-3">
						<input id="EmployeeCompanyDocument_search" type="text" class="form-control" placeholder="Search for...">
					</div>

				</div>
				<div id="EmployeeCompanyDocument_error" class="alert alert-warning" role="alert" style="display: none;">

				</div>
				<div class="row objectList">
				</div>
				<nav aria-label="">
					<ul class="pager">
						<li id="loadMoreEmployeeCompanyDocument" style="display:none;"><a href="#" style="font-size:14px;">Load More <span aria-hidden="true">&rarr;</span></a></li>
					</ul>
				</nav>
			</div>
		</div>
		<div class="tab-pane" id="tabPageEmployeeDocument">
			<div id="EmployeeDocument" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="EmployeeDocumentForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabEmployeeDocument'] = new EmployeeDocumentAdapter('EmployeeDocument','EmployeeDocument');

<?php if(isset($modulePermissions['perm']['Add Documents']) && $modulePermissions['perm']['Add Documents'] == "No"){?>
modJsList['tabEmployeeDocument'].setShowAddNew(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Delete Documents']) && $modulePermissions['perm']['Delete Documents'] == "No"){?>
modJsList['tabEmployeeDocument'].setShowDelete(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Edit Documents']) && $modulePermissions['perm']['Edit Documents'] == "No"){?>
modJsList['tabEmployeeDocument'].setShowEdit(false);
<?php }?>

modJsList['tabEmployeeDocument'].setRemoteTable(true);


modJsList['tabEmployeeCompanyDocument'] = new EmployeeCompanyDocumentAdapter('CompanyDocument','EmployeeCompanyDocument');
modJsList['tabEmployeeCompanyDocument'].setLoadMoreButton($("#loadMoreEmployeeCompanyDocument"));
modJsList['tabEmployeeCompanyDocument'].setSearchBox($("#EmployeeCompanyDocument_search"));
modJsList['tabEmployeeCompanyDocument'].setShowAddNew(false);
modJsList['tabEmployeeCompanyDocument'].setShowEdit(false);
modJsList['tabEmployeeCompanyDocument'].setShowDelete(false);

var modJs = modJsList['tabEmployeeCompanyDocument'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
