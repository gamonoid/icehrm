<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 10:43 AM
 */

namespace Employees\Admin\Api;

use Classes\AbstractModuleManager;
use Classes\Macaw;
use Classes\UIManager;
use Employees\Common\Model\Employee;
use Employees\Rest\EmployeeRestEndPoint;

class EmployeesAdminManager extends AbstractModuleManager
{

    public function initializeUserClasses()
    {
    }

    public function initializeFieldMappings()
    {
    }

    public function setupRestEndPoints()
    {
        Macaw::get(REST_API_PATH.'employees/me', function () {
            $empRestEndPoint = new EmployeeRestEndPoint();
            $empRestEndPoint->process('get', 'me');
        });

        Macaw::get(REST_API_PATH.'employees/(:num)', function ($pathParams) {
            $empRestEndPoint = new EmployeeRestEndPoint();
            $empRestEndPoint->process('get', $pathParams);
        });

        Macaw::get(REST_API_PATH.'employees', function () {
            $empRestEndPoint = new EmployeeRestEndPoint();
            $empRestEndPoint->process('listAll');
        });

        Macaw::post(REST_API_PATH.'employees', function () {
            $empRestEndPoint = new EmployeeRestEndPoint();
            $empRestEndPoint->process('post');
        });

        Macaw::put(REST_API_PATH.'employees/(:num)', function ($pathParams) {
            $empRestEndPoint = new EmployeeRestEndPoint();
            $empRestEndPoint->process('put', $pathParams);
        });

        Macaw::delete(REST_API_PATH.'employees/(:num)', function ($pathParams) {
            $empRestEndPoint = new EmployeeRestEndPoint();
            $empRestEndPoint->process('delete', $pathParams);
        });
    }

    public function initializeDatabaseErrorMappings()
    {
        $this->addDatabaseErrorMapping(
            'CONSTRAINT `Fk_User_Employee` FOREIGN KEY',
            "Can not delete Employee, please delete the User for this employee first."
        );
        $this->addDatabaseErrorMapping("Duplicate entry|for key 'employee'", "A duplicate entry found");
    }

    public function setupModuleClassDefinitions()
    {
        $this->addModelClass('Employee');
        $this->addModelClass('EmploymentStatus');
        $this->addModelClass('EmployeeApproval');
        $this->addModelClass('ArchivedEmployee');
    }

    public function getDashboardItemData()
    {
        $data = array();
        $emp = new Employee();
        $data['numberOfEmployees'] = $emp->Count("1 = 1");

        return $data;
    }

    public function initQuickAccessMenu()
    {
        UIManager::getInstance()->addQuickAccessMenuItem(
            'View Employees',
            'fa-users',
            CLIENT_BASE_URL.'?g=admin&n=employees&m=admin_Employees',
            array('Admin','Manager')
        );
        UIManager::getInstance()->addQuickAccessMenuItem(
            'Add a New Employee',
            'fa-edit',
            CLIENT_BASE_URL.'?g=admin&n=employees&m=admin_Employees&action=new',
            array('Admin')
        );
    }

    public function initCalculationHooks()
    {
        $this->addCalculationHook(
            'EmployeeData_getFieldValue',
            'Get Employee Data',
            EmployeeUtil::class,
            'getEmployeeDataField'
        );
    }
}
