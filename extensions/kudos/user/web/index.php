<?php
use Classes\PermissionManager;
use KudosUser\Common\Model\Kudos;

?><div class="span9">
    <div class="tab-content">
        <div class="tab-pane active" id="tabPageMain">
            <div id="content"></div>
        </div>
    </div>
</div>
<div id="dataGroup"></div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'controller_url' => CLIENT_BASE_URL.'service.php',
    'permissions' => [
        'Kudos' => PermissionManager::checkGeneralAccess(new Kudos()),
    ],
];
?>
<script>
    initKudosUser(<?=json_encode($moduleData)?>);
    window.modJs.get();
</script>
