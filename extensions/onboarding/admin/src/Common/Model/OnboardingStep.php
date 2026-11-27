<?php

namespace OnboardingAdmin\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

/**
 * One step of one hire's onboarding, copied from the workflow when the hire was invited.
 *
 * All reads and writes go through the onboarding REST endpoints (ApiController), which
 * decide who may see what. The generic service.php paths only give Admins a read-only
 * view.
 */
class OnboardingStep extends BaseModel
{
    public $table = 'OnboardingSteps';

    public function getAdminAccess()
    {
        return array('get', 'element');
    }

    public function getManagerAccess()
    {
        return array();
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getUserOnlyMeAccess()
    {
        return array();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('onboarding', 'admin'),
        ];
    }
}
