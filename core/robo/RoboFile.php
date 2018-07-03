<?php
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
    private function includeCientConfig($client){
        include dirname(__FILE__)."/../config.base.php";
        include ALL_CLIENT_BASE_PATH. $client . "/config.php";

        include (dirname(__FILE__)."/../include.common.php");

        include(dirname(__FILE__)."/../server.includes.inc.php");
    }


    function hello(array $world)
    {
        $this->say("Hello, " . implode(', ', $world));
    }

    function languageList($client) {
	    $this->includeCientConfig($client);
	    $this->say("Supported Languages for ". $client);
	    $language = new \Metadata\Common\Model\SupportedLanguage();
	    $langs = $language->Find('1 = 1');

	    $this->say(print_r(array_column($langs, 'name'), true));
    }

    function languageExport($client) {
	    $this->includeCientConfig($client);
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
	    $fp = fopen(CLIENT_BASE_PATH.'data/translations_export.csv', 'w');
	    foreach ($data as $fields) {
		    fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
		    fputcsv($fp, $fields);
	    }
	    fclose($fp);
	    $this->say('File saved');
    }

	function languageImport($client, $file) {
		$this->includeCientConfig($client);

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

    function migrate($client, $action){
        $this->includeCientConfig($client);
        $this->say("DB Migrating " . $action . " for ". $client);
        $migrationManager = new \Classes\Migration\MigrationManager();
        $migrationManager->setMigrationPath(APP_BASE_PATH .'/migrations/');
        $res = $migrationManager->runMigration($action);
        $this->say("DB Migrating Result : " . print_r($res, true));
    }

    function migrateAll($client){
        $this->includeCientConfig($client);
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

	/**
	 * @param $lang
	 * @param $file
	 * @return mixed
	 */
	protected function getUpdatedTranslationString($lang, $file)
	{
		$handle = fopen(CLIENT_BASE_PATH . 'data/' . $file, "r");
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
}
