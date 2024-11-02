<?php
namespace Classes;

class SubActionManager
{
    public $user = null;
    /* @var \Classes\BaseService $baseService*/
    protected $baseService = null;
    public $emailTemplates = null;
    protected $emailSender = null;

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function setBaseService(BaseService $baseService)
    {
        $this->baseService = $baseService;
    }

    public function getCurrentProfileId()
    {
        return $this->baseService->getCurrentProfileId();
    }

    public function getCurrentUserProfileId()
    {
        return $this->baseService->getCurrentUserProfileId();
    }

    public function setEmailTemplates($emailTemplates)
    {

        $this->emailTemplates   = $emailTemplates;
    }

    public function getEmailTemplate($name, $modulePath = null)
    {
        if ($modulePath == null) {
            $modulePath = MODULE_PATH;
        }
        //Read module email templates
        if ($this->emailTemplates == null) {
            $this->emailTemplates = array();
            if (is_dir($modulePath.'/emailTemplates/')) {
                $ams = scandir($modulePath.'/emailTemplates/');
                foreach ($ams as $am) {
                    if (!is_dir($modulePath.'/emailTemplates/'.$am) && $am != '.' && $am != '..') {
                        $this->emailTemplates[$am] = file_get_contents($modulePath.'/emailTemplates/'.$am);
                    }
                }
            }
        }

        return  $this->emailTemplates[$name];
    }

    public function setEmailSender($emailSender)
    {
        $this->emailSender = $emailSender;
    }

    public function getUserFromProfileId($profileId)
    {
        return $this->baseService->getUserFromProfileId($profileId);
    }
}
