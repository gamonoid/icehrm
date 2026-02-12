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
use Classes\PasswordManager;
use Classes\SettingsManager;
use Classes\SubActionManager;
use Company\Common\Model\CompanyStructure;
use Employees\Common\Model\Employee;
use Users\Common\Model\User;
use Utils\LogManager;
use Utils\SessionUtils;

class EmployeesActionManager extends SubActionManager
{
    public function get($req)
    {
        $profileId = $this->getCurrentProfileId();
        $cemp = $profileId;
        $obj = new Employee();

        $cempObj = new Employee();
        $cempObj->Load("id = ?", array($cemp));

        if ($this->user->user_level == 'Admin') {
            $id = $req->id;
        } elseif ($obj->getUserOnlyMeAccessField() == 'id'
            && SettingsManager::getInstance()->getSetting('System: Company Structure Managers Enabled') == 1
            && CompanyStructure::isHeadOfCompanyStructure($cempObj->department, $cemp)
        ) {
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
            LogManager::getInstance()->notifyException($e);
        }

		$fs = FileService::getInstance();
		$employee = $fs->updateSmallProfileImage($employee);

		// Add supervisor image if supervisor exists
		if ( isset($employee->supervisor)) {
			$supervisor = new Employee();
			$supervisor->Load("id = ?", [$employee->supervisor]);
			$supervisor = $fs->updateSmallProfileImage($supervisor);
			if (!empty($supervisor->id) && !empty($supervisor->image)) {
				$employee->supervisor_image = $supervisor->image;
			}
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
		$csrf = SessionUtils::getSessionObject('csrf-password');
		if (empty($csrf) || $csrf !== $req->csrf) {
			return new IceResponse(
				IceResponse::ERROR,
				"Error validating CSRF token."
			);
		}

        if ($this->getCurrentProfileId() != $this->user->employee || empty($this->user->employee)) {
            return new IceResponse(IceResponse::ERROR, "You are not allowed to change passwords of other employees");
        }

        $user = new User();
        $user->Load("id = ?", array($this->user->id));
        if (empty($user->id)) {
            return new IceResponse(IceResponse::ERROR, "Error occurred while changing password");
        }

        if (!PasswordManager::verifyPassword($req->current, $user->password)) {
            return new IceResponse(IceResponse::ERROR, "Current password is incorrect");
        }

        $passwordStrengthResponse = PasswordManager::isQualifiedPassword($req->pwd);
        if ($passwordStrengthResponse->getStatus() === IceResponse::ERROR) {
            return $passwordStrengthResponse;
        }

        $user->password = PasswordManager::createPasswordHash($req->pwd);
        $ok = $user->Save();
        if (!$ok) {
            return new IceResponse(IceResponse::ERROR, $user->ErrorMsg());
        }

        return new IceResponse(IceResponse::SUCCESS, []);
    }

    public function getLoginCode($req)
    {
        $url = sprintf(
            'https://icehrm.com/sapi/login-code?url=%s&token=%s',
            $req->url,
            $req->token
        );

        $arrContextOptions = [
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        ];

        $data = file_get_contents($url, false, stream_context_create($arrContextOptions));

        return new IceResponse(IceResponse::SUCCESS, json_decode($data, true));
    }
}
