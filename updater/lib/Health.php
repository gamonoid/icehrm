<?php
/**
 * Did the update work?
 *
 * Two independent signals, because neither is sufficient alone:
 *
 *   FILES    Read straight off disk: are the new version's files actually in place?
 *            This needs no network at all, so it is the signal that always works.
 *   HTTP     Does the application answer? This is the better signal when it works,
 *            but the server frequently cannot reach its own public URL.
 *
 * That last point is why this class is more careful than "curl CLIENT_BASE_URL and
 * offer a rollback if it fails". A server being unable to reach its own address is
 * ordinary, not a symptom:
 *
 *   - Containers publish a port on the HOST. CLIENT_BASE_URL says localhost:9190 while
 *     nginx inside the container listens on 8080, so the public URL is unreachable from
 *     inside by construction. (This is exactly what the release-test stack does.)
 *   - Split-horizon DNS resolves the public hostname only outside the network.
 *   - A reverse proxy or load balancer in front may not be routable from behind it.
 *   - Egress firewall rules block outbound HTTP from application servers.
 *   - A WAF may answer a server-side request with a challenge or a 403.
 *
 * So the HTTP probe tries the public URL first and then the LOOPBACK — 127.0.0.1 on the
 * port this very request arrived on, carrying the configured Host header — which is what
 * succeeds in the container case. And when neither works, an unreachable result with
 * verified files is reported as "check it in your browser", not as a failure, because
 * frightening an administrator into rolling back a good update is the worse outcome.
 */

class UpdaterHealth
{
    const TIMEOUT = 20;

    /**
     * @param int $expectedVersion the version that was just installed, 0 if unknown
     * @return array {
     *     state: 'healthy'|'broken'|'unreachable',
     *     status: int,           HTTP status, 0 when nothing answered
     *     message: string,
     *     filesOk: bool,         the new files are verifiably in place
     *     fileDetail: string,
     *     via: string            which URL answered
     * }
     */
    public static function check($expectedVersion = 0)
    {
        $files = self::verifyInstallation($expectedVersion);
        $probe = self::probeApplication();

        $result = array(
            'state' => $probe['state'],
            'status' => $probe['status'],
            'message' => $probe['message'],
            'filesOk' => $files['ok'],
            'fileDetail' => $files['detail'],
            'via' => $probe['via'],
        );

        UpdaterLog::info('Health check: files=' . ($files['ok'] ? 'ok' : 'PROBLEM')
            . ' (' . $files['detail'] . '), http=' . $probe['state']
            . ($probe['status'] ? ' ' . $probe['status'] : '')
            . ($probe['via'] !== '' ? ' via ' . $probe['via'] : ''));

        return $result;
    }

    /**
     * Confirm on disk that the new version is installed.
     *
     * Reads VERSION_NUMBER out of the freshly installed core/config.base.php rather
     * than trusting the copy the updater holds — the updater refreshes its own copies
     * at the start of every request, so by now they already reflect the new files and
     * comparing against them would prove nothing.
     */
    private static function verifyInstallation($expectedVersion)
    {
        $root = UpdaterBootstrap::$rootDir;

        $required = array('core/config.base.php', 'core/service.php', 'app/index.php', 'web');
        $missing = array();
        foreach ($required as $path) {
            if (!file_exists($root . '/' . $path)) {
                $missing[] = $path;
            }
        }
        if (!empty($missing)) {
            return array('ok' => false, 'detail' => 'missing after update: ' . implode(', ', $missing));
        }

        $installed = self::installedVersion();
        if ($installed === 0) {
            return array('ok' => false, 'detail' => 'could not read VERSION_NUMBER from the installed files');
        }

        if ($expectedVersion > 0 && $installed !== $expectedVersion) {
            return array(
                'ok' => false,
                'detail' => 'installed version is ' . UpdaterPackage::formatVersion($installed)
                    . ', expected ' . UpdaterPackage::formatVersion($expectedVersion),
            );
        }

        return array(
            'ok' => true,
            'detail' => 'version ' . UpdaterPackage::formatVersion($installed) . ' in place',
        );
    }

    /** VERSION_NUMBER as it now stands in the installed core/config.base.php. */
    private static function installedVersion()
    {
        $path = UpdaterBootstrap::$rootDir . '/core/config.base.php';
        if (!is_readable($path)) {
            return 0;
        }
        $contents = (string) @file_get_contents($path);
        if (preg_match("/define\s*\(\s*'VERSION_NUMBER'\s*,\s*'([0-9]+)'\s*\)/", $contents, $m)) {
            return (int) $m[1];
        }
        return 0;
    }

    /**
     * Is the application answering? Tries the configured URL, then the loopback.
     *
     * Public because the token gate needs it too: when IceHRM itself is down there is
     * no banner to mint an update link from, and the updater is exactly the tool needed.
     *
     * @return array ['state'=>'healthy'|'broken'|'unreachable', 'status'=>int, 'via'=>string, 'message'=>string]
     */
    public static function probeApplication()
    {
        $url = defined('CLIENT_BASE_URL') ? CLIENT_BASE_URL : '';

        if ($url === '' || !function_exists('curl_init')) {
            return array(
                'state' => 'unreachable',
                'status' => 0,
                'via' => '',
                'message' => $url === ''
                    ? 'CLIENT_BASE_URL is not configured, so the application could not be checked automatically.'
                    : 'The curl extension is unavailable, so the application could not be checked automatically.',
            );
        }

        $attempt = self::request($url);
        if ($attempt['status'] > 0) {
            return self::classify($attempt, $url);
        }

        // The public URL did not answer. Try the same path on the loopback, on the port
        // this request came in on, with the configured Host header — which is what a
        // containerised or proxied install needs.
        $loopback = self::loopbackUrl($url);
        if ($loopback !== null) {
            $attempt = self::request($loopback['url'], $loopback['host']);
            if ($attempt['status'] > 0) {
                return self::classify($attempt, $loopback['url'] . ' (Host: ' . $loopback['host'] . ')');
            }
        }

        return array(
            'state' => 'unreachable',
            'status' => 0,
            'via' => '',
            'message' => 'The updater could not reach ' . htmlspecialchars($url) . ' from the server itself'
                . ($loopback === null ? '' : ', nor ' . htmlspecialchars($loopback['url']))
                . '. That is common and usually means the server cannot route to its own '
                . 'public address — containers publish their port on the host, and split-horizon '
                . 'DNS, reverse proxies and egress firewalls all have the same effect. It does not '
                . 'by itself mean the update failed.',
        );
    }

    /** Build 127.0.0.1:<this request's port><path> plus the Host header to send with it. */
    private static function loopbackUrl($configuredUrl)
    {
        $parts = parse_url($configuredUrl);
        if (empty($parts['host'])) {
            return null;
        }
        $port = isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 0;
        if ($port <= 0) {
            return null;
        }

        $host = $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
        $path = isset($parts['path']) ? $parts['path'] : '/';

        return array(
            'url' => 'http://127.0.0.1:' . $port . $path,
            'host' => $host,
        );
    }

    /**
     * @return array ['status'=>int,'error'=>string]
     */
    private static function request($url, $hostHeader = null)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        // Self-signed and internal certificates are common on private deployments, and
        // this is a liveness probe of the server's own site, not a channel carrying
        // anything secret.
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, 'IceHRM-Updater-HealthCheck');
        if ($hostHeader !== null) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Host: ' . $hostHeader));
        }

        $body = curl_exec($curl);
        $error = curl_error($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($body === false && $status === 0) {
            return array('status' => 0, 'error' => $error);
        }
        return array('status' => $status, 'error' => $error);
    }

    private static function classify($attempt, $via)
    {
        if ($attempt['status'] >= 200 && $attempt['status'] < 400) {
            return array(
                'state' => 'healthy',
                'status' => $attempt['status'],
                'via' => $via,
                'message' => 'IceHRM responded normally (HTTP ' . $attempt['status'] . ').',
            );
        }
        return array(
            'state' => 'broken',
            'status' => $attempt['status'],
            'via' => $via,
            'message' => 'IceHRM returned HTTP ' . $attempt['status'] . ' after the update.',
        );
    }
}
