<?php
if(!defined('SIGN_IN_ELEMENT_MAPPING_FIELD_NAME')){define('SIGN_IN_ELEMENT_MAPPING_FIELD_NAME','employee');}

if(!defined('APP_NAME')){define('APP_NAME','ICE Hrm');}
if(!defined('FB_URL')){define('FB_URL', 'https://www.facebook.com/icehrm');};
if(!defined('TWITTER_URL')){define('TWITTER_URL', 'https://twitter.com/icehrmapp');};

if(defined('CLIENT_BASE_URL')) {
    define('HOME_LINK_ADMIN', CLIENT_BASE_URL . "?g=admin&n=dashboard&m=admin_Admin");
    define('HOME_LINK_OTHERS', CLIENT_BASE_URL . "?g=modules&n=dashboard&m=module_Personal_Information");
}

//Version
define('VERSION', '19.0.OS');
define('CACHE_VALUE', '19.0.OS');
define('VERSION_NUMBER', '190');
define('VERSION_DATE', '19/11/2016');

if(!defined('CONTACT_EMAIL')){define('CONTACT_EMAIL','icehrm@gamonoid.com');}
if(!defined('KEY_PREFIX')){define('KEY_PREFIX','IceHrm');}
if(!defined('APP_SEC')){define('APP_SEC','dbcs234d2s111');}

define('UI_SHOW_SWITCH_PROFILE', true);
define('CRON_LOG', '/var/log/nginx/icehrmcron.log');

define('MEMCACHE_HOST', '127.0.0.1');
define('MEMCACHE_PORT', '11211');

if(!defined('WK_HTML_PATH')){
    define('WK_HTML_PATH', '/usr/bin/wkhtmltopdf');
}

define('ALL_CLIENT_BASE_PATH', '/vagrant/deployment/clients/');

define('CHECK_UPDATE_URL', 'https://icehrm.com/a.php?a=checkUpdate&');
