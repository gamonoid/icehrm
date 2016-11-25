<?php
namespace PHPDocsMD;


/**
 * Interface for classes that can compute ClassEntity objects
 * @package PHPDocsMD
 */
interface ReflectorInterface
{

    /**
     * @return \PHPDocsMD\ClassEntity
     */
    function getClassEntity();
}