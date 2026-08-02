<?php

namespace Classes;

use Users\Common\Model\User;

/**
 * AppShellRestEndPoint
 *
 * Phase 0 of the SPA migration (see docs/SPA_MIGRATION_PLAN.md).
 *
 * Read-only endpoints that feed the React app shell the same data the legacy
 * PHP header/footer compute today, so the shell can render the sidebar/top-nav,
 * gate menus by permission, and discover extensions WITHOUT reimplementing any
 * of that logic:
 *
 *   GET /app/api/appshell/bootstrap         -> shell bootstrap (user + menu + chrome)
 *   GET /app/api/appshell/menu              -> filtered menu tree for the current user
 *   GET /app/api/appshell/permissions?path= -> per-action permissions for a module
 *   GET /app/api/appshell/extensions        -> directory-driven extension manifest
 *
 * Auth is handled by RestEndPoint::process() (bearer/JWT), identical to the
 * existing data.php / REST adapters.
 */
class AppShellRestEndPoint extends RestEndPoint
{
    /**
     * Filtered menu tree for the authenticated user.
     * Mirrors exactly what the legacy sidebar (core/header.php) would show.
     */
    public function getMenu(User $user)
    {
        if (!MenuService::getInstance()->isCaptured()) {
            return new IceResponse(IceResponse::ERROR, 'Menu tree was not initialized for this request', 500);
        }
        return new IceResponse(IceResponse::SUCCESS, MenuService::getInstance()->getMenuForUser($user));
    }

    /**
     * Shell bootstrap: cleaned user + filtered menu tree + chrome metadata
     * (company name, logo, instance id, REST base, home link).
     */
    public function getBootstrap(User $user)
    {
        $cleanUser = BaseService::getInstance()->cleanUpUser(clone $user);
        $menu = MenuService::getInstance()->getMenuForUser($user);
        $views = MenuService::getInstance()->getViewMenus($user);

        // Collect the area ids actually referenced by this user's menu so the
        // registry includes any extension-defined area too.
        $areaIds = [];
        foreach (['admin', 'employee'] as $vk) {
            foreach (isset($views[$vk]) && is_array($views[$vk]) ? $views[$vk] : [] as $grp) {
                foreach (isset($grp['items']) ? $grp['items'] : [] as $it) {
                    if (!empty($it['area'])) {
                        $areaIds[$it['area']] = true;
                    }
                }
            }
        }

        return new IceResponse(IceResponse::SUCCESS, [
            'user' => $cleanUser,
            'userLevel' => $user->user_level,
            // The signed-in user's profile photo + name (from their employee
            // profile; empty if none). Mirrors core/includes.inc.php.
            'profile' => $this->getProfileInfo($user),
            // If an admin/manager has switched into an employee's profile, the
            // employee being viewed (so the shell can show a Switch-back banner).
            'switchedProfile' => $this->getSwitchedProfile($user),
            // Admin/Employee view toggle menus (data-driven, drift-proof)
            'views' => $views,
            // High-level functional areas (Home/People/Time and Work/…) the shell
            // groups the menu by. Each menu item carries its `area`.
            'areas' => MenuAreaService::areas(array_keys($areaIds)),
            'adminModules' => $menu['adminModules'],
            'userModules' => $menu['userModules'],
            'mainIcons' => $menu['mainIcons'],
            'company' => [
                'name' => $this->getCompanyName(),
                // Only the customer's uploaded logo (white-label); when none is
                // set, leave null so the shell uses its own compact mark.
                'logoUrl' => $this->safeCall(function () {
                    $logoSetting = \Classes\SettingsManager::getInstance()->getSetting('Company: Logo');
                    if (empty($logoSetting)) {
                        return null;
                    }
                    return UIManager::getInstance()->getCompanyLogoUrl();
                }),
            ],
            'restApiBase' => $this->getRestApiBase(),
            'version' => defined('VERSION') ? VERSION : null,
            'homeLink' => $this->getHomeLink($user),
            // Modules the shell can mount natively (no iframe); everything else
            // keeps using the iframe.
            'nativeModules' => NativeModuleRegistry::keys(),
            // Show admins a banner prompting them to connect this installation to
            // icehrm.com when the marketplace is installed but not yet connected.
            // Admin-only: connecting is a system-level action.
            'showConnectBanner' => $this->safeCall(function () use ($user) {
                return $user->user_level === 'Admin'
                    && BaseService::getInstance()->isModuleMenuEnabled('extension>marketplace|admin')
                    && !\Classes\ConnectionService::getInstance()->isConnected();
            }) === true,
            'licenseRenewal' => $this->safeCall(function () use ($user) {
                if (!class_exists('\\UtilAdmin\\LicenseService')) {
                    return null;
                }
                $status = \UtilAdmin\LicenseService::getInstance()->getLicenseStatus();
                if (!is_array($status) || empty($status['has_license'])
                    || !empty($status['is_expired']) || empty($status['expiry_date'])) {
                    return null;
                }
                $expiry = strtotime($status['expiry_date']);
                if ($expiry === false) {
                    return null;
                }
                $daysLeft = (int) floor(($expiry - time()) / 86400);
                if ($daysLeft < 0 || $daysLeft > 20) {
                    return null;
                }
                $licenseKey = isset($status['license_key']) ? (string) $status['license_key'] : '';
                $renewBase = defined('APP_WEB_URL') ? APP_WEB_URL : '';
                return array(
                    'daysLeft' => $daysLeft,
                    'expiryDate' => $status['expiry_date'],
                    'isAdmin' => ($user->user_level === 'Admin'),
                    'renewUrl' => ($renewBase !== '' && $licenseKey !== '')
                        ? rtrim($renewBase, '/') . '/renew-icehrmpro-license/' . rawurlencode($licenseKey)
                        : null,
                );
            }),
        ]);
    }

    /**
     * Resolve the signed-in user's profile photo + name, the same way the legacy
     * page does (core/includes.inc.php): load the mapped profile element
     * (Employee) and let FileService attach its profile_image file URL.
     */
    private function getProfileInfo(User $user)
    {
        $info = $this->safeCall(function () use ($user) {
            if (!defined('SIGN_IN_ELEMENT_MAPPING_FIELD_NAME')) {
                return null;
            }
            $profileVar = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
            if (empty($user->$profileVar)) {
                return null;
            }
            $profileClass = ucfirst($profileVar);
            $profile = BaseService::getInstance()->getElement($profileClass, $user->$profileVar, null, true);
            if (empty($profile)) {
                return null;
            }
            $profile = \Classes\FileService::getInstance()->updateProfileImage($profile);
            return array(
                'image' => (!empty($profile->image)) ? $profile->image : null,
                'firstName' => isset($profile->first_name) ? $profile->first_name : null,
                'lastName' => isset($profile->last_name) ? $profile->last_name : null,
            );
        });
        return $info ?: array('image' => null, 'firstName' => null, 'lastName' => null);
    }

    /**
     * If the admin/manager has switched into an employee's profile (legacy
     * "Login as this Employee"), return that employee; else null. Reads the
     * cookie-session profile id (sent same-origin alongside the JWT).
     */
    private function getSwitchedProfile(User $user)
    {
        return $this->safeCall(function () use ($user) {
            $adminEmpId = \Utils\SessionUtils::getSessionObject('admin_current_profile');
            if (empty($adminEmpId)) {
                return null;
            }
            $own = (defined('SIGN_IN_ELEMENT_MAPPING_FIELD_NAME'))
                ? $user->{SIGN_IN_ELEMENT_MAPPING_FIELD_NAME} : $user->employee;
            if ($adminEmpId == $own) {
                return null;
            }
            $emp = BaseService::getInstance()->getElement('Employee', $adminEmpId, null, true);
            if (empty($emp)) {
                return null;
            }
            $name = trim((isset($emp->first_name) ? $emp->first_name : '')
                . ' ' . (isset($emp->last_name) ? $emp->last_name : ''));
            return array('empId' => $adminEmpId, 'name' => $name);
        });
    }

    /**
     * Per-action permissions ({ "Add X":"Yes", ... }) + allowed levels/roles for a
     * module, keyed by its update_path (e.g. "admin>employees", "extension>expenses|admin").
     * Passed as a query param to avoid URL-encoding the ">" and "|" separators.
     */
    public function getPermissions(User $user)
    {
        $updatePath = isset($_REQUEST['path']) ? $_REQUEST['path'] : null;
        if (empty($updatePath)) {
            return new IceResponse(IceResponse::ERROR, 'Missing required "path" parameter', 400);
        }
        $perms = BaseService::getInstance()->loadModulePermissions($updatePath, $user->user_level);
        return new IceResponse(IceResponse::SUCCESS, $perms);
    }

    /**
     * Directory-driven extension manifest. Enumerates every extension that physically
     * exists under /extensions/ (the install model), with its admin/user meta, menu
     * placement, bundle URL and the legacy hard-load route the shell can use for
     * dual-mode navigation. Adding an extension directory makes it appear here with
     * no shell rebuild.
     */
    public function getExtensions(User $user)
    {
        // path-resolver.php defines the global getExtensionRelativePath() used below;
        // it's loaded by app/index.php on the page path but not on the REST path.
        if (!function_exists('getExtensionRelativePath') && defined('APP_BASE_PATH')) {
            $resolver = APP_BASE_PATH . 'extensions/path-resolver.php';
            if (file_exists($resolver)) {
                require_once $resolver;
            }
        }

        $groupManager = ExtensionGroupManager::getInstance();
        $extManager = new ExtensionManager();
        $names = $groupManager->getAllExtensionNames();

        $manifest = [];
        foreach ($names as $name) {
            foreach (['admin', 'user'] as $type) {
                $meta = $extManager->getExtensionMetaData($name . '/' . $type);
                if (empty($meta)) {
                    continue;
                }
                $relative = $this->safeCall(function () use ($groupManager, $name) {
                    return $groupManager->getExtensionRelativePath($name);
                });
                $manifest[] = [
                    'name' => $name,
                    'type' => $type,
                    'label' => isset($meta->label) ? $meta->label : $name,
                    'menu' => isset($meta->menu) ? $meta->menu : null,
                    'icon' => isset($meta->icon) ? $meta->icon : null,
                    'order' => isset($meta->order) ? $meta->order : null,
                    'userLevels' => isset($meta->user_levels) ? $meta->user_levels : [],
                    'headless' => isset($meta->headless) ? (bool)$meta->headless : false,
                    'showInMenu' => isset($meta->show_in_menu) ? (bool)$meta->show_in_menu : true,
                    'dashboardPosition' => isset($meta->dashboardPosition) ? $meta->dashboardPosition : null,
                    'updatePath' => 'extension>' . $name . '|' . $type,
                    // legacy hard-load route (dual-mode navigation fallback)
                    'indexUrl' => CLIENT_BASE_URL . '?g=extension&n=' . $name . '|' . $type,
                    // React bundle for soft-mount (may not exist for legacy extensions).
                    // Asset URL root is per-extension (extensions/ vs extensions-pro/).
                    'bundleUrl' => $relative
                        ? rtrim(extensionAssetUrlBase($name, APP_BASE_PATH . '../extensions/'), '/') . '/' . trim($relative, '/') . '/' . $type . '/dist/' . $name . '.js'
                        : null,
                ];
            }
        }

        return new IceResponse(IceResponse::SUCCESS, ['extensions' => $manifest]);
    }

    /**
     * Aggregated data for the native React admin dashboard. Extension-tied
     * sections (leave/expenses/recruitment) are included only when the extension
     * exists. GET /app/api/appshell/dashboard
     */
    public function getDashboard(User $user)
    {
        if ($user->user_level !== 'Admin' && $user->user_level !== 'Manager') {
            return new IceResponse(IceResponse::ERROR, 'Not allowed', 403);
        }
        $data = $this->safeCall(function () use ($user) {
            return (new DashboardService())->getData($user);
        });
        if ($data === null) {
            return new IceResponse(IceResponse::ERROR, 'Failed to build dashboard', 500);
        }
        // Greet by the signed-in user's actual profile (employee) name — the User
        // model has no first_name, so DashboardService' fallback would otherwise
        // prettify the email. Reuse the same profile lookup the bootstrap uses.
        $profile = $this->safeCall(function () use ($user) {
            return $this->getProfileInfo($user);
        });
        if (!empty($profile['firstName'])) {
            $data['greetingName'] = $profile['firstName'];
        }
        return new IceResponse(IceResponse::SUCCESS, $data);
    }

    /**
     * News/announcement from icehrm.com for the dashboard (mirrors the legacy
     * dashboard "news" feature). Returns the single news item to show, or null.
     * The item is only returned when icehrm.com flags it to show AND the user
     * hasn't dismissed it (per-user, via NewsService/UserMeta). Dismissal is
     * handled by the existing dismiss-news endpoint. GET /app/api/appshell/news
     */
    public function getNews(User $user)
    {
        $news = $this->safeCall(function () use ($user) {
            return $this->fetchRemoteNews($user->user_level);
        });
        if (empty($news) || empty($news['id']) || empty($news['show']) || $news['show'] !== true) {
            return new IceResponse(IceResponse::SUCCESS, null);
        }
        $canShow = $this->safeCall(function () use ($user, $news) {
            return (new NewsService($user->id))->canShowNews($news['id']);
        });
        if ($canShow !== true) {
            return new IceResponse(IceResponse::SUCCESS, null);
        }
        return new IceResponse(IceResponse::SUCCESS, $news);
    }

    /**
     * Fetch (and cache) the announcement from icehrm.com's public /sapi/news for
     * this installation's version + the user's level. Cached briefly so it isn't
     * fetched on every dashboard load; failures degrade to no news.
     *
     * @param string $userLevel
     * @return array the news item (may be empty)
     */
    private function fetchRemoteNews($userLevel)
    {
        $version = defined('VERSION') ? VERSION : '';
        $parts = explode('.', (string) $version);
        $type = strtolower((string) end($parts));

        $cacheKey = 'appshell:news:' . $type . ':' . $version . ':' . $userLevel;
        $cache = DatabaseCache::getInstance();
        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            return is_array($cached) ? $cached : [];
        }

        $base = defined('APP_WEB_URL') ? APP_WEB_URL : 'https://icehrm.com';
        $url = $base . '/sapi/news?type=' . urlencode($type)
            . '&version=' . urlencode($version)
            . '&user=' . urlencode($userLevel);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => BaseService::shouldVerifyOutboundTls(),
            CURLOPT_SSL_VERIFYHOST => BaseService::shouldVerifyOutboundTls() ? 2 : 0,
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'User-Agent: IceHrm/1.0'],
        ]);
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $errno !== 0 || $httpCode < 200 || $httpCode >= 300) {
            // Briefly cache the failure so a slow/unreachable server isn't hit on
            // every dashboard load — but keep it short so a transient blip doesn't
            // hide freshly published news for long.
            $cache->set($cacheKey, [], 120);
            return [];
        }

        $body = json_decode($response, true);
        $news = (is_array($body) && isset($body['data']) && is_array($body['data'])) ? $body['data'] : [];
        $cache->set($cacheKey, $news, 3600);
        return $news;
    }

    /**
     * Personal / manager dashboard (modules::dashboard). Available to every
     * authenticated user. GET /app/api/appshell/employee-dashboard
     */
    public function getEmployeeDashboard(User $user)
    {
        $data = $this->safeCall(function () use ($user) {
            return (new EmployeeDashboardService())->getData($user);
        });
        if ($data === null) {
            return new IceResponse(IceResponse::ERROR, 'Failed to build dashboard', 500);
        }
        $profile = $this->safeCall(function () use ($user) {
            return $this->getProfileInfo($user);
        });
        $data['greetingName'] = (!empty($profile['firstName']))
            ? $profile['firstName']
            : (!empty($user->first_name) ? $user->first_name : $user->email);
        return new IceResponse(IceResponse::SUCCESS, $data);
    }

    /**
     * Everything the shell needs to natively mount a registered module (no iframe):
     * the mount config (init fn, scripts, tabs), the module-specific data its
     * index.php computes (general access + custom fields), and the footer context
     * (translations, templates, permissions, user, urls).
     * GET /app/api/appshell/module-context?group=admin&name=company_structure
     */
    public function getModuleContext(User $user)
    {
        $group = isset($_REQUEST['group']) ? $_REQUEST['group'] : null;
        $name = isset($_REQUEST['name']) ? $_REQUEST['name'] : null;
        if (empty($group) || empty($name)) {
            return new IceResponse(IceResponse::ERROR, 'Missing group/name', 400);
        }
        $config = NativeModuleRegistry::get($group, $name);
        if (empty($config)) {
            return new IceResponse(IceResponse::ERROR, 'Module not registered for native mount', 404);
        }

        // Gate individual tabs. Optional conditions (all must pass):
        //  - 'requiresSetting' => 'Setting Name' : dropped unless the setting is '1'
        //    (mirrors the legacy index.php, e.g. overtime's approvals tab).
        //  - 'requiresRecords' => '\\Ns\\Model\\X' : dropped when that model has no
        //    rows (e.g. the Leave Rules tab only shows once a rule is defined).
        //  - 'requiresLevel' => ['Admin', ...] : dropped unless the current user's
        //    level is listed (e.g. performance's admin-only template tabs).
        // Precompute, once, whether the current (non-privileged) user manages
        // anyone — used to gate supervisor-only tabs (e.g. leave "Direct Reports").
        $privileged = in_array($user->user_level, array('Admin', 'Manager', 'Restricted Admin'), true);
        $profileId = $this->safeCall(function () {
            return BaseService::getInstance()->getCurrentProfileId();
        });
        $hasSubordinates = $this->safeCall(function () use ($profileId) {
            if (empty($profileId)) {
                return false;
            }
            $subs = (new \Employees\Common\Model\Employee())->Find('supervisor = ?', array($profileId));
            return is_array($subs) && count($subs) > 0;
        });

        if (!empty($config['tabs']) && is_array($config['tabs'])) {
            $config['tabs'] = array_values(array_filter($config['tabs'], function ($tab) use ($user, $privileged, $hasSubordinates) {
                if (!empty($tab['requiresLevel'])) {
                    $levels = is_array($tab['requiresLevel'])
                        ? $tab['requiresLevel'] : array($tab['requiresLevel']);
                    if (!in_array($user->user_level, $levels)) {
                        return false;
                    }
                }
                // Supervisor-only tab: a non-privileged user (Employee level) sees
                // it only if they actually manage someone (legacy data can leave an
                // employee with subordinates). Managers/Admins always see it.
                if (!empty($tab['requiresSubordinatesUnlessPrivileged'])) {
                    if (!$privileged && !$hasSubordinates) {
                        return false;
                    }
                }
                if (!empty($tab['requiresSetting'])) {
                    $val = $this->safeCall(function () use ($tab) {
                        return \Classes\SettingsManager::getInstance()->getSetting($tab['requiresSetting']);
                    });
                    if ($val != '1') {
                        return false;
                    }
                }
                if (!empty($tab['requiresRecords'])) {
                    $count = $this->safeCall(function () use ($tab) {
                        $cls = $tab['requiresRecords'];
                        $rows = (new $cls())->Find('1=1', array());
                        return is_array($rows) ? count($rows) : 0;
                    });
                    if (empty($count)) {
                        return false;
                    }
                }
                return true;
            }));
        }

        // Leaves module: attach pending-item counts to the "For Approval" tabs so
        // the shell can render a count bubble on each.
        if ($group === 'modules' && $name === 'leaves' && !empty($config['tabs'])
            && !empty($profileId) && class_exists('\\Leaves\\Common\\Model\\EmployeeLeave')) {
            $counts = $this->leaveReviewTabCounts($profileId);
            foreach ($config['tabs'] as &$leaveTab) {
                if (array_key_exists($leaveTab['key'], $counts)) {
                    $leaveTab['count'] = intval($counts[$leaveTab['key']]);
                }
            }
            unset($leaveTab);
        }

        // Cache-bust each module bundle by its own mtime so the browser always
        // picks up a rebuilt bundle (unlike the static $jsVersion, which does
        // not change on a dev rebuild). The shell loads these scripts by URL.
        if (!empty($config['scripts']) && is_array($config['scripts'])) {
            $webDir = defined('APP_BASE_PATH') ? APP_BASE_PATH . '../web/' : null;
            $extDir = defined('APP_BASE_PATH') ? APP_BASE_PATH . '../extensions/' : null;
            $extUrl = defined('EXTENSIONS_URL') ? EXTENSIONS_URL : null;
            $proDir = defined('APP_BASE_PATH') ? APP_BASE_PATH . '../extensions-pro/' : null;
            $proUrl = defined('EXTENSIONS_PRO_URL') ? EXTENSIONS_PRO_URL : null;
            $config['scripts'] = array_map(function ($script) use ($webDir, $extDir, $extUrl, $proDir, $proUrl) {
                if (strpos($script, '?') !== false) {
                    return $script;
                }
                $path = null;
                if ($proUrl && $proDir && strpos($script, $proUrl) === 0) {
                    // Absolute pro-extension URL -> map back to the extensions-pro dir.
                    $path = $proDir . substr($script, strlen($proUrl));
                } elseif ($extUrl && $extDir && strpos($script, $extUrl) === 0) {
                    // Absolute extension URL -> map back to the extensions dir.
                    $path = $extDir . substr($script, strlen($extUrl));
                } elseif ($webDir !== null && strpos($script, '://') === false) {
                    $path = $webDir . $script;
                }
                if ($path !== null && is_file($path)) {
                    return $script . '?v=' . filemtime($path);
                }
                return $script;
            }, $config['scripts']);
        }

        $license = null;
        if (class_exists('\\UtilAdmin\\LicenseService')) {
            $status = $this->safeCall(function () {
                return \UtilAdmin\LicenseService::getInstance()->getLicenseStatus();
            });
            if (is_array($status)) {
                $license = array(
                    'has_license' => !empty($status['has_license']),
                    'is_expired' => !empty($status['is_expired']),
                    'expiry_date' => isset($status['expiry_date']) ? $status['expiry_date'] : null,
                    'is_admin' => !empty($status['is_admin']),
                );
            }
        }

        $currentUser = BaseService::getInstance()->getCurrentUser();
        $permissions = array();
        $entities = isset($config['entities']) ? $config['entities'] : array();
        foreach ($entities as $entity => $class) {
            $obj = $this->safeCall(function () use ($class) {
                return new $class();
            });
            $access = $obj ? PermissionManager::checkGeneralAccess($obj) : array();
            // Employee self-service card lists (e.g. #modules::leaves) only ever
            // show the current user's OWN records — the data load is profile-scoped.
            // checkGeneralAccess is evaluated on a blank model, so it can't see the
            // rows are the user's own and returns only the general (read-only)
            // matrix. That hid the "Apply Leave"/"Add" button for Employee-level
            // users on every self-service module (leaves, emergency contacts,
            // skills, ...). For employees, surface their own-record rights
            // (getUserOnlyMeAccess: add/edit/delete own) so the buttons appear.
            // Every write is still authorised per-record by checkSecureAccess, and
            // Manager/Admin are untouched (their role access already covers this).
            if ($obj && $currentUser && $currentUser->user_level === 'Employee') {
                $meOnly = $this->safeCall(function () use ($obj) {
                    return $obj->getUserOnlyMeAccess();
                });
                if (is_array($meOnly)) {
                    $access = array_values(array_unique(array_merge($access, $meOnly)));
                }
            }
            $permissions[$entity] = $access;
        }
        // Custom fields: either a single type (legacy company_structure) or a map
        // keyed by entity (multi-entity modules like projects).
        $customFields = new \stdClass();
        if (!empty($config['customFieldType'])) {
            $cf = $this->safeCall(function () use ($config) {
                return BaseService::getInstance()->getCustomFields($config['customFieldType']);
            });
            if ($cf !== null) {
                $customFields = $cf;
            }
        } elseif (!empty($config['customFieldEntities']) && is_array($config['customFieldEntities'])) {
            $map = array();
            foreach ($config['customFieldEntities'] as $type) {
                $cf = $this->safeCall(function () use ($type) {
                    return BaseService::getInstance()->getCustomFields($type);
                });
                $map[$type] = ($cf !== null) ? $cf : new \stdClass();
            }
            $customFields = $map;
        }

        // Some modules (custom fields manager) need the list of custom-field-able
        // model classes to populate their "Object Type" select.
        $types = null;
        if (!empty($config['includeCustomFieldTypes'])) {
            $types = $this->safeCall(function () {
                return BaseService::getInstance()->getCustomFieldClassMap();
            });
        }

        // Dynamic field labels (employees module needs the Employee field map).
        $fieldNameMap = null;
        if (!empty($config['includeFieldNameMappings'])) {
            $fieldNameMap = $this->safeCall(function () use ($config) {
                return BaseService::getInstance()->getFieldNameMappings($config['includeFieldNameMappings']);
            });
        }

        // Whether managers may switch into an employee profile (employees module).
        $managersCanSwitch = null;
        if (!empty($config['includeManagersCanSwitch'])) {
            $managersCanSwitch = $this->safeCall(function () {
                return \Classes\SettingsManager::getInstance()
                    ->getSetting('System: Managers Can Switch to Employee Profiles') == '1';
            });
        }

        $perms = $this->safeCall(function () use ($group, $name, $user) {
            return BaseService::getInstance()->loadModulePermissions($group . '>' . $name, $user->user_level);
        });

        $moduleData = array(
            'user_level' => $user->user_level,
            'customFields' => $customFields,
            'permissions' => $permissions,
            // Extension controllers (new XxxExtensionController(scope, controller_url))
            // need the legacy service endpoint, same as their index.php passed.
            'controller_url' => (defined('CLIENT_BASE_URL') ? CLIENT_BASE_URL : '') . 'service.php',
        );
        if ($types !== null) {
            $moduleData['types'] = $types;
        }
        if ($fieldNameMap !== null) {
            $moduleData['fieldNameMap'] = $fieldNameMap;
        }
        if ($managersCanSwitch !== null) {
            $moduleData['managersCanSwitch'] = $managersCanSwitch;
        }

        // CSRF token for modules whose adapters post a custom action guarded by
        // one (e.g. users -> saveUser). generateCsrf both mints and stores it in
        // the session that the later service.php call validates against.
        if (!empty($config['includeCsrf'])) {
            $csrf = $this->safeCall(function () use ($config) {
                return BaseService::getInstance()->generateCsrf($config['includeCsrf']);
            });
            if ($csrf !== null) {
                $moduleData['csrf'] = $csrf;
            }
        }

        // A long-lived JWT access token (modules/employees Mobile App tab shows it
        // as the API access token and uses it for the one-time login code), minted
        // only for modules that ask for it. includeJwtToken => lifetime in seconds.
        if (!empty($config['includeJwtToken'])) {
            $jwt = $this->safeCall(function () use ($config) {
                return (new \Classes\JwtTokenService())->create((int) $config['includeJwtToken']);
            });
            if ($jwt !== null) {
                $moduleData['jwtToken'] = $jwt;
            }
        }

        // The list of model classes a permission picker needs (users -> UserRole
        // "Additional Permissions"). Shaped as [[class, class], ...] like the
        // legacy index.php passed to setTables().
        if (!empty($config['includeModelClasses'])) {
            $classes = $this->safeCall(function () {
                return array_keys(BaseService::getInstance()->getModelClassMap());
            });
            if (is_array($classes)) {
                $moduleData['modelClasses'] = array_map(function ($item) {
                    return array($item, $item);
                }, $classes);
            }
        }

        // Attendance (modules) needs the employee's current punch state + time
        // settings its legacy index.php computed via AttendanceUtil.
        if (!empty($config['includeAttendancePunch'])) {
            $punch = $this->safeCall(function () {
                $useServerTime = \Classes\SettingsManager::getInstance()
                    ->getSetting('Attendance: Use Department Time Zone');
                $tz = BaseService::getInstance()->getCurrentEmployeeTimeZone();
                if (empty($tz)) {
                    $useServerTime = 0;
                }
                $util = new \Attendance\Admin\Api\AttendanceUtil();
                $empId = BaseService::getInstance()->getCurrentProfileId();
                $ot = \Classes\SettingsManager::getInstance()
                    ->getSetting('Attendance: Overtime Start Hour');
                return array(
                    'useServerTime' => (int) $useServerTime,
                    'hasOpenPunch' => $util->isEmployeeHasOpenPunch(date('Y-m-d'), $empId) ? 1 : 0,
                    'punchedOutToday' => $util->isEmployeePunchedOut(date('Y-m-d'), $empId) ? 1 : 0,
                    'overtimeStartHour' => (int) $ot,
                );
            });
            if (is_array($punch)) {
                $moduleData = array_merge($moduleData, $punch);
            }
        }

        // Static extra data merged into the init payload (e.g. overtime's
        // moduleMainName/approveModName/subModuleMainName + flat permissions array
        // its legacy index.php hardcoded). Declared in the registry as extraData.
        if (!empty($config['extraData']) && is_array($config['extraData'])) {
            $moduleData = array_merge($moduleData, $config['extraData']);
        }

        // Module-specific settings a native module's init() needs (the bits its
        // legacy index.php pulled from SettingsManager). Declared in the registry
        // as extraSettings => { dataKey: 'Setting Name' }. Numeric values are cast
        // to int to match the legacy (int) casts.
        if (!empty($config['extraSettings']) && is_array($config['extraSettings'])) {
            foreach ($config['extraSettings'] as $dataKey => $settingName) {
                $val = $this->safeCall(function () use ($settingName) {
                    return \Classes\SettingsManager::getInstance()->getSetting($settingName);
                });
                $moduleData[$dataKey] = is_numeric($val) ? (int) $val : $val;
            }
        }

        return new IceResponse(IceResponse::SUCCESS, array(
            'config' => $config,
            'data' => $moduleData,
            'license' => $license,
            'context' => array(
                'translations' => $this->safeCall(function () {
                    return json_decode(LanguageManager::getTranslations(), true);
                }),
                'fieldTemplates' => $this->buildFieldTemplates(),
                'templates' => $this->buildTemplates(),
                'customTemplates' => new \stdClass(),
                'perm' => (is_array($perms) && isset($perms['perm'])) ? $perms['perm'] : new \stdClass(),
                'user' => BaseService::getInstance()->cleanUpUser(clone $user),
                'baseUrl' => (defined('CLIENT_BASE_URL') ? CLIENT_BASE_URL : '') . 'service.php',
                'clientUrl' => defined('CLIENT_BASE_URL') ? CLIENT_BASE_URL : '',
                'webBaseUrl' => defined('BASE_URL') ? BASE_URL : '',
                'restApiBase' => $this->getRestApiBase(),
            ),
        ));
    }

    /**
     * The company structure hierarchy (id/title/type/parent + live headcount) for
     * the native org chart. GET /app/api/appshell/org-structure
     */
    public function getOrgStructure(User $user)
    {
        $db = BaseService::getInstance()->getDB();
        $rows = $this->safeCall(function () use ($db) {
            return $db->Execute("SELECT cs.id, cs.title, COALESCE(cs.type,'') AS type,
                COALESCE(cs.parent,0) AS parent, COALESCE(cs.country,'') AS country,
                COALESCE(cs.address,'') AS address, COALESCE(cs.timezone,'') AS timezone,
                (SELECT p.title FROM CompanyStructures p WHERE p.id = cs.parent) AS parentTitle,
                (SELECT COUNT(*) FROM Employees e WHERE e.status='Active' AND e.department = cs.id) AS headcount
                FROM CompanyStructures cs ORDER BY cs.id");
        });
        $nodes = array();
        foreach ((is_array($rows) ? $rows : array()) as $r) {
            $nodes[] = array(
                'id' => (int) $r['id'],
                'title' => $r['title'],
                'type' => $r['type'],
                'parent' => (int) $r['parent'],
                'parentTitle' => isset($r['parentTitle']) ? $r['parentTitle'] : null,
                'country' => $r['country'],
                'address' => $r['address'],
                'timezone' => $r['timezone'],
                'headcount' => (int) $r['headcount'],
            );
        }
        return new IceResponse(IceResponse::SUCCESS, array('nodes' => $nodes));
    }

    private function buildFieldTemplates()
    {
        $dir = APP_BASE_PATH . 'templates/fields/';
        $types = array(
            'hidden', 'text', 'textarea', 'select', 'select2', 'select2multi', 'date',
            'datetime', 'time', 'fileupload', 'label', 'placeholder', 'datagroup',
            'colorpick', 'signature', 'simplemde', 'tinymce', 'code',
        );
        $out = array();
        foreach ($types as $tp) {
            $f = $dir . $tp . '.html';
            if (file_exists($f)) {
                $out[$tp] = $this->trans(file_get_contents($f));
            }
        }
        return $out;
    }

    private function buildTemplates()
    {
        $dir = APP_BASE_PATH . 'templates/';
        $files = array(
            'formTemplate' => 'form_template.html',
            'filterTemplate' => 'filter_template.html',
            'datagroupTemplate' => 'datagroup_template.html',
        );
        $out = array();
        foreach ($files as $k => $f) {
            if (file_exists($dir . $f)) {
                $out[$k] = $this->trans(file_get_contents($dir . $f));
            }
        }
        return $out;
    }

    private function trans($html)
    {
        return function_exists('t') ? t($html) : $html;
    }

    // --- helpers -------------------------------------------------------------

    private function getCompanyName()
    {
        $name = $this->safeCall(function () {
            return SettingsManager::getInstance()->getSetting('Company: Name');
        });
        if (empty($name) || $name === 'Sample Company Pvt Ltd') {
            return 'IceHrm';
        }
        return substr($name, 0, 40);
    }

    private function getRestApiBase()
    {
        if (defined('SYM_CLIENT')) {
            return (defined('WEB_APP_BASE_URL') ? WEB_APP_BASE_URL : '') . '/api/' . SYM_CLIENT . '/';
        }
        if (defined('REST_API_BASE')) {
            return REST_API_BASE;
        }
        return (defined('CLIENT_BASE_URL') ? CLIENT_BASE_URL : '') . 'api/';
    }

    /**
     * Mirrors the default landing link in core/header.php:13-27 (default_module,
     * else admin/employee home), as a {group,name} the shell can route to.
     */
    private function getHomeLink(User $user)
    {
        if (!empty($user->default_module)) {
            $module = new \Modules\Common\Model\Module();
            $module->Load('id = ?', [$user->default_module]);
            if (!empty($module->id)) {
                $group = $module->mod_group === 'user' ? 'modules' : $module->mod_group;
                return ['group' => $group, 'name' => $module->name];
            }
        }
        if ($user->user_level === 'Admin') {
            return ['group' => 'admin', 'name' => 'dashboard'];
        }
        return ['group' => 'modules', 'name' => 'dashboard'];
    }

    private function safeCall(callable $fn)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Pending-item counts for the leaves module "For Approval" tabs, keyed by
     * tab key. Errors degrade to 0 so the module still mounts.
     *
     * @param mixed $profileId current user's employee id
     * @return array<string,int>
     */
    private function leaveReviewTabCounts($profileId)
    {
        // Model->Count(where, binds) returns an int using the model's own table.
        $leaveCount = function ($where, $binds) {
            return intval($this->safeCall(function () use ($where, $binds) {
                return (new \Leaves\Common\Model\EmployeeLeave())->Count($where, $binds);
            }));
        };

        return array(
            // Direct reports' leaves awaiting the supervisor (Pending).
            'tabSubLeaveAll' => $leaveCount(
                "employee IN (SELECT id FROM Employees WHERE supervisor = ?) AND status = 'Pending'",
                array($profileId)
            ),
            // Direct reports' cancellation requests.
            'tabSubLeaveCancel' => $leaveCount(
                "employee IN (SELECT id FROM Employees WHERE supervisor = ?) AND status = 'Cancellation Requested'",
                array($profileId)
            ),
            // Multi-level approvals currently awaiting THIS user's action.
            'tabLeaveApproval' => intval($this->safeCall(function () use ($profileId) {
                return (new \Employees\Common\Model\EmployeeApproval())->Count(
                    "type = 'EmployeeLeave' AND approver = ? AND status = -1 AND active = 1",
                    array($profileId)
                );
            })),
        );
    }
}
