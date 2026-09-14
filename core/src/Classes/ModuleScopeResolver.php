<?php

namespace Classes;

/**
 * ModuleScopeResolver
 *
 * The new SPA UI sends the module a data request belongs to EXPLICITLY (mg/mn
 * request params) instead of relying on the ambient session `modulePath` (which
 * is shared across tabs/navigations and causes wrong admin-vs-user data scope —
 * see docs/DATA_SCOPE_ISSUE.md).
 *
 * This resolver maps the explicit (group, name) to an absolute MODULE_PATH (used
 * to derive MODULE_TYPE → data scope) and validates the current user is actually
 * authorized for that module, so a client cannot escalate scope by naming an
 * admin module it can't access.
 *
 * Legacy requests send no mg/mn, so this is never engaged for them.
 */
class ModuleScopeResolver
{
    const NAME_RE = '/^[A-Za-z0-9_\-]+$/';

    /**
     * Pure string mapping of an SPA module (group, name) to an absolute
     * MODULE_PATH. No DB/session needed, so it can run BEFORE server.includes.
     * Returns null for anything malformed (also prevents path traversal).
     *
     *   admin     / attendance    -> <core>/admin/attendance
     *   modules   / attendance    -> <core>/modules/attendance
     *   extension / tasks|admin   -> <core>/../extensions/tasks/admin
     */
    public static function pathFor($group, $name)
    {
        if (empty($group) || empty($name)) {
            return null;
        }
        $core = defined('CLIENT_PATH') ? CLIENT_PATH : null;
        if ($core === null) {
            return null;
        }

        if ($group === 'admin' || $group === 'modules') {
            if (!preg_match(self::NAME_RE, $name)) {
                return null;
            }
            return $core . '/' . $group . '/' . $name;
        }

        if ($group === 'extension') {
            $parts = explode('|', $name);
            if (count($parts) !== 2) {
                return null;
            }
            list($ext, $type) = $parts;
            if (!preg_match(self::NAME_RE, $ext) || !in_array($type, array('admin', 'user'), true)) {
                return null;
            }
            return $core . '/../extensions/' . $ext . '/' . $type;
        }

        return null;
    }

    /**
     * Is the user authorized for this SPA module? Mirrors menu visibility via
     * ModuleAccessService, so naming an admin module you can't access is rejected.
     * Requires ModuleAccessService to be populated — call AFTER server.includes.
     */
    public static function isAuthorized($group, $name, $user)
    {
        if (empty($user) || empty($group) || empty($name)) {
            return false;
        }

        if ($group === 'admin') {
            $dbGroup = 'admin';
            $lookup = $name;
        } elseif ($group === 'modules') {
            $dbGroup = 'user';
            $lookup = $name;
        } elseif ($group === 'extension') {
            $dbGroup = 'extension';
            $parts = explode('|', $name);
            $lookup = $parts[0];
        } else {
            return false;
        }

        try {
            $module = ModuleAccessService::getInstance()->getModule($lookup, $dbGroup);
            if (empty($module) || empty($module->id)) {
                return false;
            }
            return ModuleAccessService::getInstance()->isModuleEnabledForUser($module->id, $user);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
