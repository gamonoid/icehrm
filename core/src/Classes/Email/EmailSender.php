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

    /**
     * Send an in-app notification as an email. The subject is the notification itself
     * (e.g. "Jane Doe submitted an overtime request"), so it reads well in the inbox.
     */
    public function sendEmailFromNotification($notification, $delayed = false)
    {
        $user = new User();
        $user->Load("id = ?", array($notification->toUser));
        if (empty($user->email)) {
            return;
        }

        $name = '';
        $employee = new Employee();
        $employee->Load("id = ?", array($user->employee));
        if ($employee->id == $user->employee && !empty($employee->id)) {
            $name = $employee->first_name;
        }

        $message = trim(html_entity_decode(strip_tags((string) $notification->message), ENT_QUOTES, 'UTF-8'));
        $action = json_decode((string) $notification->action);
        $url = (!empty($action) && isset($action->type) && $action->type == 'url' && !empty($action->url))
            ? EmailParts::appUrl($action->url)
            : CLIENT_BASE_URL;

        $body = EmailParts::greeting($name)
            . EmailParts::text($message)
            . EmailParts::button($url, 'Open in ' . EmailParts::companyName())
            . EmailParts::fallbackLink($url);

        $subject = self::subjectFrom($message, 'You have a new notification');
        $params = array('url' => $url, 'preheader' => EmailParts::esc($message));

        if ($delayed) {
            $this->sendEmailDelayed($subject, $user->email, $body, $params, array(), array());
        } else {
            $this->sendEmail($subject, $user->email, $body, $params, array(), array());
        }
    }

    /** A one-line subject from a message: first sentence, at most 90 characters. */
    public static function subjectFrom($message, $fallback)
    {
        $line = trim(preg_replace('/\s+/', ' ', (string) $message));
        if ($line === '') {
            return $fallback;
        }
        $first = preg_split('/(?<=[.!?])\s/', $line, 2);
        $line = rtrim($first[0], '.');
        if (mb_strlen($line) > 90) {
            $line = rtrim(mb_substr($line, 0, 87)) . '...';
        }
        return $line;
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
        list($emailBody, $replyTo) = $this->renderEmail($template, $params);
        return $this->sendEmailWithLogging(
            $subject,
            $emailBody,
            $toEmail,
            $this->settings->getSetting("Email: Email From"),
            $replyTo,
            $ccList,
            $bccList,
            EmailParts::companyName()
        );
    }

    /**
     * Fill a body template and wrap it in the standard layout
     * (core/templates/email/emailBody.html). Returns [html, replyToEmail].
     *
     * Wrapper params (all optional): preheader (preview text, HTML-escaped),
     * emailReason (footer sentence, trusted HTML), url (logo link), adminEmail.
     */
    public function renderEmail($template, $params)
    {
        $body = (string) $template;
        foreach ($params as $k => $v) {
            $body = str_replace("#_".$k."_#", (string) $v, $body);
        }

        $companyName = EmailParts::companyName();
        $emailBody = file_get_contents(APP_BASE_PATH.'/templates/email/emailBody.html');

        if (empty($params['adminEmail'])) {
            $params['adminEmail'] = $this->adminEmail();
        }
        if (!isset($params['emailReason'])) {
            $params['emailReason'] = 'You received this email because you have an account at '
                . EmailParts::esc($companyName) . '.';
        }
        if (!isset($params['preheader'])) {
            $params['preheader'] = '';
        }
        // The body goes in first, so placeholders it contains are filled below too.
        $emailBody = str_replace("#_emailBody_#", $body, $emailBody);

        $params['year'] = date("Y");
        $params['icehrm_url'] = 'https://icehrm.com';
        $params['company_name'] = EmailParts::esc($companyName);
        $params['logourl'] = UIManager::getInstance()->getCompanyLogoUrl();
        if (!isset($params['url'])) {
            $params['url'] = CLIENT_BASE_URL;
        }

        foreach ($params as $k => $v) {
            $emailBody = str_replace("#_".$k."_#", (string) $v, $emailBody);
        }
        return array($emailBody, $params['adminEmail']);
    }

    /** The main admin's email address (reply-to and footer contact). */
    protected function adminEmail()
    {
        $user = new User();
        $user->Load("username = ?", array('admin'));
        if (empty($user->id)) {
            $users = $user->Find("user_level = ?", array('Admin'));
            $user = empty($users) ? $user : $users[0];
        }
        return (string) $user->email;
    }

    public function sendEmailWithoutWrap($subject, $toEmail, $template, $params, $ccList = array(), $bccList = array())
    {

        $body = $template;

        foreach ($params as $k => $v) {
            $body = str_replace("#_".$k."_#", $v, $body);
        }

        $fromEmail = EmailParts::companyName()." <".$this->settings->getSetting("Email: Email From").">";

        //Convert to an html email
        $emailBody = $body;
        $emailBody = str_replace(
            "#_logourl_#",
            UIManager::getInstance()->getCompanyLogoUrl(),
            $emailBody
        );

        if (empty($params['adminEmail'])) {
            $user = new User();
            $user->Load("username = ?", array('admin'));

            if (empty($user->id)) {
                $users = $user->Find("user_level = ?", array('Admin'));
                $user = $users[0];
            }
            $params['adminEmail'] = $user->email;
        }
        $emailBody = str_replace("#_adminEmail_#", $params['adminEmail'], $emailBody);


        $emailBody = str_replace("#_url_#", CLIENT_BASE_URL, $emailBody);
        foreach ($params as $k => $v) {
            $emailBody = str_replace("#_".$k."_#", $v, $emailBody);
        }

        $this->sendEmailWithLogging($subject, $emailBody, $toEmail, $fromEmail, $params['adminEmail'], $ccList, $bccList);
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

        $firstName = '';
        if (!empty($user->employee)) {
            $employee = new Employee();
            $employee->Load("id = ?", array($user->employee));
            if ($employee->id == $user->employee) {
                $firstName = $employee->first_name;
            }
        }

        $company = EmailParts::companyName();
        $resetUrl = CLIENT_BASE_URL."service.php?a=rsp&key=".PasswordManager::createPasswordRestKey($user);

        // PasswordManager::verifyPasswordRestKey() accepts the key for 30 minutes, and it
        // stops working once the password changes (it is encrypted with the old hash).
        $body = EmailParts::greeting($firstName)
            . EmailParts::paragraph(
                'We received a request to reset the password for your ' . EmailParts::esc($company)
                . ' account (username ' . EmailParts::strong($user->username) . ').'
            )
            . EmailParts::button($resetUrl, 'Set a new password')
            . EmailParts::fallbackLink($resetUrl)
            . EmailParts::small(
                'This link expires in 30 minutes and works only once. If you did not ask to reset your password, '
                . 'you can ignore this email: your password will not change.'
            )
            . EmailParts::signOff();

        $this->sendEmail('Reset your password', $user->email, $body, array(
            'preheader' => EmailParts::esc('Use this link within 30 minutes to choose a new password.'),
        ));
        return true;
    }
}
