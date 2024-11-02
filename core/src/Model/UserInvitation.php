<?php

namespace Model;

use Classes\BaseService;
use Classes\IceResponse;
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

	public function executePreSaveActions($obj)
	{
		// Make sure email is not duplicated.
		$ui = new UserInvitation();
		$ui->Load('email = ? and invitation_status < 2', [$obj->email]);
		if ($ui->email === $obj->email) {
			return new IceResponse(IceResponse::ERROR, 'There is an active invitation for this email address.');
		}

		// Make sure there is no user with same email.
		$user = new User();
		$user->Load('email = ?', [$obj->email]);
		if ($user->email === $obj->email) {
			return new IceResponse(IceResponse::ERROR, 'There is an employee with this email address. You are not allowed to send invitations to registered employees.');
		}

		// Make sure employee id is not duplicated.
		$ui = new UserInvitation();
		$ui->Load('employee_id = ? and invitation_status < 2', [$obj->email]);
		if ($ui->employee_id === $obj->employee_id) {
			return new IceResponse(IceResponse::ERROR, 'There is an active invitation with same employee ID.');
		}

		// Make sure there is no user with same employee id.
		$emp = new Employee();
		$emp->Load('employee_id = ?', [$obj->employee_id]);
		if ($emp->employee_id === $obj->employee_id) {
			return new IceResponse(IceResponse::ERROR, 'There is an employee with same employee ID. You are not allowed to send invitations to registered employees.');
		}

		$service = new UserInvitationService();
		$obj->invitation_status = UserInvitationService::PENDING;
		$obj->username = $service->getAvailableUserName($obj->email);
		$obj->password = md5(PasswordManager::createPasswordHash(StringUtils::randomString(12))).md5(StringUtils::randomString(10));

		return new IceResponse(IceResponse::SUCCESS, $obj);
	}

	public function executePostSaveActions($obj)
	{
		if ($obj->invitation_status !== UserInvitationService::PENDING) {
			return;
		}

		$userEmailSender = new UsersEmailSender(BaseService::getInstance()->getEmailSender(), new SubActionManager());
		$result = $userEmailSender->sendInviteUserEmail(
			$obj,
			BaseService::getInstance()->getCurrentUserEmployee()
		);

		$userInvitationService = new UserInvitationService();
		if (!$result) {
			$userInvitationService->setInvitationStatus($obj, UserInvitationService::INVITATION_EMAIL_ERROR);
		} else {
			$userInvitationService->setInvitationStatus($obj, UserInvitationService::INVITATION_EMAIL_SENT);
		}
	}

	public function postProcessGetData($obj)
	{
		$obj->invitation_status_text = UserInvitationService::getInvitationStatusLabel($obj->invitation_status);
		return $obj;
	}

	public function postProcessGetElement($obj)
	{
		$obj->invitation_status_text = UserInvitationService::getInvitationStatusLabel($obj->invitation_status);
		return $obj;
	}
}
