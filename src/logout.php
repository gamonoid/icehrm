<?php
session_start();
$_SESSION['user'] = null;
session_destroy();
session_write_close();
$user = null;
header("Location:".CLIENT_BASE_URL."login.php");