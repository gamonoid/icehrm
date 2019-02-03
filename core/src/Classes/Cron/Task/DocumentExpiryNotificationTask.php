<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 1:31 PM
 */

namespace Classes\Cron\Task;

use Classes\SettingsManager;
use Documents\Common\Model\Document;
use Documents\Common\Model\EmployeeDocument;
use Employees\Common\Model\Employee;
use Utils\LogManager;

class DocumentExpiryNotificationTask extends EmailIceTask
{

    protected $documentCache = array();
    protected $notificationList = array();
    protected $employeeDocList = array();
    protected $employeeEmails = array();

    public function execute($cron)
    {

        if (SettingsManager::getInstance()->getSetting('Notifications: Send Document Expiry Emails') != '1') {
            LogManager::getInstance()->info(
                "Notifications: Send Document Expiry Emails is set to No. Do not send emails"
            );
            return;
        }

        //Get documents

        $dayList = array();
        $dayList[30] = 'expire_notification_month';
        $dayList[7] = 'expire_notification_week';
        $dayList[1] = 'expire_notification_day';
        $dayList[0] = 'expire_notification';

        foreach ($dayList as $k => $v) {
            $this->expiryDayNotification($k, $v);
        }

        $this->getExpireDocumentHTMLByEmployee();
        $this->sendEmployeeEmails($this->employeeEmails, "IceHrm Employee Document Expiry Reminder");
    }

    private function expiryDayNotification($day, $param)
    {
        $date = date('Y-m-d', strtotime("+".$day." days"));

        $employeeDocument = new EmployeeDocument();
        $employeeDocuments = $employeeDocument->Find(
            "valid_until IS NOT NULL and valid_until = ? 
            and (expire_notification_last > ? or expire_notification_last = -1) and status = ?",
            array($date, $day, 'Active')
        );

        if (!$employeeDocuments) {
            LogManager::getInstance()->error("Error :".$employeeDocument->ErrorMsg());
            return;
        }

        $query = "valid_until IS NOT NULL and valid_until = $date and
            (expire_notification_last > $day or expire_notification_last == -1)
            and status = 'Active';";

        LogManager::getInstance()->debug($query);

        foreach ($employeeDocuments as $doc) {
            LogManager::getInstance()->debug("Employee Doc :".print_r($doc, true));

            if (empty($doc->document)) {
                continue;
            }
            $document = null;
            if (isset($this->documentCache[$doc->id])) {
                $document = $this->documentCache[$doc->id];
            } else {
                $document = new Document();
                $document->Load("id = ?", array($doc->document));
                $this->documentCache[$document->id] = $document;
            }

            if ($document->$param == "Yes") {
                if (!isset($this->notificationList[$doc->employee])) {
                    $this->notificationList[$doc->employee] = array();
                }
                $this->notificationList[$doc->employee][] = array($doc, $document, $day);
            }

            $doc->expire_notification_last = $day;
            $doc->Save();
        }
    }

    private function getExpireDocumentHTMLByEmployee()
    {
        $row = '<p style="background-color: #EEE;padding: 5px;font-size: 0.9em;font-weight: bold;">'
            .'<span style="font-size: 1em;font-weight: bold;">'
            .'#_name_#</span> - Expire in #_days_# day(s)</p><br/><span style="font-size: 0.8em;font-weight: bold;">'
            .'#_description_#</span><hr/>';

        foreach ($this->notificationList as $key => $val) {
            $employeeEmail = "";

            foreach ($val as $list) {
                $trow = $row;
                $doc = $list[0];
                $document = $list[1];
                $days = $list[2];

                $trow = str_replace("#_name_#", $document->name, $trow);
                $trow = str_replace("#_days_#", $days, $trow);
                $trow = str_replace("#_description_#", $doc->details, $trow);

                $employeeEmail.=$trow;
            }

            $employee = new Employee();
            $employee->Load("id = ?", array($key));

            if (empty($employee->id) || $employee->id != $key) {
                LogManager::getInstance()->error("Could not load employee with id");
                return;
            }

            $emailBody = file_get_contents(
                APP_BASE_PATH.'/admin/documents/emailTemplates/documentExpireEmailTemplate.html'
            );
            $emailBody = str_replace("#_employee_#", $employee->first_name, $emailBody);
            $emailBody = str_replace("#_documents_#", $employeeEmail, $emailBody);

            $this->employeeEmails[$employee->id] = $emailBody;
        }
    }
}
