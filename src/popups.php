<!-- Delete Modal -->
<div class="modal fade" id="deleteModel" tabindex="-1" role="dialog" aria-labelledby="deleteModelLabel" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">	
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><li class="fa fa-times"/></button>
		<h3 id="deleteModelLabel" style="font-size: 17px;"></h3>
	</div>
	<div class="modal-body">
		<p id="deleteModelBody"></p>
	</div>
	<div class="modal-footer">
 		<button class="btn" onclick="modJs.cancelDelete();">Cancel</button>
		<button class="btn btn-primary" onclick="modJs.confirmDelete();">Delete</button>
	</div>
</div>
</div>
</div>
<!-- Delete Modal -->

<!-- Message Modal -->
<div class="modal fade" id="messageModel" tabindex="-1" role="dialog" aria-labelledby="messageModelLabel" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">	
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><li class="fa fa-times"/></button>
		<h3 id="messageModelLabel" style="font-size: 17px;"></h3>
	</div>
	<div class="modal-body">
		<p id="messageModelBody"></p>
	</div>
	<div class="modal-footer">
 		<button class="btn" onclick="modJs.closeMessage();">Ok</button>
	</div>
</div>
</div>
</div>
<!-- Message Modal -->

<!-- Plain Message Modal -->
<div class="modal fade" id="plainMessageModel" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="plainMessageModelLabel" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">	
	<div class="modal-header" style="border-bottom:none;/*background-color: #3c8dbc;*/">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="margin-top:-10px;"><li class="fa fa-times"/></button>
	</div>
	<div class="modal-body" id="plainMessageModelBody">
	</div>
</div>
</div>
</div>
<!-- Plain Message Modal -->

<!-- Data Message Modal -->
<div class="modal fade" id="dataMessageModel" tabindex="-1" role="dialog" aria-labelledby="dataMessageModelLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:none;/*background-color: #3c8dbc;*/">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="margin-top:-10px;"><li class="fa fa-times"/></button>
            </div>
            <div class="modal-body">
                <p id="dataMessageModelBody"></p>
            </div>
        </div>
    </div>
</div>
<!-- Data Message Modal -->

<!-- Yes No Modal -->
<div class="modal fade" id="yesnoModel" tabindex="-1" role="dialog" aria-labelledby="yesnoModelLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><li class="fa fa-times"/></button>
                <h3 id="yesnoModelLabel" style="font-size: 17px;"></h3>
            </div>
            <div class="modal-body">
                <p id="yesnoModelBody"></p>
            </div>
            <div class="modal-footer">
                <button id="yesnoModelNoBtn" class="btn" onclick="modJs.cancelYesno();">No</button>
                <button id="yesnoModelYesBtn" class="btn btn-primary">Yes</button>
            </div>
        </div>
    </div>
</div>
<!-- Yes No Modal -->


<!-- Upload Modal -->
<div class="modal fade" id="uploadModel" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><li class="fa fa-times"/></button>
		<h3 id="uploadModelLabel" style="font-size: 17px;"></h3>
	</div>
	<div class="modal-body">
		<p id="uploadModelBody"></p>
	</div>
	<div class="modal-footer">
 		<button class="btn" onclick="modJs.closeMessage();">Upload File</button>
 		<button class="btn" onclick="modJs.closeMessage();">Cancel</button>
	</div>
</div>
</div>
</div>
<!-- Upload Modal -->


<?php if($user->user_level == 'Admin'){?>
	<!-- Modal -->
	<div class="modal fade" id="profileSwitchModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><li class="fa fa-times"/></button>
			<h3 id="myModalLabel">Switch Profile</h3>
		  </div>
		  <div class="modal-body">
			<p>Select the profile to Edit</p>
			<div style="border: solid 1px #EEE;">
			<select id="switch_emp" style="width:100%;">
			<!--
			<option value="-1">No Profile</option>
			-->
			<?php
			$profileClass = ucfirst(SIGN_IN_ELEMENT_MAPPING_FIELD_NAME);
			$profiles = BaseService::getInstance()->get($profileClass);
			foreach($profiles as $empTemp){
			?>
			<option value="<?=$empTemp->id?>"><?=$empTemp->first_name." ".$empTemp->last_name?></option>
			<?php }?>
			</select>
			</div>
		  </div>
		  <div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			<button class="btn btn-primary" onclick="modJs.setAdminProfile($('#switch_emp').val());return false;">Switch</button>
		  </div>
		</div>
	</div>
	</div>
	<!-- Modal -->
<?php }?>


<?php if(isset($itemNameLower)){?>
<div class="modal" id="<?=$itemNameLower?>StatusModel" tabindex="-1" role="dialog" aria-labelledby="messageModelLabel" aria-hidden="true">
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
<?php } ?>