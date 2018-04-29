<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 1:35 PM
 */

namespace Classes\Cron\Task;

use Classes\BaseService;
use Classes\Cron\IceTask;
use Model\IceEmail;
use Utils\LogManager;

class EmailSenderTask implements IceTask
{
    public function execute($cron)
    {
        $email = new IceEmail();
        $emails = $email->Find("status = ? limit 10", array('Pending'));
        $emailSender = BaseService::getInstance()->getEmailSender();
        /* @var IceEmail $email */
        foreach ($emails as $email) {
            try {
                $emailSender->sendEmailFromDB($email);
            } catch (\Exception $e) {
                LogManager::getInstance()->error("Error sending email:".$e->getMessage());
            }

            $email->status = 'Sent';
            $email->updated = date('Y-m-d H:i:s');
            $email->Save();
        }
    }
}
