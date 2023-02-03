<?php

use Classes\BaseService;

if (php_sapi_name() != 'cli') {
    echo "Bye browser";
    exit();
}
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
error_reporting(0);
class RoboFile extends \Robo\Tasks
{
    private function includeClientConfig($client = 'app'){
        include dirname(__FILE__)."/core/config.base.php";
        include (dirname(__FILE__)."/app/config.php");
        include (dirname(__FILE__)."/core/include.common.php");
        include(dirname(__FILE__)."/core/server.includes.inc.php");
    }

    public function hello(array $world)
    {
        $this->say("Hello, " . implode(', ', $world));
    }

    public function createExtension($name, $type = 'admin')
    {
        $this->yell("Creating extension:" . $type.'/'.$name);
        require dirname(__FILE__).'/core/robo/includes/create-extension/includes.php';
        $extensionBuilder = new \CreateExtension\ExtensionBuilder($name, EXT_ROOT, $type);
        try {
            $extensionBuilder->build($this);
            $this->say("Building frontend assets for :" . $type.'/'.$name);
            $this->say("gulp ejs --x$name/$type");
            $result = exec("gulp ejs --x$name/$type");
            if (!$result) {
                $this->yell("Building frontend assets for :" . $type.'/'.$name . " failed", 40, 'red');
                $this->say('Please run "gulp ejs --x'.$name.'/'.$type.'" manually');
            }
        } catch (Exception $e) {
        }
    }

    public function languageList($client) {
	    $this->includeClientConfig($client);
	    $this->say("Supported Languages for ". $client);
	    $language = new \Metadata\Common\Model\SupportedLanguage();
	    $langs = $language->Find('1 = 1');

	    $this->say(print_r(array_column($langs, 'name'), true));
    }

    public function languageExport($client, $file) {
	    $this->includeClientConfig($client);
	    $language = new \Metadata\Common\Model\SupportedLanguage();
	    $languages = $language->Find('1 = 1 order by id');
	    $data = [];
	    $data[0] = [];
	    $data[0][] = 'Key';
	    foreach ($languages as $lang) {
	    	$data[0][] = $lang->name;
	    	$trans = \Classes\LanguageManager::getTranslations($lang->name);
	    	$trans = json_decode($trans, true)['messages'][''];
		    $count = 1;
	    	foreach ($trans as $enVal => $langVal) {
			    if (!isset($data[$count])) {
				    $data[$count] = [];
				    $data[$count][] = $enVal;
			    }
			    $data[$count][] = $langVal[0];
			    $count += 1;
		    }
	    }
	    $fp = fopen(BaseService::getInstance()->getDataDirectory().$file, 'w');
	    foreach ($data as $fields) {
		    //fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
		    fputcsv($fp, $fields);
	    }
	    fclose($fp);
	    $this->say('File saved');
    }

    public function languageImport($client, $file) {
		$this->includeClientConfig($client);

		$language = new \Metadata\Common\Model\SupportedLanguage();
		$languages = $language->Find('1 = 1 order by id');
		foreach ($languages as $language) {
			$str = $this->getUpdatedTranslationString($language->name, $file);
			file_put_contents(
				__DIR__.'/../lang/'.$language->name.'.po',
				$str);
			$this->say('Updated :'.realpath(__DIR__.'/../lang/'.$language->name.'.po'));
		}
	}

    public function migrate($client, $action){
        $this->includeClientConfig($client);
        $this->say("DB Migrating " . $action . " for ". $client);
        $migrationManager = new \Classes\Migration\MigrationManager();
        $migrationManager->setMigrationPath(APP_BASE_PATH .'/migrations/');
        $res = $migrationManager->runMigration($action);
        $this->say("DB Migrating Result : " . print_r($res, true));
    }

    public function migrateAll($client){
        $this->includeClientConfig($client);
        $this->say("Run all pending migrations " . " for ". $client);
        $migrationManager = new \Classes\Migration\MigrationManager();
        $migrationManager->setMigrationPath(APP_BASE_PATH .'/migrations/');
        $migrationManager->queueMigrations();
        $migrations = $migrationManager->getPendingMigrations();
        foreach ($migrations as $migration) {
            $res = $migrationManager->runMigrationUp($migration);
            if(empty($res)){
                $this->yell("Migration not found", 40, 'yellow');
            }elseif($res->status != 'Up'){
                $this->yell("Migration error ".$res->file." (".$res->status.")", 40, 'red');
            }else{
                $this->yell("Migration OK ".$res->file." (".$res->status.")");
            }
        }
        $this->say("DB Migration Completed !!!");
    }

    public function resetDb($client){
        $this->includeClientConfig($client);
        $connection = new mysqli(APP_HOST, APP_USERNAME, APP_PASSWORD);

        if ($connection->connect_error) {
            $this->say("Connection failed: " . $connection->connect_error);
            exit(1);
        }

        $this->executeQuery($connection, sprintf('DROP DATABASE %s', APP_DB));
        $this->executeQuery($connection, sprintf('CREATE DATABASE %s', APP_DB));

        $this->executeQuery(
            $connection,
            sprintf("GRANT ALL ON %s.* to '%s'@'localhost'", APP_DB, APP_USERNAME)
        );

        $this->say("DB Reset Successful");
    }

    public function createTables($client){
        $this->includeClientConfig($client);
        $connection = new mysqli(APP_HOST, APP_USERNAME, APP_PASSWORD);

        if ($connection->connect_error) {
            $this->say("Connection failed: " . $connection->connect_error);
            exit(1);
        }

        $this->executeQuery($connection, sprintf('use %s', APP_DB));


        //Run create table script
        $insql = file_get_contents(APP_BASE_PATH."scripts/icehrmdb.sql");
        $sql_list = preg_split('/;/',$insql);
        foreach($sql_list as $sql){
            if (preg_match('/^\s+$/', $sql) || $sql == '') { # skip empty lines
                continue;
            }
            $this->executeQuery($connection, $sql);
        }

        //Run master data script
        $insql = file_get_contents(APP_BASE_PATH."scripts/icehrm_master_data.sql");
        $sql_list = preg_split('/;/',$insql);
        foreach($sql_list as $sql){
            if (preg_match('/^\s+$/', $sql) || $sql == '') { # skip empty lines
                continue;
            }
            $this->executeQuery($connection, $sql);
        }

        $this->say("Create Tables Successful");
    }

    public function executeFixtures($client){
        $this->includeClientConfig($client);
        $connection = new mysqli(APP_HOST, APP_USERNAME, APP_PASSWORD);

        if ($connection->connect_error) {
            $this->say("Connection failed: " . $connection->connect_error);
            exit(1);
        }

        $this->executeQuery($connection, sprintf('use %s', APP_DB));

        $insql = file_get_contents(APP_BASE_PATH."scripts/icehrm_fixtures.sql");
        $sql_list = preg_split('/;/',$insql);
        foreach($sql_list as $sql){
            if (preg_match('/^\s+$/', $sql) || $sql == '') { # skip empty lines
                continue;
            }
            $this->executeQuery($connection, $sql);
        }

        $this->say("Execute Fixtures Successful : ");
    }

    private function executeQuery($connection, $sql) {
        $connection->query($sql);
        if ($connection->error) {
            $this->say($connection->error);
            $this->say($sql);
        }
    }

	/**
	 * @param $lang
	 * @param $file
	 * @return mixed
	 */
	protected function getUpdatedTranslationString($lang, $file)
	{
		$handle = fopen(BaseService::getInstance()->getDataDirectory() . $file, "r");
		$langColumn = null;
		/* @var \Gettext\Translations $trans */
		$trans = \Classes\LanguageManager::getTranslationsObject($lang);
		while (($data = fgetcsv($handle)) !== FALSE) {
			if ($langColumn === null) {
				$currentColumn = 0;
				foreach ($data as $language) {
					if ($language === $lang) {
						$langColumn = $currentColumn;
						break;
					}
					$currentColumn++;
				}

				if ($langColumn === null) {
					$this->say('Invalid Language');
					exit();
				}
			} else {
				/* @var \Gettext\Translation $tran */
				$tran = $trans->find('', $data[0]);
				if ($tran !== false) {
					$tran->setTranslation($data[$langColumn]);
				} else {
					$trans->insert('', $data[0]);
                    $tran = $trans->find('', $data[0]);
                    $tran->setTranslation($data[$langColumn]);
				}
			}
		}

		return $trans->toPoString();
	}

    public function sayIt($text)
    {
        $this->say($text);
    }

    /**
     * @param string $text
     * @param int $length
     * @param string $color
     */
    public function yellIt($text, $length = 40, $color = 'green')
    {
        $this->yell($text, $length, $color);
    }
}
