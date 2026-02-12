<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 8:13 AM
 */

namespace Travel\Admin\Api;

use Classes\Approval\ApproveAdminActionManager;
use Classes\BaseService;
use Classes\IceResponse;
use Classes\FileService\FileService;
use Classes\StatusChangeLogManager;
use Employees\Common\Model\Employee;
use Travel\Common\Model\TravelProject;
use CompanyStructure\Common\Model\CompanyStructure;
use Classes\Migration\Model\JobTitle;

class TravelActionManager extends ApproveAdminActionManager
{

    public function getModelClass()
    {
        return "EmployeeTravelRecord";
    }

    public function getItemName()
    {
        return "TravelRequest";
    }

    public function getModuleName()
    {
        return "Travel Management";
    }

    public function getModuleTabUrl()
    {
        return "g=modules&n=travel&m=module_Travel_Management#tabEmployeeTravelRecord";
    }

    public function getModuleSubordinateTabUrl()
    {
        return "g=modules&n=travel&m=module_Travel_Management#tabSubordinateEmployeeTravelRecord";
    }

    public function getModuleApprovalTabUrl()
    {
        return "g=modules&n=travel&m=module_Travel_Management#tabEmployeeTravelRecordApproval";
    }

    public function getLogs($req)
    {
        return parent::getLogs($req);
    }

    public function changeStatus($req)
    {
        return parent::changeStatus($req);
    }

    public function viewFullTravelRequest($req)
    {
        $user = BaseService::getInstance()->getCurrentUser();

        // Get travel request with mapped fields
        $map = '{"employee":["Employee","id","first_name+last_name"]'
            .',"project_code":["TravelProject","code","name"]'
            .',"currency":["CurrencyType","id","code"]}';

        $travelRequest = BaseService::getInstance()->getElement(
            'EmployeeTravelRecord',
            $req->id,
            $map
        );

        if (empty($travelRequest)) {
            return new IceResponse(IceResponse::ERROR, "Travel request not found");
        }

        // Get employee details with profile image
        $employee = new Employee();
        $employee->Load('id = ?', [$travelRequest->employee]);
        $employee = \Classes\FileService::getInstance()->updateSmallProfileImage($employee);

        // Get job title name
        $jobTitleName = '';
        if (!empty($employee->job_title)) {
            $jobTitle = new \Jobs\Common\Model\JobTitle();
            $jobTitle->Load('id = ?', [$employee->job_title]);
            if (!empty($jobTitle->id)) {
                $jobTitleName = $jobTitle->name;
            }
        }

        // Get department name
        $departmentName = '';
        if (!empty($employee->department)) {
            $department = new \Company\Common\Model\CompanyStructure();
            $department->Load('id = ?', [$employee->department]);
            if (!empty($department->id)) {
                $departmentName = $department->title;
            }
        }

        // Get additional employee fields
        $employeeData = [
            'id' => $employee->id,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'job_title' => $jobTitleName,
            'department' => $departmentName,
            'work_email' => $employee->work_email,
            'image' => $employee->image,
        ];

        // Get currency code if currency is set
        if (!empty($travelRequest->currency)) {
            $currencyType = BaseService::getInstance()->getElement(
                'CurrencyType',
                $travelRequest->currency,
                null,
                true
            );
            if ($currencyType) {
                $travelRequest->currency_code = $currencyType->code;
            }
        }

        // Process travel_from and travel_to: replace country ID with country name
        foreach (['travel_from', 'travel_to'] as $field) {
            if (!empty($travelRequest->$field)) {
                $locationData = json_decode($travelRequest->$field, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($locationData)) {
                    // Valid JSON, check if country is an ID (numeric)
                    if (!empty($locationData['country']) && is_numeric($locationData['country'])) {
                        $country = new \Metadata\Common\Model\Country();
                        $country->Load('id = ?', [$locationData['country']]);
                        if (!empty($country->id)) {
                            $locationData['country'] = $country->name;
                            $travelRequest->$field = json_encode($locationData);
                        }
                    }
                }
            }
        }

        // Process attachments
        $attachments = [];
        for ($i = 1; $i <= 3; $i++) {
            $attachmentField = 'attachment' . $i;
            if (!empty($travelRequest->$attachmentField)) {
                $attachmentName = 'Attachment ' . $i;
                if ($i === 1) {
                    $attachmentName = 'Booking Confirmation';
                } elseif ($i === 2) {
                    $attachmentName = 'Visa/Insurance';
                } elseif ($i === 3) {
                    $attachmentName = 'Other Documents';
                }

                $attachments[] = [
                    'name' => $attachmentName,
                    'url' => \Classes\FileService::getInstance()->getFileUrl($travelRequest->$attachmentField),
                ];
            }
        }

        // Get status change logs
        $logsResponse = StatusChangeLogManager::getInstance()->getLogs(
            'EmployeeTravelRecord',
            $req->id
        );
        $statusLogs = $logsResponse->getStatus() === IceResponse::SUCCESS ? $logsResponse->getData() : [];

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'employee' => $employeeData,
                'travelRequest' => $travelRequest,
                'attachments' => $attachments,
                'statusLogs' => $statusLogs,
            ]
        );
    }
}
