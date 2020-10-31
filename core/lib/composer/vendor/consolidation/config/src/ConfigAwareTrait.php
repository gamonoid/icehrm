<?php

namespace Consolidation\Config;

trait ConfigAwareTrait
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * Set the config management object.
     *
     * @param ConfigInterface $config
     *
     * @return $this
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get the config management object.
     *
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }
}
