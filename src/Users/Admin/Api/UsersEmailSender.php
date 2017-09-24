<?php
namespace Users\Admin\Api;

use Classes\Email\EmailSender;
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

    public function sendWelcomeUserEmail(User $user, string $password, $employee = null)
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

        $email = $this->subActionManager->getEmailTemplate('welcomeUser.html');

        $emailTo = null;
        if (!empty($user)) {
            $emailTo = $user->email;
        }

        if (!empty($emailTo)) {
            if (!empty($this->emailSender)) {
                LogManager::getInstance()->info("[sendWelcomeUserEmail] sending email to $emailTo : ".$email);
                $this->emailSender->sendEmail("Your IceHrm account is ready", $emailTo, $email, $params);
            }
        } else {
            LogManager::getInstance()->info("[sendWelcomeUserEmail] email is empty");
        }
    }
}
