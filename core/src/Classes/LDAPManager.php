<?php
namespace Classes;

use Utils\LogManager;

class LDAPManager
{

    private static $me = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$me)) {
            self::$me = new LDAPManager();
        }

        return self::$me;
    }

    public function checkLDAPLogin($user, $password)
    {
        $ldap_host = SettingsManager::getInstance()->getSetting("LDAP: Server");
        $ldap_port = SettingsManager::getInstance()->getSetting("LDAP: Port");
        $ldap_dn = SettingsManager::getInstance()->getSetting("LDAP: Root DN");

        $managerDN = SettingsManager::getInstance()->getSetting("LDAP: Manager DN");
        $managerPassword = SettingsManager::getInstance()->getSetting("LDAP: Manager Password");

        // connect to active directory
        if (empty($ldap_port)) {
            $ldap_port = 389;
        }

        $ldap = ldap_connect($ldap_host, intval($ldap_port));

        if (!$ldap) {
            return new IceResponse(IceResponse::ERROR, "Could not connect to LDAP Server");
        }

        LogManager::getInstance()->debug("LDAP Connect Result:".print_r($ldap, true));

        if (SettingsManager::getInstance()->getSetting("LDAP: Version 3") == "1") {
            ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        }
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        // verify user and password
        $bind = @ldap_bind($ldap, $managerDN, $managerPassword);

        LogManager::getInstance()->debug("LDAP Manager Bind:".print_r($bind, true));

        if ($bind) {
            $userFilterStr = SettingsManager::getInstance()->getSetting("LDAP: User Filter");

            $filter = str_replace("{}", $user, $userFilterStr);   //"(uid=" . $user . ")";
            $result = ldap_search($ldap, $ldap_dn, $filter);
            LogManager::getInstance()->debug("LDAP Search Result:".print_r($result, true));
            if (!$result) {
                exit("Unable to search LDAP server");
            }
            $entries = ldap_get_entries($ldap, $result);
            LogManager::getInstance()->debug("LDAP Search Entries:".print_r($entries, true));

            if (empty($entries) || !isset($entries[0]) || !isset($entries[0]['dn'])) {
                return new IceResponse(IceResponse::ERROR, "Invalid user");
            }

            $bind = @ldap_bind($ldap, $entries[0]['dn'], $password);
            ldap_unbind($ldap);

            if ($bind) {
                return new IceResponse(IceResponse::SUCCESS, $entries[0]);
            } else {
                return new IceResponse(IceResponse::ERROR, "Invalid user");
            }
        } else {
            return new IceResponse(IceResponse::ERROR, "Invalid manager user");
        }
    }
}
