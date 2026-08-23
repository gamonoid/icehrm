<?php
// Idempotent: safe to include more than once. app/index.php loads this early (to
// gate pro extensions before the pro loader runs) and the later bootstrap
// includes it again — the guard makes the second include a no-op.
if (defined('ICEHRM_CONFIG_BASE_LOADED')) {
    return;
}
define('ICEHRM_CONFIG_BASE_LOADED', true);

// Session cookie hardening (security finding 1.7). Set here, before any session_start().
// HttpOnly and SameSite are safe on both HTTP and HTTPS. Secure is enabled ONLY on
// HTTPS installs — detected from the configured CLIENT_BASE_URL scheme (correct even
// behind a TLS-terminating proxy, unlike $_SERVER['HTTPS']) — so HTTP-only environments
// (e.g. local dev on http://localhost) keep working: a Secure cookie is never sent over
// plain HTTP, which would otherwise break the session there.
if (function_exists('ini_set')) {
    $iceCookieSecure = defined('CLIENT_BASE_URL') && stripos(CLIENT_BASE_URL, 'https://') === 0;
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax'); // PHP 7.3+ ini directive
    ini_set('session.cookie_secure', $iceCookieSecure ? '1' : '0');
    ini_set('session.use_strict_mode', '1');    // reject attacker-supplied session IDs
    unset($iceCookieSecure);
}

// Per-deployment session cookie name. Cookies are scoped by host, NOT port
// (RFC 6265), so several IceHRM stacks on the same host — e.g. http://localhost:5555
// and http://localhost:5666 — would otherwise share one PHPSESSID cookie. Each app,
// on seeing a session id another app issued, rejects it (use_strict_mode above) and
// rewrites the cookie with a fresh id, so the stacks continually log each other out.
// Deriving the cookie name from the (port-specific) base URL gives every deployment
// its own cookie, so their sessions no longer collide.
if (defined('CLIENT_BASE_URL') && session_status() !== PHP_SESSION_ACTIVE) {
    session_name('ICESESS_' . substr(md5(CLIENT_BASE_URL), 0, 8));
}

if(!defined('SIGN_IN_ELEMENT_MAPPING_FIELD_NAME')){define('SIGN_IN_ELEMENT_MAPPING_FIELD_NAME','employee');}

if(!defined('APP_NAME')){define('APP_NAME','ICE Hrm');}
if(!defined('FB_URL')){define('FB_URL', 'https://www.facebook.com/icehrm');};
if(!defined('TWITTER_URL')){define('TWITTER_URL', 'https://twitter.com/icehrmapp');};

if(!defined('HOME_LINK_ADMIN')){
    define('HOME_LINK_ADMIN', CLIENT_BASE_URL . "?g=admin&n=dashboard&m=admin_Admin");
}
if(!defined('HOME_LINK_OTHERS')){
    define('HOME_LINK_OTHERS', CLIENT_BASE_URL . "?g=modules&n=dashboard&m=module_Personal_Information");
}

//Version
define('VERSION', '36.0.0');
define('CACHE_VALUE', '36.0.0.2026-08021822');
define('VERSION_NUMBER', '360000');
define('VERSION_DATE', '02/08/2026');

if(!defined('CONTACT_EMAIL')){define('CONTACT_EMAIL','icehrm@gamonoid.com');}
if(!defined('KEY_PREFIX')){define('KEY_PREFIX','IceHrm');}

// Google Analytics GA4 measurement ID.
if(!defined('GA4_MEASUREMENT_ID')){define('GA4_MEASUREMENT_ID','G-WQ0B30PDPY');}

define('UI_SHOW_SWITCH_PROFILE', true);
define('CRON_LOG', ini_get('error_log'));

define('MEMCACHE_HOST', '127.0.0.1');
define('MEMCACHE_PORT', '11211');

if (!defined('ICEHRM_ENV')) {
    define('ICEHRM_ENV', 'production');
}

if (!defined('ICEHRM_CORS_ALLOWED_ORIGINS')) {
    define('ICEHRM_CORS_ALLOWED_ORIGINS', '');
}

if (!function_exists('iceProExtensionsEnabled')) {
    function iceProExtensionsEnabled()
    {
        return false;
    }
}
define('LDAP_ENABLED', true);
define('SAML_ENABLED', true);
// Leave ships as a bundled extension (extensions/leave); its settings tab is
// additionally gated on that package being installed.
if (!defined('LEAVE_ENABLED')) {
    define('LEAVE_ENABLED', true);
}
if(!defined('APP_WEB_URL')) {define('APP_WEB_URL', 'https://icehrm.com');}

if (!defined('EXTENSIONS_URL')) {
    define('EXTENSIONS_URL', str_replace('/web/', '/extensions/', BASE_URL));
}

