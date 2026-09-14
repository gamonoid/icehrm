<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 2:26 PM
 */

namespace Leaves\Admin\Api;

use Classes\AbstractModuleManager;
use Classes\SystemTasks\SystemTasksService;
use Classes\UIManager;
use Leaves\Common\Model\EmployeeLeave;
use Leaves\Common\Model\LeaveType;
use Leaves\Rest\LeaveRestEndPoint;

class LeavesAdminManager extends AbstractModuleManager
{

    public function initialize()
    {
        SystemTasksService::getInstance()->registerTaskCreator((new LeaveTaskCreator()));
    }

    public function initializeUserClasses()
    {
    }

    public function initializeFieldMappings()
    {
    }

    public function getInitializer()
    {
        $init = new LeaveInitializer();
        $init->setLeaveActionManager(new LeavesActionManager());
        return $init;
    }

    public function initializeDatabaseErrorMappings()
    {
        $this->addDatabaseErrorMapping(
            "LeaveGroupEmployees_employee",
            "An employee can only be added to one leave group"
        );
    }

    public function setupModuleClassDefinitions()
    {

        $this->addModelClass('LeaveType');
        $this->addModelClass('LeavePeriod');
        $this->addModelClass('WorkDay');
        $this->addModelClass('HoliDay');
        $this->addModelClass('LeaveRule');
        $this->addModelClass('LeaveGroup');
        $this->addModelClass('LeaveGroupEmployee');
        $this->addModelClass('LeaveStartingBalance');
    }

    public function getDashboardItemData()
    {
        $data = array();
        $empLeave = new EmployeeLeave();
        $data['numberOfLeaves'] = $empLeave->Count("date_start > '".date("Y-m-d")."'");
        return $data;
    }

    public function initQuickAccessMenu()
    {
        UIManager::getInstance()->addQuickAccessMenuItem(
            'Leave Calendar',
            'fa-calendar',
            CLIENT_BASE_URL.'?g=modules&n=leavecal&m=module_Leaves',
            array('Admin','Manager')
        );
    }

    public function initCalculationHooks()
    {
		$leaveType = new LeaveType();
		$leaveTypes = $leaveType->Find('1 = 1');
		$additionalData = [];
		foreach ($leaveTypes as $leaveType) {
			$additionalData[$leaveType->name] = $leaveType->name;
		}

        $this->addCalculationHook(
            'LeaveUtil_getLeaveHours',
            'Total Hours of Approved Leave Requests',
            LeaveUtil::class,
            'getLeaveHours',
			true,
			$additionalData
        );
        $this->addCalculationHook(
            'LeaveUtil_getHolidayHours',
            'Total Hours of Holidays',
            LeaveUtil::class,
            'getHolidayHours'
        );
    }

    public function setupRestEndPoints()
    {
        \Classes\Macaw::get(
            REST_API_PATH.'leave', function ($pathParams = null) {
                $restEndPoint = new LeaveRestEndPoint();
                $restEndPoint->process('getAllMyLeave', $pathParams);
            }
        );

        \Classes\Macaw::get(
            REST_API_PATH.'leave/(:num)', function ($pathParams = null) {
                $restEndPoint = new LeaveRestEndPoint();
                $restEndPoint->process('getLeaveDetails', $pathParams);
            }
        );

        \Classes\Macaw::get(
            REST_API_PATH.'leave/check/(:any)/(:any)/(:any)', function ($type, $start, $end) {
                $restEndPoint = new LeaveRestEndPoint();
                $restEndPoint->process('checkLeave', [$type, $start, $end]);
            }
        );

        \Classes\Macaw::post(
            REST_API_PATH.'leave/apply', function ($pathParams = null) {
                $restEndPoint = new LeaveRestEndPoint();
                $restEndPoint->process('apply', $pathParams);
            }
        );

        // Admin: apply for leave on behalf of another employee (body carries employeeId)
        \Classes\Macaw::post(
            REST_API_PATH.'leave/apply-for', function ($pathParams = null) {
                $restEndPoint = new LeaveRestEndPoint();
                $restEndPoint->process('applyFor', $pathParams);
            }
        );

        \Classes\Macaw::get(
            REST_API_PATH.'leave/entitlement', function ($pathParams = null) {
                $restEndPoint = new LeaveRestEndPoint();
                $restEndPoint->process('getEntitlement', $pathParams);
            }
        );

        \Classes\Macaw::post(
            REST_API_PATH.'leave/file-upload', function ($pathParams = null) {
                $restEndPoint = new LeaveRestEndPoint();
                $restEndPoint->process('uploadFile', []);
            }
        );

        \Classes\Macaw::get(
            REST_API_PATH.'leave/direct-reports', function ($pathParams = null) {
                $restEndPoint = new LeaveRestEndPoint();
                $restEndPoint->process('getDirectReportsLeave', $pathParams);
            }
        );

        \Classes\Macaw::get(
            REST_API_PATH.'leave/direct-reports/pending', function ($pathParams = null) {
                $restEndPoint = new LeaveRestEndPoint();
                $restEndPoint->process('getDirectReportsPendingLeave', $pathParams);
            }
        );

        \Classes\Macaw::get(
            REST_API_PATH.'leave/details/(:num)', function ($leaveId) {
                $restEndPoint = new LeaveRestEndPoint();
                $restEndPoint->process('getLeaveDetails', [$leaveId]);
            }
        );

        \Classes\Macaw::post(
            REST_API_PATH.'leave/(:num)/approve', function ($leaveId) {
                $restEndPoint = new LeaveRestEndPoint();
                $restEndPoint->process('approveLeave', [$leaveId]);
            }
        );

        \Classes\Macaw::post(
            REST_API_PATH.'leave/(:num)/reject', function ($leaveId) {
                $restEndPoint = new LeaveRestEndPoint();
                $restEndPoint->process('rejectLeave', [$leaveId]);
            }
        );
    }
}
