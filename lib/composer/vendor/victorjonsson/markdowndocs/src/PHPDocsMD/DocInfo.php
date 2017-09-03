<?php
namespace PHPDocsMD;

/**
 * Class containing information about a function/class that's being made
 * available via a comment block
 *
 * @package PHPDocsMD
 */
class DocInfo
{
    /**
     * @var array
     */
    private $data = [];


    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = array_merge([
            'return' => '',
            'params' => [],
            'description' => '',
            'example' => false,
            'deprecated' => false
        ], $data);
    }

    /**
     * @return string
     */
    public function getReturnType()
    {
        return $this->data['return'];
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->data['params'];
    }

    /**
     * @param string $name
     * @return array
     */
    public function getParameterInfo($name)
    {
        if (isset($this->data['params'][$name])) {
            return $this->data['params'][$name];
        }
        return [];
    }

    /**
     * @return string
     */
    public function getExample()
    {
        return $this->data['example'];
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->data['description'];
    }

    /**
     * @return string
     */
    public function getDeprecationMessage()
    {
        return $this->data['deprecated'];
    }

    /**
     * @return bool
     */
    public function shouldInheritDoc()
    {
        return isset($this->data['inheritDoc']) || isset($this->data['inheritdoc']);
    }

    /**
     * @return bool
     */
    public function shouldBeIgnored()
    {
        return isset($this->data['ignore']);
    }
}