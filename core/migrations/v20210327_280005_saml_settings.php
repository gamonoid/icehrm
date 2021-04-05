<?php
namespace Classes\Migration;

class v20210327_280005_saml_settings extends AbstractMigration {

    public function up(){

        $sql[] = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) 
        VALUES (
        'SAML: Enabled', 
        '0',
        'Enable SAML Login',
        '["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]',
        'SAML'
        );
SQL;
        $sql[] = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) 
        VALUES (
        'SAML: Auto Login', 
        '0',
        'Try to login via SAML by redirecting the user to SSO Url',
        '["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]',
        'SAML'
        );
SQL;

        $sql[] = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) 
        VALUES (
        'SAML: IDP SSO Url', 
        '',
        'Identity Provider Single Sign-On URL. Users will be redirected to this URL for authentication',
        '',
        'SAML'
        );
SQL;

        $sql[] = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) 
        VALUES (
        'SAML: IDP Issuer', 
        '',
        'Identity Provider Issuer',
        '',
        'SAML'
        );
SQL;

        $sql[] = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) 
        VALUES (
        'SAML: X.509 Certificate', 
        '',
        'X.509 Certificate provided by the Identity Provider. This certificate will be encrypted',
        '["value", {"label":"Value","type":"textarea"}]',
        'SAML'
        );
SQL;

        $sql[] = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) 
        VALUES (
        'SAML: Name ID Mapping', 
        'email',
        'SAML Name id mapped to can be mapped to icehrm user email or the username',
        '["value", {"label":"Value","type":"select","source":[["email","Email"],["username","Username"]]}]',
        'SAML'
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
