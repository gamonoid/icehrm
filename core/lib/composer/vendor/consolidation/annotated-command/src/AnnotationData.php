<?php
namespace Consolidation\AnnotatedCommand;

use Consolidation\AnnotatedCommand\Parser\Internal\CsvUtils;

class AnnotationData extends \ArrayObject
{
    public function get($key, $default = '')
    {
        return $this->has($key) ? CsvUtils::toString($this[$key]) : $default;
    }

    public function getList($key, $default = [])
    {
        return $this->has($key) ? CsvUtils::toList($this[$key]) : $default;
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
