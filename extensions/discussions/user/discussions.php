<?php
$dir = \Classes\BaseService::getInstance()->getExtensionSourceDirectory(__DIR__);

require_once $dir.'Extension.php';
require_once $dir.'Controller.php';
require_once $dir.'ChannelAccess.php';
require_once $dir.'ApiController.php';
require_once $dir.'Mentions.php';

// Models
require_once $dir.'Common/Model/DiscussionChannel.php';
require_once $dir.'Common/Model/DiscussionPost.php';
require_once $dir.'Common/Model/DiscussionReply.php';
