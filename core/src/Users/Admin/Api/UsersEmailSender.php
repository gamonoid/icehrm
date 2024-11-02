<?php
namespace Users\Admin\Api;

use Classes\Email\EmailSender;
use Classes\SettingsManager;
use Model\UserInvitation;
use Users\Common\Model\User;
use Classes\SubActionManager;
use Utils\LogManager;

class UsersEmailSender
{

    protected $emailSender = null;
    protected $subActionManager = null;

    public function __construct(EmailSender $emailSender, SubActionManager $subActionManager)
    {
        $this->emailSender = $emailSender;
        $this->subActionManager = $subActionManager;
    }

    public function sendWelcomeUserEmail(User $user, $password, $employee = null)
    {

        $params = array();
        if (!empty($employee)) {
            $params['name'] = $employee->first_name." ".$employee->last_name;
        } else {
            $params['name'] = $user->username;
        }
        $params['url'] = CLIENT_BASE_URL;
        $params['password'] = $password;
        $params['email'] = $user->email;
        $params['username'] = $user->username;

        $email = $this->subActionManager->getEmailTemplate('welcomeUser.html', APP_BASE_PATH.'admin/users');

        $emailTo = null;
        if (!empty($user)) {
            $emailTo = $user->email;
        }

        if (!empty($emailTo)) {
            if (!empty($this->emailSender)) {
                LogManager::getInstance()->info("[sendWelcomeUserEmail] sending email to $emailTo : ".$email);
                return $this->emailSender->sendEmail("Your IceHrm account is ready", $emailTo, $email, $params);
            }
        } else {
            LogManager::getInstance()->info("[sendWelcomeUserEmail] email is empty");
        }
        
        return false;
    }

	public function sendInviteUserEmail(UserInvitation $userInvitation, $inviter)
	{

		$params = [];
		$params['name'] = $userInvitation->first_name." ".$userInvitation->last_name;
		$companyName = SettingsManager::getInstance()->getSetting('Company: Name');
		$companyName = substr($companyName,0,40);
		if(empty($companyName) || $companyName == "Sample Company Pvt Ltd"){
			$companyName = 'IceHrm';
		}

		if (empty($inviter)) {
			$inviterName = $companyName;
		} else {
			$inviterName = sprintf('%s %s', $inviter->first_name, $inviter->last_name);
		}

		$params['url'] = CLIENT_BASE_URL;
		$params['inviter'] = $inviterName;
		$params['hash'] = $userInvitation->password;

		$email = $this->subActionManager->getEmailTemplate('inviteUser.html', APP_BASE_PATH.'admin/users');

		if (!empty($userInvitation->email)) {
			if (!empty($this->emailSender)) {
				LogManager::getInstance()->info("[sendInviteUserEmail] sending email to $userInvitation->email : ".$email);
				return $this->emailSender->sendEmail(sprintf('%s invites you to IceHrm', $inviterName), $userInvitation->email, $email, $params);
			}
		} else {
			LogManager::getInstance()->info("[sendInviteUserEmail] email is empty");
		}

		return false;
	}
}
