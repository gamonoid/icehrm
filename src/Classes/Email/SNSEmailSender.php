<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:39 AM
 */
namespace Classes\Email;

use Aws\Ses\SesClient;
use Utils\LogManager;

class SNSEmailSender extends EmailSender
{
    private $ses = null;
    public function __construct($settings)
    {
        parent::__construct($settings);
        $arr = array(
            'key'    => $this->settings->getSetting('Email: Amazon Access Key ID'),
            'secret' => $this->settings->getSetting('Email: Amazon Secret Access Key'),
            'region' => AWS_REGION
        );
        $this->ses = SesClient::factory($arr);
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

            $toArray = array('ToAddresses' => array($toEmail),
                'CcAddresses' => $ccList,
                'BccAddresses' => $bccList);
            $message = array(
                'Subject' => array(
                    'Data' => $subject,
                    'Charset' => 'UTF-8'
                ),
                'Body' => array(
                    'Html' => array(
                        'Data' => $body,
                        'Charset' => 'UTF-8'
                    )
                )
            );

            //$response = $this->ses->sendEmail($fromEmail, $toArray, $message);
            $response = $this->ses->sendEmail(
                array(
                    'Source' => $fromEmail,
                    'Destination' => $toArray,
                    'Message' => $message,
                    'ReplyToAddresses' => array($replyToEmail),
                    'ReturnPath' => $fromEmail
                )
            );

            LogManager::getInstance()->info("SES Response:" . print_r($response, true));

            return $response;
        } catch (\Exception $e) {
            LogManager::getInstance()->error("Error sending email:" . $e->getMessage());
            return false;
        }
    }
}
