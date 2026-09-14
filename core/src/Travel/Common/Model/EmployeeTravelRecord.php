<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 8:05 AM
 */

namespace Travel\Common\Model;

use Classes\ModuleAccess;
use Classes\SettingsManager;
use Classes\FileService\FileService;
use Employees\Common\Model\Employee;
use Model\ApproveModel;
use Model\CustomFieldTrait;

class EmployeeTravelRecord extends ApproveModel
{
    use CustomFieldTrait;

    public $table = 'EmployeeTravelRecords';
    public $objectName = 'Travel Request';
    protected $allowCustomFields = true;

    public $notificationModuleName = "Travel Management";
    public $notificationUnitName = "TravelRequest";
    public $notificationUnitPrefix = "A";
    public $notificationUnitAdminUrl
        = "g=modules&n=travel&m=module_Travel_Management#tabSubordinateEmployeeTravelRecord";
    public $preApproveSettingName = "Travel: Pre-Approve Travel Request";

    public function isMultiLevelApprovalsEnabled()
    {
        return (SettingsManager::getInstance()->getSetting('Travel: Enable Multi Level Approvals') == '1');
    }

    public function getAdminAccess()
    {
        return array("get", "element", "add","save", "delete");
    }

    public function getManagerAccess()
    {
        return array("get", "element", "add","save", "delete");
    }

    public function getUserAccess()
    {
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array("element", "add","save", "delete");
    }

    /**
     * Mass-assignment guard: 'status' is set only by the approval workflow
     * (ApproveAdminActionManager::changeStatus, which Saves the model directly and does
     * NOT pass through addElement). Block it on the generic save/add path for non-admins
     * so the owner cannot self-approve by posting a=add&t=EmployeeTravelRecord with
     * status='Approved'. ApproveModel::executePreSaveActions only defaults status when it
     * is EMPTY, so without this a supplied value is written straight through.
     * Admins may still make manual corrections.
     */
    public function getProtectedFields($user)
    {
        if (!empty($user) && $user->user_level === 'Admin') {
            return array();
        }
        return array('status');
    }

    public function fieldsNeedToBeApproved()
    {
        return array(
            "travel_from",
            "travel_to",
            "travel_date",
            "return_date",
            "funding",
            "currency"
        );
    }

    public function getType()
    {
        return 'EmployeeTravelRecord';
    }

    public function allowIndirectMapping()
    {
        if (SettingsManager::getInstance()->getSetting('Travel: Allow Indirect Admins to Approve') == '1') {
            return true;
        }
        return false;
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('travel', 'admin'),
            new ModuleAccess('travel', 'user'),
        ];
    }

    public function postProcessGetData($entry)
    {
        // Add employee profile image
        $employee = new Employee();
        $employee->Load('id = ?', [$entry->employee]);
        $employee = \Classes\FileService::getInstance()->updateSmallProfileImage($employee);
        $entry->image = $employee->image;

        return $entry;
    }

    /**
     * A team list exists for this model: the adapter opts into `type=sub`
     * (isSubProfileTable), so BaseService::getData() may scope its rows to the
     * caller's direct reports. See BaseModel::allowsSubordinateList().
     */
    public function allowsSubordinateList()
    {
        return true;
    }

}
