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

$moduleName = 'dashboard';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

?><div class="span9">
    <div class="row">
        <?php
        $moduleManagers = BaseService::getInstance()->getModuleManagers();
        $dashBoardList = array();
        foreach($moduleManagers as $moduleManagerObj){

            //Check if this is not an admin module
            if($moduleManagerObj->getModuleType() != 'admin'){
                continue;
            }

            $allowed = BaseService::getInstance()->isModuleAllowedForUser($moduleManagerObj);

            if(!$allowed){
                continue;
            }

            $item = $moduleManagerObj->getDashboardItem();
            if(!empty($item)) {
                $index = $moduleManagerObj->getDashboardItemIndex();
                $dashBoardList[$index] = $item;
            }
        }

        ksort($dashBoardList);

        foreach($dashBoardList as $k=>$v){
            echo $v;
        }
        ?>
    </div>
    <div id="iceannon">
        <div class="callout callout-danger lead" style="font-size: 14px;font-weight: bold;">
            <h4>Why not upgrade to IceHrm Pro Version</h4>
            <p>
                IceHrm Pro is the feature rich upgrade to IceHrm open source version. It comes with improved modules for
                employee management, leave management and number of other features over open source version.
                Hit this <a href="http://icehrm.com/#compare" class="btn btn-primary btn-xs target="_blank">link</a> to do a full one to one comparison.

                Also you can learn more about IceHrm Pro <a href="http://blog.icehrm.com/docs/icehrm-pro/" class="btn btn-primary btn-xs" target="_blank">here</a>
                <br/>
                <br/>
                <a href="http://icehrm.com/modules.php" class="btn btn-success btm-xs" target="_blank"><i class="fa fa-checkout"></i> Buy IceHrm Pro</a>
            </p>
        </div>
    </div>

</div>
<script>
    var modJsList = new Array();

    modJsList['tabDashboard'] = new DashboardAdapter('Dashboard','Dashboard');

    var modJs = modJsList['tabDashboard'];

    $(document).ready(function() {
        try {
            $.ajax({
                url: "https://icehrm-public.s3.amazonaws.com/icehrmnews.html",
                success: function (result) {
                    $('#iceannon').html(result);
                }
            });
        } catch (e) {
        }

    });
</script>
<?php include APP_BASE_PATH.'footer.php';?>