<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 2:32 PM
 */

namespace Leaves\Common;

use Classes\BaseService;
use Classes\Email\EmailSender;
use Classes\SettingsManager;
use Company\Common\Model\CompanyStructure;
use Employees\Common\Model\Employee;
use Leaves\Admin\Api\LeaveUtil;
use Leaves\Common\Model\EmployeeLeave;
use Leaves\Common\Model\LeaveType;
use Leaves\User\Api\LeavesActionManager;
use Utils\LogManager;

class LeavesEmailSender
{

    /**
 * @var EmailSender $emailSender 
*/
    protected $emailSender = null;
    protected $subActionManager = null;
    protected $modulePath = null;

    public function __construct($emailSender, $subActionManager, $modulePath = null)
    {
        $this->emailSender = $emailSender;
        $this->subActionManager = $subActionManager;
        $this->modulePath = $modulePath;
    }

    private function getEmployeeSupervisor($employee)
    {

        if (empty($employee->supervisor)) {
            LogManager::getInstance()->info("Employee supervisor is empty");
            return null;
        }

        $sup = new Employee();
        $sup->Load("id = ?", array($employee->supervisor));
        if ($sup->id != $employee->supervisor) {
            LogManager::getInstance()->info("Employee supervisor not found");
            return null;
        }

        return $sup;
    }

    private function getEmployeeById($id)
    {
        $sup = new Employee();
        $sup->Load("id = ?", array($id));
        if ($sup->id != $id) {
            LogManager::getInstance()->info("Employee not found");
            return null;
        }

        return $sup;
    }

    /**
     * @param Employee      $employee
     * @param EmployeeLeave $employeeLeave
     * @param false         $cancellation
     * @return false
     */
    public function sendLeaveApplicationEmail($employee, $employeeLeave, $cancellation = false)
    {

        $sup = $this->getEmployeeSupervisor($employee);
        if (empty($sup)) {
            return false;
        }

        $dep = new CompanyStructure();
        $dep->Load("id = ?", [$employee->department]);

        $type = new LeaveType();
        $type->Load("id = ?", [$employeeLeave->leave_type]);

        $leaveDaysData = $this->getLeaveDaysData($employeeLeave);
        $total_leaves = LeaveUtil::countLeaveAmountByDays($leaveDaysData->data[0]);

        $params = array();
        $params['supervisor'] = $sup->first_name." ".$sup->last_name;
        $params['name'] = $employee->first_name." ".$employee->last_name;
        $params['url'] = CLIENT_BASE_URL;
        $params['indirect'] = "Direct";
        $params['department'] = $dep->title;
        $params['leaveType'] = $type->name;
        $params['startDate'] = $employeeLeave->date_start;
        $params['endDate'] = $employeeLeave->date_end;
        $params['reason'] = $employeeLeave->details;
        $params['days'] = $total_leaves;

        $table = $this->getHtmlLeaveDaysTable($leaveDaysData);


        if ($cancellation) {
            $email = $this->subActionManager->getEmailTemplate('leaveCancelled.html', $this->modulePath);
        } else {
            $email = $this->subActionManager->getEmailTemplate('leaveApplied.html', $this->modulePath);
        }

        $email = str_replace('#_table_#', $table, $email);

        $user = $this->subActionManager->getUserFromProfileId($sup->id);

        $emailTo = null;
        if (!empty($user)) {
            $emailTo = $user->email;
        }

        if (!empty($emailTo)) {
            if (!empty($this->emailSender)) {
                $ccList = array();
                $ccListStr = SettingsManager::getInstance()->getSetting("Leave: CC Emails");
                if (!empty($ccListStr)) {
                    $arr = explode(",", $ccListStr);
                    $count = count($arr)<=10?count($arr):10;
                    for ($i = 0; $i<$count; $i++) {
                        if (filter_var($arr[$i], FILTER_VALIDATE_EMAIL)) {
                            $ccList[] = $arr[$i];
                        }
                    }
                }

                $bccList = array();
                $bccListStr = SettingsManager::getInstance()->getSetting("Leave: BCC Emails");
                if (!empty($bccListStr)) {
                    $arr = explode(",", $bccListStr);
                    $count = count($arr)<=4?count($arr):4;
                    for ($i = 0; $i<$count; $i++) {
                        if (filter_var($arr[$i], FILTER_VALIDATE_EMAIL)) {
                            $bccList[] = $arr[$i];
                        }
                    }
                }
                if ($cancellation) {
                    $this->emailSender->sendEmail(
                        "Leave Cancellation Request Received",
                        $emailTo,
                        $email,
                        $params,
                        $ccList,
                        $bccList
                    );
                } else {
                    $this->emailSender->sendEmail(
                        "Leave Application Received",
                        $emailTo,
                        $email,
                        $params,
                        $ccList,
                        $bccList
                    );
                }
            }
        } else {
            LogManager::getInstance()->info("[sendLeaveApplicationEmail] email is empty");
        }

        //Send approval emails to indirect supervisors
        if ($employeeLeave->allowIndirectMapping()) {
            if (!empty($employee->indirect_supervisors)) {
                $indirectSupervisors = json_decode($employee->indirect_supervisors, true);
                $params = array();

                $params['name'] = $employee->first_name." ".$employee->last_name;
                $params['url'] = CLIENT_BASE_URL;
                $params['indirect'] = "Indirect";
                $params['department'] = $dep->title;
                $params['leaveType'] = $type->name;
                $params['startDate'] = $employeeLeave->date_start;
                $params['endDate'] = $employeeLeave->date_end;
                $params['reason'] = $employeeLeave->details;
                $params['days'] = $total_leaves;

                foreach ($indirectSupervisors as $is) {
                    $supervisor = new Employee();
                    $supervisor->Load("id = ?", array($is));
                    $params['supervisor'] = $supervisor->first_name." ".$supervisor->last_name;
                    $user = $this->subActionManager->getUserFromProfileId($is);

                    $emailTo = null;
                    if (!empty($user)) {
                        $emailTo = $user->email;
                    }

                    if (!empty($emailTo)) {
                        if (!empty($this->emailSender)) {
                            $ccList = array();
                            $bccList = array();

                            if ($cancellation) {
                                $this->emailSender->sendEmail(
                                    "Leave Cancellation Request Received from an Indirect Report",
                                    $emailTo,
                                    $email,
                                    $params,
                                    $ccList,
                                    $bccList
                                );
                            } else {
                                $this->emailSender->sendEmail(
                                    "Leave Application Received from an Indirect Report",
                                    $emailTo,
                                    $email,
                                    $params,
                                    $ccList,
                                    $bccList
                                );
                            }
                        }
                    } else {
                        LogManager::getInstance()->info("[sendLeaveApplicationEmail] indirect email is empty");
                    }
                }
            }
        }
    }

    public function sendLeaveApplicationSubmittedEmail($employee, $employeeLeave)
    {
        $type = new LeaveType();
        $type->Load("id = ?", [$employeeLeave->leave_type]);

        $leaveDaysData = $this->getLeaveDaysData($employeeLeave);
        $total_leaves = LeaveUtil::countLeaveAmountByDays($leaveDaysData->data[0]);

        $params = array();
        $params['name'] = $employee->first_name." ".$employee->last_name;
        $params['leaveType'] = $type->name;
        $params['startDate'] = $employeeLeave->date_start;
        $params['endDate'] = $employeeLeave->date_end;
        $params['reason'] = $employeeLeave->details;
        $params['days'] = $total_leaves;

        $email = $this->subActionManager->getEmailTemplate('leaveSubmittedForReview.html', $this->modulePath);
        $table = $this->getHtmlLeaveDaysTable($leaveDaysData);
        $email = str_replace('#_table_#', $table, $email);

        $user = $this->subActionManager->getUserFromProfileId($employee->id);

        $emailTo = null;
        if (!empty($user)) {
            $emailTo = $user->email;
        }

        if (!empty($emailTo)) {
            if (!empty($this->emailSender)) {
                $this->emailSender->sendEmail("Leave Application Submitted", $emailTo, $email, $params);
            }
        } else {
            LogManager::getInstance()->info("[sendLeaveApplicationSubmittedEmail] email is empty");
        }
    }

    public function sendLeaveStatusChangedEmail($employee, $leave)
    {

        $emp = $this->getEmployeeById($leave->employee);

        $params = array();
        $params['name'] = $emp->first_name." ".$emp->last_name;
        $params['startdate'] = $leave->date_start;
        $params['enddate'] = $leave->date_end;
        $params['status'] = $leave->status;

        $email = $this->subActionManager->getEmailTemplate('leaveStatusChanged.html', $this->modulePath);

        $user = $this->subActionManager->getUserFromProfileId($emp->id);

        $emailTo = null;
        if (!empty($user)) {
            $emailTo = $user->email;
        }

        if (!empty($emailTo)) {
            if (!empty($this->emailSender)) {
                $this->emailSender->sendEmail("Leave Application ".$leave->status, $emailTo, $email, $params);
            }
        } else {
            LogManager::getInstance()->info("[sendLeaveStatusChangedEmail] email is empty");
        }
    }

    /**
     * @param EmployeeLeave $employeeLeave
     * @return \Classes\IceResponse
     */
    protected function getLeaveDaysData(EmployeeLeave $employeeLeave)
    {
        $leaveActionManager = new LeavesActionManager();
        $leaveActionManager->setBaseService(BaseService::getInstance());
        $req = new \stdClass();
        $req->leave_id = $employeeLeave->id;

        return $leaveActionManager->getLeaveDaysReadonly($req);
    }

    /**
     * @param \Classes\IceResponse $leaveDaysData
     * @return string
     */
    protected function getHtmlLeaveDaysTable($leaveDaysData)
    {
        $table = '<table>
                   <thead><tr><th>Leave Date</th><th>Leave Type</th></tr></thead><tbody>_days_</tbody></table> ';

        $row = '<tr><td>_date_</td><td>_type_</td></tr>';

        $days = $leaveDaysData->data[0];
        $rows = '';
        $trow = '';

        for ($i = 0; $i < sizeof($days); $i++) {
            $trow = $row;
            $trow = str_replace('_date_', $days[$i]->leave_date, $trow);
            $trow = str_replace('_type_', $days[$i]->leave_type, $trow);
            $rows .= $trow;
        }

        $table = str_replace('_days_', $rows, $table);
        return $table;
    }
}
