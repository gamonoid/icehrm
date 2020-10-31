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
        $moduleUserLevels = json_decode($module->user_levels, true);
        $moduleUserRoles = json_decode($module->user_roles, true);
        $userRoles = json_decode($user->user_roles, true);

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
