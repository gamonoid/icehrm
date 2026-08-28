<?php

namespace KudosAdmin;

use Classes\IceController;
use Classes\IceResponse;

class Controller extends IceController
{
    public function testAction($req): IceResponse
    {
        return new IceResponse(IceResponse::SUCCESS, 'Echo from server: '.$req->data);
    }
}

