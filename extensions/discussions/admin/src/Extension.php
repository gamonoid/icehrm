<?php

namespace DiscussionsAdmin;

use Classes\BaseService;
use Classes\IceExtension;
use DiscussionsAdmin\Migrations\CreatePublicChannel;
use DiscussionsAdmin\Migrations\CreateTables;

class Extension extends IceExtension
{
    public function initialize()
    {
        BaseService::getInstance()->registerExtensionMigration(new CreateTables());
        // Registered after CreateTables so the tables (and the Announcements seed)
        // exist first; both are idempotent.
        BaseService::getInstance()->registerExtensionMigration(new CreatePublicChannel());
    }

    public function setupModuleClassDefinitions()
    {
        $this->addModelClass('DiscussionChannel');
        $this->addModelClass('DiscussionPost');
        $this->addModelClass('DiscussionReply');
    }

    public function setupRestEndPoints()
    {
        (new ApiController())->registerEndPoints();
    }
}
