<?php
namespace Consolidation\Config\Util;

/**
 * Fetch a configuration value from a configuration group. If the
 * desired configuration value is not found in the most specific
 * group named, keep stepping up to the next parent group until a
 * value is located.
 *
 * Given the following constructor inputs:
 *   - $prefix  = "command."
 *   - $group   = "foo.bar.baz"
 *   - $postfix = ".options."
 * Then the `get` method will then consider, in order:
 *   - command.foo.bar.baz.options
 *   - command.foo.bar.options
 *   - command.foo.options
 * If any of these contain an option for "$key", then return its value.
 */
abstract class ConfigGroup
{
    protected $config;
    protected $group;
    protected $prefix;
    protected $postfix;

    public function __construct($config, $group, $prefix = '', $postfix = '.')
    {
        $this->config = $config;
        $this->group = $group;
        $this->prefix = $prefix;
        $this->postfix = $postfix;
    }

    /**
     * Return a description of the configuration group (with prefix and postfix).
     */
    public function describe($property)
    {
        return $this->prefix . $this->group . $this->postfix . $property;
    }

    /**
     * Get the requested configuration key from the most specific configuration
     * group that contains it.
     */
    abstract public function get($key);

    /**
     * Given a group name, such as "foo.bar.baz", return the next configuration
     * group in the fallback hierarchy, e.g. "foo.bar".
     */
    protected function moreGeneralGroupName($group)
    {
        $result = preg_replace('#\.[^.]*$#', '', $group);
        if ($result != $group) {
            return $result;
        }
        return false;
    }
}
