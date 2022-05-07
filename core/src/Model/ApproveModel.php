<?php
namespace Model;

use Classes\Approval\ApprovalStatus;
use Classes\BaseService;
use Classes\IceResponse;
use Classes\SettingsManager;
use Employees\Common\Model\EmployeeApproval;
use Travel\Common\Model\EmployeeTravelRecord;

abstract class ApproveModel extends BaseModel
{

    public function isMultiLevelApprovalsEnabled()
    {
        return false;
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
                $notificationMsg = "A new "
                    .$this->notificationUnitName." has been added by "
                    . $employee->first_name . " " . $employee->last_name
                    . ". Please visit ".$this->notificationModuleName." module to review it";

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
                    $notificationMsg = 'Your '.$this->notificationUnitName
                        .' is auto approved since you are an administrator and do not have any supervisor assigned';
                    BaseService::getInstance()->notificationManager->addNotification(
                        null,
                        $notificationMsg,
                        '{"type":"url","url":"'.$this->notificationUnitAdminUrl.'"}',
                        $this->notificationModuleName,
                        $user->id,
                        false,
                        $sendNotificationEmail
                    );
                } else {
                    //If the user do not have a supervisor, notify all admins
                    $admins = BaseService::getInstance()->getAllAdmins();
                    foreach ($admins as $admin) {
                        $notificationMsg = 'A new '.$this->notificationUnitName.' has been added by '
                            .$employee->first_name . ' ' . $employee->last_name . '. Please visit '
                            .$this->notificationModuleName
                            .' module to review it. You are getting this notification since you are an '
                            .'administrator and the user do not have any supervisor assigned.';
                        BaseService::getInstance()->notificationManager->addNotification(
                            null,
                            $notificationMsg,
                            '{"type":"url","url":"'.$this->notificationUnitAdminUrl.'"}',
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

        $travelRequest = new EmployeeTravelRecord();
        $travelRequest->Load('id = ?', array($obj->id));

        $needToApprove = false;
        if ($preApprove != "1") {
            foreach ($fieldsToCheck as $field) {
                if ($obj->$field != $travelRequest->$field) {
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
                    $notificationMsg = $this->notificationUnitPrefix." "
                        .$this->notificationUnitName." has been updated by "
                        .$employee->first_name . " " . $employee->last_name
                        .". Please visit ".$this->notificationModuleName." module to review it";

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
                        //If the user do not have a supervisor, notify all admins
                        $admins = BaseService::getInstance()->getAllAdmins();
                        foreach ($admins as $admin) {
                            $notificationMsg = $this->notificationUnitPrefix.' '
                                .$this->notificationUnitName.' request has been updated by '
                                .$employee->first_name . ' ' . $employee->last_name
                                .'. Please visit '.$this->notificationModuleName
                                .' module to review it. You are getting this notification since you are '
                                .'an administrator and the user do not have any supervisor assigned.';
                            BaseService::getInstance()->notificationManager->addNotification(
                                null,
                                $notificationMsg,
                                '{"type":"url","url":"g=admin&n=travel&m=admin_Employees"}',
                                'Travel Module',
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
