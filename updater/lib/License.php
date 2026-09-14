<?php
/**
 * What is this installation licensed for?
 *
 * Only one question is asked: does the customer hold a live IceHRM Pro subscription?
 * The answer decides whether an OPEN-SOURCE installation may move up to Pro — paste a
 * signed Pro download link, unpack an `icehrmpro/` archive over itself, and gain
 * extensions-pro/. Without it, the free edition stays pinned to the published free
 * release, which is what it was before this existed.
 *
 * The data is the marketplace snapshot the application syncs from icehrm.com, in
 * SystemData['marketplace:my_extensions'] — a PHP-serialized array with one entry per
 * licensed module, of which 'icehrm' and 'icehrmpro' describe the platform itself.
 * BOTH are present whatever edition is running: icehrm.com reports the whole
 * subscription, not just the part in use.
 *
 * Read directly out of the database with the updater's own tiny mysqli wrapper, and the
 * validity rules are written out here rather than shared, for the same reason nothing
 * else in updater/ loads an application class: half way through an update, core/ does
 * not exist. Keep the rules in step with Classes\UpdateAvailability::isCurrent().
 *
 * This is an ENABLING check, not a security boundary. It cannot be: an installation is
 * free to lie to itself about what it owns. The real gate is icehrm.com, which only
 * mints a download link for a subscription that is actually paid up — and the host
 * allowlist in UpdaterPackage, which means even a forged entitlement can only fetch a
 * release from IceHRM's own servers.
 */

class UpdaterLicense
{
    const SYSTEM_DATA_KEY = 'marketplace:my_extensions';
    const PLATFORM_PRO = 'icehrmpro';

    /** @var bool|null memoised answer for this request */
    private static $hasPro = null;

    /**
     * Does the customer hold an IceHRM Pro subscription that has not lapsed?
     *
     * @return bool false whenever the answer is not a clear yes — no snapshot, no
     *              database, an expired licence or an unreadable one
     */
    public static function hasProSubscription()
    {
        if (self::$hasPro !== null) {
            return self::$hasPro;
        }

        self::$hasPro = false;

        $entries = self::snapshotEntries();
        if ($entries === null) {
            return self::$hasPro;
        }

        foreach ($entries as $entry) {
            if (!is_array($entry) || !isset($entry['directory'])
                || $entry['directory'] !== self::PLATFORM_PRO
            ) {
                continue;
            }
            self::$hasPro = self::isCurrent($entry);
            break;
        }

        if (self::$hasPro) {
            UpdaterLog::info('Pro subscription found in the marketplace snapshot');
        }

        return self::$hasPro;
    }

    /**
     * The marketplace snapshot as an array of entries, or null.
     *
     * @return array|null
     */
    private static function snapshotEntries()
    {
        if (UpdaterDb::connect() === null) {
            return null;
        }

        $rows = UpdaterDb::select(
            'SELECT value FROM SystemData WHERE name = ? LIMIT 1',
            array(self::SYSTEM_DATA_KEY)
        );
        if (empty($rows) || !isset($rows[0]['value']) || $rows[0]['value'] === '') {
            return null;
        }

        // allowed_classes => false: a crafted payload can produce arrays and scalars,
        // never an object, so unserializing a database value cannot run code.
        $entries = @unserialize((string) $rows[0]['value'], array('allowed_classes' => false));

        return is_array($entries) ? $entries : null;
    }

    /**
     * Is this licence still live?
     *
     * `is_expired` is what icehrm.com decided, and is trusted first. `expiry_date` is
     * checked as well so a stale snapshot — one synced before the subscription lapsed —
     * does not keep offering an upgrade the customer can no longer download.
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
}
