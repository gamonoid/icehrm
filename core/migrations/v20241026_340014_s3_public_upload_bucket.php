<?php

namespace Classes\Migration;

class v20241026_340014_s3_public_upload_bucket extends AbstractMigration
{

	public function up()
	{

		$sql = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) VALUES
  ('Files: Upload Files to S3 for Editor', '0', '','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]', 'System'),
  ('Files: Amazon S3 Key for Editor File Upload', '', 'Please provide S3 Key for uploading files','', 'System'),
  ('Files: Amazon S3 Secret for Editor File Upload', '',  'Please provide S3 Secret for uploading files','', 'System'),
  ('Files: S3 Bucket for Editor', '',  'Please provide S3 Bucket name for uploading files','', 'System'),
  ('Files: S3 Web Url for Editor', '',  'Please provide Url to the s3 bucket','', 'System'),
  ('System: AWS Region for Editor', 'us-east-1',  'Amazon AWS Region used for file storage','', 'System');
SQL;

		return $this->executeQuery($sql);
	}

	public function down()
	{
		return true;
	}
}
