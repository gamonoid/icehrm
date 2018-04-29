<?php

namespace Consolidation\TestUtils;

use Consolidation\Config\Loader\ConfigLoaderInterface;

class TestLoader implements ConfigLoaderInterface
{
    protected $data;
    protected $sourceName;

    public function set($data)
    {
        $this->data = $data;
    }

    public function setSourceName($name)
    {
        $this->sourceName = $name;
    }

    public function export()
    {
        return $this->data;
    }

    public function keys()
    {
        return array_keys($this->data);
    }

    public function getSourceName()
    {
        return $this->sourceName;
    }
}
