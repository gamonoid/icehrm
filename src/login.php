<?php
define('CLIENT_PATH',dirname(__FILE__));
include ("config.base.php");
include ("include.common.php"); 
include("server.includes.inc.php");
if(empty($user)){
	if(!empty($_REQUEST['username']) && !empty($_REQUEST['password'])){
		$suser = null;
		$ssoUserLoaded = false;
		
		include 'login.com.inc.php';
		
		if(empty($suser)){
			$suser = new User();
			$suser->Load("(username = ? or email = ?) and password = ?",array($_REQUEST['username'],$_REQUEST['username'],md5($_REQUEST['password'])));
		}
		
		if($suser->password == md5($_REQUEST['password']) || $ssoUserLoaded){
			$user = $suser;
			SessionUtils::saveSessionObject('user', $user);
			$suser->last_login = date("Y-m-d H:i:s");
			$suser->Save();
			
			if(!$ssoUserLoaded && !empty(BaseService::getInstance()->auditManager)){
				BaseService::getInstance()->auditManager->user = $user;
				BaseService::getInstance()->audit(IceConstants::AUDIT_AUTHENTICATION, "User Login");
			}
			
			if($user->user_level == "Admin"){
				header("Location:".HOME_LINK_ADMIN);	
			}else{
				header("Location:".HOME_LINK_OTHERS);	
			}
		}else{
			header("Location:".CLIENT_BASE_URL."login.php?f=1");
		}			
	}
}else{
	if($user->user_level == "Admin"){
		header("Location:".HOME_LINK_ADMIN);	
	}else{
		header("Location:".HOME_LINK_OTHERS);	
	}
	
}

$tuser = SessionUtils::getSessionObject('user');
//check user

$logoFileName = CLIENT_BASE_PATH."data/logo.png";
$logoFileUrl = CLIENT_BASE_URL."data/logo.png";
if(!file_exists($logoFileName)){
	$logoFileUrl = BASE_URL."images/logo.png";	
}

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?=APP_NAME?> Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="<?=BASE_URL?>bootstrap/css/bootstrap.css" rel="stylesheet">
	
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.js"></script>
    <script src="<?=BASE_URL?>bootstrap/js/bootstrap.js"></script>
	<script src="<?=BASE_URL?>js/jquery.placeholder.js"></script>
	<script src="<?=BASE_URL?>js/jquery.dataTables.js"></script>
	<script src="<?=BASE_URL?>js/bootstrap-datepicker.js"></script>
    <link href="<?=BASE_URL?>bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="<?=BASE_URL?>css/DT_bootstrap.css?v=0.4" rel="stylesheet">
    <link href="<?=BASE_URL?>css/datepicker.css" rel="stylesheet">
    <link href="<?=BASE_URL?>css/style.css?v=<?=$cssVersion?>" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
	
	<style type="text/css">
      /* Override some defaults */
      html, body {
        background-color: #829AA8;
      }
      body {
        padding-top: 40px; 
      }
      .container {
        width: 300px;
      }

      /* The white background content wrapper */
      .container > .content {
        background-color: #fff;
        padding: 20px;
        margin: 0 -20px; 
        -webkit-border-radius: 10px 10px 10px 10px;
           -moz-border-radius: 10px 10px 10px 10px;
                border-radius: 10px 10px 10px 10px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.15);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.15);
                box-shadow: 0 1px 2px rgba(0,0,0,.15);
      }

	  .login-form {
		margin-left: 65px;
	  }
	
	  legend {
		margin-right: -50px;
		font-weight: bold;
	  	color: #404040;
	  }

    </style>
	
	
  </head>

  <body>
  
  <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '<?=BaseService::getInstance()->getGAKey()?>', 'gamonoid.com');
  ga('send', 'pageview');

  </script>
  
  <script type="text/javascript">
	var key = "";
  <?php if(isset($_REQUEST['key'])){?>
  	key = '<?=$_REQUEST['key']?>';
  	key = key.replace(/ /g,"+");
  <?php }?>

  $(document).ready(function() {
	  $(window).keydown(function(event){
	    if(event.keyCode == 13) {
	      event.preventDefault();
	      return false;
	    }
	  });

	  $("#password").keydown(function(event){
		    if(event.keyCode == 13) {
		      submitLogin();
		      return false;
		    }
		  });
	});

  function showForgotPassword(){
	  $("#loginForm").hide();
	  $("#requestPasswordChangeForm").show();
  }

  function requestPasswordChange(){
	  $("#requestPasswordChangeFormAlert").hide();
	  var id = $("#usernameChange").val();
	  $.post("service.php", {'a':'rpc','id':id}, function(data) {
			if(data.status == "SUCCESS"){
				$("#requestPasswordChangeFormAlert").show();	
				$("#requestPasswordChangeFormAlert").html(data.message);
			}else{
				$("#requestPasswordChangeFormAlert").show();	
				$("#requestPasswordChangeFormAlert").html(data.message);
			}
	},"json");
  }

  function changePassword(){
	  $("#newPasswordFormAlert").hide();
	  var password = $("#password").val();

	  	var passwordValidation =  function (str) {  
			var val = /^[a-zA-Z0-9]\w{6,}$/;  
			return str != null && val.test(str);  
		};
	
		
		if(!passwordValidation(password)){
			$("#newPasswordFormAlert").show();	
			$("#newPasswordFormAlert").html("Password may contain only letters, numbers and should be longer than 6 characters");
			return;
		}

	  
	  $.post("service.php", {'a':'rsp','key':key,'pwd':password,"now":"1"}, function(data) {
		  if(data.status == "SUCCESS"){
			  top.location.href = "login.php?c=1";
			}else{
				$("#newPasswordFormAlert").show();	
				$("#newPasswordFormAlert").html(data.message);
			}
	},"json");
  }

  function submitLogin(){
	$("#loginForm").submit();  
  }
  
  </script>
	<div class="container">
		<div class="content" style="margin-top:100px;">
			<div class="row">
				<div class="login-form">
					<h2><img src="<?=$logoFileUrl?>"/></h2>
					<?php if(!isset($_REQUEST['cp'])){?>
					<form id="loginForm" action="login.php" method="POST">
						<fieldset>
							<div class="clearfix">
								<div class="input-prepend">
								  	<span class="add-on"><i class="icon-user"></i></span>
								  	<input class="span2" type="text" id="username" name="username" placeholder="Username">
								</div>
							</div>
							<div class="clearfix">
								<div class="input-prepend">
								  	<span class="add-on"><i class="icon-lock"></i></span>
								  	<input class="span2" type="password" id="password" name="password" placeholder="Password">
								</div>
							</div>
							<?php if(isset($_REQUEST['f'])){?>
							<div class="clearfix alert alert-error" style="font-size:11px;width:147px;margin-bottom: 5px;">
								Login failed
								<?php if(isset($_REQUEST['fm'])){
									echo $_REQUEST['fm'];	
								}?>
							</div>
							<?php } ?>
							<?php if(isset($_REQUEST['c'])){?>
							<div class="clearfix alert alert-info" style="font-size:11px;width:147px;margin-bottom: 5px;">
								Password changed successfully	
							</div>
							<?php } ?>
							<button class="btn" style="margin-top: 5px;" type="button" onclick="submitLogin();return false;">Sign in&nbsp;&nbsp;<span class="icon-arrow-right"></span></button>
						</fieldset>
						<div class="clearfix">
							<a href="" onclick="showForgotPassword();return false;" style="float:left;margin-top: 10px;">Forgot password</a>
							<a href="<?=TWITTER_URL?>" target="_blank" style="float:right;"><img src="<?=BASE_URL?>images/32x32-Circle-53-TW.png"/></a>
							<a href="<?=FB_URL?>" target="_blank" style="float:right;margin-right: 7px;"><img src="<?=BASE_URL?>images/32x32-Circle-54-FB.png"/></a>
						</div>
					</form>
					<form id="requestPasswordChangeForm" style="display:none;" action="">
						<fieldset>
							<div class="clearfix">
								<div class="input-prepend">
								  	<span class="add-on"><i class="icon-user"></i></span>
								  	<input class="span2" type="text" id="usernameChange" name="usernameChange" placeholder="Username or Email">
								</div>
							</div>
							<div id="requestPasswordChangeFormAlert" class="clearfix alert alert-info" style="font-size:11px;width:147px;margin-bottom: 5px;display:none;">
									
							</div>
							<button class="btn" style="margin-top: 5px;" type="button" onclick="requestPasswordChange();return false;">Request Password Change&nbsp;&nbsp;<span class="icon-arrow-right"></span></button>
						</fieldset>
					</form>
					<?php }else{?>
					<form id="newPasswordForm" action="">
						<fieldset>
							<div class="clearfix">
								<div class="input-prepend">
								  	<span class="add-on"><i class="icon-lock"></i></span>
								  	<input class="span2" type="password" id="password" name="password" placeholder="New Password">
								</div>
							</div>
							<div id="newPasswordFormAlert" class="clearfix alert alert-error" style="font-size:11px;width:147px;margin-bottom: 5px;display:none;">
									
							</div>
							<button class="btn" style="margin-top: 5px;" type="button" onclick="changePassword();return false;">Change Password&nbsp;&nbsp;<span class="icon-arrow-right"></span></button>
						</fieldset>
					</form>
					<?php }?>
				</div>
			</div>
		</div>
	</div> <!-- /container -->
</body>
</html>
