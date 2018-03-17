<?php
namespace Classes\Migration;

use Metadata\Common\Model\SupportedLanguage;

class v20180305_210100_drop_si_hi_languages extends AbstractMigration{

	public function up(){

		$supportedLanguage = new SupportedLanguage();
		$supportedLanguage->Load('name = ?', array('si'));
		$supportedLanguage->Delete();

		$supportedLanguage = new SupportedLanguage();
		$supportedLanguage->Load('name = ?', array('hi'));
		$supportedLanguage->Delete();

		return true;
	}

	public function down(){
		$supportedLanguage = new SupportedLanguage();
		$supportedLanguage->name = 'si';
		$supportedLanguage->description = 'Sinhala';
		$supportedLanguage->Save();

		$supportedLanguage = new SupportedLanguage();
		$supportedLanguage->name = 'hi';
		$supportedLanguage->description = 'Hindi';
		$supportedLanguage->Save();
		return true;
	}

}

