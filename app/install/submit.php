<?php
include dirname(__FILE__).'/config.php';
include(CLIENT_APP_PATH.'../core/src/MyORM/MySqlActiveRecord.php');


function clean_input($input, $html = false) {
    $input = str_replace([';', '#', "'", '"'], '', $input);
    return htmlentities($input, ENT_QUOTES, 'UTF-8');
}

$inputList = [
   'APP_HOST',
   'APP_USERNAME',
   'APP_PASSWORD',
   'APP_DB',
   'LOG',
   'BASE_URL',
];

foreach ($inputList as $key) {
    if (empty($_REQUEST[$key])) {
        $_REQUEST[$key] = '';
    }
    $cleaned = clean_input($_REQUEST[$key]);
    if ($cleaned !== $_REQUEST[$key]) {
        $ret["status"] = "ERROR";
        $ret["msg"] = "The value provided for $key is not valid.";
        echo json_encode($ret);
        exit();
    }
}


$isConfigFileExists = file_exists(CLIENT_APP_PATH."config.php");
$configData = file_get_contents(CLIENT_APP_PATH."config.php");

error_log("isConfigFileExists $isConfigFileExists");
error_log("configData $configData");

$ret = array();

if(!$isConfigFileExists || $configData != ""){
    $ret["status"] = "ERROR";
    $ret["msg"] = "You are trying to install IceHrm on an existing installation.";
    echo json_encode($ret);
    exit();
}

$action = $_REQUEST['action'];

if($action == "TEST_DB"){

//	$db = NewADOConnection('mysqli');
//	$res = $db->Connect($_REQUEST["APP_HOST"], $_REQUEST["APP_USERNAME"], $_REQUEST["APP_PASSWORD"], $_REQUEST["APP_DB"]);

    $db = new \MyORM\MySqlActiveRecord();
    $res = $db->Connect(
        $_REQUEST["APP_HOST"],
        $_REQUEST["APP_USERNAME"],
        $_REQUEST["APP_PASSWORD"],
        $_REQUEST["APP_DB"]
    );

    if (!$res){
        error_log('Could not connect: ' . $db->ErrorMsg());
        $ret["status"] = "ERROR";
        $ret["msg"] = "Incorrect credentials or incorrect DB host :".$db->ErrorMsg();
        echo json_encode($ret);
        exit();
    }

    $result = $db->Execute("Show tables");
    $num_rows = count($result);
    error_log(print_r("Number of tables:".$num_rows,true));

    if($num_rows != 0){
        $ret["status"] = "ERROR";
        $ret["msg"] = "Database is not empty";
        echo json_encode($ret);
        exit();
    }

    $ret["status"] = "SUCCESS";
    $ret["msg"] = "Successfully connected to the database";
    echo json_encode($ret);

}else if($action == "INS"){

    $config = file_get_contents(CLIENT_APP_PATH."config.sample.php");

    if(empty($config)){
        error_log('Sample config file is empty');
        $ret["status"] = "ERROR";
        $ret["msg"] = "Sample config file not found";
        echo json_encode($ret);
        exit();
    }

    $config = str_replace("_LOG_", $_REQUEST['LOG'], $config);
    $config = str_replace("_APP_BASE_PATH_", APP_PATH, $config);
    $config = str_replace("_CLIENT_BASE_PATH_", CLIENT_APP_PATH, $config);
    $config = str_replace("_BASE_URL_", $_REQUEST['BASE_URL'], $config);
    $config = str_replace("_CLIENTBASE_URL_", $_REQUEST['BASE_URL']."app/", $config);
    $config = str_replace("_APP_DB_", $_REQUEST['APP_DB'], $config);
    $config = str_replace("_APP_USERNAME_", $_REQUEST['APP_USERNAME'], $config);
    $config = str_replace("_APP_PASSWORD_", $_REQUEST['APP_PASSWORD'], $config);
    $config = str_replace("_APP_HOST_", $_REQUEST['APP_HOST'], $config);
    $config = str_replace("_CLIENT_", 'app', $config);


//	$db = NewADOConnection('mysqli');
//	$res = $db->Connect($_REQUEST["APP_HOST"], $_REQUEST["APP_USERNAME"], $_REQUEST["APP_PASSWORD"], $_REQUEST["APP_DB"]);

    $db = new \MyORM\MySqlActiveRecord();
    $res = $db->Connect($_REQUEST["APP_HOST"], $_REQUEST["APP_USERNAME"], $_REQUEST["APP_PASSWORD"], $_REQUEST["APP_DB"]);


    if (!$res){
        error_log('Could not connect: ' . $db->ErrorMsg());
        $ret["status"] = "ERROR";
        $ret["msg"] = "Incorrect credentials or incorrect DB host. ".'Could not connect: ' . $db->ErrorMsg();
        echo json_encode($ret);
        exit();
    }

    $result = $db->Execute("Show tables");
    $num_rows = count($result);
    error_log(print_r("Number of tables:".$num_rows,true));
    if($num_rows != 0){
        $ret["status"] = "ERROR";
        $ret["msg"] = "Database is not empty";
        echo json_encode($ret);
        exit();
    }


    //Run create table script
    $insql = file_get_contents(CLIENT_APP_PATH."../core/scripts/".APP_ID."db.sql");
    $sql_list = preg_split('/;/',$insql);
    foreach($sql_list as $sql){
        if (preg_match('/^\s+$/', $sql) || $sql == '') { # skip empty lines
            continue;
        }
        $db->Execute($sql);
    }

    //Run create table script
    $insql = file_get_contents(CLIENT_APP_PATH."../core/scripts/".APP_ID."_master_data.sql");
    $sql_list = preg_split('/;/',$insql);
    foreach($sql_list as $sql){
        if (preg_match('/^\s+$/', $sql) || $sql == '') { # skip empty lines
            continue;
        }
        $db->Execute($sql);
    }


    //Write config file

    $file = fopen(CLIENT_APP_PATH."config.php","w");
    if($file){
        fwrite($file,$config);
        fclose($file);
    }else{
        error_log('Unable to write configurations to file');
        $ret["status"] = "ERROR";
        $ret["msg"] = "Unable to write configurations to file";
        echo json_encode($ret);
        exit();
    }

    $ret["status"] = "SUCCESS";
    $ret["msg"] = "Successfully installed. Please rename or delete install folder";
    echo json_encode($ret);
}
