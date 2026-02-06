<?php
use Classes\PermissionManager;

$moduleData = [
	'controller_url' => CLIENT_BASE_URL.'service.php',
	'user_level' => $user->user_level,
	'app_web_url' => defined('APP_WEB_URL') ? APP_WEB_URL : 'https://icehrm.com',
];
?><div class="span9"><div id="content"></div></div>
<script>
initMarketplaceAdmin(<?=json_encode($moduleData)?>);
</script>
