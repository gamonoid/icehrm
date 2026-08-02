<?php

namespace Classes;

/**
 * NativeModuleRegistry — declares which modules the SPA shell can mount natively
 * (no iframe) and the metadata needed to do so: the global init function, the JS
 * bundles/extra scripts to load, the tab + mount-container structure (the ids the
 * module's adapters render into), and the model entities whose general access +
 * custom fields must be supplied (the per-module bit each index.php computes).
 *
 * Adding a module here makes it eligible for native mounting; everything else
 * keeps using the iframe. SPA migration Phase 3.
 */
class NativeModuleRegistry
{
    public static function get($group, $name)
    {
        $map = self::map();
        $key = $group . '/' . $name;
        return isset($map[$key]) ? $map[$key] : null;
    }

    /** Keys (group/name) of all natively-mountable modules, for the shell. */
    public static function keys()
    {
        return array_keys(self::map());
    }

    /** The admin-module JS bundles every natively-mounted admin module loads. */
    private static function adminScripts()
    {
        return array(
            'dist/vendorOther.js',
            'dist/third-party.js',
            'dist/common.js',
            'dist/common-bundle.js',
            'dist/admin-bundle.js',
        );
    }

    /**
     * All natively-mountable modules: the hardcoded core modules plus any that an
     * installed EXTENSION declares via a `native` block in its meta.json. The
     * extension-declared ones disappear automatically when the extension is
     * removed (its module manager is no longer loaded), keeping it pluggable.
     */
    private static function map()
    {
        return array_merge(self::coreMap(), self::discoverExtensionModules());
    }

    /** JS bundles a natively-mounted USER (modules group) module loads. */
    /**
     * The settings module's tabs are dynamic — one per settings category that
     * actually has visible settings, mirroring the legacy page's rules.
     */
    private static function settingsTabs()
    {
        $mk = function ($key, $label) {
            return array('key' => 'tab' . $key, 'label' => $label, 'component' => 'NativeAdapterView', 'entity' => 'Setting');
        };
        // The visibility queries only matter when the request targets the
        // settings module; the bootstrap only needs the entry to exist.
        $g = isset($_REQUEST['group']) ? $_REQUEST['group'] : null;
        $n = isset($_REQUEST['name']) ? $_REQUEST['name'] : null;
        if ($g !== 'admin' || $n !== 'settings') {
            return array($mk('CompanySetting', 'Company'), $mk('SystemSetting', 'System'), $mk('OtherSetting', 'Other'));
        }
        try {
            $sm = \Classes\SettingsManager::getInstance();
            $hidden = $sm->getHiddenSettings();
            $deprecated = $sm->getDeprecatedSettings();
            $visible = function ($categories) use ($hidden, $deprecated) {
                if (!is_array($categories)) {
                    $categories = array($categories);
                }
                $setting = new \Model\Setting();
                foreach ($categories as $category) {
                    foreach ($setting->Find('category = ?', array($category)) as $s) {
                        if (!in_array($s->name, $hidden) && !in_array($s->name, $deprecated)) {
                            return true;
                        }
                    }
                }
                return false;
            };
            $tabs = array();
            $notCloud = !defined('IS_CLOUD') || IS_CLOUD == false;
            // Leave is a paid (extensions-pro) module — only surface its settings
            // tab on a Pro/Cloud build, matching when the module actually loads.
            $proEnabled = function_exists('iceProExtensionsEnabled') && iceProExtensionsEnabled();
            if ($visible('Company')) {
                $tabs[] = $mk('CompanySetting', 'Company');
            }
            if ($visible('System')) {
                $tabs[] = $mk('SystemSetting', 'System');
            }
            if ($notCloud && $visible('Email')) {
                $tabs[] = $mk('EmailSetting', 'Email');
            }
            if (defined('LEAVE_ENABLED') && LEAVE_ENABLED == true && $proEnabled && $visible('Leave')) {
                $tabs[] = $mk('LeaveSetting', 'Leave');
            }
            if ($visible('Attendance')) {
                $tabs[] = $mk('AttendanceSetting', 'Attendance');
            }
            if ($visible('Files')) {
                $tabs[] = $mk('FilesSetting', 'Files');
            }
            if ((!defined('LDAP_ENABLED') || LDAP_ENABLED == true) && $visible('LDAP')) {
                $tabs[] = $mk('LDAPSetting', 'LDAP');
            }
            if ((!defined('SAML_ENABLED') || SAML_ENABLED == true) && $visible('SAML')) {
                $tabs[] = $mk('SAMLSetting', 'SAML');
            }
            $other = array('Projects', 'Recruitment', 'Notifications', 'Expense', 'Travel', 'Api', 'Overtime', 'Microsoft', 'Google');
            if ($visible($other)) {
                $tabs[] = $mk('OtherSetting', 'Other');
            }
            return $tabs;
        } catch (\Throwable $e) {
            return array($mk('CompanySetting', 'Company'), $mk('SystemSetting', 'System'), $mk('OtherSetting', 'Other'));
        }
    }

    /**
     * The connection module's system report is expensive (log tail, system
     * probes), so only compute it when the request actually targets it.
     */
    private static function connectionExtraData()
    {
        $g = isset($_REQUEST['group']) ? $_REQUEST['group'] : null;
        $n = isset($_REQUEST['name']) ? $_REQUEST['name'] : null;
        if ($g !== 'admin' || $n !== 'connection') {
            return array();
        }
        try {
            $connectionService = new \Connection\Common\ConnectionService();
            return array('components' => array(
                'employeeCount' => array('count' => \Classes\StatsHelper::getActiveEmployeeCount()),
                'systemData' => array(
                    'data' => $connectionService->getSystemReport(),
                    'issues' => $connectionService->getSystemErrors(),
                    'logFileRows' => $connectionService->getLastLogFileRows(1000),
                ),
            ));
        } catch (\Throwable $e) {
            return array('components' => array());
        }
    }

    /**
     * The served-asset URL base for an extension — extensions-pro/ when the
     * extension physically lives there, else the free EXTENSIONS_URL. Ensures the
     * autoloader-free path-resolver is loaded (this runs on the REST path too).
     */
    private static function extAssetUrl($extName)
    {
        if (!function_exists('extensionAssetUrlBase') && defined('APP_BASE_PATH')) {
            $resolver = APP_BASE_PATH . 'extensions/path-resolver.php';
            if (file_exists($resolver)) {
                require_once $resolver;
            }
        }
        if (function_exists('extensionAssetUrlBase') && defined('APP_BASE_PATH')) {
            return extensionAssetUrlBase($extName, APP_BASE_PATH . '../extensions/');
        }
        return defined('EXTENSIONS_URL') ? EXTENSIONS_URL : '';
    }

    /**
     * The served-asset URL for a file inside an extension, or null when the
     * extension isn't installed. Callers filter the nulls out so a PRO bundle
     * that's absent from the free build is simply never requested — a 404 there
     * aborts the module's whole script chain ("Could not load this module").
     * Same file_exists guard as core/modulejslibs.inc.php.
     */
    private static function extScript($extName, $relPath)
    {
        // Also loads the autoloader-free path resolver as a side effect.
        $urlBase = self::extAssetUrl($extName);
        if (!defined('APP_BASE_PATH') || !function_exists('resolveExtensionPath')) {
            return null;
        }
        $path = resolveExtensionPath($extName, APP_BASE_PATH . '../extensions/');
        if (!file_exists($path . $relPath)) {
            return null;
        }
        return $urlBase . $extName . '/' . $relPath;
    }

    private static function moduleScripts()
    {
        return array_values(array_filter(array(
            'dist/vendorOther.js',
            'dist/third-party.js',
            'dist/common.js',
            'dist/modules-bundle.js',
            self::extScript('leave_and_performance', 'web/dist/modules-bundle.js'),
            'dist/common-bundle.js',
        )));
    }

    /**
     * Build native-mount entries from installed extensions whose admin/user
     * meta.json carries a `native` block. The block is declarative JSON:
     *   native: { initFn, bundle, entities?, tabs:[{key,label,entity,card}] }
     * The extension bundle is loaded as an absolute EXTENSIONS_URL script after
     * the core admin bundle (same order as core/modulejslibs.inc.php).
     */
    private static function discoverExtensionModules()
    {
        $discovered = array();
        $managers = self::safeModuleManagers();
        foreach ($managers as $manager) {
            if (!($manager instanceof IceExtension)) {
                continue;
            }
            $obj = $manager->getModuleObject();
            if (empty($obj['native']) || !is_array($obj['native']) || empty($obj['native']['initFn'])) {
                continue;
            }
            $native = $obj['native'];
            $extName = isset($obj['name']) ? $obj['name'] : null;
            $subType = $manager->getModuleType(); // 'admin' | 'user'
            if (empty($extName)) {
                continue;
            }
            $key = 'extension/' . $extName . '|' . $subType;

            $scripts = self::adminScripts();
            if (!empty($native['bundle'])) {
                $scripts[] = self::extAssetUrl($extName) . $native['bundle'];
            }

            $tabs = isset($native['tabs']) && is_array($native['tabs']) ? $native['tabs'] : array();
            foreach ($tabs as &$tab) {
                if (empty($tab['component'])) {
                    $tab['component'] = 'NativeCardList';
                }
            }
            unset($tab);

            $entry = array(
                'initFn' => $native['initFn'],
                'scripts' => $scripts,
                'entities' => isset($native['entities']) && is_array($native['entities'])
                    ? $native['entities'] : array(),
                'customFieldEntities' => isset($native['customFieldEntities']) && is_array($native['customFieldEntities'])
                    ? $native['customFieldEntities'] : array(),
                'tabs' => $tabs,
            );
            // Optional: a single custom-field type, and extraData / settings the
            // module's init() expects (e.g. moduleMainName / approveModName).
            if (!empty($native['customFieldType'])) {
                $entry['customFieldType'] = $native['customFieldType'];
            }
            if (isset($native['extraData']) && is_array($native['extraData'])) {
                $entry['extraData'] = $native['extraData'];
            }
            $discovered[$key] = $entry;
        }
        return $discovered;
    }

    private static function safeModuleManagers()
    {
        try {
            return BaseService::getInstance()->getModuleManagers();
        } catch (\Throwable $e) {
            return array();
        }
    }

    private static function coreMap()
    {
        $scripts = self::adminScripts();

        // REST API base URL shown on the employee "API Access" tab. Self-hosted
        // installs serve the API at <base>api/; on cloud the app path (/app/) is
        // swapped for /api/ on the same host.
        $apiBaseUrl = (!defined('IS_CLOUD') || IS_CLOUD == false)
            ? CLIENT_BASE_URL . 'api/'
            : str_replace('/app/', '/api/', CLIENT_BASE_URL);

        return array(
            'admin/company_structure' => array(
                'initFn' => 'initAdminCompanyStructure',
                // Loaded in order, after the shell's always-present vendor bundles
                // (react/antd/icons/antv). Paths are relative to BASE_URL.
                'scripts' => $scripts,
                'entities' => array(
                    'CompanyStructure' => '\\Company\\Common\\Model\\CompanyStructure',
                ),
                'customFieldType' => 'CompanyStructure',
                'tabs' => array(
                    array(
                        'key' => 'tabCompanyStructure',
                        'label' => 'Company Structure',
                        'component' => 'CompanyStructureCards',
                    ),
                    array(
                        'key' => 'tabCompanyGraph',
                        'label' => 'Org Chart',
                        'component' => 'OrgChart',
                    ),
                ),
            ),

            'admin/jobs' => array(
                'initFn' => 'initAdminJobs',
                'scripts' => $scripts,
                'entities' => array(
                    'JobTitle' => '\\Jobs\\Common\\Model\\JobTitle',
                    'PayGrade' => '\\Jobs\\Common\\Model\\PayGrade',
                    'EmploymentStatus' => '\\Employees\\Common\\Model\\EmploymentStatus',
                ),
                'tabs' => array(
                    array('key' => 'tabJobTitle', 'label' => 'Job Titles', 'component' => 'NativeCardList', 'entity' => 'JobTitle', 'card' => array(
                        // Toolbar action: generate job titles from a chosen industry
                        // (Classes\IndustryData via the jobs REST endpoints).
                        'generateFromIndustry' => array(
                            'industriesEndpoint' => 'jobs/industries',
                            'generateEndpoint' => 'jobs/generate-job-titles',
                            'label' => 'Generate Job Titles',
                            'itemsLabel' => 'job titles',
                            'allowDeleteExisting' => true,
                            'deleteExistingLabel' => 'Delete existing job titles first (only those not assigned to an employee)',
                        ),
                    )),
                    array('key' => 'tabPayGrade', 'label' => 'Pay Grades', 'component' => 'NativeCardList', 'entity' => 'PayGrade'),
                    array('key' => 'tabEmploymentStatus', 'label' => 'Employment Status', 'component' => 'NativeCardList', 'entity' => 'EmploymentStatus'),
                ),
            ),

            'admin/qualifications' => array(
                'initFn' => 'initAdminQualifications',
                'scripts' => $scripts,
                'entities' => array(
                    'Skill' => '\\Qualifications\\Common\\Model\\Skill',
                    'Education' => '\\Qualifications\\Common\\Model\\Education',
                    'Certification' => '\\Qualifications\\Common\\Model\\Certification',
                    'Language' => '\\Qualifications\\Common\\Model\\Language',
                ),
                'tabs' => array(
                    array('key' => 'tabSkill', 'label' => 'Skills', 'component' => 'NativeCardList', 'entity' => 'Skill', 'card' => array(
                        // Toolbar action: generate skills from a chosen industry
                        // (Classes\IndustryData via the qualifications REST endpoints).
                        'generateFromIndustry' => array(
                            'industriesEndpoint' => 'qualifications/industries',
                            'generateEndpoint' => 'qualifications/generate-skills',
                            'label' => 'Generate Skills',
                            'itemsLabel' => 'skills',
                            'allowDeleteExisting' => true,
                            'deleteExistingLabel' => 'Delete existing skills first (only those not assigned to an employee)',
                        ),
                    )),
                    array('key' => 'tabEducation', 'label' => 'Education', 'component' => 'NativeCardList', 'entity' => 'Education'),
                    array('key' => 'tabCertification', 'label' => 'Certifications', 'component' => 'NativeCardList', 'entity' => 'Certification', 'card' => array(
                        // Toolbar action: generate certifications from a chosen industry
                        // (Classes\IndustryData via the qualifications REST endpoints).
                        'generateFromIndustry' => array(
                            'industriesEndpoint' => 'qualifications/industries',
                            'generateEndpoint' => 'qualifications/generate-certifications',
                            'label' => 'Generate Certifications',
                            'itemsLabel' => 'certifications',
                            'allowDeleteExisting' => true,
                            'deleteExistingLabel' => 'Delete existing certifications first (only those not assigned to an employee)',
                        ),
                    )),
                    array('key' => 'tabLanguage', 'label' => 'Languages', 'component' => 'NativeCardList', 'entity' => 'Language'),
                ),
            ),

            'admin/projects' => array(
                'initFn' => 'initAdminProjects',
                'scripts' => $scripts,
                'entities' => array(
                    'Project' => '\\Projects\\Common\\Model\\Project',
                    'EmployeeProject' => '\\Projects\\Common\\Model\\EmployeeProject',
                    'Client' => '\\Projects\\Common\\Model\\Client',
                ),
                'customFieldEntities' => array('Project', 'EmployeeProject', 'Client'),
                'tabs' => array(
                    // Project/Client rows carry an editor document_link; open it natively
                    // in-shell (same contract as the tasks extension) instead of letting
                    // the legacy URL bounce the SPA back to the dashboard.
                    array('key' => 'tabProject', 'label' => 'Projects', 'component' => 'NativeCardList', 'entity' => 'Project', 'card' => array(
                        // Clicking the card opens the detailed project view (not the
                        // document, which stays available via its own button).
                        'cardClickView' => true,
                        'documentAction' => array(
                            'label' => 'Project Document',
                            'icon' => 'form',
                            'openIn' => 'native',
                            'bundle' => 'editor/user/dist/editor.js',
                            'mountFn' => 'mountEditorDocument',
                        ),
                    )),
                    // The card body is not clickable (no view on click); use the buttons.
                    array('key' => 'tabEmployeeProject', 'label' => 'Employee Project Assignments', 'component' => 'NativeCardList', 'entity' => 'EmployeeProject', 'card' => array('disableCardClick' => true)),
                    array('key' => 'tabClient', 'label' => 'Clients', 'component' => 'NativeCardList', 'entity' => 'Client', 'card' => array(
                        'documentAction' => array(
                            'label' => 'Document',
                            'icon' => 'form',
                            'openIn' => 'native',
                            'bundle' => 'editor/user/dist/editor.js',
                            'mountFn' => 'mountEditorDocument',
                        ),
                    )),
                ),
            ),

            'admin/custom_fields' => array(
                'initFn' => 'initAdminCustomFields',
                'scripts' => $scripts,
                'entities' => array(
                    'CustomField' => '\\FieldNames\\Common\\Model\\CustomField',
                ),
                'includeCustomFieldTypes' => true,
                'tabs' => array(
                    array('key' => 'tabCustomField', 'label' => 'Custom Fields', 'component' => 'NativeCardList', 'entity' => 'CustomField'),
                ),
            ),

            'admin/employees' => array(
                'initFn' => 'initAdminEmployees',
                'scripts' => $scripts,
                'entities' => array(
                    'Employee' => '\\Employees\\Common\\Model\\Employee',
                    'EmployeeCareer' => '\\Employees\\Common\\Model\\EmployeeCareer',
                    'EmployeeSkill' => '\\Qualifications\\Common\\Model\\EmployeeSkill',
                    'EmployeeEducation' => '\\Qualifications\\Common\\Model\\EmployeeEducation',
                    'EmployeeCertification' => '\\Qualifications\\Common\\Model\\EmployeeCertification',
                    'EmployeeLanguage' => '\\Qualifications\\Common\\Model\\EmployeeLanguage',
                    'EmployeeDependent' => '\\Dependents\\Common\\Model\\EmployeeDependent',
                    'EmergencyContact' => '\\EmergencyContacts\\Common\\Model\\EmergencyContact',
                ),
                'customFieldType' => 'Employee',
                'includeFieldNameMappings' => 'Employee',
                'includeManagersCanSwitch' => true,
                // Employee-number auto-generation + multi-level-approval settings,
                // surfaced to init(data). The Approvals tab shows when ANY module
                // has multi-level approvals enabled.
                'extraSettings' => array(
                    'generateEmployeeNumbers' => 'Company: Generate Employee Numbers',
                    'employeeNumberPrefix' => 'Company: Employee Number Prefix',
                    'mlaLeave' => 'Leave: Enable Multi Level Approvals',
                    'mlaExpense' => 'Expense: Enable Multi Level Approvals',
                    'mlaOvertime' => 'Overtime: Enable Multi Level Approvals',
                    'mlaTravel' => 'Travel: Enable Multi Level Approvals',
                ),
                'tabs' => array(
                    // The main Employees tab keeps the exact legacy directory +
                    // profile view (EmployeeAdminView) — mounted as a legacy adapter
                    // tab (its initTable renders the view into #EmployeeTable).
                    array('key' => 'tabEmployee', 'label' => 'Employees', 'ids' => array(
                        'EmployeeTable', 'EmployeeForm', 'EmployeeFilterForm', 'UserForm', 'UserInvitationForm',
                    )),
                    // The sub-tabs are simple card lists with edit/delete; the
                    // read-only View modal is redundant here, so hide it (scoped
                    // to this module via the per-tab card config — these entities
                    // keep their View button in their own self-service modules).
                    array('key' => 'tabEmployeeCareer', 'label' => 'Work History', 'component' => 'NativeCardList', 'entity' => 'EmployeeCareer', 'card' => array('hideViewButton' => true)),
                    array('key' => 'tabEmployeeSkill', 'label' => 'Skills', 'component' => 'NativeCardList', 'entity' => 'EmployeeSkill', 'card' => array('hideViewButton' => true)),
                    array('key' => 'tabEmployeeEducation', 'label' => 'Education', 'component' => 'NativeCardList', 'entity' => 'EmployeeEducation', 'card' => array('hideViewButton' => true)),
                    array('key' => 'tabEmployeeCertification', 'label' => 'Certifications', 'component' => 'NativeCardList', 'entity' => 'EmployeeCertification', 'card' => array('hideViewButton' => true)),
                    array('key' => 'tabEmployeeLanguage', 'label' => 'Languages', 'component' => 'NativeCardList', 'entity' => 'EmployeeLanguage', 'card' => array('hideViewButton' => true)),
                    array('key' => 'tabEmployeeDependent', 'label' => 'Dependents', 'component' => 'NativeCardList', 'entity' => 'EmployeeDependent', 'card' => array('hideViewButton' => true)),
                    array('key' => 'tabEmergencyContact', 'label' => 'Contacts', 'component' => 'NativeCardList', 'entity' => 'EmergencyContact', 'card' => array('hideViewButton' => true)),
                    array('key' => 'tabTerminatedEmployee', 'label' => 'Resigned', 'component' => 'NativeCardList', 'entity' => 'TerminatedEmployee', 'card' => array('hideViewButton' => true)),
                    array('key' => 'tabArchivedEmployee', 'label' => 'Archived', 'component' => 'NativeCardList', 'entity' => 'ArchivedEmployee', 'card' => array('hideViewButton' => true)),
                ),
            ),

            'admin/travel' => array(
                'initFn' => 'initAdminTravel',
                'scripts' => $scripts,
                'customFieldType' => 'EmployeeTravelRecord',
                'extraData' => array('permissions' => array('get', 'element', 'save', 'delete')),
                'entities' => array(
                    'TravelProject' => '\\Travel\\Common\\Model\\TravelProject',
                    'EmployeeTravelRecord' => '\\Travel\\Common\\Model\\EmployeeTravelRecord',
                ),
                'tabs' => array(
                    array('key' => 'tabTravelProject', 'label' => 'Travel Projects', 'component' => 'NativeCardList', 'entity' => 'TravelProject'),
                    array('key' => 'tabEmployeeTravelRecord', 'label' => 'Travel Requests', 'component' => 'NativeCardList', 'entity' => 'EmployeeTravelRecord'),
                ),
            ),

            'admin/salary' => array(
                'initFn' => 'initAdminSalary',
                'scripts' => $scripts,
                'entities' => array(
                    'SalaryComponentType' => '\\Salary\\Common\\Model\\SalaryComponentType',
                    'SalaryComponent' => '\\Salary\\Common\\Model\\SalaryComponent',
                    'EmployeeSalary' => '\\Salary\\Common\\Model\\EmployeeSalary',
                ),
                'tabs' => array(
                    array('key' => 'tabSalaryComponentType', 'label' => 'Salary Component Types', 'component' => 'NativeCardList', 'entity' => 'SalaryComponentType'),
                    array('key' => 'tabSalaryComponent', 'label' => 'Salary Components', 'component' => 'NativeCardList', 'entity' => 'SalaryComponent'),
                    array('key' => 'tabEmployeeSalary', 'label' => 'Salary', 'component' => 'NativeCardList', 'entity' => 'EmployeeSalary'),
                ),
            ),

            'admin/loans' => array(
                'initFn' => 'initAdminLoans',
                'scripts' => $scripts,
                'entities' => array(
                    'CompanyLoan' => '\\Loans\\Common\\Model\\CompanyLoan',
                    'EmployeeCompanyLoan' => '\\Loans\\Common\\Model\\EmployeeCompanyLoan',
                ),
                'tabs' => array(
                    array('key' => 'tabCompanyLoan', 'label' => 'Loan Types', 'component' => 'NativeCardList', 'entity' => 'CompanyLoan'),
                    array('key' => 'tabEmployeeCompanyLoan', 'label' => 'Employee Loans', 'component' => 'NativeCardList', 'entity' => 'EmployeeCompanyLoan'),
                ),
            ),

            'admin/modules' => array(
                'initFn' => 'initAdminModules',
                'scripts' => $scripts,
                'entities' => array(
                    'Module' => '\\Modules\\Common\\Model\\Module',
                ),
                'tabs' => array(
                    array('key' => 'tabModule', 'label' => 'Modules', 'component' => 'NativeCardList', 'entity' => 'Module'),
                ),
            ),

            'admin/permissions' => array(
                'initFn' => 'initAdminPermissions',
                'scripts' => $scripts,
                'entities' => array(
                    'Permission' => '\\Permissions\\Common\\Model\\Permission',
                ),
                'tabs' => array(
                    array('key' => 'tabPermission', 'label' => 'Permissions', 'component' => 'NativeCardList', 'entity' => 'Permission'),
                ),
            ),

            'admin/connection' => array(
                'initFn' => 'initAdminConnection',
                'scripts' => $scripts,
                'entities' => array(),
                'extraData' => self::connectionExtraData(),
                'tabs' => array(
                    array('key' => 'tabConnection', 'label' => 'System Status', 'component' => 'NativeExtensionView', 'viewGlobal' => 'ConnectionSystemView'),
                    array('key' => 'tabBackups', 'label' => 'Backups', 'component' => 'NativeExtensionView', 'viewGlobal' => 'ConnectionBackupsView'),
                ),
            ),

            'admin/settings' => array(
                'initFn' => 'initAdminSettings',
                'scripts' => $scripts,
                'entities' => array(
                    'Setting' => '\\Model\\Setting',
                ),
                'tabs' => self::settingsTabs(),
            ),

            'admin/metadata' => array(
                'initFn' => 'initAdminMetadata',
                'scripts' => $scripts,
                'entities' => array(
                    'Country' => '\\Metadata\\Common\\Model\\Country',
                    'Province' => '\\Metadata\\Common\\Model\\Province',
                    'CurrencyType' => '\\Metadata\\Common\\Model\\CurrencyType',
                    'Nationality' => '\\Metadata\\Common\\Model\\Nationality',
                    'Ethnicity' => '\\Metadata\\Common\\Model\\Ethnicity',
                    'ImmigrationStatus' => '\\Metadata\\Common\\Model\\ImmigrationStatus',
                ),
                'tabs' => array(
                    array('key' => 'tabCountry', 'label' => 'Countries', 'component' => 'NativeCardList', 'entity' => 'Country'),
                    array('key' => 'tabProvince', 'label' => 'Provinces', 'component' => 'NativeCardList', 'entity' => 'Province'),
                    array('key' => 'tabCurrencyType', 'label' => 'Currency Types', 'component' => 'NativeCardList', 'entity' => 'CurrencyType'),
                    array('key' => 'tabNationality', 'label' => 'Nationality', 'component' => 'NativeCardList', 'entity' => 'Nationality'),
                    array('key' => 'tabEthnicity', 'label' => 'Ethnicity', 'component' => 'NativeCardList', 'entity' => 'Ethnicity'),
                    array('key' => 'tabImmigrationStatus', 'label' => 'Immigration Status', 'component' => 'NativeCardList', 'entity' => 'ImmigrationStatus'),
                ),
            ),

            'admin/documents' => array(
                'initFn' => 'initAdminDocuments',
                'scripts' => $scripts,
                'entities' => array(
                    'CompanyDocument' => '\\Documents\\Common\\Model\\CompanyDocument',
                    'Document' => '\\Documents\\Common\\Model\\Document',
                    'EmployeeDocument' => '\\Documents\\Common\\Model\\EmployeeDocument',
                    'PayslipDocument' => '\\Documents\\Common\\Model\\PayslipDocument',
                ),
                'tabs' => array(
                    // Admin-only tabs — managers see only Employee Documents (of
                    // their direct reports; scoping is enforced server-side).
                    array('key' => 'tabCompanyDocument', 'label' => 'Company Documents', 'component' => 'NativeCardList', 'entity' => 'CompanyDocument', 'requiresLevel' => 'Admin', 'card' => array(
                        'hideViewButton' => true,
                        // Rich editor-extension "content" document. Admins edit it
                        // (they have 'save'); eligible managers/employees view it.
                        'documentAction' => array(
                            'label' => 'Content',
                            'icon' => 'form',
                            'openIn' => 'native',
                            'bundle' => 'editor/user/dist/editor.js',
                            'mountFn' => 'mountEditorDocument',
                        ),
                    )),
                    array('key' => 'tabDocument', 'label' => 'Document Types', 'component' => 'NativeCardList', 'entity' => 'Document', 'requiresLevel' => 'Admin'),
                    array('key' => 'tabEmployeeDocument', 'label' => 'Employee Documents', 'component' => 'NativeCardList', 'entity' => 'EmployeeDocument'),
                    array('key' => 'tabPayslipDocument', 'label' => 'Employee Payslip', 'component' => 'NativeCardList', 'entity' => 'PayslipDocument', 'requiresLevel' => 'Admin'),
                ),
            ),

            'admin/performance' => array(
                'initFn' => 'initAdminPerformance',
                // Adapters + init live in the leave_and_performance PRO bundle.
                'scripts' => array_values(array_filter(array(
                    'dist/vendorOther.js',
                    'dist/third-party.js',
                    'dist/common.js',
                    'dist/admin-bundle.js',
                    self::extScript('leave_and_performance', 'web/dist/admin-bundle.js'),
                    'dist/common-bundle.js',
                ))),
                'entities' => array(
                    'PerformanceReview' => '\\Performance\\Common\\Model\\PerformanceReview',
                    'ReviewFeedback' => '\\Performance\\Common\\Model\\ReviewFeedback',
                    'ReviewTemplate' => '\\Performance\\Common\\Model\\ReviewTemplate',
                    'EmployeeGoal' => '\\Performance\\Common\\Model\\EmployeeGoal',
                ),
                'tabs' => array(
                    array('key' => 'tabPerformanceReview', 'label' => 'Performance Reviews', 'component' => 'NativeCardList', 'entity' => 'PerformanceReview'),
                    array('key' => 'tabReviewFeedback', 'label' => 'Feedback Requests', 'component' => 'NativeCardList', 'entity' => 'ReviewFeedback', 'requiresLevel' => array('Admin')),
                    array('key' => 'tabReviewTemplate', 'label' => 'Employee Feedback Templates', 'component' => 'NativeCardList', 'entity' => 'ReviewTemplate', 'requiresLevel' => array('Admin')),
                    array('key' => 'tabEmployeeGoal', 'label' => 'Employee Goals', 'component' => 'NativeCardList', 'entity' => 'EmployeeGoal'),
                ),
            ),

            'admin/employeehistory' => array(
                'initFn' => 'initAdminEmployeeHistory',
                // The adapter + init live in the leave_and_performance PRO bundle
                // (absolute URL via EXTENSIONS_URL), loaded after the core admin
                // bundle — same order as core/modulejslibs.inc.php.
                'scripts' => array_values(array_filter(array(
                    'dist/vendorOther.js',
                    'dist/third-party.js',
                    'dist/common.js',
                    'dist/admin-bundle.js',
                    self::extScript('leave_and_performance', 'web/dist/admin-bundle.js'),
                    'dist/common-bundle.js',
                ))),
                'entities' => array(),
                'tabs' => array(
                    array('key' => 'tabEmployeeDataHistory', 'label' => 'Employee Basic Details', 'component' => 'NativeCardList', 'entity' => 'EmployeeDataHistory'),
                ),
            ),

            // Leave admin (leave_and_performance PRO). Built with ModuleBuilderV2;
            // initAdminLeaves replicates its tabs. The Employee Leave list uses the
            // native approve workflow with a leave-specific status action.
            'admin/leaves' => array(
                'initFn' => 'initAdminLeaves',
                'scripts' => array_values(array_filter(array(
                    'dist/vendorOther.js',
                    'dist/third-party.js',
                    'dist/common.js',
                    'dist/admin-bundle.js',
                    self::extScript('leave_and_performance', 'web/dist/admin-bundle.js'),
                    'dist/common-bundle.js',
                ))),
                'entities' => array(
                    'LeaveType' => '\\Leaves\\Common\\Model\\LeaveType',
                    'LeavePeriod' => '\\Leaves\\Common\\Model\\LeavePeriod',
                    'WorkDay' => '\\Leaves\\Common\\Model\\WorkDay',
                    'HoliDay' => '\\Leaves\\Common\\Model\\HoliDay',
                    'LeaveRule' => '\\Leaves\\Common\\Model\\LeaveRule',
                    'LeaveStartingBalance' => '\\Leaves\\Common\\Model\\LeaveStartingBalance',
                    'LeaveGroup' => '\\Leaves\\Common\\Model\\LeaveGroup',
                    'LeaveGroupEmployee' => '\\Leaves\\Common\\Model\\LeaveGroupEmployee',
                    'EmployeeLeave' => '\\Leaves\\Common\\Model\\EmployeeLeave',
                ),
                'tabs' => array(
                    array('key' => 'tabLeaveType', 'label' => 'Leave Types', 'component' => 'NativeCardList', 'entity' => 'LeaveType', 'card' => array('disableView' => true, 'hideMeta' => array('id'))),
                    array('key' => 'tabLeavePeriod', 'label' => 'Leave Period', 'component' => 'NativeCardList', 'entity' => 'LeavePeriod', 'card' => array('disableView' => true, 'hideCopyButton' => true)),
                    array('key' => 'tabWorkDay', 'label' => 'Work Week', 'component' => 'NativeCardList', 'entity' => 'WorkDay', 'card' => array('disableView' => true)),
                    array('key' => 'tabHoliDay', 'label' => 'Holidays', 'component' => 'NativeCardList', 'entity' => 'HoliDay', 'card' => array('disableView' => true, 'bulkDelete' => true)),
                    // Only shown once at least one leave rule exists.
                    array('key' => 'tabLeaveRule', 'label' => 'Leave Rules', 'component' => 'NativeCardList', 'entity' => 'LeaveRule', 'requiresRecords' => '\\Leaves\\Common\\Model\\LeaveRule'),
                    array('key' => 'tabLeaveStartingBalance', 'label' => 'Leave Adjustments', 'component' => 'NativeCardList', 'entity' => 'LeaveStartingBalance', 'card' => array('disableView' => true)),
                    array('key' => 'tabLeaveGroup', 'label' => 'Leave Groups', 'component' => 'NativeCardList', 'entity' => 'LeaveGroup'),
                    array('key' => 'tabEmployeeLeave', 'label' => 'Employee Leave List', 'component' => 'NativeCardList', 'entity' => 'EmployeeLeave', 'card' => array('statusAction' => 'changeLeaveStatus')),
                ),
            ),

            'admin/attendance' => array(
                'initFn' => 'initAdminAttendance',
                'scripts' => $scripts,
                'entities' => array(
                    'Attendance' => '\\Attendance\\Common\\Model\\Attendance',
                    'AttendanceStatus' => '\\Attendance\\Common\\Model\\AttendanceStatus',
                ),
                'extraSettings' => array(
                    'overtimeStartHour' => 'Attendance: Overtime Start Hour',
                ),
                'tabs' => array(
                    array('key' => 'tabAttendance', 'label' => 'Attendance', 'component' => 'NativeCardList', 'entity' => 'Attendance'),
                    array('key' => 'tabAttendanceStatus', 'label' => 'Attendance Status', 'component' => 'NativeCardList', 'entity' => 'AttendanceStatus'),
                ),
            ),

            'admin/overtime' => array(
                'initFn' => 'initAdminOvertime',
                'scripts' => $scripts,
                'entities' => array(
                    'OvertimeCategory' => '\\Overtime\\Common\\Model\\OvertimeCategory',
                    'EmployeeOvertime' => '\\Overtime\\Common\\Model\\EmployeeOvertime',
                ),
                'tabs' => array(
                    array('key' => 'tabOvertimeCategory', 'label' => 'Overtime Categories', 'component' => 'NativeCardList', 'entity' => 'OvertimeCategory', 'card' => array('disableView' => true)),
                    array('key' => 'tabEmployeeOvertime', 'label' => 'Overtime Requests', 'component' => 'NativeCardList', 'entity' => 'EmployeeOvertime', 'card' => array('disableView' => true)),
                ),
            ),

            'admin/training' => array(
                'initFn' => 'initAdminTraining',
                'scripts' => $scripts,
                'entities' => array(
                    'Course' => '\\Training\\Common\\Model\\Course',
                    'TrainingSession' => '\\Training\\Common\\Model\\TrainingSession',
                    'EmployeeTrainingSession' => '\\Training\\Common\\Model\\EmployeeTrainingSession',
                ),
                'tabs' => array(
                    array('key' => 'tabCourse', 'label' => 'Courses', 'component' => 'NativeCardList', 'entity' => 'Course'),
                    array('key' => 'tabTrainingSession', 'label' => 'Training Sessions', 'component' => 'NativeCardList', 'entity' => 'TrainingSession'),
                    array('key' => 'tabEmployeeTrainingSession', 'label' => 'Employee Training Sessions', 'component' => 'NativeCardList', 'entity' => 'EmployeeTrainingSession'),
                ),
            ),

            'admin/fieldnames' => array(
                'initFn' => 'initAdminEmployeeFieldName',
                'scripts' => $scripts,
                'entities' => array(
                    'FieldNameMapping' => '\\FieldNames\\Common\\Model\\FieldNameMapping',
                ),
                'tabs' => array(
                    array('key' => 'tabEmployeeFieldName', 'label' => 'Field Names', 'component' => 'NativeCardList', 'entity' => 'FieldNameMapping'),
                ),
            ),

            'admin/audit' => array(
                'initFn' => 'initAdminAudit',
                'scripts' => $scripts,
                'entities' => array(
                    'Audit' => '\\Model\\Audit',
                    'EmailLog' => '\\Model\\EmailLogEntry',
                ),
                'tabs' => array(
                    array('key' => 'tabAudit', 'label' => 'Audit Log', 'component' => 'NativeCardList', 'entity' => 'Audit'),
                    array('key' => 'tabEmailLog', 'label' => 'Email Log', 'component' => 'NativeCardList', 'entity' => 'EmailLogEntry'),
                ),
            ),

            'admin/users' => array(
                'initFn' => 'initAdminUsers',
                'scripts' => $scripts,
                'entities' => array(
                    'User' => '\\Users\\Common\\Model\\User',
                    'UserRole' => '\\Users\\Common\\Model\\UserRole',
                    'UserInvitation' => '\\Model\\UserInvitation',
                ),
                // The User adapter posts saveUser with a CSRF token; the UserRole
                // form needs the list of model classes for its permission picker.
                'includeCsrf' => 'User',
                'includeModelClasses' => true,
                'tabs' => array(
                    array('key' => 'tabUser', 'label' => 'Users', 'component' => 'NativeCardList', 'entity' => 'User'),
                    array('key' => 'tabUserRole', 'label' => 'User Roles', 'component' => 'NativeCardList', 'entity' => 'UserRole'),
                    array('key' => 'tabUserInvitation', 'label' => 'User Invitations', 'component' => 'NativeCardList', 'entity' => 'UserInvitation'),
                ),
            ),

            // --- user (modules group) self-service lists -------------------
            'modules/dependents' => array(
                'initFn' => 'initModulesDependents',
                'scripts' => self::moduleScripts(),
                'entities' => array(
                    'EmployeeDependent' => '\\Dependents\\Common\\Model\\EmployeeDependent',
                ),
                'tabs' => array(
                    array('key' => 'tabEmployeeDependent', 'label' => 'Dependents', 'component' => 'NativeCardList', 'entity' => 'EmployeeDependent', 'card' => array('disableView' => true)),
                ),
            ),

            'modules/attendance' => array(
                'initFn' => 'initModulesAttendance',
                'scripts' => self::moduleScripts(),
                'entities' => array(
                    'Attendance' => '\\Attendance\\Common\\Model\\Attendance',
                ),
                'includeAttendancePunch' => true,
                'tabs' => array(
                    array('key' => 'tabAttendance', 'label' => 'Attendance', 'component' => 'NativeCardList', 'entity' => 'MyAttendance'),
                ),
            ),

            'modules/overtime' => array(
                'initFn' => 'initUserOvertime',
                'scripts' => self::moduleScripts(),
                'customFieldType' => 'EmployeeOvertime',
                'entities' => array(
                    'EmployeeOvertime' => '\\Overtime\\Common\\Model\\EmployeeOvertime',
                    'EmployeeOvertimeApproval' => '\\Overtime\\Common\\Model\\EmployeeOvertimeApproval',
                ),
                // Hardcoded in the legacy core/modules/overtime/index.php $moduleData.
                'extraData' => array(
                    'permissions' => array('get', 'element', 'save', 'delete'),
                    'moduleMainName' => 'EmployeeOvertime',
                    'approveModName' => 'EmployeeOvertimeApproval',
                    'subModuleMainName' => 'SubordinateEmployeeOvertime',
                ),
                'tabs' => array(
                    array('key' => 'tabEmployeeOvertime', 'label' => 'Overtime Requests', 'component' => 'NativeCardList', 'entity' => 'MyOvertime', 'card' => array('disableView' => true)),
                    array('key' => 'tabSubordinateEmployeeOvertime', 'label' => 'Direct Reports', 'component' => 'NativeCardList', 'entity' => 'EmployeeOvertime', 'card' => array('disableView' => true)),
                    // Legacy gates the approvals tab on this setting.
                    array('key' => 'tabEmployeeOvertimeApproval', 'label' => 'Approvals', 'component' => 'NativeCardList', 'entity' => 'EmployeeOvertimeApproval', 'requiresSetting' => 'Overtime: Enable Multi Level Approvals', 'card' => array('disableView' => true)),
                ),
            ),

            'modules/travel' => array(
                'initFn' => 'initUserTravel',
                'scripts' => self::moduleScripts(),
                'customFieldType' => 'EmployeeTravelRecord',
                'extraData' => array('permissions' => array('get', 'element', 'save', 'delete')),
                'entities' => array(
                    'EmployeeTravelRecord' => '\\Travel\\Common\\Model\\EmployeeTravelRecord',
                ),
                'tabs' => array(
                    array('key' => 'tabEmployeeTravelRecord', 'label' => 'Travel Requests', 'component' => 'NativeCardList', 'entity' => 'MyTravel'),
                    array('key' => 'tabSubordinateEmployeeTravelRecord', 'label' => 'Travel Requests (Direct Reports)', 'component' => 'NativeCardList', 'entity' => 'SubTravel'),
                    array('key' => 'tabEmployeeTravelRecordApprove', 'label' => 'Travel Requests for Approval', 'component' => 'NativeCardList', 'entity' => 'TravelApproval', 'requiresSetting' => 'Travel: Enable Multi Level Approvals'),
                ),
            ),

            'modules/documents' => array(
                'initFn' => 'initDocumentsModule',
                'scripts' => self::moduleScripts(),
                'entities' => array(),
                // The legacy page hardcodes these permission sets.
                'extraData' => array('permissions' => array(
                    'EmployeeDocument' => array('get', 'element', 'save', 'delete'),
                    'EmployeePayslipDocument' => array('get', 'element'),
                    'CompanyDocument' => array('get', 'element'),
                )),
                'tabs' => array(
                    array('key' => 'tabEmployeeDocument', 'label' => 'My Documents', 'component' => 'NativeCardList', 'entity' => 'MyDocument'),
                    array('key' => 'tabCompanyDocument', 'label' => 'Company Documents', 'component' => 'NativeCardList', 'entity' => 'MyCompanyDocument', 'card' => array(
                        // View-only editor "content" for eligible employees (no
                        // 'save' access → read-only; see CompanyDocument::getEditorPermissions).
                        'documentAction' => array(
                            'label' => 'Content',
                            'icon' => 'form',
                            'openIn' => 'native',
                            'bundle' => 'editor/user/dist/editor.js',
                            'mountFn' => 'mountEditorDocument',
                        ),
                    )),
                    array('key' => 'tabEmployeePayslipDocument', 'label' => 'Payslips', 'component' => 'NativeCardList', 'entity' => 'MyPayslip'),
                ),
            ),

            'modules/performance' => array(
                'initFn' => 'initPerformaceModule',
                'scripts' => self::moduleScripts(),
                'entities' => array(),
                // The legacy page hardcodes flat permissions; natively we trim
                // save/delete where the legacy UI never exposed those actions.
                // PerformanceReview stays read-only: employees must not create
                // or edit their own reviews — those are started by managers.
                'extraData' => array('permissions' => array(
                    'PerformanceReview' => array('get', 'element'),
                    'CoordinatedPerformanceReview' => array('get', 'element', 'save', 'delete'),
                    'ReviewFeedback' => array('get', 'element', 'save'),
                )),
                'tabs' => array(
                    array('key' => 'tabPerformanceReview', 'label' => 'Self Assessments', 'component' => 'NativeCardList', 'entity' => 'MyPerformanceReview'),
                    array('key' => 'tabReviewFeedback', 'label' => 'Feedback Requests', 'component' => 'NativeCardList', 'entity' => 'MyReviewFeedback'),
                ),
            ),

            'modules/training' => array(
                'initFn' => 'initUserTraining',
                'scripts' => self::moduleScripts(),
                'entities' => array(
                    'TrainingSessionWithCourse' => '\\Training\\Common\\Model\\TrainingSessionWithCourse',
                    'EmployeeTrainingSession' => '\\Training\\Common\\Model\\EmployeeTrainingSession',
                    'CoordinatedTrainingSession' => '\\Training\\Common\\Model\\CoordinatedTrainingSession',
                ),
                'tabs' => array(
                    array('key' => 'tabTrainingSession', 'label' => 'All Training Sessions', 'component' => 'NativeCardList', 'entity' => 'TrainingSessionWithCourse'),
                    array('key' => 'tabEmployeeTrainingSession', 'label' => 'My Training Sessions', 'component' => 'NativeCardList', 'entity' => 'EmployeeTrainingSession'),
                    array('key' => 'tabSubEmployeeTraining', 'label' => 'Training Sessions of Direct Reports', 'component' => 'NativeCardList', 'entity' => 'SubEmployeeTraining'),
                    array('key' => 'tabCoordinatedTrainingSession', 'label' => 'Training Sessions Coordinated by Me', 'component' => 'NativeCardList', 'entity' => 'CoordinatedTrainingSession'),
                ),
            ),

            'modules/loans' => array(
                'initFn' => 'initUserLoans',
                'scripts' => self::moduleScripts(),
                // Entity key MyLoan (the EmployeeCompanyLoan model) so the card
                // config/permissions reflect the read-only "my loans" view.
                'entities' => array(
                    'MyLoan' => '\\Loans\\Common\\Model\\EmployeeCompanyLoan',
                ),
                'tabs' => array(
                    array('key' => 'tabEmployeeCompanyLoan', 'label' => 'Loans Taken', 'component' => 'NativeCardList', 'entity' => 'MyLoan'),
                ),
            ),

            'modules/leaves' => array(
                'initFn' => 'initUserLeaves',
                'scripts' => self::moduleScripts(),
                // Distinct entity keys (all the EmployeeLeave model) so each tab
                // gets its own card config; permissions resolve identically.
                'entities' => array(
                    'MyLeave' => '\\Leaves\\Common\\Model\\EmployeeLeave',
                    'MyLeaveApproved' => '\\Leaves\\Common\\Model\\EmployeeLeave',
                    'SubLeave' => '\\Leaves\\Common\\Model\\EmployeeLeave',
                    // EmployeeLeaveEntitlement is a computed view (no model
                    // permissions), so it is not registered here.
                    'LeaveApproval' => '\\Leaves\\Common\\Model\\EmployeeLeaveApprove',
                ),
                'tabs' => array(
                    // Entitlement leads: it's the "what do I have / apply from here"
                    // landing view of the leave module.
                    array('key' => 'tabMyLeaveEntitlement', 'label' => 'Leave Entitlement', 'component' => 'LeaveEntitlement', 'entity' => 'EmployeeLeaveEntitlement'),
                    array('key' => 'tabMyLeaveAll', 'label' => 'All My Leaves', 'component' => 'NativeCardList', 'entity' => 'MyLeave'),
                    array('key' => 'tabMyLeaveApproved', 'label' => 'Approved Leave', 'component' => 'NativeCardList', 'entity' => 'MyLeaveApproved'),
                    array('key' => 'tabMyLeavePending', 'label' => 'Pending Leave', 'component' => 'NativeCardList', 'entity' => 'MyLeave'),
                    // "For Approval" group — leaves of OTHER employees this user acts on.
                    // Direct Reports / Cancellations are supervisor queues (hidden for
                    // non-privileged users who manage no one); Approval Requests is the
                    // multi-level approver queue. Counts + visibility are resolved per
                    // user in AppShellRestEndPoint::getModuleContext.
                    array('key' => 'tabSubLeaveAll', 'label' => 'Leave Requests', 'component' => 'NativeCardList', 'entity' => 'SubLeave', 'group' => 'review', 'groupLabel' => 'For Approval', 'requiresSubordinatesUnlessPrivileged' => true),
                    array('key' => 'tabSubLeaveCancel', 'label' => 'Cancellation Requests', 'component' => 'NativeCardList', 'entity' => 'SubLeave', 'group' => 'review', 'groupLabel' => 'For Approval', 'requiresSubordinatesUnlessPrivileged' => true),
                    // Only shown when multi-level leave approvals are enabled.
                    array('key' => 'tabLeaveApproval', 'label' => 'Approval Requests', 'component' => 'NativeCardList', 'entity' => 'LeaveApproval', 'group' => 'review', 'groupLabel' => 'For Approval', 'requiresSetting' => 'Leave: Enable Multi Level Approvals'),
                ),
            ),

            'modules/leavecal' => array(
                'initFn' => 'initUserLeaveCal',
                'scripts' => self::moduleScripts(),
                'entities' => array(),
                'tabs' => array(
                    array('key' => 'tabEmployeeLeaveCalendar', 'label' => 'Leave Calendar', 'component' => 'LeaveCalendar'),
                ),
            ),

            'modules/emergency_contact' => array(
                'initFn' => 'initModulesEmergencyContact',
                'scripts' => self::moduleScripts(),
                'entities' => array(
                    'EmergencyContact' => '\\EmergencyContacts\\Common\\Model\\EmergencyContact',
                ),
                'tabs' => array(
                    array('key' => 'tabEmergencyContact', 'label' => 'Emergency Contacts', 'component' => 'NativeCardList', 'entity' => 'EmergencyContact', 'card' => array('disableView' => true)),
                ),
            ),

            'modules/qualifications' => array(
                'initFn' => 'initModulesQualifications',
                'scripts' => self::moduleScripts(),
                'entities' => array(
                    'EmployeeSkill' => '\\Qualifications\\Common\\Model\\EmployeeSkill',
                    'EmployeeEducation' => '\\Qualifications\\Common\\Model\\EmployeeEducation',
                    'EmployeeCertification' => '\\Qualifications\\Common\\Model\\EmployeeCertification',
                    'EmployeeLanguage' => '\\Qualifications\\Common\\Model\\EmployeeLanguage',
                ),
                'tabs' => array(
                    // disableView (not just hideViewButton): everything these
                    // records hold is on the card already, so viewing is off
                    // entirely — no eye button and no row-click view modal.
                    array('key' => 'tabEmployeeSkill', 'label' => 'Skills', 'component' => 'NativeCardList', 'entity' => 'EmployeeSkill', 'card' => array('disableView' => true)),
                    array('key' => 'tabEmployeeEducation', 'label' => 'Education', 'component' => 'NativeCardList', 'entity' => 'EmployeeEducation', 'card' => array('disableView' => true)),
                    array('key' => 'tabEmployeeCertification', 'label' => 'Certifications', 'component' => 'NativeCardList', 'entity' => 'EmployeeCertification', 'card' => array('disableView' => true)),
                    array('key' => 'tabEmployeeLanguage', 'label' => 'Languages', 'component' => 'NativeCardList', 'entity' => 'EmployeeLanguage', 'card' => array('disableView' => true)),
                ),
            ),

            // Personal Details (self-service). My Details is the user's own profile
            // (legacy adapter renders EmployeeProfile into #Employee); Company is the
            // native org chart; Mobile App is a native component.
            'modules/employees' => array(
                'initFn' => 'initModulesEmployees',
                'scripts' => self::moduleScripts(),
                'entities' => array(
                    'Employee' => '\\Employees\\Common\\Model\\Employee',
                ),
                'customFieldType' => 'Employee',
                'includeFieldNameMappings' => 'Employee',
                'includeCsrf' => 'password',
                'includeJwtToken' => 15552000, // 180 days, like the legacy index.php
                'extraData' => array(
                    'apiBaseUrl' => $apiBaseUrl,
                ),
                'extraSettings' => array(
                    'apiEnabled' => 'Api: REST Api Enabled',
                ),
                'tabs' => array(
                    array('key' => 'tabEmployee', 'label' => 'My Details', 'ids' => array(
                        'Employee', 'EmployeeForm', 'EmployeeFilterForm',
                    )),
                    array('key' => 'tabCompanyGraph', 'label' => 'Company', 'component' => 'OrgChart', 'scroll' => true),
                    array('key' => 'tabMobileApp', 'label' => 'Mobile App', 'component' => 'MobileApp'),
                    array('key' => 'tabApiAccess', 'label' => 'API Access', 'component' => 'ApiAccess'),
                ),
            ),

            // Time Sheets — a native React component (TimeSheets) renders the list
            // (status tabs + direct reports) and the editable project x date hours
            // grid; the legacy adapters back it via their proven custom actions.
            'modules/time_sheets' => array(
                'initFn' => 'initTimeSheets',
                'scripts' => self::moduleScripts(),
                'entities' => array(
                    'EmployeeTimeSheet' => '\\TimeSheets\\Common\\Model\\EmployeeTimeSheet',
                    'EmployeeTimeEntry' => '\\TimeSheets\\Common\\Model\\EmployeeTimeEntry',
                ),
                'extraSettings' => array(
                    'advancedTimesheets' => 'System: Advanced Timesheets',
                ),
                'tabs' => array(
                    array('key' => 'tabTimeSheets', 'label' => 'Time Sheets', 'component' => 'TimeSheets'),
                ),
            ),
        );
    }
}
