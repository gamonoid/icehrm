<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 10:52 AM
 */

namespace Employees\User\Api;

use Classes\BaseService;
use Classes\FileService;
use Classes\IceResponse;
use Classes\SettingsManager;
use Classes\SubActionManager;
use Company\Common\Model\CompanyStructure;
use Employees\Common\Model\Employee;
use Users\Common\Model\User;

class EmployeesActionManager extends SubActionManager
{
    public function get($req)
    {
        $profileId = $this->getCurrentProfileId();
        $cemp = $profileId;
        $obj = new Employee();

        $cempObj = new Employee();
        $cempObj->Load("id = ?", array($cemp));
        if ($obj->getUserOnlyMeAccessField() == 'id' &&
            SettingsManager::getInstance()->getSetting('System: Company Structure Managers Enabled') == 1 &&
            CompanyStructure::isHeadOfCompanyStructure($cempObj->department, $cemp)) {
            $subordinates = $obj->Find("supervisor = ?", array($cemp));

            if (empty($subordinates)) {
                $subordinates = array();
            }

            $childCompaniesIds = array();
            if (SettingsManager::getInstance()->getSetting('System: Child Company Structure Managers Enabled') == '1') {
                $childCompaniesResp = CompanyStructure::getAllChildCompanyStructures($cempObj->department);
                $childCompanies = $childCompaniesResp->getObject();

                foreach ($childCompanies as $cc) {
                    $childCompaniesIds[] = $cc->id;
                }
            } else {
                $childCompaniesIds[] = $cempObj->department;
            }

            if (!empty($childCompaniesIds)) {
                $childStructureSubordinates
                    = $obj->Find(
                        "department in (" . implode(',', $childCompaniesIds) . ") and id != ?",
                        array($cemp)
                    );
                $subordinates = array_merge($subordinates, $childStructureSubordinates);
            }

            foreach ($subordinates as $subordinate) {
                if ($subordinate->id == $req->id) {
                    $id = $req->id;
                    break;
                }
            }
        } else {
            $subordinate = new Employee();
            $subordinatesCount = $subordinate->Count("supervisor = ? and id = ?", array($profileId, $req->id));

            if ($this->user->user_level == 'Admin' || $subordinatesCount > 0) {
                $id = $req->id;
            }
        }

        if (empty($id)) {
            $id = $profileId;
        }

        $employee = $this->baseService->getElement('Employee', $id, $req->map, true);

        $subordinate = new Employee();
        $subordinates = $subordinate->Find("supervisor = ?", array($employee->id));
        $employee->subordinates = $subordinates;

        $fs = FileService::getInstance();
        $employee = $fs->updateProfileImage($employee);

        if (!empty($employee->birthday)) {
            $employee->birthday = date("F jS, Y", strtotime($employee->birthday));
        }

        if (!empty($employee->driving_license_exp_date)) {
            $employee->driving_license_exp_date = date("F jS, Y", strtotime($employee->driving_license_exp_date));
        }

        if (!empty($employee->joined_date)) {
            $employee->joined_date = date("F jS, Y", strtotime($employee->joined_date));
        }

        //Read custom fields
        try {
            $employee = BaseService::getInstance()->customFieldManager->enrichObjectCustomFields('Employee', $employee);
        } catch (\Exception $e) {
        }

        if (empty($employee->id)) {
            return new IceResponse(IceResponse::ERROR, $employee);
        }
        return new IceResponse(
            IceResponse::SUCCESS,
            array($employee,$this->getCurrentProfileId(),$this->user->employee)
        );
    }

    public function deleteProfileImage($req)
    {

        $profileId = $this->getCurrentProfileId();
        $subordinate = new Employee();
        $subordinatesCount = $subordinate->Count("supervisor = ? and id = ?", array($profileId, $req->id));

        if ($this->user->user_level == 'Admin' || $this->user->employee == $req->id || $subordinatesCount == 1) {
            $fs = FileService::getInstance();
            $res = $fs->deleteProfileImage($req->id);
            return new IceResponse(IceResponse::SUCCESS, $res);
        }

        return new IceResponse(IceResponse::ERROR, "Not allowed to delete profile image");
    }

    public function changePassword($req)
    {

        if ($this->getCurrentProfileId() != $this->user->employee || empty($this->user->employee)) {
            return new IceResponse(IceResponse::ERROR, "You are not allowed to change passwords of other employees");
        }

        $user = new User();
        $user->Load("id = ?", array($this->user->id));
        if (empty($user->id)) {
            return new IceResponse(IceResponse::ERROR, "Error occurred while changing password");
        }
        $user->password = md5($req->pwd);
        $ok = $user->Save();
        if (!$ok) {
            return new IceResponse(IceResponse::ERROR, $user->ErrorMsg());
        }
        return new IceResponse(IceResponse::SUCCESS, $user);
    }
}
