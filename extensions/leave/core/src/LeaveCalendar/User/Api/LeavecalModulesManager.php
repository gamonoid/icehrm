<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 4:23 PM
 */

namespace LeaveCalendar\User\Api;

use Classes\AbstractModuleManager;
use Classes\Macaw;
use LeaveCalendar\User\Rest\LeavecalRestEndpoint;

class LeavecalModulesManager extends AbstractModuleManager
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

    public function setupRestEndPoints()
    {
        Macaw::get(
            REST_API_PATH.'leave-calendar/month/(:any)/(:any)',
            function ($year, $month) {
                $restEndPoint = new LeavecalRestEndpoint();
                $restEndPoint->process('getMonthlyLeaves', [$year, $month]);
            }
        );

        Macaw::get(
            REST_API_PATH.'leave-calendar/year/(:any)',
            function ($year) {
                $restEndPoint = new LeavecalRestEndpoint();
                $restEndPoint->process('getYearlyLeaves', [$year]);
            }
        );
    }
}
