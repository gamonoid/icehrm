<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 1:31 PM
 */

namespace Classes\Cron\Task;

use Classes\BaseService;
use Classes\Email\EmailParts;
use Classes\SettingsManager;
use Documents\Common\Model\Document;
use Documents\Common\Model\EmployeeDocument;
use Employees\Common\Model\Employee;
use Utils\LogManager;

class DocumentExpiryNotificationTask extends EmailIceTask
{
    const EMPLOYEE_DOCUMENTS_ROUTE = 'g=modules&n=documents&m=module_Documents#tabEmployeeDocument';
    const MANAGER_DOCUMENTS_ROUTE = 'g=admin&n=documents&m=admin_Manage';

    protected $documentCache = array();
    protected $notificationList = array();
    protected $employeeEmails = array();

    public function execute($cron)
    {

        if (SettingsManager::getInstance()->getSetting('Notifications: Send Document Expiry Emails') != '1') {
            LogManager::getInstance()->info(
                "Notifications: Send Document Expiry Emails is set to No. Do not send emails"
            );
            return;
        }

        $dayList = array();
        $dayList[30] = 'expire_notification_month';
        $dayList[7] = 'expire_notification_week';
        $dayList[1] = 'expire_notification_day';
        $dayList[0] = 'expire_notification';

        foreach ($dayList as $k => $v) {
            $this->expiryDayNotification($k, $v);
        }

        $this->buildEmployeeEmails();
        $this->sendEmployeeEmails($this->employeeEmails, 'Your documents expire soon');
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
            return;
        }

        foreach ($employeeDocuments as $doc) {
            if (empty($doc->document)) {
                continue;
            }
            if (isset($this->documentCache[$doc->document])) {
                $document = $this->documentCache[$doc->document];
            } else {
                $document = new Document();
                $document->Load("id = ?", array($doc->document));
                $this->documentCache[$doc->document] = $document;
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

    /** "today", "in 1 day", "in 7 days". */
    public static function expiresIn($days)
    {
        $days = (int) $days;
        if ($days <= 0) {
            return 'Today';
        }
        return $days == 1 ? 'In 1 day' : 'In ' . $days . ' days';
    }

    /** Table rows (document, expires, expiry date, details) for one employee. */
    private function documentRows($list)
    {
        $rows = array();
        foreach ($list as $item) {
            $doc = $item[0];
            $document = $item[1];
            $rows[] = array(
                (string) $document->name,
                self::expiresIn($item[2]) . ' (' . EmailParts::date($doc->valid_until) . ')',
                trim((string) $doc->details),
            );
        }
        return $rows;
    }

    private function buildEmployeeEmails()
    {
        $copyManager = SettingsManager::getInstance()->getSetting(
            'Notifications: Copy Document Expiry Emails to Manager'
        ) == '1';

        foreach ($this->notificationList as $employeeId => $list) {
            $employee = new Employee();
            $employee->Load("id = ?", array($employeeId));

            if (empty($employee->id) || $employee->id != $employeeId) {
                LogManager::getInstance()->error("Document expiry: could not load employee " . $employeeId);
                continue;
            }

            $rows = $this->documentRows($list);
            $hasDetails = false;
            foreach ($rows as $row) {
                if ($row[2] !== '') {
                    $hasDetails = true;
                }
            }
            $headers = array('Document', 'Expires');
            if ($hasDetails) {
                $headers[] = 'Details';
            } else {
                foreach ($rows as $i => $row) {
                    $rows[$i] = array($row[0], $row[1]);
                }
            }
            $table = EmailParts::table($headers, $rows);
            $count = count($rows);

            $subject = $count == 1
                ? sprintf('Your %s expires %s', $list[0][1]->name, strtolower(self::expiresIn($list[0][2])))
                : sprintf('%d of your documents expire soon', $count);
            $intro = $count == 1
                ? 'One of your documents is about to expire. Please renew it or upload an updated copy.'
                : 'Some of your documents are about to expire. Please renew them or upload updated copies.';

            $url = EmailParts::appUrl(self::EMPLOYEE_DOCUMENTS_ROUTE);
            $body = EmailParts::greeting($employee->first_name)
                . EmailParts::text($intro)
                . $table
                . EmailParts::button($url, 'View my documents')
                . EmailParts::fallbackLink($url);

            $email = array(
                'subject' => $subject,
                'body' => $body,
                'params' => array(
                    'url' => $url,
                    'preheader' => EmailParts::esc($intro),
                ),
                'manager' => null,
            );

            if ($copyManager && !empty($employee->supervisor)) {
                $email['manager'] = $this->buildManagerEmail($employee, $list, $headers, $rows);
            }

            $this->employeeEmails[$employee->id] = $email;
        }
    }

    /** A separate email to the employee's supervisor (instead of a CC that greets the employee). */
    private function buildManagerEmail($employee, $list, $headers, $rows)
    {
        $count = count($rows);
        $manager = new Employee();
        $manager->Load("id = ?", array($employee->supervisor));
        $managerUser = BaseService::getInstance()->getUserFromProfileId($employee->supervisor);
        if (empty($managerUser) || empty($managerUser->email)) {
            return null;
        }

        $employeeName = trim($employee->first_name . ' ' . $employee->last_name);
        $intro = $count == 1
            ? sprintf('A document belonging to %s, who reports to you, is about to expire.', $employeeName)
            : sprintf('%d documents belonging to %s, who reports to you, are about to expire.', $count, $employeeName);
        $url = EmailParts::appUrl(self::MANAGER_DOCUMENTS_ROUTE);

        return array(
            'to' => $managerUser->email,
            'subject' => $count == 1
                ? sprintf("%s's %s expires %s", $employeeName, $list[0][1]->name, strtolower(self::expiresIn($list[0][2])))
                : sprintf("%d of %s's documents expire soon", $count, $employeeName),
            'body' => EmailParts::greeting($manager->first_name)
                . EmailParts::text($intro)
                . EmailParts::table($headers, $rows)
                . EmailParts::small(sprintf('%s has received the same reminder.', $employee->first_name))
                . EmailParts::button($url, 'View documents')
                . EmailParts::fallbackLink($url),
            'params' => array(
                'url' => $url,
                'preheader' => EmailParts::esc($intro),
                'emailReason' => 'You received this email because ' . EmailParts::esc($employeeName)
                    . ' reports to you at ' . EmailParts::esc(EmailParts::companyName()) . '.',
            ),
        );
    }
}
