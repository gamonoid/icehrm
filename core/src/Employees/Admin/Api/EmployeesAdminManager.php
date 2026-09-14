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
use Classes\SystemTasks\SystemTasksService;
use Classes\UIManager;
use Employees\Common\Model\Employee;
use Employees\Rest\CustomFieldRestEndPoint;
use Employees\Rest\EmployeeAttendanceRestEndPoint;
use Employees\Rest\EmployeeCertificationsRestEndPoint;
use Employees\Rest\EmployeeEducationRestEndPoint;
use Employees\Rest\EmployeeLanguageRestEndPoint;
use Employees\Rest\EmployeeLeavesRestEndPoint;
use Employees\Rest\EmployeeRestEndPoint;
use Employees\Rest\EmployeeSkillsRestEndPoint;

class EmployeesAdminManager extends AbstractModuleManager
{

    public function initialize()
    {
        SystemTasksService::getInstance()->registerTaskCreator((new EmployeeTaskCreator()));
    }

    public function initializeUserClasses()
    {
    }

    public function initializeFieldMappings()
    {
    }

    public function setupRestEndPoints()
    {
        // Preview the next auto-generated employee number for the add form.
        Macaw::get(
            REST_API_PATH.'employees/next-employee-number',
            function () {
                $enabled = Employee::isEmployeeNumberGenerationEnabled();
                (new \Classes\RestEndPoint())->sendResponse(new \Classes\IceResponse(
                    \Classes\IceResponse::SUCCESS,
                    array(
                        'enabled' => $enabled,
                        'number' => $enabled ? Employee::generateEmployeeNumber() : '',
                    )
                ));
            }
        );

        // Read an employee's multi-level approval chain (manager + approver1/2/3,
        // each resolved to {id, name, image}). Admin / all-employee-access only.
        Macaw::get(
            REST_API_PATH.'employees/(:num)/approvers',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeRestEndPoint();
                $empRestEndPoint->process('getApprovers', $pathParams);
            }
        );

        // Set an employee's multi-level approvers (approver1/2/3). The manager
        // (supervisor) is the initial approver and is NOT set here — it is edited
        // via the employee form. Admin / all-employee-access only.
        Macaw::post(
            REST_API_PATH.'employees/(:num)/approvers',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeRestEndPoint();
                $empRestEndPoint->process('saveApprovers', $pathParams);
            }
        );

        Macaw::get(
            REST_API_PATH.'employees/me',
            function () {
                $empRestEndPoint = new EmployeeRestEndPoint();
                $empRestEndPoint->process('get', 'me');
            }
        );

        Macaw::get(
            REST_API_PATH.'employees/(:num)',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeRestEndPoint();
                $empRestEndPoint->process('get', $pathParams);
            }
        );

        Macaw::get(
            REST_API_PATH.'employees',
            function () {
                $empRestEndPoint = new EmployeeRestEndPoint();
                $empRestEndPoint->process('listAll');
            }
        );

        Macaw::post(
            REST_API_PATH.'employees',
            function () {
                $empRestEndPoint = new EmployeeRestEndPoint();
                $empRestEndPoint->process('post');
            }
        );

        Macaw::put(
            REST_API_PATH.'employees/(:num)',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeRestEndPoint();
                $empRestEndPoint->process('put', $pathParams);
            }
        );

        Macaw::delete(
            REST_API_PATH.'employees/(:num)',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeRestEndPoint();
                $empRestEndPoint->process('delete', $pathParams);
            }
        );

        // Employee skills
        Macaw::get(
            REST_API_PATH.'employees/(:num)/skills',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeSkillsRestEndPoint();
                $empRestEndPoint->process('listAll', $pathParams);
            }
        );

        // Employee education
        Macaw::get(
            REST_API_PATH.'employees/(:num)/educations',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeEducationRestEndpoint();
                $empRestEndPoint->process('listAll', $pathParams);
            }
        );

        // Employee certifications
        Macaw::get(
            REST_API_PATH.'employees/(:num)/certifications',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeCertificationsRestEndpoint();
                $empRestEndPoint->process('listAll', $pathParams);
            }
        );

        // Employee languages
        Macaw::get(
            REST_API_PATH.'employees/(:num)/languages',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeLanguageRestEndpoint();
                $empRestEndPoint->process('listAll', $pathParams);
            }
        );

        // Employee attendance summary (must be before /attendance to match first)
        Macaw::get(
            REST_API_PATH.'employees/(:num)/attendance/summary',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeAttendanceRestEndPoint();
                $empRestEndPoint->process('getSummary', $pathParams);
            }
        );

        // Employee attendance
        Macaw::get(
            REST_API_PATH.'employees/(:num)/attendance',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeAttendanceRestEndPoint();
                $empRestEndPoint->process('listAll', $pathParams);
            }
        );

        // Employee leaves summary (must be before /leaves to match first)
        Macaw::get(
            REST_API_PATH.'employees/(:num)/leaves/summary',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeLeavesRestEndPoint();
                $empRestEndPoint->process('getSummary', $pathParams);
            }
        );

        // Employee leaves
        Macaw::get(
            REST_API_PATH.'employees/(:num)/leaves',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeLeavesRestEndPoint();
                $empRestEndPoint->process('listAll', $pathParams);
            }
        );

        // Employee status
        Macaw::get(
            REST_API_PATH.'employees/(:num)/status',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeRestEndPoint();
                $empRestEndPoint->process('getEmployeeStatusMessage', $pathParams);
            }
        );

        Macaw::post(
            REST_API_PATH.'employees/(:num)/status',
            function ($pathParams) {
                $empRestEndPoint = new EmployeeRestEndPoint();
                $empRestEndPoint->process('setEmployeeStatusMessage', $pathParams);
            }
        );

		Macaw::post(
			REST_API_PATH.'employees/(:num)/create-user',
			function ($pathParams) {
				$empRestEndPoint = new EmployeeRestEndPoint();
				$empRestEndPoint->process('createUserForEmployee', $pathParams);
			}
		);

        // Employee custom field DEFINITIONS (admin-only). ':num' never matches
        // 'custom-fields', so these do not collide with employees/(:num).
        Macaw::get(
            REST_API_PATH.'employees/custom-fields',
            function () {
                (new CustomFieldRestEndPoint())->process('listFields');
            }
        );

        Macaw::post(
            REST_API_PATH.'employees/custom-fields',
            function () {
                (new CustomFieldRestEndPoint())->process('createField');
            }
        );

        Macaw::delete(
            REST_API_PATH.'employees/custom-fields/(:num)',
            function ($id) {
                (new CustomFieldRestEndPoint())->process('deleteField', $id);
            }
        );

        // Employee custom field VALUES for a specific employee (admin-only).
        Macaw::get(
            REST_API_PATH.'employees/(:num)/custom-fields',
            function ($employeeId) {
                (new CustomFieldRestEndPoint())->process('getValues', $employeeId);
            }
        );

        Macaw::put(
            REST_API_PATH.'employees/(:num)/custom-fields/(:any)',
            function ($employeeId, $name) {
                (new CustomFieldRestEndPoint())->process('setValue', [$employeeId, $name]);
            }
        );

        Macaw::delete(
            REST_API_PATH.'employees/(:num)/custom-fields/(:any)',
            function ($employeeId, $name) {
                (new CustomFieldRestEndPoint())->process('deleteValue', [$employeeId, $name]);
            }
        );
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
        $this->addModelClass('EmployeeStatus');
        $this->addModelClass('EmployeeApproval');
        $this->addModelClass('ArchivedEmployee');
        $this->addModelClass('EmployeeCareer');
        $this->addModelClass('EmployeeAccess');
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
		$additionalData = [
			'employee_id' => 'Employee ID',
			'first_name' => 'First name'
		];

		// Expose employee custom fields as selectable sources for this hook, so a
		// payroll column can pull any employee custom field value (the hook resolves
		// them in EmployeeUtil::getEmployeeDataField).
		try {
			$customField = new \FieldNames\Common\Model\CustomField();
			$fields = $customField->Find('type = ? order by display_order, id', ['Employee']);
			foreach ($fields as $field) {
				if (!empty($field->name)) {
					$additionalData[$field->name] = !empty($field->field_label) ? $field->field_label : $field->name;
				}
			}
		} catch (\Exception $e) {
			// Custom fields are optional — ignore if unavailable.
		}

        $this->addCalculationHook(
            'EmployeeData_getFieldValue',
            'Get Employee Data',
            EmployeeUtil::class,
            'getEmployeeDataField',
			true,
			$additionalData
        );
    }
}
