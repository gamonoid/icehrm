<?php
if(isset($additionalJs)) {
    foreach ($additionalJs as $js) {
        ?>
        <script type="text/javascript" src="<?= $js ?>"></script>
    <?php
    }
}
?>
<script type="text/javascript" src="<?=BASE_URL.$group.'/'.$name.'/'?>lib.js?v=<?=$jsVersion?>"></script>