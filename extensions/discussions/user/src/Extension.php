<?php

namespace DiscussionsUser;

use Classes\IceExtension;

class Extension extends IceExtension
{
    public function initialize()
    {
        // Tables are created by the admin part's migration.
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
