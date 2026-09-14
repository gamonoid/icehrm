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
        \Classes\Macaw::get(
            REST_API_PATH.'meta/currency',
            function () {
                $restEndPoint = new MetadataRestEndPoint();
                $restEndPoint->process('getCurrency', []);
            }
        );

        \Classes\Macaw::get(
            REST_API_PATH.'meta/countries',
            function () {
                $restEndPoint = new MetadataRestEndPoint();
                $restEndPoint->process('getCountries', []);
            }
        );

        \Classes\Macaw::get(
            REST_API_PATH.'meta/mobile-modules',
            function () {
                $restEndPoint = new MetadataRestEndPoint();
                $restEndPoint->process('getMobileModules', []);
            }
        );

        \Classes\Macaw::get(
            REST_API_PATH.'meta/extensions',
            function () {
                $restEndPoint = new MetadataRestEndPoint();
                $restEndPoint->process('getExtensions', []);
            }
        );

        // ---- Admin config / master-data CRUD (whitelisted models) ----
        // List the manageable config models
        \Classes\Macaw::get(
            REST_API_PATH.'meta/config',
            function () {
                (new \Metadata\Rest\AdminConfigRestEndPoint())->process('listModels', []);
            }
        );
        // Describe a config model's fields (register before the list route so
        // '{model}/describe' is matched by its own handler)
        \Classes\Macaw::get(
            REST_API_PATH.'meta/config/(:any)/describe',
            function ($model) {
                (new \Metadata\Rest\AdminConfigRestEndPoint())->process('describeConfig', $model);
            }
        );
        // List rows of a config model
        \Classes\Macaw::get(
            REST_API_PATH.'meta/config/(:any)',
            function ($model) {
                (new \Metadata\Rest\AdminConfigRestEndPoint())->process('listConfig', $model);
            }
        );
        // Create / update a config row
        \Classes\Macaw::post(
            REST_API_PATH.'meta/config/(:any)',
            function ($model) {
                (new \Metadata\Rest\AdminConfigRestEndPoint())->process('saveConfig', $model);
            }
        );
        // Delete a config row
        \Classes\Macaw::delete(
            REST_API_PATH.'meta/config/(:any)/(:num)',
            function ($model, $id) {
                (new \Metadata\Rest\AdminConfigRestEndPoint())->process('deleteConfig', [$model, $id]);
            }
        );
    }
}
