<?php
$logoFileName = CLIENT_BASE_PATH."data/logo.png";
$logoFileUrl = CLIENT_BASE_URL."data/logo.png";
if(!file_exists($logoFileName)){
	$logoFileUrl = BASE_URL."images/logo.png";
}
?><!DOCTYPE html>
<html>
    <head>

        <?php if (!empty(\Classes\BaseService::getInstance()->getGAKey())) { ?>
            <!-- Google Analytics -->
            <script>
              window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
              ga('create', '<?=\Classes\BaseService::getInstance()->getGAKey()?>', 'auto');
              ga('send', 'pageview');
            </script>
            <script async src='https://www.google-analytics.com/analytics.js'></script>
            <!-- End Google Analytics -->
        <?php } else { ?>
            <script>window.ga = [];</script>
        <?php } ?>

        <meta charset="utf-8">
        <title><?=$companyName?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="https://icehrm.s3.amazonaws.com/images/icon16.png">
        <meta name="author" content="">

	    <link rel="image_src" href="<?=!empty($meta->imageUrl)?$meta->imageUrl:$logoFileUrl?>"/>
    	<meta property="og:image" content="<?=!empty($meta->imageUrl)?$meta->imageUrl:$logoFileUrl?>"/>
    	<meta property="og:url" content="<?=$meta->url?>"/>
    	<meta property="og:title" content="<?=$meta->title?>"/>
    	<meta property="og:description" content="<?=$meta->description?>"/>


        <link href="<?=BASE_URL?>dist/third-party.css?v=<?=$cssVersion?>" rel="stylesheet">
        <script type="text/javascript" src="<?=BASE_URL?>dist/third-party.js?v=<?=$jsVersion?>"></script>
        <script type="text/javascript" src="<?=BASE_URL?>dist/common.js?v=<?=$jsVersion?>"></script>

        <!-- Can not bundle - Start-->
        <script src="<?=BASE_URL?>js/jquery.timepicker.js"></script>
        <script src="<?=BASE_URL?>js/bootstrap-datetimepicker.js"></script>
        <link href="<?=BASE_URL?>bower_components/flag-icon-css/css/flag-icon.min.css" rel="stylesheet">
        <!-- Can not bundle - End-->
        <script type="text/javascript" src="<?=BASE_URL.'admin/dist/candidates.js'?>?v=<?=$jsVersion?>"></script>
        <script>
          var baseUrl = '<?=CLIENT_BASE_URL?>service.php';
          var CLIENT_BASE_URL = '<?=CLIENT_BASE_URL?>';
        </script>

  	</head>
<body>
