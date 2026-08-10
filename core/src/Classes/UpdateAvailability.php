<?php

namespace Classes;

/**
 * Is a newer IceHRM available, and what link should the admin follow to install it?
 *
 * The installed version is VERSION ('36.0.0'). The available version comes from the
 * marketplace snapshot this installation last synced, stored in
 * SystemData['marketplace:my_extensions'] — a PHP-SERIALIZED array (not JSON), one
 * entry per licensed module, of which exactly one describes the platform itself:
 * 'icehrmpro' on Pro, 'icehrm' on the open-source build. Its `version` is written
 * "v36.0.1", so the leading v is stripped before comparing.
 *
 * Admin-only. Nobody else can act on the result — the updater refuses every other user
 * level — so showing it to them would be noise ending in a refusal.
 */
class UpdateAvailability
{
    const CHANGELOG_PRO = 'https://icehrm.com/changelog-icehrmpro';
    const CHANGELOG_OSS = 'https://icehrm.com/changelog-opensource';

    /** How long the signed updater link stays valid. */
    const LINK_LIFETIME = 7200; // 2 hours

    /**
     * @param \Users\Common\Model\User $user
     * @return array|null null when up to date, unknown, or the user is not an admin
     */
    public static function get($user)
    {
        if (empty($user) || $user->user_level !== 'Admin') {
            return null;
        }

        $current = defined('VERSION') ? (string) VERSION : '';
        if ($current === '') {
            return null;
        }

        $latest = self::latestPublishedVersion();
        if ($latest === null) {
            // No marketplace snapshot yet (never connected, or the entry is absent).
            // Silence is right here: an installation that cannot know is not one to
            // nag about being out of date.
            return null;
        }

        if (version_compare($latest, $current, '<=')) {
            return null;
        }

        return array(
            'currentVersion' => $current,
            'latestVersion' => $latest,
            'isPro' => self::isPro(),
            'changelogUrl' => self::isPro() ? self::CHANGELOG_PRO : self::CHANGELOG_OSS,
            'updaterUrl' => self::signedUpdaterUrl(),
        );
    }

    /**
     * The platform's version from the marketplace snapshot, or null.
     *
     * @return string|null e.g. '36.0.1'
     */
    public static function latestPublishedVersion()
    {
        $raw = BaseService::getInstance()->getSystemData('marketplace:my_extensions');

        // The column holds a PHP-serialized array, but getSystemData() already
        // unserializes it and only falls back to the raw string when that fails. Handle
        // both: assuming a string here is what made this return null against real data.
        if (is_array($raw)) {
            $entries = $raw;
        } elseif (is_string($raw) && $raw !== '') {
            // allowed_classes => false: a crafted payload can produce arrays and
            // scalars, never an object.
            $entries = @unserialize($raw, array('allowed_classes' => false));
        } else {
            return null;
        }

        if (!is_array($entries)) {
            return null;
        }

        $wanted = self::isPro() ? 'icehrmpro' : 'icehrm';
        foreach ($entries as $entry) {
            if (!is_array($entry) || !isset($entry['directory']) || $entry['directory'] !== $wanted) {
                continue;
            }
            $version = isset($entry['version']) ? trim((string) $entry['version']) : '';
            if ($version === '') {
                return null;
            }
            // "v36.0.1" -> "36.0.1"
            if (strtolower(substr($version, 0, 1)) === 'v') {
                $version = substr($version, 1);
            }
            return preg_match('/^[0-9][0-9.]*$/', $version) ? $version : null;
        }

        return null;
    }

    private static function isPro()
    {
        return defined('IS_ICEHRM_PRO') && IS_ICEHRM_PRO;
    }

    /**
     * The updater, with a link that stops working after two hours.
     *
     * The updater sits beside the application: CLIENT_BASE_URL is ".../app/", so the
     * updater is ".../updater/".
     */
    public static function signedUpdaterUrl()
    {
        $base = defined('CLIENT_BASE_URL') ? (string) CLIENT_BASE_URL : '';
        if ($base === '') {
            return null;
        }
        // Drop the final path segment ("app/") and replace it.
        $root = rtrim($base, '/');
        $root = substr($root, 0, strrpos($root, '/') + 1);

        $expires = time() + self::LINK_LIFETIME;
        return $root . 'updater/?e=' . $expires . '&t=' . self::signature($expires);
    }

    /**
     * The link signature.
     *
     * MUST stay byte-identical to UpdaterToken::signature() in updater/lib/Token.php.
     * The updater deliberately does not load a single class from the application — it
     * has to keep working while core/ is being replaced — so the algorithm is written
     * out in both places rather than shared. Keep them in step.
     *
     * Keyed on the instance signing secret combined with APP_PASSWORD, the same
     * construction BaseService uses elsewhere: the secret lives in the database and
     * APP_PASSWORD in the config file, so neither a stolen backup nor SQL injection
     * alone is enough to mint a link.
     */
    public static function signature($expires)
    {
        $secret = BaseService::getInstance()->getSigningSecret();
        $key = $secret . (defined('APP_PASSWORD') ? APP_PASSWORD : '');
        return hash_hmac('sha256', 'updater|' . $expires, $key);
    }
}
