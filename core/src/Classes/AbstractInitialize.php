<?php
namespace Classes;

abstract class AbstractInitialize
{
    /* @var \Classes\BaseService $baseService */
    public $baseService = null;
    public function setBaseService($baseService)
    {
        $this->baseService = $baseService;
    }

    public function getCurrentProfileId()
    {
        return $this->baseService->getCurrentProfileId();
    }

    abstract public function init();
}
