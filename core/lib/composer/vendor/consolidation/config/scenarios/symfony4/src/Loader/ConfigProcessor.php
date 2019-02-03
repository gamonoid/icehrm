<?php

namespace Consolidation\Config\Loader;

use Grasmash\Expander\Expander;
use Consolidation\Config\Util\ArrayUtil;

/**
 * The config processor combines multiple configuration
 * files together, and processes them as necessary.
 */
class ConfigProcessor
{
    protected $processedConfig = [];
    protected $unprocessedConfig = [];
    protected $expander;

    public function __construct($expander = null)
    {
        $this->expander = $expander ?: new Expander();
    }

    /**
     * Extend the configuration to be processed with the
     * configuration provided by the specified loader.
     *
     * @param ConfigLoaderInterface $loader
     */
    public function extend(ConfigLoaderInterface $loader)
    {
        return $this->addFromSource($loader->export(), $loader->getSourceName());
    }

    /**
     * Extend the configuration to be processed with
     * the provided nested array.
     *
     * @param array $data
     */
    public function add($data)
    {
        $this->unprocessedConfig[] = $data;
        return $this;
    }

    /**
     * Extend the configuration to be processed with
     * the provided nested array. Also record the name
     * of the data source, if applicable.
     *
     * @param array $data
     * @param string $source
     */
    protected function addFromSource($data, $source = '')
    {
        if (empty($source)) {
            return $this->add($data);
        }
        $this->unprocessedConfig[$source] = $data;
        return $this;
    }

    /**
     * Process all of the configuration that has been collected,
     * and return a nested array.
     *
     * @return array
     */
    public function export($referenceArray = [])
    {
        if (!empty($this->unprocessedConfig)) {
            $this->processedConfig = $this->process(
                $this->processedConfig,
                $this->fetchUnprocessed(),
                $referenceArray
            );
        }
        return $this->processedConfig;
    }

    /**
     * To aid in debugging: return the source of each configuration item.
     * n.b. Must call this function *before* export and save the result
     * if persistence is desired.
     */
    public function sources()
    {
        $sources = [];
        foreach ($this->unprocessedConfig as $sourceName => $config) {
            if (!empty($sourceName)) {
                $configSources = ArrayUtil::fillRecursive($config, $sourceName);
                $sources = ArrayUtil::mergeRecursiveDistinct($sources, $configSources);
            }
        }
        return $sources;
    }

    /**
     * Get the configuration to be processed, and clear out the
     * 'unprocessed' list.
     *
     * @return array
     */
    protected function fetchUnprocessed()
    {
        $toBeProcessed = $this->unprocessedConfig;
        $this->unprocessedConfig = [];
        return $toBeProcessed;
    }

    /**
     * Use a map-reduce to evaluate the items to be processed,
     * and merge them into the processed array.
     *
     * @param array $processed
     * @param array $toBeProcessed
     * @return array
     */
    protected function process(array $processed, array $toBeProcessed, $referenceArray = [])
    {
        $toBeReduced = array_map([$this, 'preprocess'], $toBeProcessed);
        $reduced = array_reduce($toBeReduced, [$this, 'reduceOne'], $processed);
        return $this->evaluate($reduced, $referenceArray);
    }

    /**
     * Process a single configuration file from the 'to be processed'
     * list. By default this is a no-op. Override this method to
     * provide any desired configuration preprocessing, e.g. dot-notation
     * expansion of the configuration keys, etc.
     *
     * @param array $config
     * @return array
     */
    protected function preprocess(array $config)
    {
        return $config;
    }

    /**
     * Evaluate one item in the 'to be evaluated' list, and then
     * merge it into the processed configuration (the 'carry').
     *
     * @param array $processed
     * @param array $config
     * @return array
     */
    protected function reduceOne(array $processed, array $config)
    {
        return ArrayUtil::mergeRecursiveDistinct($processed, $config);
    }

    /**
     * Evaluate one configuration item.
     *
     * @param array $processed
     * @param array $config
     * @return array
     */
    protected function evaluate(array $config, $referenceArray = [])
    {
        return $this->expander->expandArrayProperties(
            $config,
            $referenceArray
        );
    }
}
