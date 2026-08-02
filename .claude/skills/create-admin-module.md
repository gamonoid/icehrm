# Create Admin Module Skill

This skill helps you create a new admin module or extension for IceHRM.

## Usage

```
/create-admin-module <module_name> [--type=extension|core] [--with-approval] [--with-api]
```

## Parameters

- `module_name`: The name of the module (e.g., "assets", "training", "projects")
- `--type`: Either `extension` (default) or `core`
- `--with-approval`: Include multi-level approval workflow support
- `--with-api`: Include REST API endpoints

## Instructions for Claude

When this skill is invoked, follow these steps:

### Step 1: Gather Requirements

Ask the user for:
1. Module name (singular form, e.g., "Asset", "Training")
2. Module label (display name, e.g., "Asset Management", "Training Programs")
3. Main entity/model name (e.g., "EmployeeAsset", "TrainingProgram")
4. Key fields for the main model (collect field name, type, and if required)
5. Which user levels should have access (Admin, Manager, Employee)
6. Menu group (Admin, Manage, Employees, Payroll, etc.)
7. Whether approval workflow is needed
8. Whether REST API is needed

### Step 2: Create Directory Structure

For **extension** type (recommended), create:
```
extensions/{module_name}/admin/
├── meta.json
├── {module_name}.php
├── web/
│   ├── index.php
│   └── js/
│       ├── index.js
│       └── lib.js
└── src/
    ├── Extension.php
    ├── Common/
    │   └── Model/
    │       └── {ModelName}.php
    └── Migrations/
        └── CreateTables.php
```

If `--with-api` is specified, also create:
```
└── src/
    ├── ApiController.php
    └── Rest/
        └── {ModuleName}RestEndPoint.php
```

If `--with-approval` is specified, also create:
```
└── src/
    └── Controller.php
```

### Step 3: File Templates

#### meta.json
```json
{
  "label": "{Module Label}",
  "menu": ["{Menu Group}", "fa-{icon}"],
  "order": "10",
  "icon": "fa-{icon}",
  "user_levels": ["Admin", "Manager"],
  "model_namespace": "\\{ModuleName}\\Common\\Model",
  "manager": "\\{ModuleName}\\Admin\\Extension",
  "controller": "\\{ModuleName}\\Admin\\Controller",
  "headless": false,
  "show_in_menu": true
}
```

#### {module_name}.php (Entry point that loads classes)
```php
<?php
namespace {ModuleName}\Admin;

use Classes\BaseService;

// Register extension namespace
BaseService::getInstance()->registerExtensionNamespace(
    '{ModuleName}',
    realpath(__DIR__)
);
```

#### src/Extension.php
```php
<?php
namespace {ModuleName}\Admin;

use Classes\BaseService;
use Classes\IceExtension;
use {ModuleName}\Admin\Migrations\CreateTables;

class Extension extends IceExtension
{
    public function initialize()
    {
        BaseService::getInstance()->registerExtensionMigration(new CreateTables());
    }

    public function setupModuleClassDefinitions()
    {
        $this->addModelClass('{ModelName}');
    }

    public function initializeUserClasses()
    {
        if (defined('MODULE_TYPE') && MODULE_TYPE != 'admin') {
            $this->addUserClass("{ModelName}");
        }
    }

    public function setupRestEndPoints()
    {
        // REST endpoints registered in ApiController
    }
}
```

#### src/Common/Model/{ModelName}.php
```php
<?php
namespace {ModuleName}\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class {ModelName} extends BaseModel
{
    public $table = '{TableName}';

    public function getAdminAccess()
    {
        return ["get", "element", "save", "delete"];
    }

    public function getManagerAccess()
    {
        return ["get", "element", "save", "delete"];
    }

    public function getUserAccess()
    {
        return ["get", "element"];
    }

    public function getUserOnlyMeAccess()
    {
        return ["get", "element", "save"];
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('{module_name}', 'admin'),
        ];
    }
}
```

#### src/Migrations/CreateTables.php
```php
<?php
namespace {ModuleName}\Admin\Migrations;

use Classes\Migration\AbstractMigration;
use Classes\Migration\MigrationInterface;

class CreateTables extends AbstractMigration implements MigrationInterface
{
    public function getName()
    {
        return '{module_name}_create_tables';
    }

    public function up()
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS `{TableName}` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `employee` BIGINT(20) DEFAULT NULL,
    -- Add your fields here
    `status` ENUM('Active','Inactive') DEFAULT 'Active',
    `created` DATETIME DEFAULT NULL,
    `updated` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `{TableName}_employee` (`employee`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        $this->executeQuery($sql);
    }

    public function down()
    {
        // Optional rollback
    }
}
```

#### web/index.php
```php
<?php
use Classes\BaseService;
use Classes\PermissionManager;
use {ModuleName}\Common\Model\{ModelName};

$moduleName = '{module_name}';
$moduleGroup = 'admin';
define('MODULE_PATH', dirname(__FILE__));

include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

$customFields = BaseService::getInstance()->getCustomFields("{ModelName}");
$model = new {ModelName}();

$moduleData = [
    'user_level' => $user->user_level,
    'customFields' => [
        '{ModelName}' => $customFields,
    ],
    'permissions' => [
        '{ModelName}' => PermissionManager::checkGeneralAccess($model),
    ]
];
?>
<div class="span9">
    <ul class="nav nav-tabs" id="modTab">
        <li class="active">
            <a id="tab{ModelName}" href="#tabPage{ModelName}"><?=t('{Model Label}')?></a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="tabPage{ModelName}">
            <div id="{ModelName}Table" class="reviewBlock" data-content="List"></div>
            <div id="{ModelName}Form"></div>
            <div id="{ModelName}FilterForm"></div>
        </div>
    </div>
</div>

<script>
    window.{module_name}ExtensionController = new IceExtensionController('<?=$moduleName?>');
    initAdmin{ModuleName}(<?=json_encode($moduleData)?>);
</script>
<?php include APP_BASE_PATH.'footer.php'; ?>
```

#### web/js/index.js
```javascript
import { {ModelName}AdminAdapter } from './lib';
import IceDataPipe from '../../../../../web/api/IceDataPipe';

function init(data) {
    const modJsList = {};

    modJsList.tab{ModelName} = new {ModelName}AdminAdapter(
        '{ModelName}',
        '{ModelName}',
        '',
        'id desc'
    );

    modJsList.tab{ModelName}.setObjectTypeName('{Model Label}');
    modJsList.tab{ModelName}.setDataPipe(new IceDataPipe(modJsList.tab{ModelName}));
    modJsList.tab{ModelName}.setRemoteTableType('simpleReact');
    modJsList.tab{ModelName}.setAccess(data.permissions.{ModelName});

    if (data.customFields && data.customFields.{ModelName}) {
        modJsList.tab{ModelName}.setCustomFields(data.customFields.{ModelName});
    }

    window.modJs = modJsList.tab{ModelName};
    window.modJsList = modJsList;
}

window.initAdmin{ModuleName} = init;
```

#### web/js/lib.js
```javascript
import ReactModalAdminAdapter from '../../../../../web/api/ReactModalAdminAdapter';

class {ModelName}AdminAdapter extends ReactModalAdminAdapter {
    constructor(endPoint, tab, filter, orderBy) {
        super(endPoint, tab, filter, orderBy);
    }

    getDataMapping() {
        return [
            'id',
            'employee',
            // Add your field mappings here
            'status',
        ];
    }

    getHeaders() {
        return [
            { sTitle: 'ID', bVisible: false },
            { sTitle: 'Employee' },
            // Add your headers here
            { sTitle: 'Status' },
        ];
    }

    getTableColumns() {
        return [
            {
                title: 'Employee',
                dataIndex: 'employee',
                sorter: true,
            },
            // Add your columns here
            {
                title: 'Status',
                dataIndex: 'status',
            },
        ];
    }

    getFormFields() {
        return [
            ['id', { label: 'ID', type: 'hidden' }],
            ['employee', {
                label: 'Employee',
                type: 'select2',
                'remote-source': ['Employee', 'id', 'first_name+last_name'],
            }],
            // Add your form fields here
            ['status', {
                label: 'Status',
                type: 'select',
                source: [['Active', 'Active'], ['Inactive', 'Inactive']],
            }],
        ];
    }

    getFilters() {
        return [
            ['employee', {
                label: 'Employee',
                type: 'select2',
                'allow-null': true,
                'null-label': 'All Employees',
                'remote-source': ['Employee', 'id', 'first_name+last_name'],
            }],
            ['status', {
                label: 'Status',
                type: 'select',
                'allow-null': true,
                'null-label': 'All',
                source: [['Active', 'Active'], ['Inactive', 'Inactive']],
            }],
        ];
    }
}

export { {ModelName}AdminAdapter };
```

### Step 4: If --with-approval is specified

#### src/Controller.php
```php
<?php
namespace {ModuleName}\Admin;

use Classes\Approval\ApproveAdminActionManager;

class Controller extends ApproveAdminActionManager
{
    public function getModelClass()
    {
        return "{ModelName}";
    }

    public function getItemName()
    {
        return "{Model Label}";
    }

    public function getModuleName()
    {
        return "{Module Label}";
    }

    public function changeStatus($req)
    {
        return parent::changeStatus($req);
    }
}
```

Update the Model to extend `ApproveModel` instead of `BaseModel`:
```php
use Model\ApproveModel;

class {ModelName} extends ApproveModel
{
    // Add approval-specific configuration
    public $notificationModuleName = "{Module Label}";
    public $notificationUnitName = "{Model Label}";

    public function getType()
    {
        return '{ModelName}';
    }

    public function fieldsNeedToBeApproved()
    {
        return ['field1', 'field2'];
    }
}
```

### Step 5: If --with-api is specified

#### src/ApiController.php
```php
<?php
namespace {ModuleName}\Admin;

use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;

class ApiController extends IceApiController
{
    public function registerEndPoints()
    {
        self::register(
            REST_API_PATH . '{module_name}/list',
            self::GET,
            function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                // Implement list endpoint
                $restEndpoint->sendResponse(
                    new IceResponse(IceResponse::SUCCESS, [])
                );
            }
        );

        self::register(
            REST_API_PATH . '{module_name}/(:num)',
            self::GET,
            function ($id) {
                $restEndpoint = new RestEndPoint();
                // Implement get by ID endpoint
                $restEndpoint->sendResponse(
                    new IceResponse(IceResponse::SUCCESS, [])
                );
            }
        );
    }
}
```

### Step 6: Update Build Configuration

Add the new module to the webpack build in `webpack.config.js` if it uses JavaScript:

```javascript
'{module_name}': './extensions/{module_name}/admin/web/js/index.js',
```

### Step 7: Final Steps

After creating all files:
1. Run database migrations (the migration will run automatically on first load)
2. Clear any caches
3. Build JavaScript with gulp (run `nvm use 20` first): for an extension run `gulp ejs --x{module_name}/admin` (and `--x{module_name}/user` if it has a user side). See `obfuscate-js.sh` and the "Building the Frontend" section in `CLAUDE.md`.
4. Test the module in the browser

## Common Field Types for Forms

- `text` - Text input
- `textarea` - Multi-line text
- `select` - Dropdown (with `source` array)
- `select2` - Searchable dropdown (with `remote-source`)
- `date` - Date picker
- `datetime` - Date and time picker
- `number` - Numeric input
- `hidden` - Hidden field
- `fileupload` - File upload
- `colorpick` - Color picker
- `signature` - Signature pad
- `datagroup` - Field grouping

## Remote Source Format

```javascript
'remote-source': ['ModelName', 'valueField', 'displayField']
// Example: ['Employee', 'id', 'first_name+last_name']
// Use + to concatenate fields
```

## Icon Reference

Common FontAwesome icons for modules:
- `fa-box` - Assets/Inventory
- `fa-graduation-cap` - Training
- `fa-project-diagram` - Projects
- `fa-file-alt` - Documents
- `fa-users` - Teams
- `fa-clipboard-list` - Tasks
- `fa-chart-bar` - Reports
- `fa-cog` - Settings
- `fa-money-bill` - Finance
- `fa-calendar` - Scheduling
