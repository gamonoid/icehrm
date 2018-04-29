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
use Classes\SettingsManager;
use Employees\Common\Model\Employee;
use Model\IceEmail;
use Utils\LogManager;

abstract class EmailIceTask implements IceTask
{
    abstract public function execute($cron);

    public function sendEmployeeEmails($emailList, $subject)
    {

        foreach ($emailList as $employeeId => $emailData) {
            $ccList = array();
            if (SettingsManager::getInstance()->getSetting(
                'Notifications: Copy Document Expiry Emails to Manager'
            ) == '1'
            ) {
                $employee = new Employee();
                $employee->Load("id = ?", array($employeeId));
                if (!empty($employee->supervisor)) {
                    $supperuser = BaseService::getInstance()->getUserFromProfileId($employee->supervisor);
                    if (!empty($supperuser)) {
                        $ccList[] = $supperuser->email;
                    }
                }
            }
            $user = BaseService::getInstance()->getUserFromProfileId($employeeId);
            if (!empty($user) && !empty($user->email)) {
                $email = new IceEmail();
                $email->subject = $subject;
                $email->toEmail = $user->email;
                $email->template = $emailData;
                $email->params = '[]';
                $email->cclist = json_encode($ccList);
                $email->bcclist = '[]';
                $email->status = 'Pending';
                $email->created = date('Y-m-d H:i:s');
                $email->updated = date('Y-m-d H:i:s');
                $ok = $email->Save();
                if (!$ok) {
                    LogManager::getInstance()->error("Error Saving Email: ".$email->ErrorMsg());
                }
            }
        }
    }
}
