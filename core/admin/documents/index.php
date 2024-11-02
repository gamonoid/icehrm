<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Classes\PermissionManager;
use Documents\Common\Model\CompanyDocument;
use Documents\Common\Model\Document;
use Documents\Common\Model\EmployeeDocument;
use Documents\Common\Model\PayslipDocument;

$moduleName = 'documents';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?>
    <div class="span9">
        <ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;"><li class="active"><a id="tabCompanyDocument" href="#tabPageCompanyDocument">Company Documents</a></li>
            <li class=""><a id="tabDocument" href="#tabPageDocument">Document Types</a></li>
            <li class=""><a id="tabEmployeeDocument" href="#tabPageEmployeeDocument">Employee Documents</a></li>
            <?php if($user->user_level === "Admin"){?>
            <li class=""><a id="tabPayslipDocument" href="#tabPagePayslipDocument">Employee Payslip</a></li>
            <?php }?>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tabPageCompanyDocument">
                <div id="CompanyDocumentTableTop" class="reviewBlock"></div>
                <div id="CompanyDocumentTable" class="reviewBlock" data-content="List"></div>
                <div id="CompanyDocumentForm"></div>
                <div id="CompanyDocumentFilterForm"></div>
            </div>
            <div class="tab-pane" id="tabPageDocument">
                <div id="DocumentTableTop" class="reviewBlock"></div>
                <div id="DocumentTable" class="reviewBlock" data-content="List"></div>
                <div id="DocumentForm"></div>
                <div id="DocumentFilterForm"></div>
            </div>
            <div class="tab-pane" id="tabPageEmployeeDocument">
                <div id="EmployeeDocumentTableTop" class="reviewBlock"></div>
                <div id="EmployeeDocumentTable" class="reviewBlock" data-content="List"></div>
                <div id="EmployeeDocumentForm"></div>
                <div id="EmployeeDocumentFilterForm"></div>
            </div>
            <?php if($user->user_level === "Admin"){?>
                <div class="tab-pane" id="tabPagePayslipDocument">
                    <div id="PayslipDocumentTableTop" class="reviewBlock"></div>
                    <div id="PayslipDocumentTable" class="reviewBlock" data-content="List"></div>
                    <div id="PayslipDocumentForm"></div>
                    <div id="PayslipDocumentFilterForm"></div>
                </div>
            <?php }?>
    </div>
    <?php
    $moduleData = [
        'user_level' => $user->user_level,
        'permissions' => [
            'CompanyDocument' => PermissionManager::checkGeneralAccess(new CompanyDocument()),
            'Document' => PermissionManager::checkGeneralAccess(new Document()),
            'EmployeeDocument' => PermissionManager::checkGeneralAccess(new EmployeeDocument()),
            'PayslipDocument' => PermissionManager::checkGeneralAccess(new PayslipDocument()),
        ]
    ];
    ?>
    <script type="text/javascript">
      //var data = {"user_level":"Admin","permissions":{"CompanyDocument":["get","element","save","delete"],"Document":["get","element","save","delete"],"EmployeeDocument":["get","element","save","delete"],"PayslipDocument":["get","element","save","delete"]}};
      var data = <?php echo json_encode($moduleData);?>;
      var modJsList = [];
      modJsList['tabCompanyDocument'] = new CompanyDocumentAdapter('CompanyDocument','CompanyDocument','','');
      modJsList['tabCompanyDocument'].setObjectTypeName('CompanyDocument');
      modJsList['tabCompanyDocument'].setAccess(data.permissions.CompanyDocument ? data.permissions.CompanyDocument : {});
      modJsList['tabCompanyDocument'].setDataPipe(new IceDataPipe(modJsList.tabCompanyDocument));
      modJsList['tabCompanyDocument'].setRemoteTable(1);
      modJsList['tabCompanyDocument'].setTitle('Company Documents');

      modJsList['tabDocument'] = new DocumentAdapter('Document','Document','','');
      modJsList['tabDocument'].setObjectTypeName('Document');
      modJsList['tabDocument'].setAccess(data.permissions.Document ? data.permissions.Document : {});
      modJsList['tabDocument'].setDataPipe(new IceDataPipe(modJsList.tabDocument));
      modJsList['tabDocument'].setRemoteTable(1);
      modJsList['tabDocument'].setTitle('Document Types');

      modJsList['tabEmployeeDocument'] = new EmployeeDocumentAdapter('EmployeeDocument','EmployeeDocument','','date_added desc');
      modJsList['tabEmployeeDocument'].setRemoteTable(1);
      modJsList['tabEmployeeDocument'].setObjectTypeName('EmployeeDocument');
      modJsList['tabEmployeeDocument'].setAccess(data.permissions.EmployeeDocument ? data.permissions.EmployeeDocument : {});
      modJsList['tabEmployeeDocument'].setDataPipe(new IceDataPipe(modJsList.tabEmployeeDocument));
      modJsList['tabEmployeeDocument'].setTitle('Employee Documents');

      modJsList['tabPayslipDocument'] = new EmployeePayslipDocumentAdapter('PayslipDocument','PayslipDocument','','date_added desc');
      modJsList['tabPayslipDocument'].setRemoteTable(1);
      modJsList['tabPayslipDocument'].setObjectTypeName('PayslipDocument');
      modJsList['tabPayslipDocument'].setAccess(data.permissions.PayslipDocument ? data.permissions.PayslipDocument : {});
      modJsList['tabPayslipDocument'].setDataPipe(new IceDataPipe(modJsList.tabPayslipDocument));
      modJsList['tabPayslipDocument'].setTitle('Employee Payslip');

      var modJs = modJsList['tabCompanyDocument'];
    </script>
<?php

include APP_BASE_PATH.'footer.php';
