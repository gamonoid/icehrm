<?php
namespace Model;

use Classes\Approval\ApprovalStatus;
use Classes\BaseService;
use Classes\IceResponse;
use Classes\SettingsManager;
use Employees\Common\Model\EmployeeApproval;

abstract class ApproveModel extends BaseModel
{

    public function isMultiLevelApprovalsEnabled()
    {
        return false;
    }

    /** "an overtime request", "a travel request" (from $notificationUnitName). */
    public function notificationUnitWithArticle()
    {
        $unit = (string) $this->notificationUnitName;
        return (preg_match('/^[aeiou]/i', $unit) ? 'an ' : 'a ') . $unit;
    }

    /**
     * Where admins review requests from employees without a supervisor. Defaults to
     * the supervisor's page; models override it with their admin module.
     */
    public function getNotificationAdminUrl()
    {
        return $this->notificationUnitAdminUrl;
    }

    protected static function employeeFullName($employee)
    {
        return trim($employee->first_name . ' ' . $employee->last_name);
    }

    public function executePreSaveActions($obj)
    {
        $preApprove = SettingsManager::getInstance()->getSetting($this->preApproveSettingName);
        $sendNotificationEmail = true;
        if (empty($obj->status)) {
            if ($preApprove == "1") {
                $obj->status = "Approved";
                $sendNotificationEmail = false;
            } else {
                $obj->status = "Pending";
            }
        }

        if ($preApprove) {
            return new IceResponse(IceResponse::SUCCESS, $obj);
        }

        $currentEmpId = BaseService::getInstance()->getCurrentProfileId();

        //Auto approve if the current user is an admin

        if (!empty($currentEmpId)) {
            $employee = BaseService::getInstance()->getElement('Employee', $currentEmpId, null, true);

            if (!empty($employee->supervisor)) {
                $notificationMsg = sprintf(
                    '%s submitted %s for your approval.',
                    self::employeeFullName($employee),
                    $this->notificationUnitWithArticle()
                );

                BaseService::getInstance()->notificationManager->addNotification(
                    $employee->supervisor,
                    $notificationMsg,
                    '{"type":"url","url":"'.$this->notificationUnitAdminUrl.'"}',
                    $this->notificationModuleName,
                    null,
                    false,
                    $sendNotificationEmail
                );
            } else {
                $user = BaseService::getInstance()->getCurrentUser();

                if ($user->user_level == "Admin") {
                    //Auto approve
                    $obj->status = 'Approved';
                    $notificationMsg = sprintf(
                        'Your %s was approved automatically because you are an administrator '
                        . 'with no supervisor assigned.',
                        $this->notificationUnitName
                    );
                    BaseService::getInstance()->notificationManager->addNotification(
                        null,
                        $notificationMsg,
                        '{"type":"url","url":"'.$this->getNotificationAdminUrl().'"}',
                        $this->notificationModuleName,
                        $user->id,
                        false,
                        $sendNotificationEmail
                    );
                } else {
                    //If the user does not have a supervisor, notify all admins
                    $admins = BaseService::getInstance()->getAllAdmins();
                    $notificationMsg = sprintf(
                        '%s submitted %s for your approval. You are receiving this because %s '
                        . 'has no supervisor assigned.',
                        self::employeeFullName($employee),
                        $this->notificationUnitWithArticle(),
                        $employee->first_name
                    );
                    foreach ($admins as $admin) {
                        BaseService::getInstance()->notificationManager->addNotification(
                            null,
                            $notificationMsg,
                            '{"type":"url","url":"'.$this->getNotificationAdminUrl().'"}',
                            $this->notificationModuleName,
                            $admin->id,
                            false,
                            $sendNotificationEmail
                        );
                    }
                }
            }
        }

        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function executePreUpdateActions($obj)
    {

        $preApprove = SettingsManager::getInstance()->getSetting($this->preApproveSettingName);
        $sendNotificationEmail = true;

        $fieldsToCheck = $this->fieldsNeedToBeApproved();

        // Compare against the stored record of the same model (overtime, travel, expense...).
        $modelClass = get_class($this);
        $savedRecord = new $modelClass();
        $savedRecord->Load('id = ?', array($obj->id));

        $needToApprove = false;
        if ($preApprove != "1") {
            foreach ($fieldsToCheck as $field) {
                if ($obj->$field != $savedRecord->$field) {
                    $needToApprove = true;
                    break;
                }
            }
        } else {
            $sendNotificationEmail = false;
        }

        if ($preApprove) {
            return new IceResponse(IceResponse::SUCCESS, $obj);
        }

        if ($needToApprove && $obj->status != 'Pending') {
            $currentEmpId = BaseService::getInstance()->getCurrentProfileId();

            //Auto approve if the current user is an admin

            if (!empty($currentEmpId)) {
                $employee = BaseService::getInstance()->getElement('Employee', $currentEmpId, null, true);

                if (!empty($employee->supervisor)) {
                    $notificationMsg = sprintf(
                        '%s updated %s, and it needs your approval again.',
                        self::employeeFullName($employee),
                        $this->notificationUnitWithArticle()
                    );

                    BaseService::getInstance()->notificationManager->addNotification(
                        $employee->supervisor,
                        $notificationMsg,
                        '{"type":"url","url":"'.$this->notificationUnitAdminUrl.'"}',
                        $this->notificationModuleName,
                        null,
                        false,
                        $sendNotificationEmail
                    );
                } else {
                    $user = BaseService::getInstance()->getCurrentUser();

                    if ($user->user_level == 'Admin') {
                    } else {
                        //If the user does not have a supervisor, notify all admins
                        $admins = BaseService::getInstance()->getAllAdmins();
                        $notificationMsg = sprintf(
                            '%s updated %s, and it needs your approval again. You are receiving this '
                            . 'because %s has no supervisor assigned.',
                            self::employeeFullName($employee),
                            $this->notificationUnitWithArticle(),
                            $employee->first_name
                        );
                        foreach ($admins as $admin) {
                            BaseService::getInstance()->notificationManager->addNotification(
                                null,
                                $notificationMsg,
                                '{"type":"url","url":"'.$this->getNotificationAdminUrl().'"}',
                                $this->notificationModuleName,
                                $admin->id,
                                false,
                                $sendNotificationEmail
                            );
                        }
                    }
                }
            }
        }

        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function executePostSaveActions($obj)
    {
        $directAppr = ApprovalStatus::getInstance()->isDirectApproval($obj->employee);

        if (!$directAppr && $this->isMultiLevelApprovalsEnabled()) {
            $classPaths = explode("\\", get_called_class());
            ApprovalStatus::getInstance()->initializeApprovalChain($classPaths[count($classPaths) - 1], $obj->id);
        }
    }

    abstract public function getType();

    public function findApprovals($obj, $whereOrderBy, $bindarr = false, $pkeysArr = false, $extra = array())
    {
        $currentEmployee = BaseService::getInstance()->getCurrentProfileId();
        $approveal = new EmployeeApproval();
        $approveals = $approveal->Find(
            "type = ? and approver = ? and status = -1 and active = 1",
            array($this->getType(), $currentEmployee)
        );
        $ids = array();
        foreach ($approveals as $appr) {
            $ids[] = $appr->element;
        }

        if (empty($ids)) {
            return [];
        }

        $data = $obj->Find("id in (".implode(",", $ids).")", array());

        return $data;
    }
}
