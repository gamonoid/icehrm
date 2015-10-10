/**
 * Author: Thilina Hasantha
 */

function UserAdapter(endPoint) {
	this.initAdapter(endPoint);
}

UserAdapter.inherits(AdapterBase);


UserAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "username",
	        "email",
	        "employee",
	        "user_level"
	];
});

UserAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" },
			{ "sTitle": "User Name" },
			{ "sTitle": "Authentication Email" },
			{ "sTitle": "Employee"},
			{ "sTitle": "User Level"}
	];
});

UserAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden","validation":""}],
	        [ "username", {"label":"User Name","type":"text","validation":"username"}],
	        [ "email", {"label":"Email","type":"text","validation":"email"}],
	        [ "employee", {"label":"Employee","type":"select2","allow-null":true,"remote-source":["Employee","id","first_name+last_name"]}],
	        [ "user_level", {"label":"User Level","type":"select","source":[["Admin","Admin"],["Manager","Manager"],["Employee","Employee"]]}]
	];
});

UserAdapter.method('postRenderForm', function(object, $tempDomObj) {
	if(object == null || object == undefined){
		$tempDomObj.find("#changePasswordBtn").remove();
	}
});

UserAdapter.method('changePassword', function() {
	$('#adminUsersModel').modal('show');
	$('#adminUsersChangePwd #newpwd').val('');
	$('#adminUsersChangePwd #conpwd').val('');
});

UserAdapter.method('saveUserSuccessCallBack', function(callBackData,serverData) {
	this.showMessage("Create User","An email has been sent to "+callBackData['email']+" with a temporary password to login to IceHrm.");
	this.get([]);
});

UserAdapter.method('saveUserFailCallBack', function(callBackData,serverData) {
	this.showMessage("Error",callBackData);
});

UserAdapter.method('doCustomValidation', function(params) {
	var msg = null;
	if(params['user_level'] != "Admin" && params['employee'] == "NULL"){
		msg = "For non Admin users, you have to assign an employee when adding or editing the user.<br/>";
		msg += " You may create a new employee through 'Admin'->'Employees' menu";
	}
	return msg;
});

UserAdapter.method('save', function() {
	var validator = new FormValidation(this.getTableName()+"_submit",true,{'ShowPopup':false,"LabelErrorClass":"error"});
	if(validator.checkValues()){
		var params = validator.getFormParameters();
		
		var msg = this.doCustomValidation(params);
		if(msg == null){
			var id = $('#'+this.getTableName()+"_submit #id").val();
			if(id != null && id != undefined && id != ""){
				$(params).attr('id',id);
				this.add(params,[]);
			}else{
				
				var reqJson = JSON.stringify(params);
				
				var callBackData = [];
				callBackData['callBackData'] = [];
				callBackData['callBackSuccess'] = 'saveUserSuccessCallBack';
				callBackData['callBackFail'] = 'saveUserFailCallBack';
				
				this.customAction('saveUser','admin=users',reqJson,callBackData);
			}
			
		}else{
			//$("#"+this.getTableName()+'Form .label').html(msg);
			//$("#"+this.getTableName()+'Form .label').show();
			this.showMessage("Error Saving User",msg);
		}
		
		
		
	}
});


UserAdapter.method('changePasswordConfirm', function() {
	$('#adminUsersChangePwd_error').hide();
	
	var passwordValidation =  function (str) {  
		var val = /^[a-zA-Z0-9]\w{6,}$/;  
		return str != null && val.test(str);  
	};
	
	var password = $('#adminUsersChangePwd #newpwd').val();
	
	if(!passwordValidation(password)){
		$('#adminUsersChangePwd_error').html("Password may contain only letters, numbers and should be longer than 6 characters");
		$('#adminUsersChangePwd_error').show();
		return;
	}
	
	var conPassword = $('#adminUsersChangePwd #conpwd').val();
	
	if(conPassword != password){
		$('#adminUsersChangePwd_error').html("Passwords don't match");
		$('#adminUsersChangePwd_error').show();
		return;
	}
	
	var req = {"id":this.currentId,"pwd":conPassword};
	var reqJson = JSON.stringify(req);
	
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'changePasswordSuccessCallBack';
	callBackData['callBackFail'] = 'changePasswordFailCallBack';
	
	this.customAction('changePassword','admin=users',reqJson,callBackData);
	
});

UserAdapter.method('closeChangePassword', function() {
	$('#adminUsersModel').modal('hide');
});

UserAdapter.method('changePasswordSuccessCallBack', function(callBackData,serverData) {
	this.closeChangePassword();
	this.showMessage("Password Change","Password changed successfully");
});

UserAdapter.method('changePasswordFailCallBack', function(callBackData,serverData) {
	this.closeChangePassword();
	this.showMessage("Error",callBackData);
});

UserAdapter.method('getHelpLink', function () {
	return 'http://blog.icehrm.com/?page_id=132';
});



