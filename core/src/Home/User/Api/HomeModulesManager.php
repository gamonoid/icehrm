<?php
/**
 * Module manager for the Home module — the application's landing page and entry
 * point for every user level (see docs/HOME_AND_KUDOS_PLAN.md).
 *
 * Home owns no models of its own: it composes data other modules already compute
 * (EmployeeDashboardService, DashboardService) through Classes\HomeService, served
 * by GET appshell/home. This manager exists so core/modules.php can register the
 * module row and load it like any other core user module.
 */

namespace Home\User\Api;

use Classes\AbstractModuleManager;

class HomeModulesManager extends AbstractModuleManager
{
    public function initializeUserClasses()
    {
    }

    public function initializeFieldMappings()
    {
    }

    public function initializeDatabaseErrorMappings()
    {
    }

    public function setupModuleClassDefinitions()
    {
    }
}
