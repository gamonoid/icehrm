<?php
/*
This file is part of iCE Hrm.

iCE Hrm is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

iCE Hrm is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with iCE Hrm. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */
namespace Modules\Admin\Api;

use Classes\SubActionManager;
use Classes\SettingsManager;
use Classes\IceResponse;
use Modules\Common\Model\Module;

class ModulesActionManager extends SubActionManager
{

    public function saveUsage($req)
    {
        $groups = explode(",", $req->groups);

        $groupModules = array();
        $groupModules['basic'] = array(
            "admin>audit",
            "admin>billing",
            "admin>charts",
            "admin>company_structure",
            "admin>dashboard",
            "admin>employeehistory",
            "admin>employees",
            "admin>fieldnames",
            "admin>jobs",
            "admin>metadata",
            "admin>modules",
            "admin>permissions",
            "admin>projects",
            "admin>qualifications",
            "admin>report_files",
            "admin>reports",
            "admin>settings",
            "admin>users",
            "modules>dashboard",
            "modules>dependents",
            "modules>emergency_contact",
            "modules>employees",
            "modules>projects",
            "modules>qualifications",
            "modules>report_files",
            "modules>reports",
            "modules>staffdirectory",
            "admin>data"
        );

        $groupModules['leave'] = array(
            "admin>leaves",
            "modules>leavecal",
            "modules>leaves"
        );

        $groupModules['documents'] = array(
            "admin>documents",
            "modules>documents",
            "modules>forms",
            "admin>forms"
        );

        $groupModules['attendance'] = array(
            "admin>attendance",
            "modules>overtime",
            "modules>attendance",
            "modules>attendance_sheets",
            "admin>overtime",
            "modules>time_sheets"
        );

        $groupModules['training'] = array(
            "admin>training",
            "modules>training"
        );

        $groupModules['finance'] = array(
            "admin>expenses",
            "admin>loans",
            "admin>travel",
            "modules>expenses",
            "modules>loans",
            "modules>travel"
        );

        $groupModules['recruitment'] = array(
            "admin>candidates",
            "admin>jobpositions",
            "admin>jobsetup"
        );

        $groupModules['payroll'] = array(
            "admin>payroll",
            "admin>salary",
            "modules>salary"
        );

        $module = new Module();

        if (!in_array('all', $groups)) {
            $modules = $module->Find("1 = 1");
            /* @var Module $mod */
            foreach ($modules as $mod) {
                $mod->status = "Disabled";
                $mod->Save();
            }
            $groups[] = "basic";
        }

        foreach ($groups as $group) {
            if ($group == 'all') {
                $modules = $module->Find("1 = 1");
                foreach ($modules as $mod) {
                    $mod->status = "Enabled";
                    $mod->Save();
                }
                break;
            } else {
                $modList = $groupModules[$group];
                /* @var Module $mod */
                foreach ($modules as $mod) {
                    if (in_array($mod->update_path, $modList)) {
                        $mod->status = "Enabled";
                        $mod->Save();
                    }
                }
            }
        }

        SettingsManager::getInstance()->addSetting("Modules : Group", implode(",", $groups));

        return new IceResponse(IceResponse::SUCCESS, $groups);
    }
}
