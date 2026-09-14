<?php
namespace Employees\Rest;

use Classes\BaseService;
use Classes\Data\Query\DataQuery;
use Classes\Data\Query\Filter;
use Classes\FileService;
use Classes\IceResponse;
use Classes\PermissionManager;
use Classes\RestEndPoint;
use Employees\Common\Model\Employee;
use Employees\Common\Model\EmployeeAccess;
use Employees\Common\Model\EmployeeStatus;
use Users\Common\Model\User;
use Utils\CalendarTools;
use Utils\LogManager;
use Utils\NetworkUtils;

class EmployeeRestEndPoint extends RestEndPoint
{
    const ELEMENT_NAME = 'Employee';

    public function getModelObject($id)
    {
        $obj = new Employee();
        $obj->Load("id = ?", array($id));
        return $obj;
    }

    public function listAll(User $user, $parameter = null)
    {
        $query = new DataQuery('Employee');
        $query->addFilter(new Filter('employee', $parameter));
        $mapping = <<<JSON
{
  "job_title": [ "JobTitle", "id", "name" ],
  "country": [ "Country", "code", "name" ],
  "province": [ "Province", "id", "name" ],
  "department": [ "CompanyStructure", "id", "title" ],
  "supervisor": [ "Employee", "id", "first_name+last_name" ],
  "employment_status": [ "EmploymentStatus", "id", "name" ],
  "pay_grade": [ "PayGrade", "id", "name" ]
}
JSON;
        $query->setFieldMapping($mapping);

        $limit = self::DEFAULT_LIMIT;
        if (isset($_GET['limit']) && intval($_GET['limit']) > 0) {
            $limit = intval($_GET['limit']);
        }
        $query->setLength($limit);

        if (!empty($_GET['filters'])) {
            $query->setFilters(json_decode($_GET['filters'], true));
        }

        if (!empty($_GET['search'])) {
            $query->setSearchTerm($_GET['search']);
            $query->setSearchColumns(['first_name', 'last_name', 'employee_id','ssn_number','nic_number','other_id','driving_license','country']);
        }

        if (isset($_GET['sortField']) && !empty($_GET['sortField'])) {
            $query->setSortColumn($_GET['sortField']);
            $query->setSortingEnabled(true);
            $query->setSortOrder(
                empty($_GET['sortOrder']) || $_GET['sortOrder'] === 'ascend' ? 'ASC' : 'DESC'
            );
        }

        $me = null;
        if ($user->user_level !== 'Admin') {
            $query->setIsSubOrdinates(true);
            $me = new Employee();
            $me->Load("id = ?", array(BaseService::getInstance()->getCurrentProfileId()));
        }

        $response = $this->listByQuery($query);

        $responseData = $response->getData();


        $employeesList = $responseData['data'];
        // SHOW own employee - disabled for now due to data security
//        if ( null !== $me && !empty($me->id)){
//            $me = $this->enrichAndCleanObject($query, $me, []);
//            $me = $me->postProcessGetData($me);
//            array_unshift($employeesList, $me);
//            $responseData['total'] = (int)$response->getData()['total'] + 1;
//        }

        if($user->user_level === 'Admin') {
            // move the current admin employee to the top of the list if exists
            foreach ($employeesList as $key => $employee) {
                if ($employee->id === BaseService::getInstance()->getCurrentProfileId()) {
                    unset($employeesList[$key]);
                    array_unshift($employeesList, $employee);
                    break;
                }
            }
        }

        $responseData['data'] = $employeesList;

        $response->setData($responseData);

        return $response;
    }


    public function get(User $user, $parameter)
    {
        if (empty($parameter)) {
            return new IceResponse(IceResponse::ERROR, "Employee not found", 404);
        }

        if ($parameter === 'me') {
            $parameter = BaseService::getInstance()->getCurrentProfileId();
        }

        if ($user->user_level !== 'Admin' && !PermissionManager::manipulationAllowed(
            BaseService::getInstance()->getCurrentProfileId(),
            $this->getModelObject($parameter)
        )
        ) {
            return new IceResponse(IceResponse::ERROR, "Permission denied", 403);
        }

        // https://csvjson.com/json_beautifier


        $mapping = <<<JSON
{
  "nationality": [ "Nationality", "id", "name" ],
  "ethnicity": [ "Ethnicity", "id", "name" ],
  "immigration_status": [ "ImmigrationStatus", "id", "name" ],
  "employment_status": [ "EmploymentStatus", "id", "name" ],
  "job_title": [ "JobTitle", "id", "name" ],
  "pay_grade": [ "PayGrade", "id", "name" ],
  "country": [ "Country", "code", "name" ],
  "province": [ "Province", "id", "name" ],
  "department": [ "CompanyStructure", "id", "title" ],
  "supervisor": [ "Employee", "id", "first_name+last_name" ],
  "indirect_supervisors": [ "Employee", "id", "first_name+last_name" ],
  "approver1": [ "Employee", "id", "first_name+last_name" ],
  "approver2": [ "Employee", "id", "first_name+last_name" ],
  "approver3": [ "Employee", "id", "first_name+last_name" ]
}
JSON;

        $emp = BaseService::getInstance()->getElement(
            self::ELEMENT_NAME,
            $parameter,
            null,
            true
        );

        $emp = $this->enrichElement($emp, json_decode($mapping, true));

        // Add supervisor image if supervisor exists
        if (!empty($emp->supervisor) && isset($emp->supervisor['id'])) {
            $supervisor = new Employee();
            $supervisor->Load("id = ?", [$emp->supervisor['id']]);
			$supervisor = FileService::getInstance()->updateSmallProfileImage($supervisor);
            if (!empty($supervisor->id)) {
                $emp->supervisor['image'] = $supervisor->image;
            }
        }

        //Get User for the employee
        $user = new User();
        $user->Load('employee = ?', [$emp->id]);

        $emp->can_login = 0;
        if (!empty($user->id)) {
            $emp->can_login = 1;
            $emp->user_name = $user->username;
            $emp->user_email = $user->email;
            $emp->user_level = $user->user_level;
        }

        if (!empty($emp)) {
            $emp = $this->cleanObject($emp);
            $emp = $this->removeNullFields($emp);
            return new IceResponse(IceResponse::SUCCESS, $emp);
        }
        return new IceResponse(IceResponse::ERROR, "Employee not found", 404);
    }

    public function post(User $user)
    {
        if ($user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, "Permission denied", 403);
        }
        $body = $this->getRequestBody();
        $relationCheck = $this->validateEmployeeRelations($body, null);
        if ($relationCheck->getStatus() !== IceResponse::SUCCESS) {
            return new IceResponse(IceResponse::ERROR, $relationCheck->getData(), 400);
        }
        $response = BaseService::getInstance()->addElement(self::ELEMENT_NAME, $body);
        if ($response->getStatus() === IceResponse::SUCCESS) {
            $response = $this->get($user, $response->getData()->id);
            $response->setCode(201);
            return $response;
        }

        return new IceResponse(IceResponse::ERROR, $response->getData(), 400);
    }

    public function put(User $user, $parameter)
    {

        if ($user->user_level !== 'Admin'
            && !PermissionManager::manipulationAllowed(
                BaseService::getInstance()->getCurrentProfileId(),
                $this->getModelObject($parameter)
            )
        ) {
            return new IceResponse(IceResponse::ERROR, "Permission denied", 403);
        }

        $body = $this->getRequestBody();
        $body['id'] = $parameter;
        // A non-admin editing their own record may not change their own Manager
        // or indirect Managers.
        $selfCheck = $this->assertSelfManagersUnchanged($user, $parameter, $body);
        if ($selfCheck->getStatus() !== IceResponse::SUCCESS) {
            return new IceResponse(IceResponse::ERROR, $selfCheck->getData(), 403);
        }
        $relationCheck = $this->validateEmployeeRelations($body, $parameter);
        if ($relationCheck->getStatus() !== IceResponse::SUCCESS) {
            return new IceResponse(IceResponse::ERROR, $relationCheck->getData(), 400);
        }
        $response = BaseService::getInstance()->addElement(self::ELEMENT_NAME, $body);
        if ($response->getStatus() === IceResponse::SUCCESS) {
            return $this->get($user, $response->getData()->id);
        }

        return new IceResponse(IceResponse::ERROR, 'Error modifying employee', 400);
    }

    public function delete(User $user, $parameter)
    {
        if ($user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, "Permission denied", 403);
        }

        $response = BaseService::getInstance()->deleteElement(
            self::ELEMENT_NAME,
            $parameter
        );
        if ($response->getStatus() === IceResponse::SUCCESS) {
            return new IceResponse(IceResponse::SUCCESS, ['id' => $parameter], 200);
        }
        return new IceResponse(IceResponse::ERROR, $response->getData(), 400);
    }

    public function getEmployeeStatusMessage(User $user, $parameter)
    {
        // Ownership gate. $parameter is the employee id straight from the URL; without
        // this any authenticated employee could read a colleague's records by changing it
        // (finding 2.9 / gamonoid/icehrm#375). checkBasicPermissions allows Admin, a Manager
        // over their own subordinates, and an Employee only for themselves - the same guard
        // already used by EmployeeRestEndPoint::setEmployeeStatusMessage and the attendance
        // endpoints.
        $permissionResponse = $this->checkBasicPermissions($user, $parameter);
        if ($permissionResponse->getStatus() !== IceResponse::SUCCESS) {
            return $permissionResponse;
        }

        $date = CalendarTools::getServerDate();

        $employeeId = (int)$parameter;

        $employeeState = new EmployeeStatus();
        $employeeState->Load('employee = ? and status_date = ?', [ $employeeId, $date]);

        $data = $this->cleanObject($employeeState);
        unset($data->objectName);
        unset($data->id);
        unset($data->status_date);

        return new IceResponse(IceResponse::SUCCESS, $data, 200);
    }

    public function setEmployeeStatusMessage(User $user, $parameter)
    {
        $body = $this->getRequestBody();

        $employeeId = (int)$parameter;

        $permissionResponse = $this->checkBasicPermissions($user, $employeeId);
        if ($permissionResponse->getStatus() !== IceResponse::SUCCESS) {
            return $permissionResponse;
        }

        $date = CalendarTools::getServerDate();

        $employeeState = new EmployeeStatus();
        $employeeState->Load('employee = ? and status_date = ?', [ $employeeId, $date]);

        $employeeState->employee = $employeeId;
        $employeeState->status = $body['status'];
        $employeeState->feeling = $body['feeling'];
        $employeeState->message = $body['message'];
        $employeeState->status_date = $date;

        $employeeState->Save();

        $data = $this->cleanObject($employeeState);
        unset($data->objectName);
        unset($data->id);

        return new IceResponse(IceResponse::SUCCESS, $data, 200);
    }

	/**
	 * Create a user account for an employee (used by the "Create a User" form on
	 * the employee profile). Token-authenticated REST — no legacy CSRF token.
	 *
	 * @param User  $user      the acting (authenticated) admin
	 * @param mixed $parameter the employee id from the route (employees/{id}/create-user)
	 */
	public function createUserForEmployee(User $user, $parameter) {
		if ($user->user_level !== 'Admin') {
			return new IceResponse(IceResponse::ERROR, 'Only an admin can add a user', 403);
		}

		$employeeId = is_array($parameter) ? ($parameter[0] ?? null) : $parameter;
		$body = $this->getRequestBody();
		if (empty($body) || !is_array($body)) {
			return new IceResponse(IceResponse::ERROR, 'No data provided', 400);
		}

		$email = isset($body['email']) ? trim($body['email']) : '';
		$username = isset($body['username']) ? trim($body['username']) : '';
		$userLevel = isset($body['user_level']) ? $body['user_level'] : 'Employee';

		if (empty($username) || empty($email)) {
			return new IceResponse(IceResponse::ERROR, 'Username and email are required');
		}
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return new IceResponse(IceResponse::ERROR, 'Invalid email address');
		}

		// Admin / Restricted Admin may be standalone; every other level needs an employee.
		if ($userLevel !== 'Admin' && $userLevel !== 'Restricted Admin' && empty($employeeId)) {
			return new IceResponse(IceResponse::ERROR, 'You should assign an employee to this user');
		}

		if (!empty($employeeId)) {
			$employee = new Employee();
			$employee->Load('id = ?', array($employeeId));
			if (empty($employee->id)) {
				return new IceResponse(IceResponse::ERROR, 'Employee not found', 404);
			}
			$existingForEmployee = new User();
			$existingForEmployee->Load('employee = ?', array($employeeId));
			if (!empty($existingForEmployee->id)) {
				return new IceResponse(IceResponse::ERROR, 'A user already exists for this employee');
			}
		}

		$dup = new User();
		$dup->Load('email = ?', array($email));
		if ($dup->email == $email) {
			return new IceResponse(IceResponse::ERROR, 'User with same email already exists');
		}
		$dup = new User();
		$dup->Load('username = ?', array($username));
		if ($dup->username == $username) {
			return new IceResponse(IceResponse::ERROR, 'User with same username already exists');
		}

		$password = $this->generateRandomPassword(8);

		$newUser = new User();
		$newUser->email = $email;
		$newUser->username = $username;
		$newUser->password = \Classes\PasswordManager::createPasswordHash($password);
		$newUser->employee = empty($employeeId) ? null : $employeeId;
		$newUser->user_level = $userLevel;
		$newUser->user_roles = isset($body['user_roles']) ? $body['user_roles'] : null;
		$newUser->lang = isset($body['lang']) ? $body['lang'] : null;
		$newUser->default_module = isset($body['default_module']) ? $body['default_module'] : null;
		$newUser->last_login = date('Y-m-d H:i:s');
		$newUser->last_update = date('Y-m-d H:i:s');
		$newUser->created = date('Y-m-d H:i:s');

		if (!$newUser->Save()) {
			LogManager::getInstance()->error('Create user error: ' . $newUser->ErrorMsg());
			return new IceResponse(IceResponse::ERROR, 'Error occurred while saving the user');
		}

		$employeeObj = null;
		if (!empty($newUser->employee)) {
			$employeeObj = BaseService::getInstance()->getElement('Employee', $newUser->employee, null, true);
		}

		// Email the welcome/temporary-password message (best effort).
		$mailResponse = 'none';
		$emailSender = BaseService::getInstance()->getEmailSender();
		if (!empty($emailSender)) {
			try {
				// UsersEmailSender only needs a SubActionManager for getEmailTemplate();
				// give it a UsersActionManager wired to BaseService.
				$actionManager = new \Users\Admin\Api\UsersActionManager();
				$actionManager->setBaseService(BaseService::getInstance());
				$usersEmailSender = new \Users\Admin\Api\UsersEmailSender($emailSender, $actionManager);
				$sent = $usersEmailSender->sendWelcomeUserEmail($newUser, $password, $employeeObj);
				$mailResponse = (false !== $sent) ? 'sent' : 'not_sent';
			} catch (\Exception $e) {
				$mailResponse = 'not_sent';
			}
		}

		$newUser->password = '';
		$newUser = BaseService::getInstance()->cleanUpAdoDB($newUser);

		return new IceResponse(IceResponse::SUCCESS, array(
			'user' => $newUser,
			'email_status' => $mailResponse,
		));
	}

	private function generateRandomPassword($length = 8) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$max = strlen($characters) - 1;
		$str = '';
		for ($i = 0; $i < $length; $i++) {
			$str .= $characters[random_int(0, $max)];
		}
		return $str;
	}

	/**
	 * Whether the current user may view/manage another employee's approval chain.
	 * Admin, or anyone with access to all employee data.
	 */
	private function canManageApprovers($user)
	{
		return !empty($user)
			&& ($user->user_level === 'Admin'
				|| EmployeeAccess::hasAccessToAllEmployeeData());
	}

	/**
	 * Normalise a stored approver/supervisor id into a display object the
	 * Approvals tab can render: {id, name, image}. Returns null for an empty
	 * value. A dangling id (points to a deleted employee) is returned with
	 * missing=true so the UI can surface it and allow cleanup.
	 *
	 * @param mixed $id
	 * @return array|null
	 */
	private function resolveApprover($id)
	{
		if ($id === null || $id === '' || (string) $id === '0') {
			return null;
		}
		$empId = intval($id);
		$emp = new Employee();
		$emp->Load('id = ?', array($empId));
		if (empty($emp->id)) {
			return array(
				'id' => $empId,
				'name' => 'Unknown (#' . $empId . ')',
				'image' => null,
				'missing' => true,
			);
		}
		$emp = FileService::getInstance()->updateSmallProfileImage($emp);
		$name = trim($emp->first_name . ' ' . $emp->last_name);
		return array(
			'id' => intval($emp->id),
			'name' => ($name !== '' ? $name : ('#' . intval($emp->id))),
			'image' => isset($emp->image) ? $emp->image : null,
		);
	}

	/**
	 * GET employees/{id}/approvers — the full approval chain for an employee,
	 * with each level resolved to {id, name, image}. The Manager (supervisor)
	 * is the read-only initial approver; approver1/2/3 form the rest.
	 */
	public function getApprovers(User $user, $parameter)
	{
		$id = is_array($parameter) ? $parameter[0] : $parameter;
		if (!$this->canManageApprovers($user)) {
			return new IceResponse(IceResponse::ERROR, 'Not authorized to view approvers.', 403);
		}
		$employee = new Employee();
		$employee->Load('id = ?', array($id));
		if (empty($employee->id)) {
			return new IceResponse(IceResponse::ERROR, 'Employee not found.', 404);
		}
		return new IceResponse(IceResponse::SUCCESS, array(
			'employee' => intval($employee->id),
			'manager' => $this->resolveApprover($employee->supervisor),
			'approver1' => $this->resolveApprover($employee->approver1),
			'approver2' => $this->resolveApprover($employee->approver2),
			'approver3' => $this->resolveApprover($employee->approver3),
		));
	}

	/**
	 * POST employees/{id}/approvers — set the multi-level approvers
	 * (approver1/2/3). Any subset of fields may be supplied; omitted fields
	 * keep their current value. Empty / "0" clears a level. Validated against
	 * the self / manager / duplicate rules before saving. Returns the freshly
	 * resolved chain (same shape as getApprovers).
	 *
	 * Note: gaps (e.g. approver2 set while approver1 is empty) are intentionally
	 * NOT rejected here — the UI governs gap creation, and allowing gaps is what
	 * lets the tab clean up orphaned legacy approvers.
	 */
	public function saveApprovers(User $user, $parameter)
	{
		$id = is_array($parameter) ? $parameter[0] : $parameter;
		if (!$this->canManageApprovers($user)) {
			return new IceResponse(IceResponse::ERROR, 'Not authorized to set approvers.', 403);
		}
		$employee = new Employee();
		$employee->Load('id = ?', array($id));
		if (empty($employee->id)) {
			return new IceResponse(IceResponse::ERROR, 'Employee not found.', 404);
		}

		// A non-admin may not set the approvers on their own record, even if they
		// otherwise have access to all employee data (self-approval risk).
		if ($this->isRestrictedFromSelfChain($user, $employee->id)) {
			return new IceResponse(IceResponse::ERROR, 'You are not allowed to set your own approvers.', 403);
		}

		$body = $this->getRequestBody();
		$incoming = array();
		foreach (array('approver1', 'approver2', 'approver3') as $field) {
			if (is_array($body) && array_key_exists($field, $body)) {
				$val = $body[$field];
				$incoming[$field] = (empty($val) || (string) $val === '0') ? null : intval($val);
			} else {
				// Field not supplied — keep the existing value.
				$current = $employee->$field;
				$incoming[$field] = ($current === null || $current === '' || (string) $current === '0')
					? null : intval($current);
			}
		}

		$validation = $this->validateApprovers($employee, $incoming);
		if ($validation->getStatus() !== IceResponse::SUCCESS) {
			return $validation;
		}

		foreach ($incoming as $field => $val) {
			$employee->$field = $val;
		}
		$employee->Save();

		return $this->getApprovers($user, $id);
	}

	/**
	 * Validate a proposed approver1/2/3 set for an employee:
	 *  - an employee cannot be their own approver,
	 *  - the Manager (initial approver) cannot be repeated as an approver,
	 *  - the same employee cannot appear more than once in the chain.
	 *
	 * @param Employee $employee
	 * @param array    $incoming  ['approver1'=>int|null, 'approver2'=>..., 'approver3'=>...]
	 * @return IceResponse SUCCESS if valid, ERROR (with message) otherwise.
	 */
	private function validateApprovers($employee, $incoming)
	{
		$selfId = intval($employee->id);
		$managerId = ($employee->supervisor === null || $employee->supervisor === ''
			|| (string) $employee->supervisor === '0') ? null : intval($employee->supervisor);

		$seen = array();
		foreach (array('approver1', 'approver2', 'approver3') as $field) {
			$val = $incoming[$field];
			if ($val === null) {
				continue;
			}
			if ($val === $selfId) {
				return new IceResponse(IceResponse::ERROR, 'An employee cannot be their own approver.');
			}
			if ($managerId !== null && $val === $managerId) {
				return new IceResponse(
					IceResponse::ERROR,
					'The Manager is already the initial approver and cannot be added again.'
				);
			}
			if (in_array($val, $seen, true)) {
				return new IceResponse(
					IceResponse::ERROR,
					'The same employee cannot appear more than once in the approval chain.'
				);
			}
			$seen[] = $val;
		}
		return new IceResponse(IceResponse::SUCCESS, 'ok');
	}

	/**
	 * Parse an indirect_supervisors value (JSON array string, plain array, or
	 * comma-separated string) into an array of int ids.
	 *
	 * @param mixed $value
	 * @return int[]
	 */
	private function parseIdList($value)
	{
		if (empty($value)) {
			return array();
		}
		$list = $value;
		if (is_string($value)) {
			$decoded = json_decode($value, true);
			$list = is_array($decoded) ? $decoded : explode(',', $value);
		}
		if (!is_array($list)) {
			return array();
		}
		$ids = array();
		foreach ($list as $item) {
			if ($item === null || $item === '' || (string) $item === '0') {
				continue;
			}
			$ids[] = intval($item);
		}
		return $ids;
	}

	/**
	 * Validate the manager / indirect-manager relations when creating or editing
	 * an employee:
	 *  - an employee cannot be their own Manager,
	 *  - an employee cannot be their own indirect Manager,
	 *  - the Manager cannot be someone who is already an approver of this
	 *    employee (the Manager is the initial approver — no duplicates).
	 *
	 * @param array    $body    the incoming employee payload
	 * @param int|null $selfId  the employee id being edited (null on create)
	 * @return IceResponse SUCCESS if valid, ERROR (with message) otherwise.
	 */
	private function validateEmployeeRelations($body, $selfId)
	{
		$selfId = ($selfId === null || $selfId === '' || (string) $selfId === '0') ? null : intval($selfId);

		$supervisor = null;
		if (isset($body['supervisor']) && $body['supervisor'] !== ''
			&& (string) $body['supervisor'] !== '0') {
			$supervisor = intval($body['supervisor']);
		}

		if ($selfId !== null && $supervisor !== null && $supervisor === $selfId) {
			return new IceResponse(IceResponse::ERROR, 'An employee cannot be their own Manager.');
		}

		if ($selfId !== null && array_key_exists('indirect_supervisors', $body)) {
			$indirect = $this->parseIdList($body['indirect_supervisors']);
			if (in_array($selfId, $indirect, true)) {
				return new IceResponse(IceResponse::ERROR, 'An employee cannot be their own indirect Manager.');
			}
		}

		if ($supervisor !== null && $selfId !== null) {
			$existing = new Employee();
			$existing->Load('id = ?', array($selfId));
			if (!empty($existing->id)) {
				$approvers = array();
				foreach (array('approver1', 'approver2', 'approver3') as $field) {
					if ($existing->$field !== null && $existing->$field !== ''
						&& (string) $existing->$field !== '0') {
						$approvers[] = intval($existing->$field);
					}
				}
				if (in_array($supervisor, $approvers, true)) {
					return new IceResponse(
						IceResponse::ERROR,
						'This employee is already an approver and cannot also be set as the Manager.'
					);
				}
			}
		}

		return new IceResponse(IceResponse::SUCCESS, 'ok');
	}

	/**
	 * Whether $user is barred from editing the approval chain (Manager, indirect
	 * Managers, approvers) on the employee record $employeeId because it is their
	 * OWN record and they are not a full/restricted admin.
	 *
	 * Employees and Managers must never be able to appoint their own Manager or
	 * approvers (a privilege-escalation / self-approval risk), even if they
	 * otherwise have access to the record. "Own record" is derived from the
	 * authenticated user's employee id — NOT getCurrentProfileId(), which for an
	 * admin can be the profile they are currently viewing.
	 *
	 * @param User  $user
	 * @param mixed $employeeId
	 * @return bool
	 */
	private function isRestrictedFromSelfChain($user, $employeeId)
	{
		if (empty($user) || empty($employeeId)) {
			return false;
		}
		if ($user->user_level === 'Admin' || $user->user_level === 'Restricted Admin') {
			return false;
		}
		$ownEmployeeId = isset($user->employee) ? $user->employee : null;
		return !empty($ownEmployeeId) && intval($ownEmployeeId) === intval($employeeId);
	}

	/**
	 * For a self-editing non-admin, reject any attempt to change their own
	 * Manager (supervisor) or indirect Managers. Fields left unchanged (the
	 * form always re-submits them) are allowed through.
	 *
	 * @param User  $user
	 * @param mixed $employeeId
	 * @param array $body
	 * @return IceResponse SUCCESS if allowed, ERROR (with message) otherwise.
	 */
	private function assertSelfManagersUnchanged($user, $employeeId, $body)
	{
		if (!$this->isRestrictedFromSelfChain($user, $employeeId)) {
			return new IceResponse(IceResponse::SUCCESS, 'ok');
		}
		$existing = new Employee();
		$existing->Load('id = ?', array($employeeId));
		if (empty($existing->id)) {
			return new IceResponse(IceResponse::SUCCESS, 'ok');
		}

		if (is_array($body) && array_key_exists('supervisor', $body)) {
			$incoming = ($body['supervisor'] === '' || $body['supervisor'] === null
				|| (string) $body['supervisor'] === '0') ? null : intval($body['supervisor']);
			$current = ($existing->supervisor === '' || $existing->supervisor === null
				|| (string) $existing->supervisor === '0') ? null : intval($existing->supervisor);
			if ($incoming !== $current) {
				return new IceResponse(IceResponse::ERROR, 'You are not allowed to change your own Manager.');
			}
		}

		if (is_array($body) && array_key_exists('indirect_supervisors', $body)) {
			$incoming = $this->parseIdList($body['indirect_supervisors']);
			$current = $this->parseIdList($existing->indirect_supervisors);
			sort($incoming);
			sort($current);
			if ($incoming !== $current) {
				return new IceResponse(
					IceResponse::ERROR,
					'You are not allowed to change your own indirect Managers.'
				);
			}
		}

		return new IceResponse(IceResponse::SUCCESS, 'ok');
	}
}
