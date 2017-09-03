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

ValidationRules = {

	float: function (str) {
		var floatstr = /^[-+]?[0-9]+(\.[0-9]+)?$/;
		if (str != null && str.match(floatstr)) {
			return true;
		} else {
			return false;
		}
	},

	number: function (str) {
		var numstr = /^[0-9]+$/;
		if (str != null && str.match(numstr)) {
			return true;
		} else {
			return false;
		}
	},

	numberOrEmpty: function (str) {
		if(str == ""){
			return true;
		}
		var numstr = /^[0-9]+$/;
		if (str != null && str.match(numstr)) {
			return true;
		} else {
			return false;
		}
	},

	email: function (str) {
		var emailPattern = /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/;
		return str != null && emailPattern.test(str);
	},

	emailOrEmpty: function (str) {
		if(str == ""){
			return true;
		}
		var emailPattern = /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/;
		return str != null && emailPattern.test(str);
	},

	username: function (str) {
		var username = /^[a-zA-Z0-9\.-]+$/;
		return str != null && username.test(str);
	},

	input: function (str) {
		if (str != null && str.length > 0) {
			return true;
		} else {
			return false;
		}
	}


};

function FormValidation(formId,validateAll,options) {
	this.tempOptions = {};
	this.formId          = formId;
	this.formError  = false;
	this.formObject = null;
	this.errorMessages = "";
	this.popupDialog = null;
	this.validateAll = validateAll;
	this.errorMap = new Array();
	
	this.settings = {"thirdPartyPopup":null,"LabelErrorClass":false, "ShowPopup":true};
	
	this.settings = jQuery.extend(this.settings,options);
	
	this.inputTypes = new Array( "text",  "radio",  "checkbox",  "file", "password",  "select-one","select-multi",  "textarea","fileupload" ,"signature");

	this.validator = ValidationRules;

}

FormValidation.method('clearError' , function(formInput, overrideMessage) {
	var id = formInput.attr("id");
	$('#'+ this.formId +' #field_'+id).removeClass('error');
	$('#'+ this.formId +' #help_'+id).html('');
});
	
FormValidation.method('addError' , function(formInput, overrideMessage) {
	this.formError = true;
	if(formInput.attr("message") != null) {
		this.errorMessages += (formInput.attr("message") + "\n");
		this.errorMap[formInput.attr("name")] = formInput.attr("message");
	}else{
		this.errorMap[formInput.attr("name")] = "";
	}

	var id = formInput.attr("id");
	var validation = formInput.attr("validation");
	var message = formInput.attr("validation");
	$('#'+ this.formId +' #field_'+id).addClass('error');
	if(message == undefined || message == null || message == ""){
		$('#'+ this.formId +' #help_'+id).html(message);
	}else{
		if(validation == undefined || validation == null || validation == ""){
			$('#'+ this.formId +' #help_'+id).html("Required");
		}else{
			if(validation == "float" || validation == "number"){
				$('#'+ this.formId +' #help_'+id).html("Number required");
			}else if(validation == "email"){
				$('#'+ this.formId +' #help_'+id).html("Email required");
			}else{
				$('#'+ this.formId +' #help_'+id).html("Required");
			}
		}
	}
	
	
});
	

FormValidation.method('showErrors' , function() {
	if(this.formError) {
		if(this.settings['thirdPartyPopup'] != undefined && this.settings['thirdPartyPopup'] != null){
			this.settings['thirdPartyPopup'].alert();
		}else{
			if(this.settings['ShowPopup'] == true){
				if(this.tempOptions['popupTop'] != undefined && this.tempOptions['popupTop'] != null){
					this.alert("Errors Found",this.errorMessages,this.tempOptions['popupTop']);
				}else{
					this.alert("Errors Found",this.errorMessages,-1);
				}
				
			}
		}
    }
});
	
	
FormValidation.method('checkValues' , function(options) {
	this.tempOptions = options;
	var that = this;
	this.formError = false;
	this.errorMessages = "";
	this.formObject = new Object();
	var validate = function (inputObject) {
		if(that.settings['LabelErrorClass'] != false){
			$("label[for='" + name + "']").removeClass(that.settings['LabelErrorClass']);
		}
	    var id = inputObject.attr("id");
	    var name = inputObject.attr("name");
	    var type = inputObject.attr("type");
	    
	    if(inputObject.hasClass('select2-focusser') || inputObject.hasClass('select2-input')){
	    	return true;
	    }
		
	    if(jQuery.inArray(type, that.inputTypes ) >= 0) {
	    	if(inputObject.hasClass('uploadInput')){
	    		inputValue = inputObject.attr("val");
	    	//}else if(inputObject.hasClass('datetimeInput')){
	    		//inputValue = inputObject.getDate()+":00";
	    	}else{
	    		//inputValue = (type == "radio" || type == "checkbox")?$("input[name='" + name + "']:checked").val():inputObject.val();
	    		
	    		inputValue = null;
	    		if(type == "radio" || type == "checkbox"){
	    			inputValue = $("input[name='" + name + "']:checked").val();
	    		}else if(inputObject.hasClass('select2Field')){
	    			if($('#'+id).select2('data') != null && $('#'+id).select2('data') != undefined){
	    				inputValue = $('#'+id).select2('data').id;
	    			}else{
	    				inputValue = "";
	    			}
	    			
	    		}else if(inputObject.hasClass('select2Multi')){
	    			if($('#'+id).select2('data') != null && $('#'+id).select2('data') != undefined){
	    				inputValueObjects = $('#'+id).select2('data');
	    				inputValue = [];
	    				for(var i=0;i<inputValueObjects.length;i++){
	    					inputValue.push(inputValueObjects[i].id);
	    				}
	    				inputValue = JSON.stringify(inputValue);
	    			}else{
	    				inputValue = "";
	    			}
                }else if(inputObject.hasClass('signatureField')){
                    if($('#'+id).data('signaturePad').isEmpty()){
                        inputValue = '';
                    }else{
                        inputValue = $('#'+id).data('signaturePad').toDataURL();
                    }
				}else if(inputObject.hasClass('simplemde')){
					inputValue = $('#'+id).data('simplemde').value();

				}else if(inputObject.hasClass('tinymce')){
					inputValue = tinyMCE.get(id).getContent({format : 'raw'});

                }else{
	    			inputValue = inputObject.val();
	    		}
	    	}
		   
		    var validation = inputObject.attr('validation');
		    var valid = false;
		    
		    if(validation != undefined && validation != null &&  that.validator[validation] != undefined && that.validator[validation] != null){
		    	valid = that.validator[validation](inputValue);
		    	
		    }else{

		    	if(that.validateAll){
		    		if(validation != undefined && validation != null && validation == "none"){
		    			valid = true;
		    		}else{
		    			valid = that.validator['input'](inputValue);
		    		}
		    		
		    	}else{
		    		valid = true;
		    	}
		    	$(that.formObject).attr(id,inputValue);
		    }
		    
		    if(!valid) {
	    		that.addError(inputObject, null);
	    	}else{
	    		that.clearError(inputObject, null);
	    		$(that.formObject).attr(id,inputValue);
	    	}
	    }
		
	};  

	var inputs = $('#'+ this.formId + " :input");
    inputs.each(function() {
    	var that = $(this);
    	validate(that);
    });
    
    inputs = $('#'+ this.formId + " .uploadInput");
    inputs.each(function() {
    	var that = $(this);
    	validate(that);
    });

    this.showErrors();
    this.tempOptions = {};
	return !this.formError;
});

FormValidation.method('getFormParameters' , function() {
	return this.formObject;
});


FormValidation.method('alert', function (title,text,top) {
	alert(text);
});




