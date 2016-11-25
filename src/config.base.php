<?php
if(!defined('SIGN_IN_ELEMENT_MAPPING_FIELD_NAME')){define('SIGN_IN_ELEMENT_MAPPING_FIELD_NAME','employee');}

if(!defined('APP_NAME')){define('APP_NAME','Isotope');}
if(!defined('FB_URL')){define('FB_URL', 'https://www.facebook.com/icecamp');};
if(!defined('TWITTER_URL')){define('TWITTER_URL', 'https://twitter.com/icecamp');};

define('HOME_LINK_ADMIN', CLIENT_BASE_URL."?g=admin&n=dashboard&m=admin_Admin");
define('HOME_LINK_OTHERS', CLIENT_BASE_URL."?g=modules&n=dashboard&m=module_Personal_Information");

//Version
define('VERSION', '1.0.OS');
define('CACHE_VALUE', '1.0.OS');
define('VERSION_DATE', '28/06/2016');

if(!defined('CONTACT_EMAIL')){define('CONTACT_EMAIL','team@gamonoid.com');}
if(!defined('KEY_PREFIX')){define('KEY_PREFIX','IceCamp');}
if(!defined('APP_SEC')){define('APP_SEC','dsx2zhtr324');}

define('UI_SHOW_SWITCH_PROFILE', true);
define('CRON_LOG', '/var/log/nginx/icecamp.log');

define('MEMCACHE_HOST', '127.0.0.1');
define('MEMCACHE_PORT', '11211');

if(!defined('WK_HTML_PATH')){
    define('WK_HTML_PATH', '/usr/bin/wkhtmltopdf');
}