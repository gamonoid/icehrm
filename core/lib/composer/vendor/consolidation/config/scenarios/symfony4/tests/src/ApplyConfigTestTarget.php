<?php

namespace Consolidation\TestUtils;

class ApplyConfigTestTarget
{
    protected $dir;
    protected $value;

    /**
     * A proper setter for the 'dir' property
     */
    public function dir($dir)
    {
        $this->dir = $dir;
        return $this;
    }

    /**
     * A getter for the 'dir' property that we will use to
     * determine if the setter was called.
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * A bad setter that does not return $this.
     */
    public function bad($value)
    {
        $this->value = $value;
    }

    /**
     * A getter for the bad setter.
     */
    public function getBad()
    {
        return $this->value;
    }
}
