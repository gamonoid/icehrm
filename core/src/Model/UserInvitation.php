<?php

namespace Model;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Classes\PasswordManager;
use Classes\SubActionManager;
use Employees\Common\Model\Employee;
use Employees\Services\UserInvitationService;
use Users\Admin\Api\UsersEmailSender;
use Users\Common\Model\User;
use Utils\StringUtils;

class UserInvitation extends BaseModel
{
	public $table = 'UserInvitations';

	/** @var string|null plain token, set only in the request that issued it */
	public $plainToken = null;

	/**
	 * Surfaced under the Users admin module (System > Users > User Invitations), so
	 * declaring it lets a role added to that module grant access to this model.
	 */
	public function getModuleAccess()
	{
		return [
			new ModuleAccess('users', 'admin'),
			new ModuleAccess('employees', 'admin'),
		];
	}

	public function getAdminAccess()
	{
		return array("get","element","add","save","delete");
	}
	public function getManagerAccess()
	{
		return array();
	}

	public function getUserAccess()
	{
		return array();
	}

	public function getUserOnlyMeAccess()
	{
		return array();
	}

	public function executePreSaveActions($obj)
	{

		// The employee number is NOT collected at invite time. Store a throwaway random
		// 4-digit placeholder so the (NOT NULL) column is satisfied; the real, unique
		// employee number is generated when the invitation is accepted and the employee
		// profile is created (UserInvitationService::createEmployee) — even if
		// "Company: Generate Employee Numbers" is turned off. Because the number is no
		// longer user-supplied, the previous employee-id duplicate checks are dropped.
		if (empty($obj->employee_id)) {
			$obj->employee_id = (string) random_int(1000, 9999);
		}

		// Make sure email is not duplicated by an invitation that can still be accepted.
		$ui = new UserInvitation();
		$ui->Load('email = ? and invitation_status <= 2 and token_hash is not null and expires_at > ?', [$obj->email, date('Y-m-d H:i:s')]);
		if ($ui->email === $obj->email) {
			return new IceResponse(IceResponse::ERROR, 'There is an active invitation for this email address. Resend it instead.');
		}

		// Make sure there is no user with same email.
		$user = new User();
		$user->Load('email = ?', [$obj->email]);
		if ($user->email === $obj->email) {
			return new IceResponse(IceResponse::ERROR, 'There is an employee with this email address. You are not allowed to send invitations to registered employees.');
		}

		$service = new UserInvitationService();
		$obj->invitation_status = UserInvitationService::PENDING;
		$obj->username = $service->getAvailableUserName($obj->email);
		// A random single-use token; only its hash is stored. The plain token is kept on
		// the object for this request so the email can include the link.
		$obj->plainToken = $service->issueToken($obj);

		return new IceResponse(IceResponse::SUCCESS, $obj);
	}

	public function executePostSaveActions($obj)
	{
		if ($obj->invitation_status !== UserInvitationService::PENDING || empty($obj->plainToken)) {
			return;
		}

		$service = new UserInvitationService();
		$service->sendInvitation($obj, $obj->plainToken);
	}

	/** The plain token issued during this request (null afterwards). */
	public function getPlainToken()
	{
		return isset($this->plainToken) ? $this->plainToken : null;
	}

	private function present($obj)
	{
		$service = new UserInvitationService();
		$state = $service->getTokenState($obj);
		$obj->invitation_status_text = $state === 'expired'
			? 'Expired'
			: UserInvitationService::getInvitationStatusLabel($obj->invitation_status);
		$obj->invitation_state = $state;
		// Never send the stored hashes to the browser.
		unset($obj->password, $obj->token_hash);
		return $obj;
	}

	public function postProcessGetData($obj)
	{
		return $this->present($obj);
	}

	public function postProcessGetElement($obj)
	{
		return $this->present($obj);
	}
}
