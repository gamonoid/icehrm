<?php
namespace Classes\Common;

use Pimple\Container;

class IceContainer extends Container
{
    public function get($serviceName)
    {
        return $this[$serviceName];
    }

    public function set($serviceName, $callback)
    {
        $this[$serviceName] = $callback;
    }
}
