<?php
namespace Consolidation\AnnotatedCommand;

class AnnotationData extends \ArrayObject
{
    public function get($key, $default)
    {
        return $this->has($key) ? $this[$key] : $default;
    }

    public function has($key)
    {
        return isset($this[$key]);
    }

    public function keys()
    {
        return array_keys($this->getArrayCopy());
    }
}
