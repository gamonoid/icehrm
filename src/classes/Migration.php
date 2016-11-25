<?php


abstract class AbstractMigration
{
    protected $file;

    private $db;

    protected $lastError;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function up(){
        return true;
    }

    public function down(){
        return true;
    }

    protected function db(){
        if($this->db == null){
            $this->db = NewADOConnection('mysqli');
            $res = $this->db->Connect(APP_HOST, APP_USERNAME, APP_PASSWORD, APP_DB);
        }
        return $this->db;
    }

    public function getLastError(){
        return $this->lastError;
    }

    public function executeQuery($sql){
        $ret = $this->db()->Execute($sql);
        if(!$ret){
            $this->lastError =  $this->db()->ErrorMsg();
        }
        return $ret;
    }

    /*
    public function up()
    {
        $sql = <<<'SQL'
        create table `Migrations` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `file` varchar(300) NOT NULL,
            `version` int(11) NOT NULL,
            `created` DATETIME default '0000-00-00 00:00:00',
            `updated` DATETIME default '0000-00-00 00:00:00',
            `status` enum('Pending','Up','Down','UpError','DownError') default 'Pending',
            `last_error` varchar(500) NULL,
            primary key  (`id`),
            unique key `KEY_Migrations_file` (`file`),
            index `KEY_Migrations_status` (`status`),
            index `KEY_Migrations_status` (`version`)
        ) engine=innodb default charset=utf8;
SQL;
        return $this->db()->Execute($sql);
    }

    public function down()
    {
        return $this->db()->Execute('DROP TABLE Migrations');
    }
    */
}


class MigrationManager{

    private $migration_path;

    protected $db = null;

    public function __construct()
    {
        $this->migration_path = APP_BASE_PATH .'/db_migrations/';
    }

    public function setMigrationPath($migration_path){
        $this->migration_path = $migration_path;
    }

    public function getMigrationById($id){
        $migration = new Migration();
        $migration->Load("id = ?",array($id));
        return $migration;
    }

    public function getCurrentMigrations(){
        $migration = new Migration();
        return $migration->Find("1 = 1");
    }

    public function getPendingMigrations(){
        $migration = new Migration();
        return $migration->Find("status = ?",array('Pending'));
    }

    public function getFirstAddedMigration($statuses){
        $migration = new Migration();
        return $migration->Find("status in ('".implode("','",$statuses)."') order by created limit 1",array());
    }

    public function getLastRunMigration($statuses){
        $migration = new Migration();
        return $migration->Find("status in ('".implode("','",$statuses)."') order by updated desc limit 1",array());
    }

    public function queueMigrations(){

        $migrations = array();
        $ams = scandir($this->migration_path);
        foreach($ams as $am) {
            if (is_file($this->migration_path . $am)) {
                $migrations[$am] = $this->migration_path . $am;
            }
        }

        ksort($migrations);

        if(!empty($migrations)){
            $migrationsInDB = $this->getCurrentMigrations();
            $migrationsInDBKeyVal = array();
            foreach ($migrationsInDB as $migration){
                $migrationsInDBKeyVal[$migration->file] = $migration;
            }

            foreach($migrations as $file => $path){
                if(!isset($migrationsInDBKeyVal[$file])){
                    if($file == 'list.php'){
                        continue;
                    }
                    $migration = new Migration();
                    $migration->file = $file;
                    $parts = explode("_",$file);
                    $migration->version = intval($parts[1]);
                    $migration->created = date("Y-m-d H:i:s");
                    $migration->updated = date("Y-m-d H:i:s");
                    $migration->status = 'Pending';
                    $migration->Save();
                }
            }
        }
    }

    public function runPendingMigrations(){
        $migrations = $this->getPendingMigrations();
        foreach ($migrations as $migration){
            $this->runMigrationUp($migration);
        }
    }

    public function runMigration($action){
        $method = 'runMigration'.ucfirst($action);

        if($action == 'up'){
            $statuses = array("Pending","Down");
            $queryMethod = 'getFirstAddedMigration';
        }else if($action == 'down'){
            $statuses = array("Up");
            $queryMethod = 'getLastRunMigration';
        }else{
            return false;
        }
        $migrations = $this->$queryMethod($statuses);
        if(count($migrations) > 0){
            $this->$method($migrations[0]);
            return $this->getMigrationById($migrations[0]->id);
        }else{
            $this->queueMigrations();
            $migrations = $this->$queryMethod($statuses);
            if(count($migrations) > 0){
                $this->$method($migrations[0]);
                return $this->getMigrationById($migrations[0]->id);
            }
        }

        return false;
    }


    public function runMigrationUp($migration){
        if($migration->status != 'Pending' && $migration->status != 'UpError' && $migration->status != 'Down'){
            return false;
        }

        $path = $this->migration_path . $migration->file;
        if(!file_exists($path)){
            return false;
        }
        $migrationName = str_replace('.php','',$migration->file);
        if(!class_exists($migrationName)){
            include $path;
        }
        $migClass = new $migrationName;
        $res = $migClass->up();
        if(!$res){
            $migration->last_error = $migClass->getLastError();
            $migration->status = "UpError";
            $migration->updated = date("Y-m-d H:i:s");
            $migration->Save();
        }

        $migration->status = "Up";
        $migration->updated = date("Y-m-d H:i:s");
        $migration->Save();
        return $migration;

    }

    public function runMigrationDown($migration){
        if($migration->status != 'Up' && $migration->status != 'UpError'){
            return false;
        }

        $path = $this->migration_path . $migration->file;
        if(!file_exists($path)){
            return false;
        }

        $migrationName = str_replace('.php','',$migration->file);
        if(!class_exists($migrationName)){
            include $path;
        }
        $migClass = new $migrationName;
        $res = $migClass->down();
        if(!$res){
            $migration->last_error = $migClass->getLastError();
            $migration->status = "DownError";
            $migration->updated = date("Y-m-d H:i:s");
            $migration->Save();
        }

        $migration->status = "Down";
        $migration->updated = date("Y-m-d H:i:s");
        $migration->Save();
        return $migration;
    }

    public function ensureMigrations(){

        $migration = new Migration();
        $migration->Load("1 = 1 order by id desc limit 1");

        include $this->migration_path . "list.php";

        if (count($migrationList) > 0 && (empty($migration->id) || $migrationList[0].".php" != $migration->file)) {
            LogManager::getInstance()->info("ensureMigrations - execute migrations");
            $this->queueMigrations();
            $this->runPendingMigrations();
        }
    }

}