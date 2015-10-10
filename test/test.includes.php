<?php
include('/usr/lib/php5/mysql.auth.php');
include(dirname(__FILE__).'/test.config.php');

//Mock Session class
if (!class_exists('SessionUtils')) {
	class SessionUtils{
		
		public static $data;
		
		public static function getSessionObject($name){
			if(empty(self::$data)){
				self::$data = array();
			}
			if(isset(self::$data[$name.CLIENT_NAME])){
				$obj = self::$data[$name.CLIENT_NAME];
			}
			if(empty($obj)){
				return null;
			}
			return json_decode($obj);
		}

		public static function saveSessionObject($name,$obj){
			if(empty(self::$data)){
				self::$data = array();
			}
			self::$data[$name.CLIENT_NAME] = json_encode($obj);
		}
	}


}





include (APP_BASE_PATH."/include.common.php");

$dropDBCommand = 'echo "DROP DATABASE IF EXISTS '.APP_DB.'"| mysql -u'.MYSQL_ROT_USER.' -p'.MYSQL_ROT_PASS;
$createDBCommand = 'echo "CREATE DATABASE '.APP_DB.'"| mysql -u'.MYSQL_ROT_USER.' -p'.MYSQL_ROT_PASS;

//echo "Drop DB Command:".$dropDBCommand."\r\n";
exec($dropDBCommand);
//echo "Create DB Command:".$createDBCommand."\r\n";
exec($createDBCommand);



//Run create table script
$insql = APP_BASE_PATH."scripts/icehrmdb.sql";
echo "Source File:".$insql."\r\n";

$command = "cat ".$insql."| mysql -u".MYSQL_ROT_USER." -p".MYSQL_ROT_PASS." ".APP_DB;
//echo "Command:".$insql."\r\n";
exec($command);

echo "Source File Done:".$insql."\r\n";


//Run create table script
$insql = APP_BASE_PATH."scripts/icehrm_master_data.sql";
//echo "Source File:".$insql."\r\n";

$command = "cat ".$insql."| mysql -u".MYSQL_ROT_USER." -p".MYSQL_ROT_PASS." ".APP_DB;
//echo "Command:".$insql."\r\n";
exec($command);

echo "Source File Done:".$insql."\r\n";


include(APP_BASE_PATH."/server.includes.inc.php");
