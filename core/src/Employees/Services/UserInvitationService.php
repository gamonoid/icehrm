<?php

namespace Employees\Services;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\PasswordManager;
use Classes\SettingsManager;
use Utils\LogManager;
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

	const REVOKED = 7;

	/** Listeners called after an invitation is accepted: function (UserInvitation, Employee, User). */
	private static $acceptListeners = [];

	/** Providers of extra text for the accept page: function (UserInvitation) => array|null. */
	private static $acceptPageProviders = [];

	public static function onAccepted(callable $listener) {
		self::$acceptListeners[] = $listener;
	}

	public static function addAcceptPageProvider(callable $provider) {
		self::$acceptPageProviders[] = $provider;
	}

	/**
	 * Extra content for the accept page (e.g. an onboarding welcome message and start
	 * date). Each provider may return ['title' => ..., 'message' => ..., 'details' => [label => value]].
	 */
	public function getAcceptPageExtras(UserInvitation $invitation) {
		$extras = [];
		foreach (self::$acceptPageProviders as $provider) {
			try {
				$extra = call_user_func($provider, $invitation);
				if (is_array($extra)) {
					$extras = array_merge($extras, $extra);
				}
			} catch (\Exception $e) {
				LogManager::getInstance()->error('Invitation accept page provider failed: ' . $e->getMessage());
			}
		}
		return $extras;
	}

	public static function hashToken($token) {
		return hash('sha256', (string) $token);
	}

	public static function getValidityDays() {
		$days = intval(SettingsManager::getInstance()->getSetting('System: Invitation Link Validity (Days)'));
		return $days > 0 ? min($days, 365) : 14;
	}

	/**
	 * Give the invitation a new single-use token. Only its hash is stored; the plain
	 * token is returned so it can be put in the email (and shown to the admin once).
	 * Any earlier link stops working.
	 */
	public function issueToken(UserInvitation $invitation) {
		$token = bin2hex(random_bytes(32));
		$invitation->token_hash = self::hashToken($token);
		// Kept only so the NOT NULL column is filled; it is not a credential any more.
		$invitation->password = self::hashToken(random_bytes(16));
		$invitation->expires_at = date('Y-m-d H:i:s', time() + self::getValidityDays() * 86400);
		return $token;
	}

	public static function getAcceptUrl($token) {
		return CLIENT_BASE_URL . 'accept-invitation.php?token=' . rawurlencode($token);
	}

	/**
	 * Load the invitation a link points to, whatever its state. Links sent before tokens
	 * were introduced carry the old value in `hash`; the migration stored its hash too.
	 */
	public function findByToken($token) {
		if (!is_string($token) || $token === '' || strlen($token) > 128) {
			return null;
		}
		$invitation = new UserInvitation();
		$invitation->Load('token_hash = ?', [self::hashToken($token)]);
		if (empty($invitation->id) || !hash_equals((string) $invitation->token_hash, self::hashToken($token))) {
			return null;
		}
		return $invitation;
	}

	/**
	 * One of: valid, expired, accepted, revoked.
	 */
	public function getTokenState(UserInvitation $invitation) {
		$status = intval($invitation->invitation_status);
		if ($status === self::REVOKED) {
			return 'revoked';
		}
		if (!empty($invitation->accepted_at) || $status > self::INVITATION_EMAIL_ERROR) {
			return 'accepted';
		}
		if (empty($invitation->expires_at) || strtotime($invitation->expires_at) < time()) {
			return 'expired';
		}
		return 'valid';
	}

	public function setInvitationStatus(UserInvitation $userInvitation, $status) {
		$userInvitation->invitation_status = $status;
		$userInvitation->Save();

		return $userInvitation;
	}

	/**
	 * Create and send an invitation from code (used by extensions such as onboarding).
	 * Runs the same checks and emails as an invitation created by an admin.
	 *
	 * @return IceResponse SUCCESS with ['invitation' => UserInvitation, 'url' => accept link]
	 */
	public function createInvitation(array $data, $inviter = null) {
		$invitation = new UserInvitation();
		$fields = ['email', 'first_name', 'last_name', 'user_level', 'timezone', 'department', 'joined_date',
			'job_title', 'employment_status', 'pay_grade', 'supervisor', 'country', 'context', 'context_ref'];
		foreach ($fields as $field) {
			if (isset($data[$field]) && $data[$field] !== '') {
				$invitation->$field = $data[$field];
			}
		}
		if (empty($invitation->user_level)) {
			$invitation->user_level = 'Employee';
		}
		$invitation->email = trim((string) $invitation->email);
		if (!StringUtils::validateEmail($invitation->email)) {
			return new IceResponse(IceResponse::ERROR, 'Enter a valid email address.');
		}
		$pre = $invitation->executePreSaveActions($invitation);
		if ($pre->getStatus() !== IceResponse::SUCCESS) {
			return $pre;
		}
		$invitation = $pre->getData();
		$invitation->created = date('Y-m-d H:i:s');
		$invitation->updated = date('Y-m-d H:i:s');
		if (!$invitation->Save()) {
			return new IceResponse(IceResponse::ERROR, 'The invitation could not be saved.');
		}
		$token = $invitation->getPlainToken();
		$this->sendInvitation($invitation, $token, $inviter);

		return new IceResponse(IceResponse::SUCCESS, ['invitation' => $invitation, 'url' => self::getAcceptUrl($token)]);
	}

	/**
	 * Email the link for this token and record the outcome.
	 */
	public function sendInvitation(UserInvitation $invitation, $token, $inviter = null) {
		if ($inviter === null) {
			$inviter = BaseService::getInstance()->getCurrentUserEmployee();
		}
		$result = false;
		$emailSender = BaseService::getInstance()->getEmailSender();
		if (!empty($emailSender)) {
			$sender = new UsersEmailSender($emailSender, new SubActionManager());
			$result = $sender->sendInviteUserEmail($invitation, $inviter, $token);
		}
		$invitation->last_sent_at = date('Y-m-d H:i:s');
		$invitation->invitation_status = $result ? self::INVITATION_EMAIL_SENT : self::INVITATION_EMAIL_ERROR;
		$invitation->Save();
		return (bool) $result;
	}

	/**
	 * New link (the old one stops working), new expiry, email again.
	 *
	 * @return IceResponse SUCCESS with ['url' => ..., 'sent' => bool]
	 */
	public function resend($id) {
		$invitation = new UserInvitation();
		$invitation->Load('id = ?', [$id]);
		if (empty($invitation->id)) {
			return new IceResponse(IceResponse::ERROR, 'Invitation not found.');
		}
		$state = $this->getTokenState($invitation);
		if ($state === 'accepted') {
			return new IceResponse(IceResponse::ERROR, 'This invitation has already been accepted.');
		}
		if (!$this->isUserEmailAvailable($invitation->email)) {
			return new IceResponse(IceResponse::ERROR, 'A login with this email address already exists.');
		}
		$token = $this->issueToken($invitation);
		$invitation->invitation_status = self::PENDING;
		$invitation->updated = date('Y-m-d H:i:s');
		$invitation->Save();
		$sent = $this->sendInvitation($invitation, $token);

		return new IceResponse(IceResponse::SUCCESS, ['url' => self::getAcceptUrl($token), 'sent' => $sent]);
	}

	public function revoke($id) {
		$invitation = new UserInvitation();
		$invitation->Load('id = ?', [$id]);
		if (empty($invitation->id)) {
			return new IceResponse(IceResponse::ERROR, 'Invitation not found.');
		}
		if ($this->getTokenState($invitation) === 'accepted') {
			return new IceResponse(IceResponse::ERROR, 'This invitation has already been accepted.');
		}
		// The hash stays so the link shows "withdrawn"; the state check refuses it.
		$invitation->invitation_status = self::REVOKED;
		$invitation->updated = date('Y-m-d H:i:s');
		$invitation->Save();
		return new IceResponse(IceResponse::SUCCESS, $invitation->id);
	}

	/**
	 * Accept an invitation: create the employee and their login with the password the
	 * invitee chose. Nothing is created until this is called (the link alone does not).
	 *
	 * @return IceResponse SUCCESS with ['user' => User, 'employee' => Employee]
	 */
	public function acceptInvitation(UserInvitation $userInvitation, $password) {
		if ($this->getTokenState($userInvitation) !== 'valid') {
			return new IceResponse(IceResponse::ERROR, 'This invitation link is no longer valid. Please ask your HR team to send a new one.');
		}

		$strength = PasswordManager::isQualifiedPassword((string) $password);
		if ($strength->getStatus() === IceResponse::ERROR) {
			return $strength;
		}

		if (!$this->isUserEmailAvailable($userInvitation->email)) {
			return new IceResponse(IceResponse::ERROR, 'A login with this email address already exists. Please sign in or contact your HR team.');
		}

		$username = $this->getAvailableUserName($userInvitation->email);
		if (false === $username) {
			return new IceResponse(IceResponse::ERROR, 'Error processing your request. Please try again later.');
		}

		// Claim the invitation first so a double submit cannot create two employees:
		// only the request whose UPDATE still matches the current token hash wins.
		$claim = self::hashToken(random_bytes(16));
		$db = BaseService::getInstance()->getDB();
		$db->Execute(
			'UPDATE UserInvitations SET invitation_status = ?, token_hash = ? WHERE id = ? AND token_hash = ? AND invitation_status <= ?',
			[self::PROCESSING, $claim, $userInvitation->id, $userInvitation->token_hash, self::INVITATION_EMAIL_ERROR]
		);
		$claimed = $db->Execute('SELECT id FROM UserInvitations WHERE id = ? AND token_hash = ?', [$userInvitation->id, $claim]);
		if (empty($claimed)) {
			return new IceResponse(IceResponse::ERROR, 'This invitation is already being processed.');
		}
		$previousHash = $userInvitation->token_hash;
		$userInvitation->invitation_status = self::PROCESSING;
		$userInvitation->token_hash = $claim;

		$resp = $this->createEmployee($userInvitation);
		if (IceResponse::ERROR === $resp->getStatus()) {
			// Release the claim so the link can be used again.
			$userInvitation->token_hash = $previousHash;
			$this->setInvitationStatus($userInvitation, self::INVITATION_EMAIL_SENT);
			return $resp;
		}
		$employee = $resp->getData();
		$userInvitation->created_employee_id = $employee->id;
		$this->setInvitationStatus($userInvitation, self::EMPLOYEE_CREATED);

		$resp = $this->createUser($userInvitation, $username, $employee, $password);
		if (IceResponse::SUCCESS !== $resp->getStatus()) {
			return $resp;
		}
		$user = $resp->getData();

		$userInvitation->created_user_id = $user->id;
		$userInvitation->accepted_at = date('Y-m-d H:i:s');
		// Keep the hash so a reused link says "already used" rather than "not valid".
		$userInvitation->token_hash = $previousHash;
		$this->setInvitationStatus($userInvitation, self::EMPLOYEE_USER_CREATED);

		foreach (self::$acceptListeners as $listener) {
			try {
				call_user_func($listener, $userInvitation, $employee, $user);
			} catch (\Exception $e) {
				LogManager::getInstance()->error('Invitation accept listener failed: ' . $e->getMessage());
			}
		}

		return new IceResponse(IceResponse::SUCCESS, ['user' => $user, 'employee' => $employee]);
	}

	public function createUser(UserInvitation $userInvitation, $username, $employee, $password) {
		$user = new User();
		$user->username = $username;
		$user->email = $userInvitation->email;
		$user->password = PasswordManager::createPasswordHash($password);
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

		return new IceResponse(IceResponse::SUCCESS, $user);
	}

	public function createEmployee(UserInvitation $userInvitation) {
		$employee = new Employee();
		// Always generate a proper, unique employee number on acceptance — regardless of
		// the "Company: Generate Employee Numbers" setting. The invitation only carries a
		// throwaway random placeholder id (the number is not asked for at invite time).
		$employee->employee_id = Employee::generateEmployeeNumber();

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
		// New hires invited through onboarding stay out of the directory, payroll and
		// headcount until their start date (or until HR completes their onboarding).
		$employee->status = $userInvitation->context === 'onboarding' ? 'Onboarding' : 'Active';
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
			self::INVITATION_EMAIL_SENT => 'Sent',
			self::INVITATION_EMAIL_ERROR => 'Email Failed',
			self::PROCESSING => 'Processing',
			self::EMPLOYEE_CREATED => 'Employee Created',
			self::EMPLOYEE_USER_CREATED => 'Accepted',
			self::NO_WELCOME_EMAIL_SENT=> 'Accepted',
			self::REVOKED => 'Revoked',
		];

		return isset($map[$status]) ? $map[$status] : '';
	}
}
