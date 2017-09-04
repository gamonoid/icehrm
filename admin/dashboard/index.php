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

$invoices = [];
$numOfUnpaidInvoices = 0;
if (class_exists('\\Billing\\Admin\\Api\\BillingActionManager')) {
    $billingActionManager = new \Billing\Admin\Api\BillingActionManager();

    $invoices = $billingActionManager->getInvoices(null)->getData();
    if(!empty($invoices)){
        $invoices = json_decode(json_encode($invoices));
    }

    foreach($invoices as $inv){
        if($inv->status == "Sent"){
            $numOfUnpaidInvoices++;
        }
    }
}


?><div class="span9">
<div class="row">
    <?php if($numOfUnpaidInvoices == 1){?>
    <div class="callout callout-warning lead" style="font-size: 14px;">
        <h4>You have a pending invoice</h4>
        <p style="font-weight: bold;">
            You have a pending invoice. Please make you complete the payment so we can provide a better service.
            <br/>
            <br/>
            <a href="<?=CLIENT_BASE_URL?>?g=admin&n=billing&m=admin_System#tabInvoice" class="btn btn-success btm-xs"><i class="fa fa-checkout"></i> Make a Payment</a>
        </p>
    </div>
    <?php }else if($numOfUnpaidInvoices > 1){?>
    <div class="callout callout-danger lead" style="font-size: 14px;">
        <h4>You have <?=$numOfUnpaidInvoices?> pending invoices</h4>
        <p style="font-weight: bold;">
            You have <?=$numOfUnpaidInvoices?> pending invoice. None of your employees are currently allowed to login. Please make sure you complete payments to all the invoices to restore your service.
            Please logout and login after completing the payment to get your service restored.
            <br/>
            <br/>
            <a href="<?=CLIENT_BASE_URL?>?g=admin&n=billing&m=admin_System#tabInvoice" class="btn btn-success btm-xs"><i class="fa fa-checkout"></i> Make a Payment</a>
        </p>
    </div>
    <?php }?>

    <?php if(\Utils\SessionUtils::getSessionObject('account_locked') == "1"){?>
        <div class="callout callout-danger lead" style="font-size: 14px;">
            <h4>Your Trial Has Expired</h4>
            <p style="font-weight: bold;">
                Your Icehrm Trial has expired. Please upgrade subscription to continue. If not upgraded your account will be deleted with in few days.
                <br/>
                <br/>
                <a href="<?=CLIENT_BASE_URL?>?g=admin&n=billing&m=admin_System" class="btn btn-success btm-xs"><i class="fa fa-checkout"></i> Upgrade Subscription</a>
            </p>
        </div>
    <?php }?>

    <?php
    $moduleManagers = \Classes\BaseService::getInstance()->getModuleManagers();
    $dashBoardList = array();
    foreach($moduleManagers as $moduleManagerObj){

        //Check if this is not an admin module
        if($moduleManagerObj->getModuleType() != 'admin'){
            continue;
        }

        $allowed = \Classes\BaseService::getInstance()->isModuleAllowedForUser($moduleManagerObj);

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
        echo \Classes\LanguageManager::translateTnrText($v);
    }
    ?>
</div>


</div>
<script>
var modJsList = new Array();

modJsList['tabDashboard'] = new DashboardAdapter('Dashboard','Dashboard');

var modJs = modJsList['tabDashboard'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
