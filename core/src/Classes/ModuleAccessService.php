<?php

namespace Classes;

use Modules\Common\Model\Module;

class ModuleAccessService
{
    protected $moduleMap = [];
    protected $moduleIdMap = [];

    private static $me = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$me)) {
            self::$me = new ModuleAccessService();
        }

        return self::$me;
    }

    public function setModule($name, $group, $module)
    {
        $this->moduleMap[$group.'-'.$name] = $module;
        $this->moduleIdMap[$module->id] = $module;
    }

    public function getModule($name, $group)
    {
        return $this->moduleMap[$group.'-'.$name];
    }

    public function getModules()
    {
        return array_values($this->moduleMap);
    }

    public function isModuleEnabledForUser($moduleId, $user)
    {
        $module = $this->moduleIdMap[$moduleId];
        return $this->userMayAccessModuleLevels(
            json_decode($module->user_levels, true),
            json_decode($module->user_roles, true),
            $user
        );
    }

    /**
     * Whether $user is permitted for a module with these declared user_levels /
     * user_roles. This is the SAME rule the menu uses to decide visibility, factored
     * out so the request layer can enforce it directly.
     *
     * It must be enforced at the point a module's code is invoked, not just when the
     * menu is drawn: hiding a menu entry does nothing to stop a direct
     * service.php?a=ca&mod=<group>=<module> request, which reaches the module's
     * action manager regardless. See the caller in core/service.php.
     *
     * @param  array|null $moduleUserLevels
     * @param  array|null $moduleUserRoles
     * @param  mixed      $user
     * @return bool
     */
    public function userMayAccessModuleLevels($moduleUserLevels, $moduleUserRoles, $user)
    {
        $moduleUserLevels = is_array($moduleUserLevels) ? $moduleUserLevels : array();
        $moduleUserRoles = is_array($moduleUserRoles) ? $moduleUserRoles : array();
        $userRoles = empty($user->user_roles) ? array() : json_decode($user->user_roles, true);
        if (!is_array($userRoles)) {
            $userRoles = array();
        }

        // No declared levels means the module is exposed to nobody via its own
        // metadata — deny, matching the menu, rather than falling open.
        if (empty($moduleUserLevels)) {
            return false;
        }

        if (in_array($user->user_level, PermissionManager::RESTRICTED_USER_LEVELS)) {
            if (empty($userRoles)) {
                return false;
            }

            $baseUserLevel = str_replace('Restricted ', '', $user->user_level);
            // In this case base user level should have access to the module
            if (!in_array($baseUserLevel, $moduleUserLevels)) {
                return false;
            }

            return count(array_intersect($userRoles, $moduleUserRoles)) > 0;
        }

        return in_array($user->user_level, $moduleUserLevels)
            || count(array_intersect($userRoles, $moduleUserRoles)) > 0;
    }
}
