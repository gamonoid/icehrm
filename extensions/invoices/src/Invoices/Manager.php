<?php

namespace Invoices;

use Classes\IceResponse;
use Classes\SubActionManager;

class Manager extends SubActionManager
{
    public function printInvoice($req)
    {
        $id = $req->id;
        return new IceResponse(IceResponse::SUCCESS, true);
    }
}