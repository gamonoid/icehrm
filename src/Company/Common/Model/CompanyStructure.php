<?php
namespace Company\Common\Model;

use Classes\IceResponse;
use Model\BaseModel;

class CompanyStructure extends BaseModel
{
    public $table = 'CompanyStructures';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }

    public function validateSave($obj)
    {
        if ($obj->id == $obj->parent && !empty($obj->parent)) {
            return new IceResponse(
                IceResponse::ERROR,
                "A Company structure unit can not be the parent of the same unit"
            );
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
