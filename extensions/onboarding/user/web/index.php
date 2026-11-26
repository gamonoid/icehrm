<?php
$moduleData = [
    'controller_url' => CLIENT_BASE_URL.'service.php',
    'user_level' => $user->user_level,
];
?><div class="span9"><div id="content"></div></div>
<script>
initOnboardingUser(<?=json_encode($moduleData)?>);
</script>
