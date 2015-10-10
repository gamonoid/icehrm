<?php
error_reporting(E_ERROR);
$fileTypesImages  = "jpg,bmp,gif,png,jpeg"; // images
$fileTypesDocuments  = "csv,doc,xls,docx,xlsx,txt,ppt,pptx,rtf,pdf,xml"; // documents
if($_REQUEST['file_type']=="image"){
	$fileTypes = $fileTypesImages;
}else if($_REQUEST['file_type']=="doc"){
	$fileTypes = $fileTypesDocuments;
}else if($_REQUEST['file_type']=="all"){
	$fileTypes = $fileTypesDocuments.",".$fileTypesImages;
}else{
	$fileTypes = $fileTypesDocuments.",".$fileTypesImages;
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.js?v=1.1"></script>
<script type="text/javascript" src="https://webstalk-js.s3.amazonaws.com/date.js"></script>
<script type="text/javascript" src="https://webstalk-js.s3.amazonaws.com/json2.js"></script>

</head>
<body>
<script>
function uploadfile(){
	var form = document.getElementById('upload_data');
	form.submit();
}

function checkFileType (elementName, fileTypes) {
	var fileElement = document.getElementById(elementName);
	var fileExtension = "";
	if (fileElement.value.lastIndexOf(".") > 0) {
		fileExtension = fileElement.value.substring(fileElement.value.lastIndexOf(".") + 1, fileElement.value.length);
	}
	
	fileExtension = fileExtension.toLowerCase();
	
	var allowed = fileTypes.split(",");
	
	if (allowed.indexOf(fileExtension) < 0) {
		fileElement.value = "";
		alert('Selected file type is not supported');
		clearFileElement(elementName);
		return false;
	}
	
	return true;
	
}

function clearFileElement (elementName) {

	var control = $("#"+elementName);
	control.replaceWith( control = control.val('').clone( true ) );
}
</script>
	<div id="upload_form">
	<form id="upload_data" method="post" action="<?=CLIENT_BASE_URL?>fileupload.php" enctype="multipart/form-data">
	<input id="file_name" name="file_name" type="hidden" value="<?=$_REQUEST['id']?>"/>
	<input id="file_group" name="file_group" type="hidden" value="<?=$_REQUEST['file_group']?>"/>
	<input id="user" name="user" type="hidden" value="<?=$_REQUEST['user']?>"/>
	<label id="upload_status"><?=$_REQUEST['msg']?></label><input id="file" name="file"  type="file" onChange="if(checkFileType('file','<?=$fileTypes?>')){uploadfile();}"></input>
	</form>
	</div>
	<div id="upload_result" style="display:none;text-align: center;">
		<p id="upload_result_header"></p>
		<p id="upload_result_body"></p>
	</div>
<script type="text/javascript">document.body.style.overflow = 'hidden';</script>
</body>
</html>