<?php

if ($_REQUEST['editor'] === '1') {
	include("editor-upload.php");
} else {
	include("common-upload.php");
}
