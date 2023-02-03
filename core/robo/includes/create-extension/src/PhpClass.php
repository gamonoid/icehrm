<?php
namespace CreateExtension;

class PhpClass implements CodeEmitter
{
    protected $namespace = '';
    protected $name = '';
    protected $use = [];
    protected $parentClass = '';
    protected $interfaces = [];
    protected $properties = [];
    protected $methods = [];

    /**
     * PhpClass constructor.
     * @param $namespace
     * @param $name
     * @param $parentClass
     */
    public function __construct($namespace, $name, $parentClass)
    {
        $this->namespace = $namespace;
        $this->name = $name;
        $this->parentClass = $parentClass;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }


    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function setNamespace($namespace): void
    {
        $this->namespace = $namespace;
    }

    public function getUse(): array
    {
        return $this->use;
    }

    public function addUse(string $use): void
    {
        $this->use[] = $use;
    }

    public function getParentClass(): string
    {
        return $this->parentClass;
    }

    public function setParentClass($parentClass): void
    {
        $this->parentClass = $parentClass;
    }

    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    /**
     * @param mixed $interfaces
     */
    public function setInterfaces(array $interfaces): void
    {
        $this->interfaces = $interfaces;
    }

    /**
     * @return Property[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param Property $property
     */
    public function addProperty(Property $property): void
    {
        $this->properties[] = $property;
    }

    /**
     * @return Method[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param Method $method
     */
    public function addMethod(Method $method): void
    {
        $this->methods[] = $method;
    }

    public function getCode(CodeFormatter $formatter): string
    {
        $lines = [];

        //Add namespace
        $lines[] = '';
        $lines[] = sprintf('namespace %s;', $this->namespace);

        //Use statements
        $uses = $this->getUse();
        if (!empty($uses)) {
            $lines[] = '';
        }
        foreach ($uses as $use) {
            $lines[] = sprintf('use %s;', $use);
        }

        // Class signature
        $lines[] = '';
        $signature = sprintf('class %s', $this->getName());
        if ($this->getParentClass()) {
            $signature .= sprintf(' extends %s', $this->getParentClass());
        }

        if (!empty($this->getInterfaces())) {
            $signature .= sprintf(' implements %s', join(',', $this->getInterfaces()));
        }

        $lines[] = $signature;
        $lines[] = '{';

        // Class properties
        $props = $this->getProperties();
        foreach ($props as $prop) {
            $lines[] = $prop->getCode($formatter);
        }

        // Class methods
        $methods = $this->getMethods();
        foreach ($methods as $method) {
            $lines[] = $method->getCode($formatter);
        }

        // End Line
        $lines[] = '}';
        $lines[] = '';

        return join(PHP_EOL, $lines);
    }
}