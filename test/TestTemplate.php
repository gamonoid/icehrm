<?php
class TestTemplate extends PHPUnit_Framework_TestCase
{

    protected $usersArray = array();
    private $db = null;

    public function p($msg)
    {
        fwrite(STDOUT, $msg."\n");
    }

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    protected function deleteAllUsers()
    {
        $user = new \Users\Common\Model\User();
        $users = $user->Find("username <> ?", array('admin'));
        foreach ($users as $user) {
            $user->Delete();
        }
    }

    protected function createNewUsers()
    {

        $profileVar = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
        $profileClass = ucfirst(SIGN_IN_ELEMENT_MAPPING_FIELD_NAME);

        $user = new \Users\Common\Model\User();
        $user->username = 'manager';
        $user->email = 'manager@icehrm-test.com';
        $user->password = '21232f297a57a5a743894a0e4a801fc3';
        $user->user_level = 'Manager';
        $user->Save();

        $user = new \Users\Common\Model\User();
        $user->username = 'profile';
        $user->email = 'profile@icehrm-test.com';
        $user->password = '21232f297a57a5a743894a0e4a801fc3';
        $user->user_level = 'Profile';
        $user->Save();
    }

    protected function initializeObjects()
    {

        global $userTables;
        global $fileFields;
        global $mysqlErrors;

        $dbLocal = NewADOConnection(APP_CON_STR);

        \Model\File::SetDatabaseAdapter($dbLocal);
        \Model\Setting::SetDatabaseAdapter($dbLocal);
        \Model\Report::SetDatabaseAdapter($dbLocal);
        \Model\DataEntryBackup::SetDatabaseAdapter($dbLocal);
        \Model\Audit::SetDatabaseAdapter($dbLocal);
        \Model\Notification::SetDatabaseAdapter($dbLocal);
        \Model\RestAccessToken::SetDatabaseAdapter($dbLocal);

        $moduleManagers = \Classes\BaseService::getInstance()->getModuleManagers();

        foreach ($moduleManagers as $moduleManagerObj) {
            $moduleManagerObj->setupModuleClassDefinitions();
            $moduleManagerObj->initializeUserClasses();
            $moduleManagerObj->initializeFieldMappings();
            $moduleManagerObj->initializeDatabaseErrorMappings();

            $moduleManagerObj->setupUserClasses($userTables);
            $moduleManagerObj->setupFileFieldMappings($fileFields);
            $moduleManagerObj->setupErrorMappings($mysqlErrors);
            $moduleManagerObj->initCalculationHooks();

            $modelClassList = $moduleManagerObj->getModelClasses();

            foreach ($modelClassList as $modelClass) {
                $modelClass::SetDatabaseAdapter($dbLocal);
            }
        }
    }

    protected function resetDatabase()
    {
        $dropDBCommand = 'echo "DROP DATABASE IF EXISTS ' . APP_DB . '"| mysql -u' . MYSQL_ROOT_USER . ' -p' . MYSQL_ROOT_PASS;
        $createDBCommand = 'echo "CREATE DATABASE '.APP_DB.'"| mysql -u'.MYSQL_ROOT_USER.' -p'.MYSQL_ROOT_PASS;

        exec($dropDBCommand);
        exec($createDBCommand);

        $scripts = array(
            APP_BASE_PATH."scripts/icehrmdb.sql",
            APP_BASE_PATH."scripts/icehrm_master_data.sql"
        );

        foreach ($scripts as $insql) {
            $command = "cat ".$insql."| mysql -u".MYSQL_ROOT_USER." -p".MYSQL_ROOT_PASS." '".APP_DB."'";
            exec($command);
        }
    }
}
