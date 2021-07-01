<?php
namespace Invoices;

use Classes\IceExtension;

class Extension extends IceExtension
{

    public function install() {
        $migration = new Migration();
        return $migration->up();

    }

    public function uninstall() {
        $migration = new Migration();
        return $migration->down();

    }

    public function setupModuleClassDefinitions()
    {

    }

    public function setupRestEndPoints()
    {

    }
}