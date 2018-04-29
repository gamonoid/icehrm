<?php
namespace Classes\Migration;

use Model\Setting;

class v20171126_200303_swift_mail extends AbstractMigration{

	public function up(){

		$setting = new Setting();
		$setting->Load("name = ?", array('Email: Mode'));
		if(!empty($setting->id)){
			$setting->description = 'Update email sender';
			$setting->meta = '["value", {"label":"Value","type":"select","source":[["SMTP","SMTP"],["Swift SMTP","Swift SMTP"],["PHP Mailer","PHP Mailer"],["SES","Amazon SES"]]}]';
			$setting->Save();
		}

		return true;
	}

	public function down(){
		return true;
	}

}

