<?php

namespace Classes;

class ModuleAccess
{
    protected $name;
    protected $group;

    /**
     * ModuleAccess constructor.
     *
     * @param $name
     * @param $group
     */
    public function __construct($name, $group = 'extension')
    {
        $this->name = $name;
        $this->group = $group;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }
}
