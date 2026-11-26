<?php

namespace KudosUser;

use Classes\BaseService;
use Classes\IceExtension;
use KudosUser\Migrations\CreateTables;
use KudosUser\Migrations\KudosSettings;

/**
 * Kudos — peer recognition. This is the whole extension (there is no admin part):
 * everyone with the module posts, reads the feed and reacts; admins moderate.
 *
 * The Modules row for this extension may pre-date the code (it did on the
 * installation this was built on), in which case ExtensionManager reuses the row and
 * never calls install(). Everything that has to happen on first run — tables AND
 * seed values — therefore lives in the migration, which runs on the next login
 * regardless.
 */
class Extension extends IceExtension
{
    public function initialize()
    {
        BaseService::getInstance()->registerExtensionMigration(new CreateTables());
        BaseService::getInstance()->registerExtensionMigration(new KudosSettings());
    }

    public function setupModuleClassDefinitions()
    {
        $this->addModelClass('Kudos');
        $this->addModelClass('KudosValue');
    }

    public function setupRestEndPoints()
    {
        (new ApiController())->registerEndPoints();
    }
}
