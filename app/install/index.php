<?php
require dirname(__FILE__)."/config.php";
$isConfigFileExists = file_exists(CLIENT_APP_PATH."config.php");

$errorMap = array();

if($isConfigFileExists){
	$data = file_get_contents(CLIENT_APP_PATH."config.php");
	if($data != ""){
		$errorMap[] = array("important","A configuration file exists","Application is already installed. If you want to reinstall, please delete the config file, clear data folder and use a new database during the installation.");	
	}	
}else{
	$file = fopen(CLIENT_APP_PATH."config.php","w");
	fwrite($file,"");
	fclose($file);
}

$isConfigFileWriteable = is_writable(CLIENT_APP_PATH."config.php");
error_log("Config writable ".$isConfigFileWriteable);
error_log("Config exists ".file_exists(CLIENT_APP_PATH."config.php"));
if(!$isConfigFileWriteable){
	$errorMap[] = array("important","Configuration file [".CLIENT_APP_PATH."config.php] is not writable","Make this file writable",array("sudo touch ".CLIENT_APP_PATH."config.php","sudo chmod 777 ".CLIENT_APP_PATH."config.php"));				
}

$isConfigSampleFileExists = file_exists(CLIENT_APP_PATH."config.sample.php");

if(!$isConfigSampleFileExists){
	$errorMap[] = array("important","Sample configuration file doesn't exists","Please check :".CLIENT_APP_PATH."config.sample.php");			
}

$isDataFolderExists = is_dir(CLIENT_APP_PATH."data");
$isDataFolderWritable = false;


if(!$isDataFolderExists){
	$errorMap[] = array("important","Data directory does not exists","Please create directory :".CLIENT_APP_PATH."data",array("sudo mkdir ".CLIENT_APP_PATH."data"));	
}else{
	$file = fopen(CLIENT_APP_PATH."data/test.txt","w");
	if($file){
		fwrite($file,"Test file write");
		fclose($file);
		
		$data = file_get_contents(CLIENT_APP_PATH."data/test.txt");
		
		if($data == "Test file write"){
			$isDataFolderWritable = true;	
		}
		unlink(CLIENT_APP_PATH."data/test.txt");
	}
	if(!$isDataFolderWritable){
		$errorMap[] = array("important","Data folder is not writable","Provide wirte permission to the web server user to ".CLIENT_APP_PATH."data",array("sudo chmod 777 ".CLIENT_APP_PATH."data"));		
	}
}




?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>IceHRM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
	
	<script type="text/javascript" src="../../js/jquery.js"></script>
    <script src="bootstrap/js/bootstrap.js"></script>
    <link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="styles.css?v=2" rel="stylesheet">
    
    <script type="text/javascript" src="../../js/date.js"></script>
	<script type="text/javascript" src="../../js/json2.js"></script>
	<script type="text/javascript" src="../../js/CrockfordInheritance.v0.1.js"></script>

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="bootstrap/ico/favicon.ico">
	<!-- IE Fix for HTML5 Tags -->
	<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	
  </head>

  <body>
  	
  	<script type="text/javascript">

  	$(document).ready(function() {
  		var url = top.location.href;
  		
  		url = url.substring(0,url.lastIndexOf('/app'));
  		$("#BASE_URL").val(url);	
  	});

  	function testDB(){
  	  	var request = {};
  	  	request["APP_DB"] = $("#APP_DB").val();
  	  	request["APP_USERNAME"] = $("#APP_USERNAME").val();
  	  	request["APP_PASSWORD"] = $("#APP_PASSWORD").val();
  	  	request["APP_HOST"] = $("#APP_HOST").val();
  	  	request["action"] = "TEST_DB";

	  	$.post("submit.php",request , function(data) {
	  		if(data.status == "SUCCESS"){
	  			alert(data.msg);
	  			$("#installBtn").removeAttr('disabled');
	  		}else{
	  			alert(data.msg);
	  		}
	  	},"json");
  	}

  	function install(){
  	  	var request = {};
  	  	request["APP_DB"] = $("#APP_DB").val();
  	  	request["APP_USERNAME"] = $("#APP_USERNAME").val();
  	  	request["APP_PASSWORD"] = $("#APP_PASSWORD").val();
  	  	request["APP_HOST"] = $("#APP_HOST").val();
  	  	request["action"] = "INS";

  	  	request["LOG"] = $("#LOG").val();
  	  	request["BASE_URL"] = $("#BASE_URL").val();

  	  	if(request["BASE_URL"] == undefined || request["BASE_URL"] == null 
  	    	  	|| request["BASE_URL"] == ""){
  	  		alert("Invalid Base URL");
    	  	return;
  	  	}

  	  	if(request["BASE_URL"].indexOf("http://") == 0 || request["BASE_URL"].indexOf("https://") == 0){
  	  	}else{
  	  	  	alert("Invalid Base URL");
  	  	  	return;
  	  	}

  	  	if(!endsWith(request["BASE_URL"],"/")){
  	  		request["BASE_URL"] = request["BASE_URL"] + "/";  	
  	  	}
  	  	$("#installBtn").attr('disabled','disabled');
	  	$.post("submit.php",request , function(data) {
	  		if(data.status == "SUCCESS"){
	  			alert(data.msg);
	  			top.location.href = request["BASE_URL"]+"app/";
	  		}else{
	  			alert(data.msg);
	  			$("#installBtn").removeAttr('disabled');
	  		}
	  	},"json");
  	}

  	function endsWith(str,pattern) {
  	    var d = str.length - pattern.length;
  	    return d >= 0 && str.lastIndexOf(pattern) === d;
  	};
  	
  	</script>
  	
  	<div class="container-fluid bgbody" style="max-width:800px;padding-top:10px;margin:auto">
  	<h1>IceHRM Installation</h1>
  	<p class="p1">
  	Please do not install this application if you have already installed (this could break existing installation)
  	</p>
  	<?php if(count($errorMap)>0){?>
	  	<?php foreach($errorMap as $error){?>
	  	<p class="p2">
	  	<!--  
	  	<span style="" class="label label-<?=$error[0]?>"><?=$error[1]?></span><br/>
	  	-->
	  	<span style="font-size:14px;color:red;font-weight: bold;"><?=$error[1]?></span><br/>
	  	<?=$error[2]?><br/>
	  		<?php if(!empty($error[3]) && is_array($error[3])){?>
	  			
		  		<?php foreach($error[3] as $command){?>
		  		<span class="label label-inverse">
		  		<?=$command?></span><br/>
		  		<?php }?>
		  		
		  	<?php }?>
	  	</p>
		<hr/>  	
	  	<?php }?>
	  	Once above errors are corrected, please reload the page<br/><br/>
	  	<button onclick="location.reload();;return false;" class="btn">Reload</button>
	  <?php }else{?>
	  		<form class="form-horizontal" id="install_step1">
				<div class="control-group">
					<div class="controls">
					  	<span class="label label-warning" id="install_step1_error" style="display:none;"></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="LOG">Log file path</label>
				<div class="controls">
				  	<input class="input-xxlarge" type="text" id="LOG" name="LOG" value="data/icehrm.log"/>
				  	<span class="help-inline p1">Keep this empty if you want logs to be in web server's default logs</span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="BASE_URL">App Url</label>
				<div class="controls">
				  	<input class="input-xxlarge" type="text" id="BASE_URL" name="BASE_URL" value=""/>
				  	<span class="help-inline p1">This is the web path to folder that you copy icehrm sources (e.g http://yourdomain.com/icehrm/)</span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="APP_DB">MySql Database Name</label>
				<div class="controls">
				  	<input class="input-xxlarge" type="text" id="APP_DB" name="APP_DB" value="icehrmdb"/>
				  	<span class="help-inline p1">Application DB Name</span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="APP_USERNAME">Database User</label>
				<div class="controls">
				  	<input class="input-xxlarge" type="text" id="APP_USERNAME" name="APP_USERNAME" value="icehrmuser"/>
				  	<span class="help-inline p1">Database username</span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="APP_PASSWORD">Database User Password</label>
				<div class="controls">
				  	<input class="input-xxlarge" type="password" id="APP_PASSWORD" name="APP_PASSWORD" value=""/>
				  	<span class="help-inline p1">Database user's password</span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="APP_HOST">Database Host</label>
				<div class="controls">
				  	<input class="input-xxlarge" type="text" id="APP_HOST" name="APP_HOST" value="localhost"/>
				  	<span class="help-inline p1">MySql DB Host</span>
				</div>
			</div>
			<div class="control-group">
		    	<div class="controls">
		      		<button id="testBtn" onclick="testDB();return false;" class="btn">Test Database Connectivity</button>
		      		<button id="installBtn" onclick="install();return false;" class="btn" disabled="disabled">Install Application</button>
		    	</div>
		  	</div>
		</form>
	<?php }?>
  	</div>
  
  
  	<div class="row-fluid" style="height:10px;">
      <div class="span12" style="padding:5px;">
        <p style="text-align:center;font-size: 10px;">
        <?=APP_NAME?> All rights reserved.
    	</p>
      </div>
    </div>
  </body>
  </html>