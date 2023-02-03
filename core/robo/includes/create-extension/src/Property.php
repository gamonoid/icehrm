<?php


namespace CreateExtension;


class Property implements CodeEmitter
{
    const PUBLIC = 'public';
    const PRIVATE = 'private';
    const PROTECTED = 'protected';

    protected $scope;
    protected $isStatic = false;
    protected $name;
    protected $initialValue = null;

    /**
     * Property constructor.
     * @param $scope
     * @param $name
     */
    public function __construct($name, $scope = 'public')
    {
        $this->scope = $scope;
        $this->name = $name;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @param mixed $scope
     */
    public function setScope($scope): void
    {
        $this->scope = $scope;
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    /**
     * @param bool $isStatic
     */
    public function setIsStatic(bool $isStatic): void
    {
        $this->isStatic = $isStatic;
    }

    /**
     * @return mixed
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getInitialValue(): ?string
    {
        return $this->initialValue;
    }

    /**
     * @param null $initialValue
     */
    public function setInitialValue($initialValue): void
    {
        $this->initialValue = $initialValue;
    }

    public function getCode(CodeFormatter $formatter): string
    {
        if (null === $this->getInitialValue()) {
            $result = sprintf('%s %s$%s;', $this->getScope(), $this->isStatic()?'static ':'',$this->getName());
        } else {
            $result = sprintf('%s %s$%s = %s;', $this->getScope(), $this->isStatic()?'static ':'',$this->getName(), $this->getInitialValue());
        }

        return $formatter->addTab($result);
    }

}