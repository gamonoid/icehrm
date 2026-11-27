<?php

namespace OnboardingUser;

use Classes\IceExtension;

/**
 * "My Onboarding" — the new hire's checklist, and the onboarding tasks other people
 * (HR, IT, managers, buddies) own. All data and access rules live in the admin part
 * (OnboardingAdmin\ApiController, the onboarding/me/* endpoints); this part only mounts
 * the page.
 */
class Extension extends IceExtension
{
    public function initialize()
    {
    }

    public function setupModuleClassDefinitions()
    {
    }

    public function setupRestEndPoints()
    {
    }
}
