<?php
/**
 * The signed link that admits an administrator to the updater.
 *
 * The updater is not reachable on its own: even the sign-in form requires a valid,
 * unexpired token. That token is minted inside the application by the "Update" button
 * on the update-available banner, which only administrators see. So reaching this
 * endpoint at all already requires an authenticated admin session in IceHRM — the
 * updater's own password check is then a second, independent gate.
 *
 * The signature MUST stay byte-identical to Classes\UpdateAvailability::signature().
 * The algorithm is written out in both places rather than shared, because the updater
 * cannot load a class from an application it is in the middle of replacing. If one side
 * changes, change the other.
 *
 * Keyed on the instance signing secret (database) combined with APP_PASSWORD (config
 * file), so neither a stolen backup nor SQL injection alone can forge a link.
 *
 * RECOVERY: if the application is too broken to sign into, no banner and therefore no
 * link. Mint one on the server instead:
 *
 *     php updater/token.php
 */

class UpdaterToken
{
    /** Marks the session as admitted, so later form posts need not carry the token. */
    const SESSION_KEY = 'updater_token_ok';

    /**
     * Has this visitor presented a valid link, now or earlier in this session?
     *
     * The token is checked when they arrive. Once accepted it is remembered for the
     * session: the flow is a series of POSTs whose URLs carry no query string, and
     * re-validating an expiry mid-update would strand an administrator half way
     * through replacing the application — the worst possible moment to lock them out.
     *
     * @return array ['ok'=>bool, 'reason'=>string]
     */
    public static function check()
    {
        if (!empty($_SESSION[self::SESSION_KEY])) {
            return array('ok' => true, 'reason' => '');
        }

        $expires = isset($_GET['e']) ? (string) $_GET['e'] : '';
        $signature = isset($_GET['t']) ? (string) $_GET['t'] : '';

        if ($expires === '' || $signature === '') {
            return array('ok' => false, 'reason' => 'missing');
        }
        if (!ctype_digit($expires)) {
            return array('ok' => false, 'reason' => 'invalid');
        }

        $expected = self::signature($expires);
        if ($expected === null) {
            return array('ok' => false, 'reason' => 'unavailable');
        }
        if (!hash_equals($expected, $signature)) {
            UpdaterLog::error('Updater link rejected: bad signature');
            return array('ok' => false, 'reason' => 'invalid');
        }

        // Expiry is checked AFTER the signature, so an attacker cannot use the response
        // to learn whether a guessed signature was otherwise well-formed.
        if ((int) $expires < time()) {
            UpdaterLog::info('Updater link rejected: expired '
                . (time() - (int) $expires) . 's ago');
            return array('ok' => false, 'reason' => 'expired');
        }

        $_SESSION[self::SESSION_KEY] = true;
        UpdaterLog::info('Updater link accepted (valid until ' . date('Y-m-d H:i:s', (int) $expires) . ')');
        return array('ok' => true, 'reason' => '');
    }

    /**
     * Admit this session without a link, because IceHRM is not answering.
     *
     * The link exists to prove an administrator got here from inside the application.
     * When the application is down there is no banner to click, and the updater is the
     * tool needed to repair it — so requiring a link then would lock the door on the
     * only person who can fix things. The sign-in that follows is unchanged: an IceHRM
     * administrator password, checked against the same database.
     */
    public static function admitForRecovery()
    {
        $_SESSION[self::SESSION_KEY] = true;
    }

    /**
     * @return string|null null when the signing secret cannot be read
     */
    public static function signature($expires)
    {
        $secret = self::signingSecret();
        if ($secret === null) {
            return null;
        }
        $key = $secret . (defined('APP_PASSWORD') ? APP_PASSWORD : '');
        return hash_hmac('sha256', 'updater|' . $expires, $key);
    }

    /**
     * The instance signing secret, read straight from SystemData.
     *
     * Deliberately does NOT create one when missing, unlike the application's
     * getSigningSecret(): a fresh secret here would invalidate the very link the
     * visitor is holding, and the updater has no business writing to the database.
     *
     * @return string|null
     */
    private static function signingSecret()
    {
        $rows = UpdaterDb::select(
            'SELECT `value` FROM SystemData WHERE name = ? LIMIT 1',
            array('Instance: Signing Secret')
        );
        if (empty($rows) || !isset($rows[0]['value']) || $rows[0]['value'] === '') {
            UpdaterLog::error('Updater link cannot be verified: no instance signing secret');
            return null;
        }
        return (string) $rows[0]['value'];
    }

    /** Human-readable explanation for the refusal screen. */
    public static function explain($reason)
    {
        switch ($reason) {
            case 'expired':
                return 'This update link has expired. Update links are valid for two hours.';
            case 'invalid':
                return 'This update link is not valid.';
            case 'unavailable':
                return 'This installation has no signing secret yet, so update links cannot be '
                    . 'verified. Sign in to IceHRM once as an administrator, then try again.';
            default:
                return 'The updater can only be opened from IceHRM.';
        }
    }
}
