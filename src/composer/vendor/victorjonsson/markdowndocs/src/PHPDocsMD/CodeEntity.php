<?php
namespace PHPDocsMD;


/**
 * Object describing a piece of code
 * @package PHPDocsMD
 */
class CodeEntity {

    /**
     * @var string
     */
    private $name='';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var bool
     */
    private $isDeprecated = false;

    /**
     * @var string
     */
    private $deprecationMessage = '';

    /**
     * @var string
     */
    private $example = '';


    /**
     * @param bool $toggle
     * @return void|bool
     */
    public function isDeprecated($toggle=null)
    {
        if( $toggle === null ) {
            return $this->isDeprecated;
        } else {
            return $this->isDeprecated = (bool)$toggle;
        }
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $deprecationMessage
     */
    public function setDeprecationMessage($deprecationMessage)
    {
        $this->deprecationMessage = $deprecationMessage;
    }

    /**
     * @return string
     */
    public function getDeprecationMessage()
    {
        return $this->deprecationMessage;
    }

    /**
     * @param string $example
     */
    public function setExample($example)
    {
        $this->example = $example;
    }

    /**
     * @return string
     */
    public function getExample()
    {
        return $this->example;
    }
}