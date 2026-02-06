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

// Check if pro bundle exists
$proBundlePath = APP_BASE_PATH . '../extensions/leave_and_performance/web/dist/' . $group . '-bundle.js';
$hasProBundle = file_exists($proBundlePath);
$proBundleUrl = EXTENSIONS_URL . 'leave_and_performance/web/dist/' . $group . '-bundle.js';
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
