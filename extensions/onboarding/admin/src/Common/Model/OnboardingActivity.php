<?php

namespace OnboardingAdmin\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

/**
 * Audit trail of an onboarding (invited, accepted, step done, approved, ...).
 *
 * All reads and writes go through the onboarding REST endpoints (ApiController), which
 * decide who may see what. The generic service.php paths only give Admins a read-only
 * view.
 */
class OnboardingActivity extends BaseModel
{
    public $table = 'OnboardingActivity';

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
