<?php
/*
This file is part of Ice Framework.

Ice Framework is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Ice Framework is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Ice Framework. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */
namespace Classes;

abstract class SubActionManager
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

    public function setEmailTemplates($emailTemplates)
    {

        $this->emailTemplates   = $emailTemplates;
    }

    public function getEmailTemplate($name)
    {
        //Read module email templates
        if ($this->emailTemplates == null) {
            $this->emailTemplates = array();
            if (is_dir(MODULE_PATH.'/emailTemplates/')) {
                $ams = scandir(MODULE_PATH.'/emailTemplates/');
                foreach ($ams as $am) {
                    if (!is_dir(MODULE_PATH.'/emailTemplates/'.$am) && $am != '.' && $am != '..') {
                        $this->emailTemplates[$am] = file_get_contents(MODULE_PATH.'/emailTemplates/'.$am);
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
