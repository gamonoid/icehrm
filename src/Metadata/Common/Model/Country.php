<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 3:00 PM
 */

namespace Metadata\Common\Model;

use Classes\SettingsManager;
use Model\BaseModel;

class Country extends BaseModel
{
    public $table = 'Country';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getAnonymousAccess()
    {
        return array("get","element");
    }

    // @codingStandardsIgnoreStart
    function Find($whereOrderBy, $bindarr = false, $pkeysArr = false, $extra = array())
    {
        $allowedCountriesStr = SettingsManager::getInstance()->getSetting('System: Allowed Countries');
        $allowedCountries = array();
        if (!empty($allowedCountriesStr)) {
            $allowedCountries = json_decode($allowedCountriesStr, true);
        }

        if (!empty($allowedCountries)) {
            $res =  parent::Find("id in (".implode(",", $allowedCountries).")", array());
            if (empty($res)) {
                SettingsManager::getInstance()->setSetting('System: Allowed Countries', '');
            } else {
                return $res;
            }
        }

        return parent::Find($whereOrderBy, $bindarr, $pkeysArr, $extra);
    }
    // @codingStandardsIgnoreEnd
}
