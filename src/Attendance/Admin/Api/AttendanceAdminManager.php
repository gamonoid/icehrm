<?php
namespace Attendance\Admin\Api;

use Attendance\Common\Model\Attendance;
use Attendance\Rest\AttendanceRestEndPoint;
use Classes\AbstractModuleManager;
use Classes\UIManager;

class AttendanceAdminManager extends AbstractModuleManager
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
        $this->addModelClass('Attendance');
        $this->addModelClass('AttendanceStatus');
    }

    public function setupRestEndPoints()
    {
        \Classes\Macaw::get(REST_API_PATH.'attendance/(:num)', function ($pathParams) {
            $restEndPoint = new AttendanceRestEndPoint();
            $restEndPoint->process('get', $pathParams);
        });

        \Classes\Macaw::get(REST_API_PATH.'attendance', function ($pathParams) {
            $restEndPoint = new AttendanceRestEndPoint();
            $restEndPoint->process('listAll', $pathParams);
        });

        \Classes\Macaw::get(REST_API_PATH.'employee/(:num)/attendance', function ($pathParams) {
            $restEndPoint = new AttendanceRestEndPoint();
            $restEndPoint->process('listEmployeeAttendance', $pathParams);
        });

        \Classes\Macaw::post(REST_API_PATH.'attendance', function ($pathParams) {
            $restEndPoint = new AttendanceRestEndPoint();
            $restEndPoint->process('post', $pathParams);
        });

        \Classes\Macaw::delete(REST_API_PATH.'attendance/(:num)', function ($pathParams) {
            $restEndPoint = new AttendanceRestEndPoint();
            $restEndPoint->process('delete', $pathParams);
        });

        \Classes\Macaw::post(REST_API_PATH.'attendance/punch-in', function ($pathParams) {
            $restEndPoint = new AttendanceRestEndPoint();
            $restEndPoint->process('punchIn', $pathParams);
        });

        \Classes\Macaw::get(REST_API_PATH.'employee/(:num)/open-punch-in/(:any)', function ($employeeId, $date) {
            $restEndPoint = new AttendanceRestEndPoint();
            $restEndPoint->process('getOpenPunch', [$employeeId, $date]);
        });

        \Classes\Macaw::post(REST_API_PATH.'attendance/punch-out', function ($pathParams) {
            $restEndPoint = new AttendanceRestEndPoint();
            $restEndPoint->process('punchOut', $pathParams);
        });
    }

    public function getDashboardItemData()
    {
        $data = array();
        $attendance = new Attendance();
        $data['numberOfAttendanceLastWeek']
            = $attendance->Count("in_time > '".date("Y-m-d H:i:s", strtotime("-1 week"))."'");
        if (empty($data['numberOfAttendanceLastWeek'])) {
            $data['numberOfAttendanceLastWeek'] = 0;
        }
        return $data;
    }

    public function initQuickAccessMenu()
    {
        UIManager::getInstance()->addQuickAccessMenuItem(
            "Clocked In Employees",
            "fa-clock-o",
            CLIENT_BASE_URL."?g=admin&n=attendance&m=admin_Employees#tabAttendanceStatus",
            array("Admin","Manager")
        );
    }

    public function initCalculationHooks()
    {
        $this->addCalculationHook(
            'AttendanceUtil_getTimeWorkedHours',
            'Total Hours from Attendance',
            '\\Attendance\\Admin\\Api\\AttendanceUtil',
            'getTimeWorkedHours'
        );

        $this->addCalculationHook(
            'AttendanceUtil_getRegularWorkedHours',
            'Total Regular Hours from Attendance',
            '\\Attendance\\Admin\\Api\\AttendanceUtil',
            'getRegularWorkedHours'
        );
        $this->addCalculationHook(
            'AttendanceUtil_getOverTimeWorkedHours',
            'Total Overtime Hours from Attendance',
            '\\Attendance\\Admin\\Api\\AttendanceUtil',
            'getOverTimeWorkedHours'
        );
        $this->addCalculationHook(
            'AttendanceUtil_getWeeklyRegularWorkedHours',
            'Total Weekly Regular Hours from Attendance',
            '\\Attendance\\Admin\\Api\\AttendanceUtil',
            'getWeeklyBasedRegularHours'
        );
        $this->addCalculationHook(
            'AttendanceUtil_getWeeklyOverTimeWorkedHours',
            'Total Weekly Overtime Hours from Attendance',
            '\\Attendance\\Admin\\Api\\AttendanceUtil',
            'getWeeklyBasedOvertimeHours'
        );
    }
}
