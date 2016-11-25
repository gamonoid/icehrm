<?php
namespace PHPDocsMD;


/**
 * Object describing a function
 * @package PHPDocsMD
 */
class FunctionEntity extends CodeEntity {

    /**
     * @var \PHPDocsMD\ParamEntity[]
     */
    private $params = [];

    /**
     * @var string
     */
    private $returnType = 'void';

    /**
     * @var string
     */
    private $visibility = 'public';

    /**
     * @var bool
     */
    private $abstract = false;

    /**
     * @var bool
     */
    private $isStatic = false;

    /**
     * @var string
     */
    private $class = '';

    /**
     * @var bool
     */
    private $isReturningNativeClass = false;

    /**
     * @param bool $toggle
     */
    public function isStatic($toggle=null)
    {
        if ( $toggle === null ) {
            return $this->isStatic;
        } else {
            return $this->isStatic = (bool)$toggle;
        }
    }

    /**
     * @param bool $toggle
     */
    public function isAbstract($toggle=null)
    {
        if ( $toggle === null ) {
            return $this->abstract;
        } else {
            return $this->abstract = (bool)$toggle;
        }
    }

    /**
     * @param bool $toggle
     */
    public function isReturningNativeClass($toggle=null)
    {
        if ( $toggle === null ) {
            return $this->isReturningNativeClass;
        } else {
            return $this->isReturningNativeClass = (bool)$toggle;
        }
    }

    /**
     * @return bool
     */
    public function hasParams()
    {
        return !empty($this->params);
    }

    /**
     * @param \PHPDocsMD\ParamEntity[] $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * @return \PHPDocsMD\ParamEntity[]
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $returnType
     */
    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;
    }

    /**
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @param string $visibility
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }
}

