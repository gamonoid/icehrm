<?php
namespace PHPDocsMD;

/**
 * Find a specific function in a class or an array of classes
 * @package PHPDocsMD
 */
class FunctionFinder
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * @param string $methodName
     * @param array $classes
     * @return bool|FunctionEntity
     */
    public function findInClasses($methodName, $classes)
    {
        foreach ($classes as $className) {
            $function = $this->find($methodName, $className);
            if (false !== $function) {
                return $function;
            }
        }
        return false;
    }

    /**
     * @param string $methodName
     * @param string $className
     * @return bool|FunctionEntity
     */
    public function find($methodName, $className)
    {
        if ($className) {
            $classEntity = $this->loadClassEntity($className);
            $functions = $classEntity->getFunctions();
            foreach($functions as $function) {
                if($function->getName() == $methodName) {
                    return $function;
                }
            }
            if($classEntity->getExtends()) {
                return $this->find($methodName, $classEntity->getExtends());
            }
        }
        return false;
    }

    /**
     * @param $className
     * @return ClassEntity
     */
    private function loadClassEntity($className)
    {
        if (empty($this->cache[$className])) {
            $reflector = new Reflector($className, $this);
            $this->cache[$className] = $reflector->getClassEntity();
        }

        return $this->cache[$className];
    }
}