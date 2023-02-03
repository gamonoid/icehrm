<?php

namespace Classes;

interface FinderProxy
{
    public function Find($whereOrderBy, $bindarr = false, $cache = false, $pkeysArr = false, $extra = array());
    public function setIsSubOrdinateQuery($val);
}
