<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 7:58 AM
 */

namespace Training\User\Api;

use Classes\IceConstants;
use Classes\IceResponse;
use Classes\SubActionManager;
use Training\Common\Model\EmployeeTrainingSession;
use Training\Common\Model\TrainingSession;
use Utils\LogManager;

class TrainingActionManager extends SubActionManager
{

    public function signup($req)
    {
        $empId = $this->getCurrentProfileId();
        $employee = $this->baseService->getElement('Employee', $empId, null, true);

        $trainingSession = new TrainingSession();
        $trainingSession->Load("id = ?", array($req->sessionId));

        if ($trainingSession->id != $req->sessionId) {
            return new IceResponse(IceResponse::ERROR, "Training session not found");
        }

        if ($trainingSession->status != "Approved") {
            return new IceResponse(IceResponse::ERROR, "Training session is ".$trainingSession->status);
        }

        $employeeTrainingSession = new EmployeeTrainingSession();
        $employeeTrainingSession->Load("trainingSession = ? and employee = ?", array($trainingSession->id, $empId));

        if (!empty($employeeTrainingSession->id)) {
            return new IceResponse(IceResponse::ERROR, "You have already signed up to this training session");
        }

        $employeeTrainingSession->trainingSession = $trainingSession->id;
        $employeeTrainingSession->employee = $empId;
        $employeeTrainingSession->status = "Scheduled";
        $ok = $employeeTrainingSession->Save();

        if (!$ok) {
            LogManager::getInstance()->info(
                "Error saving employee trainig session :".$employeeTrainingSession->ErrorMsg()
            );
            return new IceResponse(IceResponse::ERROR, "Error occurred, please contact admin");
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            "You have successfully signed up to training session :".$trainingSession->name
        );
    }

    public function sessionAttended($req)
    {
        $empId = $this->getCurrentProfileId();
        $employee = $this->baseService->getElement('Employee', $empId, null, true);

        $trainingSession = new EmployeeTrainingSession();
        $trainingSession->Load("id = ?", array($req->sessionId));

        $trainingEmployee = $this->baseService->getElement('Employee', $trainingSession->employee, null, true);

        //check proof of completion
        $ts = new TrainingSession();
        $ts->Load("id = ?", array($trainingSession->trainingSession));
        if ($ts->requireProof == "Yes" && empty($trainingSession->proof)) {
            return new IceResponse(
                IceResponse::ERROR,
                "This training session requires a proof to be attached. 
                Please provide a proof of completion before submitting for review."
            );
        }

        //===================


        //Security check
        $securityCheck = false;
        if ($this->user->user_level == 'Admin') {
            $securityCheck = true;
        } elseif ($trainingSession->employee == $employee->id) {
            $securityCheck = true;
        } elseif ($empId == $trainingEmployee->supervisor) {
            $securityCheck = true;
        }

        if (!$securityCheck) {
            return new IceResponse(IceResponse::ERROR, "Not allowed");
        }

        LogManager::getInstance()->info("Training Session :".json_encode($trainingSession));

        if ($trainingSession->status == "Scheduled") {
            $trainingSession->status = 'Attended';
            $ok = $trainingSession->Save();

            $ts = new TrainingSession();
            $ts->Load($trainingSession->trainingSession);

            if ($ok) {
                $this->baseService->audit(
                    IceConstants::AUDIT_ACTION,
                    "Training Attended \ name:".$trainingSession->name."\ id:".$trainingSession->id
                );

                $notificationMsg = $employee->first_name." ".$employee->last_name
                    ." changed the status of training (".$ts->name.") to attended. Please approve";

                $this->baseService->notificationManager->addNotification(
                    $employee->supervisor,
                    $notificationMsg,
                    '{"type":"url","url":"g=modules&n=training&m=module_Training#tabSubEmployeeTraining"}',
                    "Training Module"
                );
            }

            return new IceResponse(IceResponse::SUCCESS, $trainingSession);
        }

        return new IceResponse(IceResponse::ERROR, "");
    }

    public function sessionCompleted($req)
    {
        $empId = $this->getCurrentProfileId();
        $employee = $this->baseService->getElement('Employee', $empId, null, true);

        $trainingSession = new EmployeeTrainingSession();
        $trainingSession->Load("id = ?", array($req->sessionId));

        $trainingEmployee = $this->baseService->getElement('Employee', $trainingSession->employee, null, true);

        //Security check
        $securityCheck = false;
        if ($this->user->user_level == 'Admin') {
            $securityCheck = true;
        } elseif ($empId == $trainingEmployee->supervisor) {
            $securityCheck = true;
        }

        if (!$securityCheck) {
            return new IceResponse(IceResponse::ERROR, "Not allowed");
        }

        LogManager::getInstance()->info("Training Session :".json_encode($trainingSession));

        if ($trainingSession->status == "Attended") {
            $trainingSession->status = 'Completed';
            $ok = $trainingSession->Save();

            $ts = new TrainingSession();
            $ts->Load($trainingSession->trainingSession);

            if ($ok) {
                $this->baseService->audit(
                    IceConstants::AUDIT_ACTION,
                    "Training Completed \ name:".$trainingSession->name."\ id:".$trainingSession->id
                );

                $notificationMsg = $employee->first_name." ".$employee->last_name
                    ." approved the attendance status of training (".$ts->name.")";

                $this->baseService->notificationManager->addNotification(
                    $employee->supervisor,
                    $notificationMsg,
                    '{"type":"url","url":"g=modules&n=training&m=module_Training#tabEmployeeTrainingSession"}',
                    "Training Module"
                );
            }

            return new IceResponse(IceResponse::SUCCESS, $trainingSession);
        }

        return new IceResponse(IceResponse::ERROR, "");
    }
}
