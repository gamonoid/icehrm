<?php

namespace Consolidation\Config\Loader;

/**
 * Load configuration files.
 */
abstract class ConfigLoader implements ConfigLoaderInterface
{
    protected $config = [];
    protected $source = '';

    public function getSourceName()
    {
        return $this->source;
    }

    protected function setSourceName($source)
    {
        $this->source = $source;
        return $this;
    }

    public function export()
    {
        return $this->config;
    }

    public function keys()
    {
        return array_keys($this->config);
    }

    abstract public function load($path);
}
