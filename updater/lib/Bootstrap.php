<?php
/**
 * Makes the updater self-contained, then starts it.
 *
 * The updater replaces core/, web/, app/, bin/ and the extension trees. It therefore
 * cannot depend on any of them: half way through an update those directories do not
 * exist. So before anything else it takes its OWN copies of the two files it needs —
 * the deployment config and the base config — and includes those.
 *
 * Both copies are refreshed on every invocation, deliberately. "Copy only if missing"
 * looks tidier and is wrong twice over: after one successful update the stale
 * config.base.php would report the OLD VERSION_NUMBER forever, so the next update's
 * version comparison is meaningless; and a rotated database password would stop the
 * updater logging in while the application kept working. Refreshing costs two file
 * copies and removes both failure modes. Self-containment is preserved because the
 * refresh happens up front, before the destructive phase, and nothing re-reads the
 * originals afterwards.
 *
 * A note on config.base.php: it contains
 *     if (iceProExtensionsEnabled() && !file_exists(__DIR__ . '/../extensions-pro/util/admin/util.php')) die(...)
 * __DIR__ is the directory of the *included* file, so the copy only resolves that path
 * correctly while it sits exactly one level below the IceHRM root — which updater/ is.
 * Do not move this directory.
 */

class UpdaterBootstrap
{
    /** @var string absolute path to updater/ */
    public static $updaterDir;
    /** @var string absolute path to the IceHRM root (updater/..) */
    public static $rootDir;
    /** @var string absolute path to updater/data/ */
    public static $dataDir;

    /**
     * @return array|null null on success, or ['title'=>..,'message'=>..,'command'=>..]
     *                    describing a problem the user has to fix first
     */
    public static function init()
    {
        self::$updaterDir = dirname(__DIR__);
        self::$rootDir = dirname(self::$updaterDir);
        self::$dataDir = self::$updaterDir . '/data';

        $configProblem = self::refreshConfigCopies();
        if ($configProblem !== null) {
            return $configProblem;
        }

        // Including the copies — never the originals — is what makes the updater
        // independent of the application it is about to overwrite.
        require_once self::$updaterDir . '/config.php';
        require_once self::$updaterDir . '/config.base.php';

        $dataProblem = self::ensureDataDir();
        if ($dataProblem !== null) {
            return $dataProblem;
        }

        UpdaterLog::setFile(self::$dataDir . '/update.log');
        self::startSession();

        return null;
    }

    /**
     * Copy app/config.php and core/config.base.php into updater/, overwriting.
     */
    private static function refreshConfigCopies()
    {
        $sources = array(
            self::$rootDir . '/app/config.php' => self::$updaterDir . '/config.php',
            self::$rootDir . '/core/config.base.php' => self::$updaterDir . '/config.base.php',
        );

        foreach ($sources as $source => $destination) {
            if (!is_readable($source)) {
                // An existing copy is better than nothing: it means a previous run
                // already made the updater self-contained, which is exactly the state
                // this is trying to reach.
                if (is_readable($destination)) {
                    continue;
                }
                return array(
                    'title' => 'IceHRM configuration not found',
                    'message' => 'The updater could not read ' . htmlspecialchars($source)
                        . '. It must run from the updater/ directory inside an IceHRM installation.',
                    'command' => null,
                );
            }

            $contents = @file_get_contents($source);
            if ($contents === false || @file_put_contents($destination, $contents) === false) {
                return array(
                    'title' => 'Cannot write to the updater directory',
                    'message' => 'The updater needs to keep its own copy of the IceHRM '
                        . 'configuration in ' . htmlspecialchars(self::$updaterDir)
                        . ', so that it does not depend on files it is about to replace. '
                        . 'Make that directory writable by the web server:',
                    'command' => self::ownershipCommand(self::$updaterDir),
                );
            }
        }

        return null;
    }

    /**
     * Create updater/data/ and prove it is writable.
     *
     * Everything downstream — the download, the extracted tree, the backup of the
     * current installation, the lock and the log — lives here, so there is no point
     * showing any further step until this holds.
     */
    private static function ensureDataDir()
    {
        if (!is_dir(self::$dataDir) && !@mkdir(self::$dataDir, 0775, true) && !is_dir(self::$dataDir)) {
            return array(
                'title' => 'Cannot create updater/data',
                'message' => 'The updater stores the downloaded package, the extracted files '
                    . 'and a backup of your current installation in '
                    . htmlspecialchars(self::$dataDir) . ', which it could not create. '
                    . 'Create it and give the web server ownership:',
                'command' => 'mkdir -p ' . self::$dataDir . "\n" . self::ownershipCommand(self::$dataDir),
            );
        }

        // is_writable() can disagree with reality under some ACL and container setups,
        // so prove it by actually writing.
        $probe = self::$dataDir . '/.write-test';
        if (@file_put_contents($probe, 'ok') === false) {
            return array(
                'title' => 'updater/data is not writable',
                'message' => 'The updater needs to write to ' . htmlspecialchars(self::$dataDir)
                    . ' to download and stage the new version. Give the web server ownership:',
                'command' => self::ownershipCommand(self::$dataDir),
            );
        }
        @unlink($probe);

        self::denyWebAccess();

        return null;
    }

    /**
     * updater/data/ holds a complete, extracted copy of the new IceHRM — every PHP file
     * of it directly invokable over the web before installation, plus the downloaded
     * archive. Deny it at the web server.
     *
     * The .htaccess covers Apache. nginx ignores it, so the shipped config also needs
     *     location ^~ /updater/data { deny all; }
     * which is documented in updater/README.md.
     */
    private static function denyWebAccess()
    {
        $htaccess = self::$dataDir . '/.htaccess';
        if (file_exists($htaccess)) {
            return;
        }
        $rules = "# Staged update files: never web-accessible.\n"
            . "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n"
            . "<IfModule !mod_authz_core.c>\n    Order allow,deny\n    Deny from all\n</IfModule>\n";
        @file_put_contents($htaccess, $rules);
    }

    /** The chown/chmod line to show a user who has to fix permissions by hand. */
    private static function ownershipCommand($path)
    {
        $user = self::webServerUser();
        return 'sudo chown -R ' . $user . ':' . $user . ' ' . $path . "\n"
            . 'sudo chmod -R 775 ' . $path;
    }

    /** Best guess at the account PHP runs as, so the command can be copied verbatim. */
    private static function webServerUser()
    {
        if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
            $info = @posix_getpwuid(@posix_geteuid());
            if (!empty($info['name'])) {
                return $info['name'];
            }
        }
        $user = getenv('APACHE_RUN_USER');
        return !empty($user) ? $user : 'www-data';
    }

    /**
     * A session of the updater's own.
     *
     * Separate name and cookie path from the application: the updater cannot validate
     * an IceHRM session (it must keep working while core/ is being replaced), so it
     * never tries. Every visit starts logged out.
     */
    private static function startSession()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        if ($path === '') {
            $path = '/';
        }

        $secure = defined('CLIENT_BASE_URL') && stripos(CLIENT_BASE_URL, 'https://') === 0;
        session_name('ICEHRM_UPDATER');
        session_set_cookie_params(0, $path . '/', '', $secure, true);
        session_start();
    }

    /** True on a Pro installation, which downloads icehrmpro.zip rather than icehrm.zip. */
    public static function isPro()
    {
        return defined('IS_ICEHRM_PRO') && IS_ICEHRM_PRO;
    }

    /** 'icehrmpro' or 'icehrm' — the archive name and the directory it extracts to. */
    public static function packageName()
    {
        return self::isPro() ? 'icehrmpro' : 'icehrm';
    }

    /** The currently installed version, from the updater's own copy of config.base.php. */
    public static function currentVersion()
    {
        return defined('VERSION_NUMBER') ? (int) VERSION_NUMBER : 0;
    }
}
