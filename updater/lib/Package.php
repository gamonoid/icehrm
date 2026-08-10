<?php
/**
 * Downloads, validates and unpacks a release archive.
 *
 * The archive is attacker-influenced in the sense that matters: on Pro the URL is typed
 * in by whoever is running the update. So it is fetched over https only, never
 * extracted blindly, and every entry is checked before a single file is written.
 */

class UpdaterPackage
{
    /** Where the free edition is published. Pro customers paste their own signed link. */
    const FREE_URL = 'https://icehrm-public.s3.us-east-1.amazonaws.com/upgrades/latest/icehrm.zip';

    /**
     * Hosts a release may be downloaded from, on any installation.
     *
     * Default-deny. On Pro the URL is typed in by whoever is running the update, and
     * whatever comes back is unpacked over the entire installation — so an arbitrary
     * host is not something an administrator should be able to point this at, whether
     * by mistake, by following a phishing link, or with a stolen session. Restricting
     * it to where IceHRM actually publishes releases removes that entirely.
     *
     * Entries starting with '.' match the domain and any subdomain.
     */
    private static $officialHosts = array(
        'icehrm.com',
        '.icehrm.com',
        'icehrm-public.s3.us-east-1.amazonaws.com',
    );

    /**
     * Optional extra hosts, for testing a release before it is published.
     *
     * Read from a file at the installation root, one `host` or `host:port` per line.
     * It does not exist on a customer install — Dockerfile-release-test creates it, so
     * the relaxation is scoped to that throwaway image and cannot be reached by
     * anything shipped.
     *
     * Certificate verification is disabled for these hosts ONLY, because an internal
     * staging server typically presents a self-signed certificate. It stays enforced
     * for the official hosts, always, and every insecure fetch is logged.
     */
    const EXTRA_HOSTS_FILE = '.icehrm-updater-hosts';

    /** Redirect hops permitted, each re-checked against the host allowlist. */
    const MAX_REDIRECTS = 5;

    /**
     * Bounds on what an archive may expand to.
     *
     * ZipArchive::extractTo() writes whatever the archive claims, so without these a
     * "release" of a few megabytes can expand to fill the disk — taking the running
     * application down with it, since app/data and the database live on the same volume
     * on most installs. A real IceHRM release is ~200 MB uncompressed across ~25k files.
     */
    const MAX_UNCOMPRESSED_BYTES = 2147483648;  // 2 GiB
    const MAX_ENTRIES = 200000;
    const MAX_COMPRESSION_RATIO = 200;

    /** Give up on a stalled download rather than holding a PHP worker forever. */
    const CONNECT_TIMEOUT = 30;
    const LOW_SPEED_BYTES = 1024;
    const LOW_SPEED_SECONDS = 120;

    public static function archivePath()
    {
        return UpdaterBootstrap::$dataDir . '/' . UpdaterBootstrap::packageName() . '.zip';
    }

    /** Where the archive extracts to: updater/data/icehrmpro or updater/data/icehrm. */
    public static function extractedPath()
    {
        return UpdaterBootstrap::$dataDir . '/' . UpdaterBootstrap::packageName();
    }

    public static function defaultUrl()
    {
        return UpdaterBootstrap::isPro() ? '' : self::FREE_URL;
    }

    /** Extra hosts this installation permits, as written in the marker file. */
    public static function extraHosts()
    {
        $path = UpdaterBootstrap::$rootDir . '/' . self::EXTRA_HOSTS_FILE;
        if (!is_readable($path)) {
            return array();
        }
        $hosts = array();
        foreach (preg_split('/\R/', (string) file_get_contents($path)) as $line) {
            $line = trim($line);
            if ($line === '' || substr($line, 0, 1) === '#') {
                continue;
            }
            $hosts[] = strtolower($line);
        }
        return $hosts;
    }

    /** Everything a user may download from here, for display and error messages. */
    public static function allowedHostsForDisplay()
    {
        $official = array();
        foreach (self::$officialHosts as $host) {
            $official[] = substr($host, 0, 1) === '.' ? '*' . $host : $host;
        }
        return array_merge($official, self::extraHosts());
    }

    /**
     * Is this URL permitted, and may certificate verification be relaxed for it?
     *
     * @return array ['allowed'=>bool, 'insecure'=>bool, 'host'=>string]
     */
    public static function checkHost($url)
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $port = parse_url($url, PHP_URL_PORT);
        $hostPort = $port === null ? $host : $host . ':' . $port;

        if ($host === '') {
            return array('allowed' => false, 'insecure' => false, 'host' => '');
        }

        // Official hosts: exact match, or a subdomain of a '.'-prefixed entry. A port
        // is not accepted here — releases are served from 443 — so an entry cannot be
        // sidestepped by pointing at another service on the same name.
        foreach (self::$officialHosts as $allowed) {
            if (substr($allowed, 0, 1) === '.') {
                $suffixMatch = substr($host, -strlen($allowed)) === $allowed;
                if (($suffixMatch || $host === substr($allowed, 1)) && $port === null) {
                    return array('allowed' => true, 'insecure' => false, 'host' => $host);
                }
                continue;
            }
            if ($host === $allowed && $port === null) {
                return array('allowed' => true, 'insecure' => false, 'host' => $host);
            }
        }

        // Extra hosts: matched with the port when one is listed, so `dascore.org:8445`
        // permits that service and not everything else on the same name.
        foreach (self::extraHosts() as $allowed) {
            if ($allowed === $hostPort || $allowed === $host) {
                return array('allowed' => true, 'insecure' => true, 'host' => $hostPort);
            }
        }

        return array('allowed' => false, 'insecure' => false, 'host' => $hostPort);
    }

    /**
     * @return string|null null on success, otherwise a message for the user
     */
    public static function download($url)
    {
        $url = trim($url);
        if ($url === '') {
            return 'Enter the download link for the new version.';
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if ($scheme !== 'https') {
            return 'The download link must start with https://';
        }

        $host = self::checkHost($url);
        if (!$host['allowed']) {
            UpdaterLog::error('Refused download from a host that is not allowed: ' . $host['host']);
            return 'Downloads are only allowed from: '
                . htmlspecialchars(implode(', ', self::allowedHostsForDisplay()))
                . '. The link you entered points at '
                . htmlspecialchars($host['host'] === '' ? 'an unreadable host' : $host['host']) . '.';
        }

        $target = self::archivePath();
        if (file_exists($target) && !@unlink($target)) {
            return 'Could not remove the previous download at ' . htmlspecialchars($target) . '.';
        }

        UpdaterLog::info('Downloading ' . $url);
        // A release is large; do not let max_execution_time abort the transfer.
        @set_time_limit(0);

        // Redirects are followed MANUALLY, one hop at a time, re-checking the host
        // allowlist before each request.
        //
        // CURLOPT_FOLLOWLOCATION would validate only the URL the administrator typed
        // and then follow a Location header anywhere — so a single open redirect on an
        // allowed host (or a phished admin pasting one) would fetch the archive from an
        // attacker and install it over the entire application. Validating the first URL
        // is not validating the download.
        $status = 0;
        $error = '';
        $ok = false;
        $current = $url;

        for ($hop = 0; $hop <= self::MAX_REDIRECTS; $hop++) {
            $hopHost = self::checkHost($current);
            if (!$hopHost['allowed']) {
                @unlink($target);
                UpdaterLog::error('Refused redirect to a host that is not allowed: ' . $hopHost['host']);
                return 'The download was redirected to '
                    . htmlspecialchars($hopHost['host'] === '' ? 'an unreadable host' : $hopHost['host'])
                    . ', which is not an allowed source. Nothing was downloaded.';
            }

            $handle = @fopen($target, 'wb');   // truncates any partial body from a redirect
            if ($handle === false) {
                return 'Could not write to ' . htmlspecialchars($target) . '.';
            }

            $headers = array();
            $curl = curl_init($current);
            curl_setopt($curl, CURLOPT_FILE, $handle);      // stream to disk, never to memory
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
            curl_setopt($curl, CURLOPT_LOW_SPEED_LIMIT, self::LOW_SPEED_BYTES);
            curl_setopt($curl, CURLOPT_LOW_SPEED_TIME, self::LOW_SPEED_SECONDS);
            curl_setopt($curl, CURLOPT_USERAGENT, 'IceHRM-Updater');
            curl_setopt($curl, CURLOPT_HEADERFUNCTION, function ($ch, $line) use (&$headers) {
                $headers[] = $line;
                return strlen($line);
            });

            // Certificate verification is on for every official host and can only be
            // relaxed for a host explicitly listed in the marker file — a staging server
            // with a self-signed certificate. Decided PER HOP, so a redirect cannot carry
            // the relaxation to another host. Logged every time, because "TLS
            // verification was off" is the first thing anyone investigating needs to know.
            if ($hopHost['insecure']) {
                UpdaterLog::info('TLS certificate verification DISABLED for ' . $hopHost['host']
                    . ' (listed in ' . self::EXTRA_HOSTS_FILE . ')');
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            } else {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            }

            $ok = curl_exec($curl);
            $error = curl_error($curl);
            $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            fclose($handle);

            if ($ok === false) {
                break;
            }

            if ($status >= 300 && $status < 400) {
                $location = self::locationHeader($headers);
                if ($location === null) {
                    $ok = false;
                    $error = 'redirect without a Location header';
                    break;
                }
                $current = self::absoluteUrl($current, $location);
                UpdaterLog::info('Redirected to ' . $current);
                continue;
            }

            if ($status >= 400) {
                $ok = false;
                $error = 'server returned HTTP ' . $status;
            }
            break;
        }

        if ($ok !== false && $status >= 300 && $status < 400) {
            $ok = false;
            $error = 'too many redirects';
        }

        if ($ok === false) {
            @unlink($target);
            UpdaterLog::error('Download failed (HTTP ' . $status . '): ' . $error);
            return 'The download failed' . ($status ? ' (HTTP ' . $status . ')' : '') . ': '
                . htmlspecialchars($error) . '. Check the link and try again.';
        }

        $size = @filesize($target);
        if ($size === false || $size < 1024) {
            @unlink($target);
            return 'The downloaded file is empty or far too small to be an IceHRM release.';
        }

        UpdaterLog::info('Downloaded ' . UpdaterPreflight::formatBytes($size) . ' to ' . $target);
        return null;
    }

    /** The last Location header from a response. */
    private static function locationHeader($headers)
    {
        $location = null;
        foreach ($headers as $line) {
            if (stripos($line, 'location:') === 0) {
                $location = trim(substr($line, 9));
            }
        }
        return ($location === null || $location === '') ? null : $location;
    }

    /** Resolve a possibly-relative Location against the URL it came from. */
    private static function absoluteUrl($base, $location)
    {
        if (preg_match('#^https?://#i', $location)) {
            return $location;
        }
        $parts = parse_url($base);
        if (empty($parts['scheme']) || empty($parts['host'])) {
            return $location;
        }
        $root = $parts['scheme'] . '://' . $parts['host']
            . (isset($parts['port']) ? ':' . $parts['port'] : '');
        if (substr($location, 0, 1) === '/') {
            return $root . $location;
        }
        $path = isset($parts['path']) ? $parts['path'] : '/';
        return $root . substr($path, 0, strrpos($path, '/') + 1) . $location;
    }

    /**
     * Validate and extract.
     *
     * Refuses any entry whose path escapes the archive root — "zip slip", where an entry
     * named ../../core/service.php would otherwise be written straight over the running
     * application, before any confirmation and regardless of the version check.
     *
     * @return string|null null on success, otherwise a message for the user
     */
    public static function extract()
    {
        $archive = self::archivePath();
        if (!is_readable($archive)) {
            return 'The downloaded file is missing. Download it again.';
        }

        $zip = new ZipArchive();
        $opened = $zip->open($archive, ZipArchive::CHECKCONS);
        if ($opened !== true) {
            UpdaterLog::error('ZipArchive::open failed with code ' . var_export($opened, true));
            return 'The downloaded file is not a valid zip archive. Check the link and download it again.';
        }

        $expectedRoot = UpdaterBootstrap::packageName() . '/';
        $sawExpectedRoot = false;

        if ($zip->numFiles > self::MAX_ENTRIES) {
            $zip->close();
            UpdaterLog::error('Archive refused: ' . $zip->numFiles . ' entries');
            return 'The archive contains an implausible number of files and was rejected.';
        }

        $totalUncompressed = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === false) {
                $zip->close();
                return 'The archive could not be read.';
            }

            // Size is read from the archive's own index, BEFORE extracting anything, so
            // a decompression bomb is refused rather than discovered when the disk fills.
            $stat = $zip->statIndex($i);
            if (is_array($stat)) {
                $totalUncompressed += (int) $stat['size'];
                if ($totalUncompressed > self::MAX_UNCOMPRESSED_BYTES) {
                    $zip->close();
                    UpdaterLog::error('Archive refused: expands to more than '
                        . UpdaterPreflight::formatBytes(self::MAX_UNCOMPRESSED_BYTES));
                    return 'The archive expands to more than '
                        . UpdaterPreflight::formatBytes(self::MAX_UNCOMPRESSED_BYTES)
                        . ' and was rejected.';
                }
                if (!empty($stat['comp_size']) && $stat['comp_size'] > 1024
                    && ($stat['size'] / $stat['comp_size']) > self::MAX_COMPRESSION_RATIO
                ) {
                    $zip->close();
                    UpdaterLog::error('Archive refused: entry "' . $name . '" has an extreme compression ratio');
                    return 'The archive contains a file with an implausible compression ratio and was rejected.';
                }
            }

            // Absolute paths, parent traversal and Windows separators are all refused.
            $normalised = str_replace('\\', '/', $name);
            if (substr($normalised, 0, 1) === '/'
                || strpos($normalised, '../') !== false
                || $normalised === '..'
                || preg_match('#^[A-Za-z]:#', $normalised)
            ) {
                $zip->close();
                UpdaterLog::error('Refused archive entry: ' . $name);
                return 'The archive contains an unsafe file path and was rejected.';
            }

            if (strpos($normalised, $expectedRoot) === 0) {
                $sawExpectedRoot = true;
            }
        }

        if (!$sawExpectedRoot) {
            $zip->close();
            return 'The archive does not contain a "' . htmlspecialchars(rtrim($expectedRoot, '/'))
                . '" directory, so it is not '
                . (UpdaterBootstrap::isPro() ? 'an IceHRM Pro' : 'an IceHRM') . ' release.';
        }

        // A previous attempt may have left a partial tree.
        $destination = self::extractedPath();
        if (is_dir($destination)) {
            UpdaterFiles::deleteTree($destination);
        }

        UpdaterLog::info('Extracting ' . $archive . ' (' . $zip->numFiles . ' entries, '
            . UpdaterPreflight::formatBytes($totalUncompressed) . ' uncompressed)');
        if (!$zip->extractTo(UpdaterBootstrap::$dataDir)) {
            $zip->close();
            return 'The archive could not be extracted. Check free disk space and permissions on '
                . htmlspecialchars(UpdaterBootstrap::$dataDir) . '.';
        }
        $zip->close();

        if (!is_dir($destination)) {
            return 'The archive extracted, but ' . htmlspecialchars($destination) . ' was not created.';
        }

        UpdaterLog::info('Extracted to ' . $destination);
        return null;
    }

    /**
     * The version of the extracted package, read from ITS config.base.php.
     *
     * VERSION_NUMBER is a numeric string ('360000'), so this parses the literal rather
     * than including the file — including it would define constants that clash with the
     * updater's own copy, and would run that file's util-module check against the wrong
     * directory.
     *
     * @return int 0 when it cannot be determined
     */
    public static function packagedVersion()
    {
        $configPath = self::extractedPath() . '/core/config.base.php';
        if (!is_readable($configPath)) {
            return 0;
        }
        $contents = (string) @file_get_contents($configPath);
        if (preg_match("/define\s*\(\s*'VERSION_NUMBER'\s*,\s*'([0-9]+)'\s*\)/", $contents, $matches)) {
            return (int) $matches[1];
        }
        // Double-quoted variant, in case the release ever changes quoting style.
        if (preg_match('/define\s*\(\s*"VERSION_NUMBER"\s*,\s*"([0-9]+)"\s*\)/', $contents, $matches)) {
            return (int) $matches[1];
        }
        return 0;
    }

    /** A human-readable form of the numeric version: 360000 -> 36.0.0 */
    public static function formatVersion($version)
    {
        $version = (string) $version;
        if (strlen($version) !== 6) {
            return $version;
        }
        return (int) substr($version, 0, 2) . '.'
            . (int) substr($version, 2, 2) . '.'
            . (int) substr($version, 4, 2);
    }

    /** Remove the archive and the extracted tree once an update has succeeded. */
    public static function cleanUp()
    {
        $archive = self::archivePath();
        if (file_exists($archive)) {
            @unlink($archive);
        }
        $extracted = self::extractedPath();
        if (is_dir($extracted)) {
            UpdaterFiles::deleteTree($extracted);
        }
    }
}
