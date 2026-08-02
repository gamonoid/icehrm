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

        return [
            'adminModules' => $this->filterMenus($this->rawAdminModules, $user, $userRoles),
            'userModules' => $this->filterMenus($this->rawUserModules, $user, $userRoles),
            'mainIcons' => $this->mainIcons,
        ];
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

        return [
            'admin' => $this->buildView(
                [[$this->rawAdminModules, 'admin']],
                $user->user_level,
                $userRoles
            ),
            'employee' => $this->buildView(
                [[$this->rawAdminModules, 'admin'], [$this->rawUserModules, 'modules']],
                'Employee',
                []
            ),
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
