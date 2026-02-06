<?php

use Classes\IceResponse;
use Employees\Services\UserInvitationService;

include 'includes.inc.php';
$logoFileUrl = \Classes\UIManager::getInstance()->getCompanyLogoUrl();

if (empty($_REQUEST['hash'])) {
	header("Location:".CLIENT_BASE_URL."login.php");
}
$uiService = new UserInvitationService();
$ui = $uiService->getInvitationByHash($_REQUEST['hash']);

if (empty($ui)) {
	header("Location:".CLIENT_BASE_URL."login.php");
}

$resp = $uiService->processUserInvitation($ui);

if ($resp->getStatus() === IceResponse::SUCCESS) {
    $title = 'Thank you for accepting the invitation';
    $message = 'We have email you a temporary password to login. Please check your email inbox and login to your <a href="'.CLIENT_BASE_URL.'">Company HR portal here.</a>';
} else {
	$title = 'Error occurred while processing the invitation';
    $message = $resp->getData();
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>IceHrm Invitation to Join</title>
	<link rel="icon" type="image/png" href="https://icehrm.s3.amazonaws.com/images/icon16.png">
	<link rel="shortcut icon" href="https://icehrm.s3.amazonaws.com/images/icon16.png">
	<style>
        body {
            background-color: #fff;
            text-align: center;
            padding: 150px; }
        h1 {
            font-size: 50px; }
        body {
            font: 20px Helvetica, sans-serif; color: #003366;
        }
	</style>
</head>
<body>
<img id="img360" src="<?=$logoFileUrl?>">
<h3 style="color: #006bb3"><?=$title?></h3>
<p><?=$message?></p>
</body>
</html>
