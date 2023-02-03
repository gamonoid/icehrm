<?php
namespace Classes;

abstract class IceExtension extends AbstractModuleManager
{
    public function initializeUserClasses()
    {
        // TODO: Implement initializeUserClasses() method.
    }

    public function initializeFieldMappings()
    {
        // TODO: Implement initializeFieldMappings() method.
    }

    public function initializeDatabaseErrorMappings()
    {
        // TODO: Implement initializeDatabaseErrorMappings() method.
    }

    public function getUpdatePath()
    {
        $moduleObject = $this->getModuleObject();

        return 'extension>'.$moduleObject['name'].'|'.$this->getModuleType();
    }

    protected function getModuleLink()
    {

        $metaData = json_decode(file_get_contents($this->modulePath."/meta.json"), true);

        $updatePath = $this->getUpdatePath();
        $data = explode('>', $updatePath);
        $mod = $data[1];
        $group = $data[0];

        return CLIENT_BASE_URL."?g=".$group."&n=".$mod."&m=".explode('|', $mod)[1]."_".str_replace(" ", "_", $metaData['label']);
    }
}
