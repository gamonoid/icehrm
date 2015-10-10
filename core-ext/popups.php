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
<div class="modal fade" id="plainMessageModel" tabindex="-1" role="dialog" aria-labelledby="plainMessageModelLabel" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">	
	<div class="modal-header" style="border-bottom:none;/*background-color: #3c8dbc;*/">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="margin-top:-10px;"><li class="fa fa-times"/></button>
	</div>
	<div class="modal-body">
		<p id="plainMessageModelBody"></p>
	</div>
</div>
</div>
</div>
<!-- Plain Message Modal -->

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
 		<button class="btn" onclick="$('#uploadModel').modal('hide');">Cancel</button>
	</div>
</div>
</div>
</div>
<!-- Upload Modal -->

<!-- Message Modal -->
<div class="modal fade" id="verifyModel" tabindex="-1" role="dialog" aria-labelledby="verifyModelLabel" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">	
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><li class="fa fa-times"/></button>
		<h3 id="verifyModelLabel" style="font-size: 17px;"></h3>
	</div>
	<div class="modal-body">
		<p id="verifyModelBody">
			<b>Step 1:</b><br/>
			Please get your Instance Key from here:<br/> 
			<a target="_blank" href="http://icehrm.com/generateInstanceKey.php?id=<?=$baseService->getInstanceId()?>">
			http://icehrm.com/generateInstanceKey.php?id=<?=$baseService->getInstanceId()?>
			</a>
			
			<br/><b>Step 2:</b><br/>
			Enter the key you generated in step 1 here and click "Verify"<br/>
			<form role="form">
			<div class="row">
				<label class="col-sm-12 control-label" for="verificationKey">Verification Key</label>
				<div class="controls col-sm-12">
					<input class="form-control" type="text" id="verificationKey" name="verificationKey" value=""/>
				</div>
			</div>
			<br/><br/>
			<div class="control-group row">
		    	<div class="controls col-sm-12">
		      		<button onclick="try{verifyInstance($('#verificationKey').val());}catch(e){};return false;" class="saveBtn btn btn-primary pull-right"><i class="fa fa-save"></i> Verify</button>
		      		<button onclick="$('#verifyModel').modal('hide');return false;" class="cancelBtn btn pull-right" style="margin-right:5px;"><i class="fa fa-times-circle-o"></i> Cancel</button>
		    	</div>
		    	<div class="controls col-sm-3">
		    	</div>
		  	</div>
		</div>
	</form>
		</p>
	</div>
</div>
</div>
</div>
<!-- Message Modal -->


<?php if($user->user_level == 'Admin'){?>
            <!-- Modal -->
            <div class="modal fade" id="profileSwitchModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
              	<div class="modal-content">
	              <div class="modal-header">
	                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><li class="fa fa-times"/></button>
	                <h3 id="myModalLabel">Switch Employee</h3>
	              </div>
	              <div class="modal-body">
	                <p>Select the employee to Edit</p>
	                <div style="border: solid 1px #EEE;">
	                <select id="switch_emp" style="width:100%;">
	                <!--
	                <option value="-1">No Employee</option>
	                -->
	                <?php 
	                $employees = $baseService->get('Employee');
	                foreach($employees as $empTemp){
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