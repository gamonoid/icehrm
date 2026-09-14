<?php

namespace Classes;

/**
 * Is a newer IceHRM available, and what link should the admin follow to install it?
 *
 * The installed version is VERSION ('36.0.0'). What is *available* comes from the
 * marketplace snapshot this installation last synced, stored in
 * SystemData['marketplace:my_extensions'] — a PHP-SERIALIZED array (not JSON), one
 * entry per licensed module. Two of those describe the platform itself rather than an
 * add-on: 'icehrm' (the open-source build) and 'icehrmpro'. Their `version` is written
 * "v36.0.1", so the leading v is stripped before comparing.
 *
 * Both platform entries are present whatever edition is installed — icehrm.com reports
 * the whole subscription, not just the part in use — which is what lets this tell the
 * three cases apart:
 *
 *   1. Pro installed, a newer Pro published        -> 'update'      (install the update)
 *   2. Open source installed, Pro subscription     -> 'pro-upgrade' (move up to Pro)
 *   3. Open source installed, a newer OSS released -> 'update'      (install the update)
 *
 * Case 2 outranks case 3: an installation entitled to Pro is told about Pro rather than
 * about an open-source point release it would leave behind anyway. It is also NOT
 * conditional on a version comparison — the offer is a change of edition, so it stands
 * even when the two editions are on the same version number.
 *
 * Admin-only. Nobody else can act on the result — the updater refuses every other user
 * level — so showing it to them would be noise ending in a refusal.
 */
class UpdateAvailability
{
    const CHANGELOG_PRO = 'https://icehrm.com/changelog-icehrmpro';
    const CHANGELOG_OSS = 'https://icehrm.com/changelog-opensource';

    /** Where a Pro customer collects the signed download link for the release. */
    const DOWNLOADS_URL = 'https://icehrm.com/app/extensions';

    /** How long the signed updater link stays valid. */
    const LINK_LIFETIME = 7200; // 2 hours

    /** Platform entries, by the `directory` they appear under in the snapshot. */
    const PLATFORM_PRO = 'icehrmpro';
    const PLATFORM_OSS = 'icehrm';

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

        $entries = self::snapshotEntries();
        if ($entries === null) {
            // No marketplace snapshot yet (never connected, or the entries are absent).
            // Silence is right here: an installation that cannot know is not one to
            // nag about being out of date.
            return null;
        }

        // Case 2 — an open-source installation whose owner is licensed for Pro.
        if (!self::isPro()) {
            $pro = self::licensedPlatform($entries, self::PLATFORM_PRO);
            if ($pro !== null) {
                return array(
                    'kind' => 'pro-upgrade',
                    'currentVersion' => $current,
                    'latestVersion' => $pro,
                    'isPro' => false,
                    'targetIsPro' => true,
                    'changelogUrl' => self::CHANGELOG_PRO,
                    'downloadsUrl' => self::DOWNLOADS_URL,
                    'updaterUrl' => self::signedUpdaterUrl(),
                );
            }
        }

        // Cases 1 and 3 — same edition, a newer version published.
        $latest = self::platformVersion($entries, self::isPro() ? self::PLATFORM_PRO : self::PLATFORM_OSS);
        if ($latest === null || version_compare($latest, $current, '<=')) {
            return null;
        }

        return array(
            'kind' => 'update',
            'currentVersion' => $current,
            'latestVersion' => $latest,
            'isPro' => self::isPro(),
            'targetIsPro' => self::isPro(),
            'changelogUrl' => self::isPro() ? self::CHANGELOG_PRO : self::CHANGELOG_OSS,
            'downloadsUrl' => self::isPro() ? self::DOWNLOADS_URL : null,
            'updaterUrl' => self::signedUpdaterUrl(),
        );
    }

    /**
     * The installed edition's version from the marketplace snapshot, or null.
     *
     * @return string|null e.g. '36.0.1'
     */
    public static function latestPublishedVersion()
    {
        $entries = self::snapshotEntries();
        if ($entries === null) {
            return null;
        }
        return self::platformVersion($entries, self::isPro() ? self::PLATFORM_PRO : self::PLATFORM_OSS);
    }

    /**
     * Does this installation hold a Pro subscription it can still act on?
     *
     * True whatever edition is installed, so an open-source build can ask whether it is
     * entitled to move up. The updater asks the same question of the same data, from its
     * own code — see UpdaterLicense — because it may not load a class from here.
     *
     * @return bool
     */
    public static function hasProSubscription()
    {
        $entries = self::snapshotEntries();
        if ($entries === null) {
            return false;
        }
        return self::licensedPlatform($entries, self::PLATFORM_PRO) !== null;
    }

    /**
     * The snapshot as an array of entries, or null when there is nothing usable.
     *
     * @return array|null
     */
    private static function snapshotEntries()
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

        return is_array($entries) ? $entries : null;
    }

    /**
     * The version of one platform entry, whatever the state of its licence.
     *
     * @param array  $entries
     * @param string $directory 'icehrm' or 'icehrmpro'
     * @return string|null
     */
    private static function platformVersion(array $entries, $directory)
    {
        $entry = self::findEntry($entries, $directory);
        return $entry === null ? null : self::entryVersion($entry);
    }

    /**
     * The version of one platform entry, but only while its licence is still good.
     *
     * An expired subscription is not an offer: sending that customer to the updater ends
     * in a download link icehrm.com will not mint.
     *
     * @param array  $entries
     * @param string $directory 'icehrm' or 'icehrmpro'
     * @return string|null
     */
    private static function licensedPlatform(array $entries, $directory)
    {
        $entry = self::findEntry($entries, $directory);
        if ($entry === null || !self::isCurrent($entry)) {
            return null;
        }
        return self::entryVersion($entry);
    }

    /**
     * @param array  $entries
     * @param string $directory
     * @return array|null
     */
    private static function findEntry(array $entries, $directory)
    {
        foreach ($entries as $entry) {
            if (is_array($entry) && isset($entry['directory']) && $entry['directory'] === $directory) {
                return $entry;
            }
        }
        return null;
    }

    /**
     * Is this licence still live?
     *
     * `is_expired` is what icehrm.com decided, and is trusted first. `expiry_date` is
     * checked as well so a stale snapshot — one synced before the subscription lapsed —
     * does not keep advertising an upgrade the customer can no longer download.
     *
     * @param array $entry
     * @return bool
     */
    private static function isCurrent(array $entry)
    {
        if (!empty($entry['is_expired'])) {
            return false;
        }
        if (!empty($entry['expiry_date'])) {
            $expiry = strtotime((string) $entry['expiry_date']);
            if ($expiry !== false && $expiry < time()) {
                return false;
            }
        }
        return true;
    }

    /**
     * "v36.0.1" -> "36.0.1", or null when it is not a version at all.
     *
     * @param array $entry
     * @return string|null
     */
    private static function entryVersion(array $entry)
    {
        $version = isset($entry['version']) ? trim((string) $entry['version']) : '';
        if ($version === '') {
            return null;
        }
        if (strtolower(substr($version, 0, 1)) === 'v') {
            $version = substr($version, 1);
        }
        return preg_match('/^[0-9][0-9.]*$/', $version) ? $version : null;
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
