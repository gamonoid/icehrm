<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:40 AM
 */

namespace Classes\Email;

use Utils\LogManager;

class PHPMailer extends EmailSender
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

            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: ' . $fromEmail . "\r\n";
            if (!empty($ccList)) {
                $headers .= 'CC: ' . implode(",", $ccList) . "\r\n";
            }
            if (!empty($bccList)) {
                $headers .= 'BCC: ' . implode(",", $bccList) . "\r\n";
            }
            $headers .= 'ReplyTo: ' . $replyToEmail . "\r\n";
            $headers .= 'Ice-Mailer: PHP/' . phpversion();

            // Mail it
            $res = mail($toEmail, $subject, $body, $headers);

            LogManager::getInstance()->info("PHP mailer result : " . $res);

            return $res;
        } catch (\Exception $e) {
            LogManager::getInstance()->error("Error sending email:" . $e->getMessage());
            return false;
        }
    }
}
