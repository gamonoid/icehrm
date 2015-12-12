<?php

class EmailSenderTask implements IceTask{
    public function execute($cron){
        $email = new IceEmail();
        $emails = $email->Find("status = ? limit 10",array('Pending'));
        $emailSender = BaseService::getInstance()->getEmailSender();
        foreach($emails as $email){
            try{
                $emailSender->sendEmailFromDB($email);
            }catch(Exception $e){
                LogManager::getInstance()->error("Error sending email:".$e->getMessage());
            }

            $email->status = 'Sent';
            $email->updated = date('Y-m-d H:i:s');
            $email->Save();
        }
    }
}


include('common.cron.tasks.ext.php');