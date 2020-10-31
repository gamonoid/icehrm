<?php
if(isset($additionalJs)) {
    foreach ($additionalJs as $js) {
        ?>
        <script type="text/javascript" src="<?= $js ?>"></script>
        <?php
    }
}
?>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorReact.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorAntd.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorAntdIcons.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorAntv.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorOther.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/'.$group.'-bundle.js'?>?v=<?=$jsVersion?>"></script>
