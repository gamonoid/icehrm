<?php
$logoFileName = CLIENT_BASE_PATH."data/logo.png";
$logoFileUrl = CLIENT_BASE_URL."data/logo.png";
if(!file_exists($logoFileName)){
	$logoFileUrl = BASE_URL."images/logo.png";
}
?><!DOCTYPE html>
<html>
    <head>
	    <meta charset="utf-8">
	    <title><?=$meta->title?></title>
	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	    <meta name="description" content="">
	    <meta name="author" content="">

	    <link rel="image_src" href="<?=!empty($meta->imageUrl)?$meta->imageUrl:$logoFileUrl?>"/>
    	<meta property="og:image" content="<?=!empty($meta->imageUrl)?$meta->imageUrl:$logoFileUrl?>"/>
    	<meta property="og:url" content="<?=$meta->url?>"/>
    	<meta property="og:title" content="<?=$meta->title?>"/>
    	<meta property="og:description" content="<?=$meta->description?>"/>


	    <link href="<?=BASE_URL?>themecss/bootstrap.min.css" rel="stylesheet">
	    <link href="<?=BASE_URL?>themecss/font-awesome.min.css" rel="stylesheet">
	    <link href="<?=BASE_URL?>themecss/ionicons.min.css" rel="stylesheet">




		<script type="text/javascript" src="<?=BASE_URL?>js/jquery2.0.2.min.js"></script>
	    <script type="text/javascript" src="<?=BASE_URL?>js/jquery-ui.js"></script>

	    <script src="<?=BASE_URL?>themejs/bootstrap.js"></script>
		<script src="<?=BASE_URL?>js/jquery.placeholder.js"></script>
		<script src="<?=BASE_URL?>js/base64.js"></script>


		<script src="<?=BASE_URL?>js/bootstrap-datepicker.js"></script>
		<script src="<?=BASE_URL?>js/jquery.timepicker.js"></script>
		<script src="<?=BASE_URL?>js/bootstrap-datetimepicker.js"></script>
		<script src="<?=BASE_URL?>js/fullcalendar.min.js"></script>
		<script src="<?=BASE_URL?>js/select2/select2.min.js"></script>
		<script src="<?=BASE_URL?>js/bootstrap-colorpicker-2.1.1/js/bootstrap-colorpicker.min.js"></script>



	    <link href="<?=BASE_URL?>themecss/datatables/dataTables.bootstrap.css" rel="stylesheet">
	    <link href="<?=BASE_URL?>css/jquery.timepicker.css" rel="stylesheet">
	    <link href="<?=BASE_URL?>css/datepicker.css" rel="stylesheet">
	    <link href="<?=BASE_URL?>css/bootstrap-datetimepicker.min.css" rel="stylesheet">
	    <link href="<?=BASE_URL?>css/fullcalendar.css" rel="stylesheet">
	    <link href="<?=BASE_URL?>js/select2/select2.css" rel="stylesheet">
	    <link href="<?=BASE_URL?>js/bootstrap-colorpicker-2.1.1/css/bootstrap-colorpicker.min.css" rel="stylesheet">


	    <link href="<?=BASE_URL?>themecss/AdminLTE.css" rel="stylesheet">

	    <script src="<?=BASE_URL?>themejs/plugins/datatables/jquery.dataTables.js"></script>
		<script src="<?=BASE_URL?>themejs/plugins/datatables/dataTables.bootstrap.js"></script>
		<script src="<?=BASE_URL?>themejs/AdminLTE/app.js"></script>


	    <link href="<?=BASE_URL?>css/style.css?v=<?=$cssVersion?>" rel="stylesheet">

	    <script type="text/javascript" src="<?=BASE_URL?>web/bower_components/tinymce/tinymce.min.js"></script>
	    <link href="<?=BASE_URL?>web/bower_components/simplemde/dist/simplemde.min.css" rel="stylesheet">
	    <script type="text/javascript" src="<?=BASE_URL?>web/bower_components/simplemde/dist/simplemde.min.js"></script>
	    <script type="text/javascript" src="<?=BASE_URL?>js/signature_pad.js"></script>
	    <script type="text/javascript" src="<?=BASE_URL?>js/date.js"></script>
		<script type="text/javascript" src="<?=BASE_URL?>js/json2.js"></script>
		<script type="text/javascript" src="<?=BASE_URL?>js/CrockfordInheritance.v0.1.js"></script>

		<script type="text/javascript" src="<?=BASE_URL?>api/Base.js?v=<?=$jsVersion?>"></script>
		<script type="text/javascript" src="<?=BASE_URL?>api/AdapterBase.js?v=<?=$jsVersion?>"></script>
		<script type="text/javascript" src="<?=BASE_URL?>api/FormValidation.js?v=<?=$jsVersion?>"></script>
		<script type="text/javascript" src="<?=BASE_URL?>api/Notifications.js?v=<?=$jsVersion?>"></script>
		<script type="text/javascript" src="<?=BASE_URL?>api/TimeUtils.js?v=<?=$jsVersion?>"></script>
		<script type="text/javascript" src="<?=BASE_URL?>api/AesCrypt.js?v=<?=$jsVersion?>"></script>
		<?php include APP_BASE_PATH.'/modulejslibs.inc.php';?>


	    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	    <!--[if lt IE 9]>
	    	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	    	<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
	    <![endif]-->
		<script>
				var baseUrl = '<?=CLIENT_BASE_URL?>service.php';
				var CLIENT_BASE_URL = '<?=CLIENT_BASE_URL?>';
		</script>
		<script type="text/javascript" src="<?=BASE_URL?>js/app-global.js"></script>

  	</head>
<body>
