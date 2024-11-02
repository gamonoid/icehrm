<?php
namespace Classes\Migration;

class v20240107_340004_add_ms_google_auth extends AbstractMigration {

    public function up(){

        $sql[] = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) 
        VALUES (
        'Microsoft: Authentication Enabled', 
        '0',
        'Enable Login via Microsoft',
        '["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]',
        'Microsoft'
        );
SQL;

        $sql[] = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) 
        VALUES (
        'Microsoft: Client ID', 
        '',
        'The client ID of the OAuth app registered in Azure',
        '',
        'Microsoft'
        );
SQL;

        $sql[] = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) 
        VALUES (
        'Microsoft: Client Secret', 
        '',
        'The client Secret of the OAuth app registered in Azure',
        '',
        'Microsoft'
        );
SQL;

        $sql[] = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) 
        VALUES (
        'Microsoft: Tenant ID', 
        '',
        'The tenant ID of the OAuth app registered in Azure',
        '',
        'Microsoft'
        );
SQL;

        $sql[] = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) 
        VALUES (
        'Google: Client ID', 
        '',
        'The client ID of the OAuth app registered in Google Cloud',
        '',
        'Google'
        );
SQL;

        $sql[] = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) 
        VALUES (
        'Google: Client Secret', 
        '',
        'The client Secret of the OAuth app registered in Google Cloud',
        '',
        'Google'
        );
SQL;

        $result = true;
        foreach ($sql as $query) {
            $result = $result && $this->executeQuery($query);
        }

        return $result;
    }

    public function down(){
        return true;
    }

}
