<?php
use Classes\PermissionManager;

$moduleData = [
	'controller_url' => CLIENT_BASE_URL.'service.php',
	'user_level' => $user->user_level,
];
?><div class="span9"><div id="content"></div></div>
<script>
initKudosAdmin(<?=json_encode($moduleData)?>);
</script>
