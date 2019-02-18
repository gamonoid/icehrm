<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 3:10 PM
 */

namespace Metadata\Admin\Api;

use Classes\AbstractModuleManager;
use Metadata\Rest\MetadataRestEndPoint;

class MetadataAdminManager extends AbstractModuleManager
{

    public function initializeUserClasses()
    {
    }

    public function initializeFieldMappings()
    {
    }

    public function initializeDatabaseErrorMappings()
    {
    }

    public function setupModuleClassDefinitions()
    {
        $this->addModelClass('Country');
        $this->addModelClass('Province');
        $this->addModelClass('CurrencyType');
        $this->addModelClass('Nationality');
        $this->addModelClass('ImmigrationStatus');
        $this->addModelClass('Ethnicity');
        $this->addModelClass('CalculationHook');
        $this->addModelClass('SupportedLanguage');
        $this->addModelClass('CustomFieldValue');
    }

    public function setupRestEndPoints()
    {
        \Classes\Macaw::get(REST_API_PATH.'meta/currency', function () {
            $restEndPoint = new MetadataRestEndPoint();
            $restEndPoint->process('getCurrency', []);
        });

        \Classes\Macaw::get(REST_API_PATH.'meta/countries', function () {
            $restEndPoint = new MetadataRestEndPoint();
            $restEndPoint->process('getCountries', []);
        });
    }
}
