<?php

namespace OnboardingAdmin\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

/**
 * One new hire being onboarded: their basics, start date, workflow and progress.
 *
 * All reads and writes go through the onboarding REST endpoints (ApiController), which
 * decide who may see what. The generic service.php paths only give Admins a read-only
 * view.
 */
class Onboarding extends BaseModel
{
    public $table = 'Onboardings';

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
