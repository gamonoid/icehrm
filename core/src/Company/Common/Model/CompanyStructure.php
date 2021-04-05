<?php
namespace Company\Common\Model;

use Classes\IceResponse;
use Classes\ModuleAccess;
use Employees\Common\Model\Employee;
use Model\BaseModel;
use Model\CustomFieldTrait;

class CompanyStructure extends BaseModel
{
    use CustomFieldTrait;
    public $objectName = 'Company Structures';
    protected $allowCustomFields = true;

    public $table = 'CompanyStructures';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('employees', 'user'),
        ];
    }

    public function validateSave($obj)
    {
        if ($obj->id == $obj->parent && !empty($obj->parent)) {
            return new IceResponse(
                IceResponse::ERROR,
                "A Company structure unit can not be the parent of the same unit"
            );
        }

        $heads = json_decode($obj->heads);
        foreach ($heads as $head) {
            $employee = new Employee();
            $employee->Load('id = ?', array($head));
            if (!empty($obj->id) && $employee->department != $obj->id) {
                $companyStructure = new CompanyStructure();
                $companyStructure->Load("id = ?", array($employee->department));

                return new IceResponse(
                    IceResponse::ERROR,
                    "An employee who is not attached to a company structure can not be the 
                    head of the company structure. ".
                    "Please remove ".$employee->first_name.' '.$employee->last_name
                    ." from list of heads as this person is attached to ".
                    $companyStructure->title
                );
            }
        }

        return new IceResponse(IceResponse::SUCCESS, "");
    }

    public static function getAllChildCompanyStructures($companyStructureId)
    {
        $structures = array();
        $companyStructure = new CompanyStructure();
        $companyStructure->Load("id = ?", array($companyStructureId));

        if ($companyStructure->id != $companyStructureId || empty($companyStructure->id)) {
            return new IceResponse(IceResponse::ERROR, array());
        }

        self::getChildCompanyStructures($companyStructure, $structures);

        $structures[$companyStructure->id] = $companyStructure;

        return new IceResponse(IceResponse::SUCCESS, array_values($structures));
    }

    private static function getChildCompanyStructures($companyStructure, &$structures)
    {
        $child = new CompanyStructure();
        $children = $child->Find("parent = ?", array($companyStructure->id));
        if (!empty($children)) {
            foreach ($children as $c) {
                if (isset($structures[$c->id])) {
                    continue;
                }
                $structures[$c->id] = $c;
                self::getChildCompanyStructures($c, $structures);
            }
        }
    }

    public static function isHeadOfCompanyStructure($companyStructureId, $profileId)
    {
        $companyStructure = new CompanyStructure();
        $companyStructure->Load("id = ?", array($companyStructureId));

        if (isset($companyStructure->heads) && !empty($companyStructure->heads)) {
            $heads = json_decode($companyStructure->heads);
            if (is_array($heads) && !empty($heads) && in_array($profileId, $heads)) {
                return true;
            }
        }

        return false;
    }
}
