<?php

$moduleName = 'documents';
$moduleGroup = 'modules';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

    <ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
        <li class="active"><a id="tabEmployeeDocument" href="#tabPageEmployeeDocument"><?=t('My Documents')?></a></li>
        <li><a id="tabCompanyDocument" href="#tabPageCompanyDocument"><?=t('Company Documents')?></a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="tabPageEmployeeDocument">
            <div id="EmployeeDocumentTable" class="reviewBlock" data-content="List"></div>
            <div id="EmployeeDocumentForm"></div>
            <div id="EmployeeDocumentFilterForm"></div>
        </div>
        <div class="tab-pane" id="tabPageCompanyDocument">
            <div id="CompanyDocumentTable" class="reviewBlock" data-content="List"></div>
            <div id="CompanyDocumentForm"></div>
            <div id="CompanyDocumentFilterForm"></div>
        </div>
    </div>

</div>
<div id="dataGroup"></div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'permissions' => [
        'EmployeeDocument' => [ "get","element","save","delete" ],
        'CompanyDocument' => [ "get","element" ],
    ]
];
?>
<script>
  initDocumentsModule(<?=json_encode($moduleData)?>);
</script>
<?php include APP_BASE_PATH.'footer.php';?>
