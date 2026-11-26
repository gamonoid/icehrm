<?php

namespace Classes;

/**
 * MenuService
 *
 * Phase 0 of the SPA migration (see docs/SPA_MIGRATION_PLAN.md).
 *
 * Captures the UNFILTERED admin/user menu trees that core/modules.php builds
 * (before the session-user filter), then exposes getMenuForUser() which applies
 * the SAME user-level / user-role / blacklist filter that the legacy header path
 * applies in core/modules.php (lines ~570-633).
 *
 * The goal is that a React shell can fetch the identical, already-filtered menu
 * a given user would see in the legacy sidebar, WITHOUT reimplementing the
 * filtering rules anywhere else. This class is the single source of truth.
 */
class MenuService
{
    private static $instance = null;

    /** @var array Raw (unfiltered) admin module menu tree as built by modules.php */
    private $rawAdminModules = [];

    /** @var array Raw (unfiltered) user module menu tree as built by modules.php */
    private $rawUserModules = [];

    /** @var array Map of menu-group name => fa-icon */
    private $mainIcons = [];

    /** @var bool Whether setRawMenus() has been called this request */
    private $captured = false;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Called from core/modules.php right after the final $adminModules/$userModules
     * arrays are built and BEFORE the legacy session-user filter runs, so we capture
     * the full unfiltered tree.
     */
    public function setRawMenus($adminModules, $userModules, $mainIcons)
    {
        $this->rawAdminModules = is_array($adminModules) ? $adminModules : [];
        $this->rawUserModules = is_array($userModules) ? $userModules : [];
        $this->mainIcons = is_array($mainIcons) ? $mainIcons : [];
        $this->captured = true;
    }

    public function isCaptured()
    {
        return $this->captured;
    }

    public function getMainIcons()
    {
        return $this->mainIcons;
    }

    /**
     * Returns the menu tree filtered for the given user, mirroring core/modules.php.
     *
     * @param  \Users\Common\Model\User $user
     * @return array { adminModules: [...], userModules: [...], mainIcons: {...} }
     */
    public function getMenuForUser($user)
    {
        $userRoles = $this->decodeUserRoles($user);
        $allowed = $this->getRestriction($user);

        return [
            'adminModules' => $this->hideMenus($this->restrictMenus($this->filterMenus($this->rawAdminModules, $user, $userRoles), 'admin', $allowed), 'admin', $user),
            'userModules' => $this->hideMenus($this->restrictMenus($this->filterMenus($this->rawUserModules, $user, $userRoles), 'modules', $allowed), 'modules', $user),
            'mainIcons' => $this->mainIcons,
        ];
    }

    /** @var callable[] see addVisibilityFilter() */
    private static $visibilityFilters = [];

    /**
     * Let an extension hide one of its menu items from users it does not apply to
     * (it stays reachable by URL; access is still checked by the module itself). The
     * callable gets (User, group, name), e.g. (user, 'extension', 'onboarding|user'),
     * and returns false to hide the item; anything else keeps it. Inert when no
     * filter is registered.
     */
    public static function addVisibilityFilter(callable $filter)
    {
        self::$visibilityFilters[] = $filter;
    }

    private function isVisibleByFilters($user, $group, $name)
    {
        foreach (self::$visibilityFilters as $filter) {
            try {
                if (call_user_func($filter, $user, $group, $name) === false) {
                    return false;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }
        return true;
    }

    private function hideMenus(array $menus, $defaultGroup, $user)
    {
        if (empty(self::$visibilityFilters) || empty($user)) {
            return $menus;
        }
        $result = [];
        foreach ($menus as $menu) {
            $kept = [];
            foreach ($menu['menu'] as $key => $item) {
                $g = !empty($item['link_group']) ? $item['link_group'] : $defaultGroup;
                $n = !empty($item['link_name']) ? $item['link_name'] : $item['name'];
                if ($this->isVisibleByFilters($user, $g, $n)) {
                    $kept[$key] = $item;
                }
            }
            if (!empty($kept)) {
                $result[] = ['name' => $menu['name'], 'menu' => $kept];
            }
        }
        return $result;
    }

    private function hideView(array $groups, $user)
    {
        if (empty(self::$visibilityFilters) || empty($user)) {
            return $groups;
        }
        $result = [];
        foreach ($groups as $group) {
            $items = [];
            foreach ($group['items'] as $item) {
                if ($this->isVisibleByFilters($user, $item['g'], $item['n'])) {
                    $items[] = $item;
                }
            }
            if (!empty($items)) {
                $result[] = ['name' => $group['name'], 'items' => $items];
            }
        }
        return $result;
    }

    /** @var callable[] see addBadgeProvider() */
    private static $badgeProviders = [];

    /**
     * Let an extension show a bubble (a number) on menu items. The callable gets the
     * User and returns [route => count], where route is the shell key "group::name",
     * e.g. ['extension::company_onboarding|admin' => 4]. Counts of 0 are dropped.
     */
    public static function addBadgeProvider(callable $provider)
    {
        self::$badgeProviders[] = $provider;
    }

    /** @return array route => count, for this user */
    public function getMenuBadges($user)
    {
        $badges = [];
        if (empty($user)) {
            return $badges;
        }
        foreach (self::$badgeProviders as $provider) {
            try {
                $r = call_user_func($provider, $user);
            } catch (\Throwable $e) {
                continue;
            }
            if (!is_array($r)) {
                continue;
            }
            foreach ($r as $route => $count) {
                if ((int) $count > 0) {
                    $badges[(string) $route] = (int) $count;
                }
            }
        }
        return $badges;
    }

    /** @var callable[] see addRestriction() */
    private static $restrictions = [];

    /**
     * Let an extension limit what a user may open, e.g. a new hire who is still
     * onboarding. The callable gets the User and returns null (no limit) or
     * ['modules' => ['modules:hrforms', ...], 'home' => ['group' => ..., 'name' => ...]].
     */
    public static function addRestriction(callable $restriction)
    {
        self::$restrictions[] = $restriction;
    }

    /**
     * The combined restriction for this user, or null when nothing limits them.
     *
     * @return array|null ['modules' => [group:name => true], 'home' => array|null]
     */
    public function getRestriction($user)
    {
        if (empty($user)) {
            return null;
        }
        $result = null;
        foreach (self::$restrictions as $restriction) {
            try {
                $r = call_user_func($restriction, $user);
            } catch (\Exception $e) {
                continue;
            }
            if (!is_array($r) || !isset($r['modules']) || !is_array($r['modules'])) {
                continue;
            }
            if ($result === null) {
                $result = ['modules' => [], 'home' => null];
            }
            foreach ($r['modules'] as $key) {
                $result['modules'][(string) $key] = true;
            }
            if (empty($result['home']) && !empty($r['home'])) {
                $result['home'] = $r['home'];
            }
        }
        return $result;
    }

    /** True when the restriction (if any) lets the user open group/name. */
    public function isAllowedByRestriction($user, $group, $name)
    {
        $allowed = $this->getRestriction($user);
        return $allowed === null || isset($allowed['modules'][$group . ':' . $name]);
    }

    private function restrictMenus(array $menus, $defaultGroup, $allowed)
    {
        if ($allowed === null) {
            return $menus;
        }
        $result = [];
        foreach ($menus as $menu) {
            $kept = [];
            foreach ($menu['menu'] as $key => $item) {
                $g = !empty($item['link_group']) ? $item['link_group'] : $defaultGroup;
                $n = !empty($item['link_name']) ? $item['link_name'] : $item['name'];
                if (isset($allowed['modules'][$g . ':' . $n])) {
                    $kept[$key] = $item;
                }
            }
            if (!empty($kept)) {
                $result[] = ['name' => $menu['name'], 'menu' => $kept];
            }
        }
        return $result;
    }

    private function restrictView(array $groups, $allowed)
    {
        if ($allowed === null) {
            return $groups;
        }
        $result = [];
        foreach ($groups as $group) {
            $items = array_values(array_filter($group['items'], function ($item) use ($allowed) {
                return isset($allowed['modules'][$item['g'] . ':' . $item['n']]);
            }));
            if (!empty($items)) {
                $result[] = ['name' => $group['name'], 'items' => $items];
            }
        }
        return $result;
    }

    /**
     * Menus for the Admin/Employee view toggle, both fully data-driven (filtered
     * by user_levels) so there is NO hardcoded module list to drift — the bug the
     * legacy user-view-switch.js has, where admin-only modules (e.g. Marketplace)
     * leak into Employee View.
     *
     *  - admin    : the admin's own admin modules (their working menu).
     *  - employee : EXACTLY what a user with user_level "Employee" sees — admin
     *               modules whose user_levels include Employee (e.g. candidates)
     *               PLUS the user-facing modules. Marketplace (Admin-only) is
     *               excluded automatically.
     *
     * Each item is stamped with its resolved { g, n } route so the shell needs no
     * group-defaulting logic.
     *
     * @return array { admin: [...groups], employee: [...groups], mainIcons: {...} }
     */
    public function getViewMenus($user)
    {
        $userRoles = $this->decodeUserRoles($user);
        $allowed = $this->getRestriction($user);

        return [
            'admin' => $this->hideView($this->restrictView($this->buildView(
                [[$this->rawAdminModules, 'admin']],
                $user->user_level,
                $userRoles
            ), $allowed), $user),
            'employee' => $this->hideView($this->restrictView($this->buildView(
                [[$this->rawAdminModules, 'admin'], [$this->rawUserModules, 'modules']],
                'Employee',
                []
            ), $allowed), $user),
            'mainIcons' => $this->mainIcons,
        ];
    }

    /**
     * Filter one or more raw trees for a target level/roles and flatten into a
     * single ordered list of groups, stamping each item with its { g, n } route.
     * Empty groups are dropped.
     *
     * @param array $trees  list of [rawTree, defaultGroup] pairs
     */
    private function buildView($trees, $level, array $roles)
    {
        $result = [];
        foreach ($trees as $pair) {
            list($modules, $defaultGroup) = $pair;
            if (!is_array($modules)) {
                continue;
            }
            foreach ($modules as $menu) {
                $items = isset($menu['menu']) && is_array($menu['menu']) ? $menu['menu'] : [];
                $kept = [];
                foreach ($items as $item) {
                    if (!$this->isItemVisibleForLevel($item, $level, $roles)) {
                        continue;
                    }
                    $g = !empty($item['link_group']) ? $item['link_group'] : $defaultGroup;
                    $n = !empty($item['link_name']) ? $item['link_name'] : $item['name'];
                    $kept[] = [
                        'g' => $g,
                        'n' => $n,
                        'name' => $item['name'],
                        'label' => isset($item['label']) ? $item['label'] : $item['name'],
                        'icon' => isset($item['icon']) ? $item['icon'] : null,
                        // High-level area (Home/People/Time and Work/…) the shell
                        // groups by. meta.json "area" wins, else a built-in default.
                        'area' => MenuAreaService::resolveArea($item, $g, $n),
                        // Optional sort position within the area section (meta.json
                        // "areaOrder"); unset items keep their natural menu order.
                        'areaOrder' => isset($item['areaOrder']) && is_numeric($item['areaOrder'])
                            ? (int) $item['areaOrder'] : null,
                    ];
                }
                if (!empty($kept)) {
                    $result[] = ['name' => $menu['name'], 'items' => $kept];
                }
            }
        }
        return $result;
    }

    private function decodeUserRoles($user)
    {
        if (empty($user) || empty($user->user_roles)) {
            return [];
        }
        $decoded = json_decode($user->user_roles, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Applies the same per-item visibility rules as core/modules.php:570-633:
     *  - hide if the user holds a blacklisted role for the item
     *  - hide if user_level not in item.user_levels AND no overlap with item.user_roles
     * Empty menu groups (all items hidden) are dropped so the shell renders nothing
     * for them, matching the legacy sidebar (which skips groups with count(menu)==0).
     */
    private function filterMenus($modules, $user, array $userRoles)
    {
        if (empty($user) || !is_array($modules)) {
            return array_values($modules ?: []);
        }

        $result = [];
        foreach ($modules as $menu) {
            $items = isset($menu['menu']) && is_array($menu['menu']) ? $menu['menu'] : [];
            $kept = [];
            foreach ($items as $key => $item) {
                if (!$this->isItemVisible($item, $user, $userRoles)) {
                    continue;
                }
                $kept[$key] = $item;
            }
            if (empty($kept)) {
                continue; // legacy header skips groups with no visible items
            }
            $result[] = [
                'name' => $menu['name'],
                'menu' => $kept,
            ];
        }
        return $result;
    }

    private function isItemVisible($item, $user, array $userRoles)
    {
        return $this->isItemVisibleForLevel($item, $user->user_level, $userRoles);
    }

    /**
     * The single visibility rule (mirrors core/modules.php:570-633): hidden if a
     * held role is blacklisted; else visible if $level is in user_levels, else
     * visible only via an overlapping custom role.
     */
    private function isItemVisibleForLevel($item, $level, array $roles)
    {
        $blacklist = !empty($item['user_roles_blacklist']) ? $item['user_roles_blacklist'] : [];
        if (!is_array($blacklist)) {
            $blacklist = [];
        }
        if (!empty(array_intersect($blacklist, $roles))) {
            return false;
        }

        $userLevels = isset($item['user_levels']) ? $item['user_levels'] : [];
        if (!is_array($userLevels)) {
            $userLevels = [];
        }
        if (in_array($level, $userLevels)) {
            return true;
        }

        // level not directly allowed -> allow only via an overlapping custom role
        if (empty($roles)) {
            return false;
        }
        $itemRoles = !empty($item['user_roles']) ? $item['user_roles'] : [];
        if (!is_array($itemRoles)) {
            $itemRoles = [];
        }
        return !empty(array_intersect($itemRoles, $roles));
    }
}
