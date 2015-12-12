<?php
/*
 * This file is part of Ice Framework.
 * Copyright Thilina Hasantha (thilina.hasantha[at]gmail.com | http://facebook.com/thilinah | https://twitter.com/thilina84)
 * Licensed under MIT (https://github.com/thilinah/ice-framework/master/LICENSE)
 */

use Aws\Ses\SesClient;

abstract class EmailSender{
    var $settings = null;
    public function __construct($settings){
        $this->settings	= $settings;
    }

    public function sendEmailFromNotification($notification){
        $toEmail = null;
        $user = new User();
        $user->Load("id = ?",array($notification->toUser));

        if(!empty($user->email)){
            $name = "User";
            $employee = new Employee();
            $employee->Load("id = ?",array($user->employee));
            if($employee->id == $user->employee && !empty($employee->id)){
                $name = $employee->first_name;
            }

            $action = json_decode($notification->action);

            $emailBody = file_get_contents(APP_BASE_PATH.'/templates/email/notificationEmail.html');
            $emailBody = str_replace("#_user_#", $name, $emailBody);
            $emailBody = str_replace("#_message_#", $notification->message, $emailBody);
            if($action->type == "url"){
                $emailBody = str_replace("#_url_#", CLIENT_BASE_URL."?".$action->url, $emailBody);
            }
            $this->sendEmail('IceHrm Notification from '.$notification->type,
                $user->email,
                $emailBody,
                array(),
                array(),
                array()
                );
        }
    }

    public function sendEmailFromDB($email){
        $params = array();
        if(!empty($email->params)){
            $params = json_decode($email->params, true);
        }

        $cclist = array();
        if(!empty($email->cclist)){
            $cclist = json_decode($email->cclist, true);
        }

        $bcclist = array();
        if(!empty($email->bcclist)){
            $bcclist = json_decode($email->bcclist, true);
        }

        $resp = $this->sendEmail($email->subject, $email->toEmail, $email->template, $params, $cclist, $bcclist);
    }

    public function sendEmail($subject, $toEmail, $template, $params, $ccList = array(), $bccList = array()){

        $body = $template;

        foreach($params as $k=>$v){
            $body = str_replace("#_".$k."_#", $v, $body);
        }

        $fromEmail = APP_NAME." <".$this->settings->getSetting("Email: Email From").">";


        //Convert to an html email
        $emailBody = file_get_contents(APP_BASE_PATH.'/templates/email/emailBody.html');

        $emailBody = str_replace("#_emailBody_#", $body, $emailBody);
        $emailBody = str_replace("#_logourl_#",
            BASE_URL."images/logo.png"
            , $emailBody);

        $user = new User();
        $user->load("username = ?",array('admin'));

        if(empty($user->id)){
            $users = $user->Find("user_level = ?",array('Admin'));
            $user = $users[0];
        }

        $emailBody = str_replace("#_adminEmail_#", $user->email, $emailBody);
        $emailBody = str_replace("#_url_#", CLIENT_BASE_URL, $emailBody);
        foreach($params as $k=>$v){
            $emailBody = str_replace("#_".$k."_#", $v, $emailBody);
        }

        $this->sendMail($subject, $emailBody, $toEmail, $fromEmail, $user->email, $ccList, $bccList);
    }

    public function sendEmailWithoutWrap($subject, $toEmail, $template, $params, $ccList = array(), $bccList = array()){

        $body = $template;

        foreach($params as $k=>$v){
            $body = str_replace("#_".$k."_#", $v, $body);
        }

        $fromEmail = APP_NAME." <".$this->settings->getSetting("Email: Email From").">";


        //Convert to an html email
        $emailBody = $body;
        $emailBody = str_replace("#_logourl_#",
            BASE_URL."images/logo.png"
            , $emailBody);

        $user = new User();
        $user->load("username = ?",array('admin'));

        if(empty($user->id)){
            $users = $user->Find("user_level = ?",array('Admin'));
            $user = $users[0];
        }

        $emailBody = str_replace("#_adminEmail_#", $user->email, $emailBody);
        $emailBody = str_replace("#_url_#", CLIENT_BASE_URL, $emailBody);
        foreach($params as $k=>$v){
            $emailBody = str_replace("#_".$k."_#", $v, $emailBody);
        }

        $this->sendMail($subject, $emailBody, $toEmail, $fromEmail, $user->email, $ccList, $bccList);
    }

    protected  abstract function sendMail($subject, $body, $toEmail, $fromEmail, $replyToEmail = null, $ccList = array(), $bccList = array());

    public function sendResetPasswordEmail($emailOrUserId){
        $user = new User();
        $user->Load("email = ?",array($emailOrUserId));
        if(empty($user->id)){
            $user = new User();
            $user->Load("username = ?",array($emailOrUserId));
            if(empty($user->id)){
                return false;
            }
        }

        $params = array();
        //$params['user'] = $user->first_name." ".$user->last_name;
        $params['url'] = CLIENT_BASE_URL;

        $newPassHash = array();
        $newPassHash["CLIENT_NAME"] = CLIENT_NAME;
        $newPassHash["oldpass"] = $user->password;
        $newPassHash["email"] = $user->email;
        $newPassHash["time"] = time();
        $json = json_encode($newPassHash);

        $encJson = AesCtr::encrypt($json, $user->password, 256);
        $encJson = urlencode($user->id."-".$encJson);
        $params['passurl'] = CLIENT_BASE_URL."service.php?a=rsp&key=".$encJson;

        $emailBody = file_get_contents(APP_BASE_PATH.'/templates/email/passwordReset.html');

        $this->sendEmail("[".APP_NAME."] Password Change Request", $user->email, $emailBody, $params);
        return true;
    }

}


class SNSEmailSender extends EmailSender{
    var $ses = null;
    public function __construct($settings){
        parent::__construct($settings);
        $arr = array(
            'key'    => $this->settings->getSetting('Email: Amazon Access Key ID'),
            'secret' => $this->settings->getSetting('Email: Amazon Secret Access Key'),
            'region' => AWS_REGION
        );
        //$this->ses = new AmazonSES($arr);
        $this->ses = SesClient::factory($arr);
    }

    protected  function sendMail($subject, $body, $toEmail, $fromEmail, $replyToEmail = null, $ccList = array(), $bccList = array()) {

        if(empty($replyToEmail)){
            $replyToEmail = $fromEmail;
        }

        LogManager::getInstance()->info("Sending email to: ".$toEmail."/ from: ".$fromEmail);

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
                'Source'=>$fromEmail,
                'Destination'=>$toArray,
                'Message'=>$message,
                'ReplyToAddresses' => array($replyToEmail),
                'ReturnPath' => $fromEmail
            )
        );

        LogManager::getInstance()->info("SES Response:".print_r($response,true));

        return $response;

    }
}


class SMTPEmailSender extends EmailSender{

    public function __construct($settings){
        parent::__construct($settings);
    }

    protected  function sendMail($subject, $body, $toEmail, $fromEmail, $replyToEmail = null, $ccList = array(), $bccList = array()) {

        if(empty($replyToEmail)){
            $replyToEmail = $fromEmail;
        }

        LogManager::getInstance()->info("Sending email to: ".$toEmail."/ from: ".$fromEmail);

        $host = $this->settings->getSetting("Email: SMTP Host");
        $username = $this->settings->getSetting("Email: SMTP User");
        $password = $this->settings->getSetting("Email: SMTP Password");
        $port = $this->settings->getSetting("Email: SMTP Port");

        if(empty($port)){
            $port = '25';
        }

        if($this->settings->getSetting("Email: SMTP Authentication Required") == "0"){
            $auth = array ('host' => $host,
                'auth' => false);
        }else{
            $auth = array ('host' => $host,
                'auth' => true,
                'username' => $username,
                'port' => $port,
                'password' => $password);
        }


        $smtp = Mail::factory('smtp',$auth);

        $headers = array ('MIME-Version' => '1.0',
            'Content-type' => 'text/html',
            'charset' => 'iso-8859-1',
            'From' => $fromEmail,
            'To' => $toEmail,
            'Reply-To' => $replyToEmail,
            'Subject' => $subject);


        $mail = $smtp->send($toEmail, $headers, $body);


        return $mail;
    }
}


class PHPMailer extends EmailSender{

    public function __construct($settings){
        parent::__construct($settings);
    }

    protected  function sendMail($subject, $body, $toEmail, $fromEmail, $replyToEmail = null, $ccList = array(), $bccList = array()) {

        if(empty($replyToEmail)){
            $replyToEmail = $fromEmail;
        }

        LogManager::getInstance()->info("Sending email to: ".$toEmail."/ from: ".$fromEmail);

        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: '.$fromEmail. "\r\n";
        $headers .= 'ReplyTo: '.$replyToEmail. "\r\n";
        $headers .= 'Ice-Mailer: PHP/' . phpversion();

        // Mail it
        $res = mail($toEmail, $subject, $body, $headers);

        LogManager::getInstance()->info("PHP mailer result : ".$res);

        return $res;
    }
}