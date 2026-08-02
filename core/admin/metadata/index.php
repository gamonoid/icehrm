<?php
use Classes\PermissionManager;
use Metadata\Common\Model\Country;
use Metadata\Common\Model\Province;
use Metadata\Common\Model\CurrencyType;
use Metadata\Common\Model\Nationality;
use Metadata\Common\Model\Ethnicity;
use Metadata\Common\Model\ImmigrationStatus;

$moduleName = 'metadata';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

$tabs = [
    ['Country', 'Countries'],
    ['Province', 'Provinces'],
    ['CurrencyType', 'Currency Types'],
    ['Nationality', 'Nationality'],
    ['Ethnicity', 'Ethnicity'],
    ['ImmigrationStatus', 'Immigration Status'],
];
?><div class="span9">
    <ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
        <?php foreach ($tabs as $i => $t) { ?>
        <li class="<?=$i === 0 ? 'active' : ''?>"><a id="tab<?=$t[0]?>" href="#tabPage<?=$t[0]?>"><?=t($t[1])?></a></li>
        <?php } ?>
    </ul>
    <div class="tab-content">
        <?php foreach ($tabs as $i => $t) { ?>
        <div class="tab-pane<?=$i === 0 ? ' active' : ''?>" id="tabPage<?=$t[0]?>">
            <div id="<?=$t[0]?>Table" class="reviewBlock" data-content="List"></div>
            <div id="<?=$t[0]?>Form"></div>
            <div id="<?=$t[0]?>FilterForm"></div>
        </div>
        <?php } ?>
    </div>
</div>
<div id="dataGroup"></div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'permissions' => [
        'Country' => PermissionManager::checkGeneralAccess(new Country()),
        'Province' => PermissionManager::checkGeneralAccess(new Province()),
        'CurrencyType' => PermissionManager::checkGeneralAccess(new CurrencyType()),
        'Nationality' => PermissionManager::checkGeneralAccess(new Nationality()),
        'Ethnicity' => PermissionManager::checkGeneralAccess(new Ethnicity()),
        'ImmigrationStatus' => PermissionManager::checkGeneralAccess(new ImmigrationStatus()),
    ]
];
?>
<script>
initAdminMetadata(<?=json_encode($moduleData)?>);
</script>
<?php include APP_BASE_PATH.'footer.php';
