<?php

namespace InboxUser;

use Classes\BaseService;
use Classes\IceExtension;
use InboxUser\Migrations\CreateTables;

class Extension extends IceExtension
{
    public function initialize()
    {
        BaseService::getInstance()->registerExtensionMigration(new CreateTables());
    }

    public function setupModuleClassDefinitions()
    {
        $this->addModelClass('InboxConversation');
        $this->addModelClass('InboxMessage');
    }

    public function setupRestEndPoints()
    {
        (new ApiController())->registerEndPoints();
    }
}
