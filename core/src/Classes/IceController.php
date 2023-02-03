<?php

namespace Classes;

abstract class IceController extends SubActionManager
{
    public function getExtensionsBasePath() 
    {
        return APP_BASE_PATH.'../extensions/';
    }
}
