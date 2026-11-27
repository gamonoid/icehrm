<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:34 AM
 */
namespace Classes\Cron\Task;

use Classes\BaseService;
use Classes\Cron\IceTask;
use Model\IceEmail;
use Utils\LogManager;

abstract class EmailIceTask implements IceTask
{
    abstract public function execute($cron);

    /**
     * Queue one email per employee.
     *
     * Each entry of $emailList (keyed by employee id) is either a body string (sent
     * with $subject) or an array:
     *   subject, body, params (wrapper params such as preheader / url),
     *   manager (optional: a separate email for the supervisor with
     *            to, subject, body, params).
     */
    public function sendEmployeeEmails($emailList, $subject)
    {
        foreach ($emailList as $employeeId => $emailData) {
            if (!is_array($emailData)) {
                $emailData = array('body' => $emailData);
            }
            $user = BaseService::getInstance()->getUserFromProfileId($employeeId);
            if (!empty($user) && !empty($user->email)) {
                $this->queueEmail(
                    $user->email,
                    !empty($emailData['subject']) ? $emailData['subject'] : $subject,
                    $emailData['body'],
                    isset($emailData['params']) ? $emailData['params'] : array()
                );
            }

            if (!empty($emailData['manager']) && !empty($emailData['manager']['to'])) {
                $manager = $emailData['manager'];
                $this->queueEmail(
                    $manager['to'],
                    $manager['subject'],
                    $manager['body'],
                    isset($manager['params']) ? $manager['params'] : array()
                );
            }
        }
    }

    protected function queueEmail($toEmail, $subject, $body, $params)
    {
        $email = new IceEmail();
        $email->subject = $subject;
        $email->toEmail = $toEmail;
        $email->template = $body;
        $email->params = json_encode($params);
        $email->cclist = '[]';
        $email->bcclist = '[]';
        $email->status = 'Pending';
        $email->created = date('Y-m-d H:i:s');
        $email->updated = date('Y-m-d H:i:s');
        $ok = $email->Save();
        if (!$ok) {
            LogManager::getInstance()->error("Error Saving Email: ".$email->ErrorMsg());
        }
        return $ok;
    }
}
