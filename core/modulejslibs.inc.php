<?php
if(isset($additionalJs)) {
    foreach ($additionalJs as $js) {
        ?>
        <script type="text/javascript" src="<?= $js ?>"></script>
        <?php
    }
}

// Use $moduleGroup if $group is not set
$group = isset($group) ? $group : (isset($moduleGroup) ? $moduleGroup : 'admin');

// Check if the leave package ships a bundle for this module group (its adapters
// live outside the core bundles). See core/leave-package.php for the lookup.
require_once APP_BASE_PATH . 'leave-package.php';
$leavePackageDir = iceLeavePackageDir();
$proBundlePath = $leavePackageDir === null
    ? null : $leavePackageDir . 'web/dist/' . $group . '-bundle.js';
$leavePackageUrl = iceLeavePackageUrl();
$proBundleUrl = $leavePackageUrl === null
    ? null : $leavePackageUrl . 'web/dist/' . $group . '-bundle.js';
$hasProBundle = $proBundlePath !== null && $proBundleUrl !== null && file_exists($proBundlePath);
?>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorReact.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorAntd.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorAntdIcons.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorAntv.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorOther.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/'.$group.'-bundle.js'?>?v=<?=$jsVersion?>"></script>
<?php if ($hasProBundle) { ?>
<script type="text/javascript" src="<?=$proBundleUrl?>?v=<?=$jsVersion?>"></script>
<?php } ?>
<script type="text/javascript" src="<?=BASE_URL.'dist/common-bundle.js'?>?v=<?=$jsVersion?>"></script>
