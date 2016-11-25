<?php
namespace PHPDocsMD;


/**
 * Object describing a function parameter
 * @package PHPDocsMD
 */
class ParamEntity extends CodeEntity {

    /**
     * @var bool
     */
    private $default=false;

    /**
     * @var string
     */
    private $type='mixed';

    /**
     * @param boolean $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return boolean
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getNativeClassType()
    {
        foreach(explode('/', $this->type) as $typeDeclaration) {
            if (Utils::isNativeClassReference($typeDeclaration)) {
                return $typeDeclaration;
            }
        }
        return null;
    }
}

