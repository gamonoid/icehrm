<?php

namespace OnboardingAdmin;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\ModuleAccessService;
use Classes\RestEndPoint;
use Employees\Common\Model\EmployeeAccess;

/**
 * Who may do what with onboarding. Decided from the signed-in user only, never from
 * the request.
 *
 *   HR        Admin, or anyone who can open the Onboarding admin module AND has access
 *             to all employee data. Builds workflows, invites new hires, sees and
 *             reviews everything, completes or cancels onboardings.
 *   Manager   Can open the Onboarding admin module (Manager level by default). Sees the
 *             onboardings of the people they manage (read only, sensitive data never
 *             shown), and the steps assigned to them.
 *   Owner     Anyone a step is assigned to (HR, IT, manager, buddy): sees that step and
 *             marks it done, through the onboarding/me/tasks endpoints.
 *   New hire  Only their own onboarding and the steps they own.
 */
class OnboardingAccess
{
    /** @return object|null the signed-in user, or null after sending a 401 */
    public static function requireUser(RestEndPoint $rest)
    {
        $user = BaseService::getInstance()->getCurrentUser();
        if (empty($user) || empty($user->id)) {
            $user = $rest->getCurrentUser();
        }
        if (empty($user) || empty($user->id)) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Not authenticated', 401));
            return null;
        }
        return $user;
    }

    public static function canOpenAdminModule($user)
    {
        if (empty($user)) {
            return false;
        }
        if ($user->user_level === 'Admin') {
            return true;
        }
        try {
            $service = ModuleAccessService::getInstance();
            $module = $service->getModule('onboarding', 'admin');
            if (empty($module) || empty($module->id)) {
                return false;
            }
            return (bool) $service->isModuleEnabledForUser($module->id, $user);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function isHr($user)
    {
        if (empty($user)) {
            return false;
        }
        if ($user->user_level === 'Admin') {
            return true;
        }
        return self::canOpenAdminModule($user) && EmployeeAccess::hasAccessToAllEmployeeData();
    }

    /** May see this onboarding in the admin view (HR, or the hire's manager). */
    public static function canView($user, array $onboarding)
    {
        if (self::isHr($user)) {
            return true;
        }
        if (!self::canOpenAdminModule($user) || empty($onboarding['employee'])) {
            return false;
        }
        if ((int) $onboarding['employee'] === (int) self::currentEmployeeId()) {
            return false;
        }
        return (bool) BaseService::getInstance()->currentUserCanAccessEmployeeData($onboarding['employee']);
    }

    public static function currentEmployeeId()
    {
        $id = BaseService::getInstance()->getCurrentProfileId();
        return empty($id) ? null : (int) $id;
    }

    /** Sends 403 and returns true when $allowed is false (guard-clause helper). */
    public static function deny(RestEndPoint $rest, $allowed)
    {
        if ($allowed) {
            return false;
        }
        $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Permission denied', 403));
        return true;
    }
}
