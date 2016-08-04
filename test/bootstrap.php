<?php
if(file_exists('/usr/lib/php5/mysql.auth.php')){
    include '/usr/lib/php5/mysql.auth.php';
}
include(dirname(__FILE__).'/test.config.php');


if(!defined('MYSQL_ROOT_USER')){
    define('MYSQL_ROOT_USER', 'root');
    define('MYSQL_ROOT_PASS', '');
}

if(MYSQL_ROOT_PASS != '') {
    $dropDBCommand = 'echo "DROP DATABASE IF EXISTS ' . APP_DB . '"| mysql -u' . MYSQL_ROOT_USER . ' -p' . MYSQL_ROOT_PASS;
    $createDBCommand = 'echo "CREATE DATABASE '.APP_DB.'"| mysql -u'.MYSQL_ROOT_USER.' -p'.MYSQL_ROOT_PASS;
}else{
    $dropDBCommand = 'echo "DROP DATABASE IF EXISTS ' . APP_DB . '"| mysql -u' . MYSQL_ROOT_USER;
    $createDBCommand = 'echo "CREATE DATABASE '.APP_DB.'"| mysql -u'.MYSQL_ROOT_USER;
}


echo "Drop DB Command:".$dropDBCommand."\r\n";
exec($dropDBCommand);
echo "Create DB Command:".$createDBCommand."\r\n";
exec($createDBCommand);



//Run create table script

$scripts = array(
    APP_BASE_PATH."scripts/icehrmdb.sql",
    APP_BASE_PATH."scripts/icehrm_master_data.sql",
    APP_BASE_PATH."scripts/icehrm_sample_data.sql",
    //APP_BASE_PATH."scripts/unit_tests.sql"
);

foreach ($scripts as $insql){
    echo $insql."\r\n";
    if(MYSQL_ROOT_PASS != ''){
        $command = "cat ".$insql."| mysql -u".MYSQL_ROOT_USER." -p".MYSQL_ROOT_PASS." '".APP_DB."'";;
    }else{
        $command = "cat ".$insql."| mysql -u".MYSQL_ROOT_USER." '".APP_DB."'";
    }
    exec($command);
}
