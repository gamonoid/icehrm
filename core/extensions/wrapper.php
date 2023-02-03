<?php
use Classes\ExtensionManager;
use Utils\LogManager;

if (!isset($extensionIndex)) {
    exit();
}
define('MODULE_PATH',APP_BASE_PATH.'/../extensions/'.$moduleName);
include APP_BASE_PATH.'header.php';
$extensionManager = new ExtensionManager();
$meta = $extensionManager->getExtensionMetaData($moduleName);
if (!$meta) {
    LogManager::getInstance()->error("Extension metadata.json not found for $moduleName");
    exit();
}

if ($meta->headless) {
    LogManager::getInstance()->error("Extension running in headless mode for $moduleName");
    exit();
}

$extensionName = explode('/', $moduleName)[0];

?>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorReact.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorAntd.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorAntdIcons.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorAntv.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorOther.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=EXTENSIONS_URL.$moduleName.'/dist/'.$extensionName.'.js'?>?v=<?=$jsVersion?>"></script>
<?php
include $extensionIndex;
include APP_BASE_PATH.'footer.php';
?>
