<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:39 AM
 */

namespace Classes\Email;

use Utils\LogManager;

class SMTPEmailSender extends EmailSender
{

    public function __construct($settings)
    {
        parent::__construct($settings);
    }

    protected function sendMail(
        $subject,
        $body,
        $toEmail,
        $fromEmail,
        $replyToEmail = null,
        $ccList = array(),
        $bccList = array()
    ) {

        try {
            if (empty($replyToEmail)) {
                $replyToEmail = $fromEmail;
            }

            LogManager::getInstance()->info("Sending email to: " . $toEmail . "/ from: " . $fromEmail);

            $host = $this->settings->getSetting("Email: SMTP Host");
            $username = $this->settings->getSetting("Email: SMTP User");
            $password = $this->settings->getSetting("Email: SMTP Password");
            $port = $this->settings->getSetting("Email: SMTP Port");

            if (empty($port)) {
                $port = '25';
            }

            if ($this->settings->getSetting("Email: SMTP Authentication Required") == "0") {
                $auth = array('host' => $host,
                    'auth' => false);
            } else {
                $auth = array('host' => $host,
                    'auth' => true,
                    'username' => $username,
                    'port' => $port,
                    'password' => $password);
            }

            $smtp = \Mail::factory('smtp', $auth);

            $headers = array('MIME-Version' => '1.0',
                'Content-type' => 'text/html',
                'charset' => 'iso-8859-1',
                'From' => $fromEmail,
                'To' => $toEmail,
                'Reply-To' => $replyToEmail,
                'Subject' => $subject);

            if (!empty($ccList)) {
                $headers['Cc'] = implode(",", $ccList);
            }

            if (!empty($bccList)) {
                $headers['Bcc'] = implode(",", $bccList);
            }

            $mail = $smtp->send($toEmail, $headers, $body);
            if (\PEAR::isError($mail)) {
                LogManager::getInstance()->info("SMTP Error Response:" . $mail->getMessage());
            }

            return $mail;
        } catch (\Exception $e) {
            LogManager::getInstance()->error("Error sending email:" . $e->getMessage());
            return false;
        }
    }
}
