<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:38 AM
 */

namespace Classes\Email;

use Classes\PasswordManager;
use Classes\UIManager;
use Employees\Common\Model\Employee;
use Model\EmailLogEntry;
use Model\IceEmail;
use Users\Common\Model\User;
use Utils\LogManager;

abstract class EmailSender
{
    /* @var \Classes\SettingsManager $settings */
    public $settings = null;
    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    public function sendEmailFromNotification($notification, $delayed = false)
    {
        $toEmail = null;
        $user = new User();
        $user->Load("id = ?", array($notification->toUser));

        if (!empty($user->email)) {
            $name = "User";
            $employee = new Employee();
            $employee->Load("id = ?", array($user->employee));
            if ($employee->id == $user->employee && !empty($employee->id)) {
                $name = $employee->first_name;
            }

            $action = json_decode($notification->action);

            $emailBody = file_get_contents(APP_BASE_PATH.'/templates/email/notificationEmail.html');
            $emailBody = str_replace("#_user_#", $name, $emailBody);
            $emailBody = str_replace("#_message_#", $notification->message, $emailBody);
            if ($action->type == "url") {
                $emailBody = str_replace("#_url_#", CLIENT_BASE_URL."?".$action->url, $emailBody);
            }

            if ($delayed) {
                $this->sendEmailDelayed(
                    'IceHrm Notification from '.$notification->type,
                    $user->email,
                    $emailBody,
                    array(),
                    array(),
                    array()
                );
            } else {
                $this->sendEmail(
                    'IceHrm Notification from '.$notification->type,
                    $user->email,
                    $emailBody,
                    array(),
                    array(),
                    array()
                );
            }
        }
    }

    public function sendEmailDelayed($subject, $toEmail, $template, $params, $ccList = array(), $bccList = array())
    {
        $email = new IceEmail();
        $email->subject = $subject;
        $email->toEmail = $toEmail;
        $email->template = $template;
        $email->params = json_encode($params);
        $email->cclist = json_encode($ccList);
        $email->bcclist = json_encode($bccList);
        $email->status = 'Pending';
        $email->created = date('Y-m-d H:i:s');
        $email->updated = date('Y-m-d H:i:s');
        $ok = $email->Save();
        if (!$ok) {
            LogManager::getInstance()->error("Error Saving Email: ".$email->ErrorMsg());
            return false;
        }

        return true;
    }

    public function sendEmailFromDB($email)
    {
        $params = array();
        if (!empty($email->params)) {
            $params = json_decode($email->params, true);
        }

        $cclist = array();
        if (!empty($email->cclist)) {
            $cclist = json_decode($email->cclist, true);
        }

        $bcclist = array();
        if (!empty($email->bcclist)) {
            $bcclist = json_decode($email->bcclist, true);
        }

        return $this->sendEmail($email->subject, $email->toEmail, $email->template, $params, $cclist, $bcclist);
    }

    public function sendEmail($subject, $toEmail, $template, $params, $ccList = array(), $bccList = array())
    {

        $body = $template;

        foreach ($params as $k => $v) {
            $body = str_replace("#_".$k."_#", $v, $body);
        }

        $fromEmail = $this->settings->getSetting("Email: Email From");

        //Convert to an html email
        $emailBody = file_get_contents(APP_BASE_PATH.'/templates/email/emailBody.html');

        $emailBody = str_replace("#_emailBody_#", $body, $emailBody);
        $emailBody = str_replace(
            "#_logourl_#",
            UIManager::getInstance()->getCompanyLogoUrl(),
            $emailBody
        );

        $user = new User();
        $user->Load("username = ?", array('admin'));

        if (empty($user->id)) {
            $users = $user->Find("user_level = ?", array('Admin'));
            $user = $users[0];
        }

        $emailBody = str_replace("#_adminEmail_#", $user->email, $emailBody);
        $emailBody = str_replace("#_url_#", CLIENT_BASE_URL, $emailBody);
        foreach ($params as $k => $v) {
            $emailBody = str_replace("#_".$k."_#", $v, $emailBody);
        }

        return $this->sendEmailWithLogging(
            $subject,
            $emailBody,
            $toEmail,
            $fromEmail,
            $user->email,
            $ccList,
            $bccList,
            APP_NAME
        );
    }

    public function sendEmailWithoutWrap($subject, $toEmail, $template, $params, $ccList = array(), $bccList = array())
    {

        $body = $template;

        foreach ($params as $k => $v) {
            $body = str_replace("#_".$k."_#", $v, $body);
        }

        $fromEmail = APP_NAME." <".$this->settings->getSetting("Email: Email From").">";

        //Convert to an html email
        $emailBody = $body;
        $emailBody = str_replace(
            "#_logourl_#",
            UIManager::getInstance()->getCompanyLogoUrl(),
            $emailBody
        );

        $user = new User();
        $user->Load("username = ?", array('admin'));

        if (empty($user->id)) {
            $users = $user->Find("user_level = ?", array('Admin'));
            $user = $users[0];
        }

        $emailBody = str_replace("#_adminEmail_#", $user->email, $emailBody);
        $emailBody = str_replace("#_url_#", CLIENT_BASE_URL, $emailBody);
        foreach ($params as $k => $v) {
            $emailBody = str_replace("#_".$k."_#", $v, $emailBody);
        }

        $this->sendEmailWithLogging($subject, $emailBody, $toEmail, $fromEmail, $user->email, $ccList, $bccList);
    }

    protected function sendEmailWithLogging(
        $subject,
        $body,
        $toEmail,
        $fromEmail,
        $replyToEmail = null,
        $ccList = array(),
        $bccList = array(),
        $fromName = null
    ) {
        $emailLogEntry = new EmailLogEntry();
        $emailLogEntry->subject = $subject;
        $emailLogEntry->toEmail = $toEmail;
        $emailLogEntry->body = $body;
        $emailLogEntry->cclist = implode(',', $ccList);
        $emailLogEntry->bcclist = implode(',', $bccList);
        $emailLogEntry->created = date('Y-m-d H:i:s');
        $emailLogEntry->updated = date('Y-m-d H:i:s');

        $result = $this->sendMail(
            $subject,
            $body,
            $toEmail,
            $fromEmail,
            $replyToEmail,
            $ccList,
            $bccList,
            $fromName
        );

        $emailLogEntry->status = $result ? 'Sent' : 'Failed';
        $ok = $emailLogEntry->Save();

        if (!$ok) {
            LogManager::getInstance()->error('Error adding email log for '.json_encode([$toEmail, $subject, $body]));
        }

        return $result;
    }

    abstract protected function sendMail(
        $subject,
        $body,
        $toEmail,
        $fromEmail,
        $replyToEmail = null,
        $ccList = array(),
        $bccList = array(),
        $fromName = null
    );

    public function sendResetPasswordEmail($emailOrUserId)
    {
        $user = new User();
        $user->Load("email = ?", array($emailOrUserId));
        if (empty($user->id)) {
            $user = new User();
            $user->Load("username = ?", array($emailOrUserId));
            if (empty($user->id)) {
                return false;
            }
        }

        $params = array();
        //$params['user'] = $user->first_name." ".$user->last_name;
        $params['url'] = CLIENT_BASE_URL;

        $encJson = PasswordManager::createPasswordRestKey($user);

        $params['passurl'] = CLIENT_BASE_URL."service.php?a=rsp&key=".$encJson;

        $emailBody = file_get_contents(APP_BASE_PATH.'/templates/email/passwordReset.html');

        $this->sendEmail("[".APP_NAME."] Password Change Request", $user->email, $emailBody, $params);
        return true;
    }
}
