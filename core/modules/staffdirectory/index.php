<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'salary';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabStaffDirectory" href="#tabPageStaffDirectory"><?=t('Staff Directory')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageStaffDirectory">
			<div id="StaffDirectory" data-content="List" style="padding-left:5px;" class="reviewBlock">
				<div class="row search-controls" style="padding-bottom:25px;display:none;">
					<div class="col-lg-4 col-md-4">
						<button id="StaffDirectory_filterBtn" onclick="modJs.showFilters();return false;" class="btn btn-small btn-primary">Filter <i class="fa fa-filter"></i></button>
						<button id="StaffDirectory_resetFilters" onclick="modJs.resetFilters();return false;" class="btn btn-small btn-default"> <i class="fa fa-times"></i></button>
					</div>
					<div class="col-lg-5 col-md-5">

					</div>
					<div class="col-lg-3 col-md-3">
						<input id="StaffDirectory_search" type="text" class="form-control" placeholder="Search for...">
					</div>

				</div>
				<div id="StaffDirectory_error" class="alert alert-warning" role="alert" style="display: none;">

				</div>
				<div class="row objectList">
				</div>
				<nav aria-label="">
					<ul class="pager">
						<li id="loadMoreStaffDirectory" style="display:none;"><a href="#" style="font-size:14px;">Load More <span aria-hidden="true">&rarr;</span></a></li>
					</ul>
				</nav>
			</div>

			</div>
		</div>
	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabStaffDirectory'] = new StaffDirectoryObjectAdapter('StaffDirectory','StaffDirectory',{"status":"Active"});
modJsList['tabStaffDirectory'].setShowAddNew(false);
modJsList['tabStaffDirectory'].setLoadMoreButton($("#loadMoreStaffDirectory"));
modJsList['tabStaffDirectory'].setSearchBox($("#StaffDirectory_search"));

var modJs = modJsList['tabStaffDirectory'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
