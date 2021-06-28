<?php

namespace Classes;

interface Authorizable
{
    public function granted() : bool;
}
