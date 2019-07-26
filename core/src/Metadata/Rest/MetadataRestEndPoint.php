<?php
namespace Metadata\Rest;

use Classes\BaseService;
use Classes\Data\Query\DataQuery;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Modules\Common\Model\Module;
use Users\Common\Model\User;

class MetadataRestEndPoint extends RestEndPoint
{
    public function getCurrency(User $user)
    {
        $query = new DataQuery('CurrencyType');
        $query->setLength(500);
        return $this->listByQuery($query);
    }

    public function getCountries(User $user)
    {
        $query = new DataQuery('Country');
        $query->setLength(500);
        return $this->listByQuery($query);
    }

    public function getMobileModules(User $user)
    {
        $mobileModules = [
            'leaves' => false,
            'attendance' => false,
            'staffdirectory' => false,
            'expenses' => false,
        ];

        foreach ($mobileModules as $key => $value) {
            $mobileModules[$key] = $this->isUserModuleEnabled($key);
        }

        return new IceResponse(IceResponse::SUCCESS, ['data' => $mobileModules]);
    }

    private function isUserModuleEnabled($name)
    {
        $module = new Module();
        $modules = $module->Find('name = ? and mod_group = ? and status = ?', [$name, 'user', 'Enabled']);

        BaseService::getInstance()->initializePro();

        return count($modules) > 0 && BaseService::getInstance()->isModuleEnabled('modules', $name);
    }
}
