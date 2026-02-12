<?php
use Classes\ExtensionManager;
use Utils\LogManager;

if (!isset($extensionIndex)) {
    exit();
}

// Parse extension name and type from moduleName (format: "extensionName/type")
$extensionName = explode('/', $moduleName)[0];
$extensionType = isset(explode('/', $moduleName)[1]) ? explode('/', $moduleName)[1] : 'admin';

// Get actual path and relative path considering grouped extensions
// Note: path-resolver.php is already included from index.php
$extensionActualPath = resolveExtensionPath($extensionName, APP_BASE_PATH . '../extensions/');
$extensionRelativePath = getExtensionRelativePath($extensionName, APP_BASE_PATH . '../extensions/');

define('MODULE_PATH', $extensionActualPath . $extensionType);
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

?>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorReact.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorAntd.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorAntdIcons.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorAntv.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/vendorOther.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=EXTENSIONS_URL.$extensionRelativePath.'/'.$extensionType.'/dist/'.$extensionName.'.js'?>?v=<?=$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'dist/common-bundle.js'?>?v=<?=$jsVersion?>"></script>
<?php
include $extensionIndex;
include APP_BASE_PATH.'footer.php';
?>
