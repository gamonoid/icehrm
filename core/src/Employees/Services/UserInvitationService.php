<?php

namespace Employees\Services;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\SubActionManager;
use Employees\Common\Model\Employee;
use Metadata\Common\Model\SupportedLanguage;
use Model\UserInvitation;
use Users\Admin\Api\UsersEmailSender;
use Users\Common\Model\User;
use Utils\StringUtils;

class UserInvitationService
{
	const PENDING = 0;
	const INVITATION_EMAIL_SENT = 1;
	const INVITATION_EMAIL_ERROR = 2;
	const PROCESSING = 3;
	const EMPLOYEE_CREATED = 4;
	const EMPLOYEE_USER_CREATED = 5;
	const NO_WELCOME_EMAIL_SENT = 6;

	public function getInvitationByHash($hash) {
		$userInvitation = new UserInvitation();
		$userInvitation->Load('password = ? and invitation_status <= ?', [$hash, self::INVITATION_EMAIL_SENT]);
		if (empty($userInvitation->password) || $userInvitation->password !== $hash) {
			return null;
		}

		return $userInvitation;
	}

	public function setInvitationStatus(UserInvitation $userInvitation, $status) {
		$userInvitation->invitation_status = $status;
		$userInvitation->Save();

		return $userInvitation;
	}

	public function processUserInvitation(UserInvitation $userInvitation) {
		if ($userInvitation->invitation_status > 1) {
			return new IceResponse(IceResponse::ERROR, 'Invitation is not in a valid state. Please contact your HR department to request a new invitation.');
		}

		if (!$this->isUserEmailAvailable($userInvitation->email)) {
			return new IceResponse(IceResponse::ERROR, 'A user with the same email address exists. Please contact your HR department to request a new invitation.');
		}

		$username = $this->getAvailableUserName($userInvitation->email);
		if (false === $username) {
			return new IceResponse(IceResponse::ERROR, 'Error processing your request. Please try again later.');
		}

		$userInvitation = $this->setInvitationStatus($userInvitation, self::PROCESSING);

		$resp = $this->createEmployee($userInvitation);

		if (IceResponse::ERROR === $resp->getStatus()) {
			return $resp;
		}

		$employee = $resp->getData();

		$userInvitation->created_employee_id = $employee->id;
		$ok = $userInvitation->Save();

		$userInvitation = $this->setInvitationStatus($userInvitation, self::EMPLOYEE_CREATED);

		if (!$ok) {
			return new IceResponse(IceResponse::ERROR, 'Error processing your request. Please try again later.');
		}
		$resp = $this->createUser($userInvitation, $username, $employee);

		if (IceResponse::SUCCESS !== $resp->getStatus()) {
			return $resp;
		}

		[$user, $mailStatus] = $resp->getData();

		$userInvitation->created_user_id = $user->id;
		$userInvitation->email_status = $mailStatus === 'sent' ? 1 : 0;
		$userInvitation->Save();

		$this->setInvitationStatus($userInvitation, self::EMPLOYEE_USER_CREATED);

		if ($mailStatus !== 'sent') {
			$this->setInvitationStatus($userInvitation, self::NO_WELCOME_EMAIL_SENT);
			return new IceResponse(IceResponse::ERROR, 'There was an error sending your login details. Please contact your HR department to receive login details.');
		}

		return new IceResponse(IceResponse::SUCCESS);
	}

	public function createUser(UserInvitation $userInvitation, $username, $employee) {
		$password = StringUtils::randomString(9);
		$user = new User();
		$user->username = $username;
		$user->email = $userInvitation->email;
		$user->password = md5($password);
		$user->employee = $employee->id;
		$user->user_level = $userInvitation->user_level;

		$supportedLang = new SupportedLanguage();
		$supportedLang->Load('name = ?', ['en']);
		$user->lang = $supportedLang->id;
		$user->last_update = date("Y-m-d H:i:s");
		$user->created = date("Y-m-d H:i:s");

		$ok = $user->Save();

		if (!$ok) {
			return new IceResponse(IceResponse::ERROR, 'Error saving the user. Please contact your HR department to request a new invitation.');
		}

		$mailResponse = 'none';
		$emailSender = BaseService::getInstance()->getEmailSender();
		if (!empty($emailSender)) {
			$usersEmailSender = new UsersEmailSender($emailSender, new SubActionManager());
			$mailResponse = $usersEmailSender->sendWelcomeUserEmail($user, $password, $employee);
			$mailResponse = false !== $mailResponse ? 'sent' : 'not_sent';
		}

		return new IceResponse(IceResponse::SUCCESS, [$user, $mailResponse]);
	}

	public function createEmployee(UserInvitation $userInvitation) {
		$employee = new Employee();
		$employee->employee_id = $userInvitation->employee_id;
		if ($this->hasDuplicateEmployeeId($userInvitation->employee_id)) {
			return new IceResponse(IceResponse::ERROR, 'An employee with the same employee Id exists. Please contact your HR department to request a new invitation.');
		}

		$employee->first_name = $userInvitation->first_name;
		$employee->last_name = $userInvitation->last_name;
		$employee->last_name = $userInvitation->last_name;
		$employee->country = $userInvitation->country;

		if (!empty($userInvitation->timezone)) {
			$employee->timezone = $userInvitation->timezone;
		}

		if (!empty($userInvitation->department)) {
			$employee->department = $userInvitation->department;
		}

		if (!empty($userInvitation->supervisor)) {
			$employee->supervisor = $userInvitation->supervisor;
		}

		if (!empty($userInvitation->joined_date)) {
			$employee->joined_date = $userInvitation->joined_date;
		}

		if (!empty($userInvitation->job_title)) {
			$employee->job_title = $userInvitation->job_title;
		}

		if (!empty($userInvitation->employment_status)) {
			$employee->employment_status = $userInvitation->employment_status;
		}

		if (!empty($userInvitation->pay_grade)) {
			$employee->pay_grade = $userInvitation->pay_grade;
		}

		if (!empty($userInvitation->department)) {
			$employee->department = $userInvitation->department;
		}

		if (!empty($userInvitation->department)) {
			$employee->department = $userInvitation->department;
		}
		$employee->status = 'Active';
		$ok = $employee->Save();

		if (!$ok) {
			return new IceResponse(IceResponse::ERROR, 'Error saving employee data. Please contact your HR department to request a new invitation.');;
		}

		return new IceResponse(IceResponse::SUCCESS, $employee);
	}

	protected function hasDuplicateEmployeeId($employeeId) {
		$emp = new Employee();
		$emp->load('employee_id = ?', [$employeeId]);

		return $employeeId === $emp->employee_id;
	}

	public function createEmployeeStub($firstName, $lastName, $employeeId = null) {
		$employee = new Employee();
		$employee->first_name = $firstName;
		$employee->last_name = $lastName;
		$employee->employee_id = $employeeId ? $employeeId : StringUtils::randomString(8);
		$employee->Save();
	}

	public function isUserEmailAvailable($email){

		if (!StringUtils::validateEmail($email)) {
			return false;
		}

		$user = new User();
		$user->Load("email = ?", array($email));
		if ($user->email == $email) {
			return false;
		}

		return true;
	}

	public function getAvailableUserName($email) {
		$username = explode('@', $email)[0];
		$username = str_replace(['+',' '], '', $username);
		$user = new User();
		$user->Load("username = ?", array($username));
		$tries = 2;
		while ($user->username === $username) {
			$username .= StringUtils::randomString($tries);
			$user = new User();
			$user->Load("username = ?", array($username));
			$tries++;
			if ($tries > 5) {
				return false;
			}
		}

		return $username;
	}

	public static function getInvitationStatusLabel($status) {
		$map = [
			self::PENDING => 'Pending',
			self::INVITATION_EMAIL_SENT => 'Invited',
			self::INVITATION_EMAIL_ERROR => 'Invitation Failed',
			self::PROCESSING => 'Processing',
			self::EMPLOYEE_CREATED => 'Employee Created',
			self::EMPLOYEE_USER_CREATED => 'Employee and User Created',
			self::NO_WELCOME_EMAIL_SENT=> 'Welcome Email Failed',
		];

		return $map[$status];
	}
}
