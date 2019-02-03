<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
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
