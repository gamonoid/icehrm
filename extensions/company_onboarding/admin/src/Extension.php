<?php

namespace CompanyOnboardingAdmin;

use Classes\BaseService;
use Classes\DashboardService;
use Classes\IceExtension;
use Classes\MenuService;
use Classes\NativeModuleRegistry;
use CompanyOnboardingAdmin\Migrations\CreateTables;

/**
 * Company Onboarding: a guided setup that brings a company onto IceHrm (company
 * details, structure, job titles, leave, ...) and then invites its existing staff.
 * While the company has fewer than two real employees, admins see a dashboard banner
 * (replacing the sample-data prompt) and a bubble on this menu item.
 */
class Extension extends IceExtension
{
    public function initialize()
    {
        BaseService::getInstance()->registerExtensionMigration(new CreateTables());

        DashboardService::addBannerProvider(function ($user) {
            return (new SetupService())->banner($user);
        });
        MenuService::addBadgeProvider(function ($user) {
            return (new SetupService())->badges($user);
        });

        // Employees are invited through Company Onboarding (and New Hire Onboarding),
        // so the plain Invitations tab under Employees is not shown, and "Invite an
        // Employee" opens Company Onboarding instead.
        NativeModuleRegistry::hideTab('admin/employees', 'tabUserInvitation', SetupService::ROUTE);
    }

    public function setupModuleClassDefinitions()
    {
    }

    public function setupRestEndPoints()
    {
        (new ApiController())->registerEndPoints();
    }
}
