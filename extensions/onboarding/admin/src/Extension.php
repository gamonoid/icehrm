<?php

namespace OnboardingAdmin;

use Classes\BaseService;
use Classes\DashboardService;
use Classes\IceExtension;
use Classes\MenuService;
use Classes\SystemTasks\SystemTasksService;
use Employees\Services\UserInvitationService;
use OnboardingAdmin\Migrations\AddTrack;
use OnboardingAdmin\Migrations\CreateTables;

/**
 * Onboarding: HR invites a new hire with a workflow (a standard one shipped in code, or
 * a custom copy), the new hire sets their password and works through a checklist
 * before and after their start date, and HR, IT, the manager and the buddy do their
 * own tasks. The user part ("My Onboarding") is only a view; every endpoint lives
 * here so there is one place that decides access.
 */
class Extension extends IceExtension
{
    /**
     * What a new hire who is still onboarding may open: their checklist, plus their
     * own profile and emergency contacts so profile steps can be completed (there is
     * no Forms extension in IceHrm open source to collect those details).
     */
    const ONBOARDING_MODULES = array(
        'extension:onboarding|user',
        'modules:employees',
        'modules:emergency_contact',
        'extension:esign|user',
        'extension:learn|user',
    );

    public function initialize()
    {
        BaseService::getInstance()->registerExtensionMigration(new CreateTables());
        BaseService::getInstance()->registerExtensionMigration(new AddTrack());
        SystemTasksService::getInstance()->registerTaskCreator(new OnboardingTaskCreator());

        // The core invitation flow tells us when an onboarding invitation is accepted,
        // and lets us add the welcome message to its page and email.
        UserInvitationService::onAccepted(array(OnboardingService::class, 'onInvitationAccepted'));
        UserInvitationService::addAcceptPageProvider(array(OnboardingService::class, 'acceptPageExtras'));

        // Someone who joined but has steps left sees a banner on Home and the dashboards.
        DashboardService::addBannerProvider(function ($user) {
            return (new OnboardingService())->checklistBanner($user);
        });

        // "My Onboarding" is only in the menu of someone who is being onboarded or has
        // onboarding tasks of their own.
        if (method_exists(MenuService::class, 'addVisibilityFilter')) {
            MenuService::addVisibilityFilter(function ($user, $group, $name) {
                if ($group !== 'extension' || $name !== 'onboarding|user') {
                    return true;
                }
                return (new OnboardingService())->showsMyOnboarding($user);
            });
        }

        // Until their start date a new hire only sees their onboarding pages.
        MenuService::addRestriction(function ($user) {
            if (empty($user) || empty($user->employee)) {
                return null;
            }
            $rows = Db::rows('SELECT status FROM Employees WHERE id = ?', array((int) $user->employee));
            if (empty($rows) || $rows[0]['status'] !== 'Onboarding') {
                return null;
            }
            return array(
                'modules' => self::ONBOARDING_MODULES,
                'home' => array('group' => 'extension', 'name' => 'onboarding|user'),
            );
        });
    }

    public function setupModuleClassDefinitions()
    {
        $this->addModelClass('OnboardingTemplate');
        $this->addModelClass('Onboarding');
        $this->addModelClass('OnboardingStep');
        $this->addModelClass('OnboardingActivity');
    }

    public function setupRestEndPoints()
    {
        (new ApiController())->registerEndPoints();
    }
}
