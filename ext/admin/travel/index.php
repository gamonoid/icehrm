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

$moduleName = 'travel';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

$options = array();
$options['setRemoteTable'] = 'true';

$moduleBuilder = new ModuleBuilder();
$moduleBuilder->addModuleOrGroup(new ModuleTab('EmployeeTravelRecord','EmployeeTravelRecord','Travel Requests','EmployeeTravelRecordAdapter','','',true,$options));
echo UIManager::getInstance()->renderModule($moduleBuilder);


$itemName = 'TravelRequest';
$moduleName = 'Travel Management';
$itemNameLower = strtolower($itemName);

$statuses = array("Approved","Pending","Rejected","Cancelled");

?><div class="modal" id="<?=$itemNameLower?>StatusModel" tabindex="-1" role="dialog" aria-labelledby="messageModelLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><li class="fa fa-times"/></button>
                <h3 style="font-size: 17px;">Change <?=$itemName?> Status</h3>
            </div>
            <div class="modal-body">
                <form id="expenseStatusForm">
                    <div class="control-group">
                        <label class="control-label" for="expense_status"><?=$itemName?> Status</label>
                        <div class="controls">
                            <select type="text" id="<?=$itemNameLower?>_status" class="form-control" name="<?=$itemNameLower?>_status" value="">
                                <?php foreach($statuses as $status){?>
                                    <option value="<?=$status?>"><?=$status?></option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="expense_status">Status Change Note</label>
                        <div class="controls">
                            <textarea id="<?=$itemNameLower?>_reason" class="form-control" name="<?=$itemNameLower?>_reason" maxlength="500"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="modJs.changeStatus();">Change <?=$itemName?> Status</button>
                <button class="btn" onclick="modJs.closeDialog();">Not Now</button>
            </div>
        </div>
    </div>
    </div>
<?php
include APP_BASE_PATH.'footer.php';