<?php

namespace CreateExtension;

class Method implements CodeEmitter
{

    const PUBLIC = 'public';
    const PRIVATE = 'private';
    const PROTECTED = 'protected';

    protected $scope;
    protected $isStatic = false;
    protected $name;
    protected $parameters;
    protected $body = [];

    /**
     * Method constructor.
     * @param $scope
     * @param $name
     * @param $parameters
     */
    public function __construct($name, $scope = 'public', $parameters = [])
    {
        $this->scope = $scope;
        $this->name = $name;
        $this->parameters = $parameters;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function setScope($scope): void
    {
        $this->scope = $scope;
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    public function setIsStatic(bool $isStatic): void
    {
        $this->isStatic = $isStatic;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param mixed $parameters
     */
    public function setParameters($parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @return mixed
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody(array $body): void
    {
        $this->body = $body;
    }

    public function getCode(CodeFormatter $formatter): string
    {
        $lines[] = '';
        $lines[] = sprintf(
            '%s %sfunction %s(%s) {',
            $this->getScope(),
            $this->isStatic()?'static ':'',
            $this->getName(),
            join(', ',$this->getParameters())
        );

        $body = array_map(function($line) use ($formatter){
            return $formatter->addTab($line);
        }, $this->getBody());
        $lines = array_merge($lines, $body);

        $lines[] = '}';

        return join(PHP_EOL, array_map(function($line) use ($formatter){
            return $formatter->addTab($line);
        }, $lines));
    }
}