<?php
/*
 Copyright (c) 2020 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Classes\PermissionManager;
use Clients\Common\Model\Client;

$moduleName = 'clients';
$moduleGroup = 'admin';
define('MODULE_PATH', dirname(__FILE__));
include APP_BASE_PATH . 'header.php';
include APP_BASE_PATH . 'modulejslibs.inc.php';
?><div class="span9">

    <ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
        <li class="active"><a id="tabClient" href="#tabPageClient"><?= t('Clients') ?></a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="tabPageClient">
            <div id="ClientTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="ClientForm"></div>
            <div id="ClientFilterForm"></div>
        </div>
    </div>

</div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'permissions' => [
        'Client' => PermissionManager::checkGeneralAccess(new Client()),
    ]
];
?>
    <script>
      var data = <?= json_encode($moduleData) ?>;
      var modJsList = [];

      modJsList['tabClient'] = new ClientAdapter('Client');

      modJsList.tabClient.setObjectTypeName('Client');
      modJsList.tabClient.setAccess(data.permissions.Client);
      modJsList.tabClient.setDataPipe(new IceDataPipe(modJsList.tabClient));
      modJsList.tabClient.setRemoteTable(true);

      var modJs = modJsList['tabClient'];
    </script>
<?php include APP_BASE_PATH . 'footer.php'; ?>