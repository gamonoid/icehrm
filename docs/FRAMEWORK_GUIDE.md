# IceHrm Framework Guide

This guide provides detailed information about IceHrm's custom framework architecture, helping AI tools and developers understand how the system works.

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Module System](#module-system)
3. [Extension System](#extension-system)
4. [Request Flow](#request-flow)
5. [Data Flow](#data-flow)
6. [Adapter System](#adapter-system)
7. [ModuleBuilder System](#modulebuilder-system)
8. [REST API System](#rest-api-system)
9. [File Upload System](#file-upload-system)
10. [Settings System](#settings-system)
11. [Integration Points](#integration-points)

## Architecture Overview

IceHrm uses a custom MVC-like architecture with clear separation between:
- **Backend (PHP)**: Custom MVC with PSR-4 autoloading
- **Frontend (React)**: React 16.13.1 with Ant Design 5.24.3
- **Bridge Layer**: Adapters, API clients, and data pipes

### Key Components

```
┌─────────────────────────────────────────────────────────┐
│                    User Browser                         │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│              React Components (Ant Design)               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │ IceTable     │  │ IceForm      │  │ Custom       │ │
│  │ IceFormModal │  │ IceUpload    │  │ Components   │ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│              Adapter Layer (JavaScript)                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │ AdapterBase  │  │ IceDataPipe   │  │ IceApiClient │ │
│  │ ReactModal   │  │ MasterData    │  │              │ │
│  │ AdapterBase  │  │ Reader        │  │              │ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│              API Layer                                  │
│  ┌──────────────┐  ┌──────────────┐                    │
│  │ service.php  │  │ REST API     │                    │
│  │ data.php     │  │ Endpoints    │                    │
│  └──────────────┘  └──────────────┘                    │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│              Service Layer (PHP)                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │ BaseService  │  │ Module       │  │ Action       │ │
│  │              │  │ Managers     │  │ Managers     │ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│              Model Layer (PHP)                         │
│  ┌──────────────┐  ┌──────────────┐                   │
│  │ BaseModel    │  │ MySqlActive  │                   │
│  │              │  │ Record       │                   │
│  └──────────────┘  └──────────────┘                   │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│              Database (MySQL)                          │
└─────────────────────────────────────────────────────────┘
```

## Module System

### Module Structure

Each module follows this structure:

```
ModuleName/
├── Admin/                      # Admin interface
│   ├── Api/                   # Admin API endpoints
│   │   ├── ModuleNameAdminManager.php
│   │   └── ModuleNameActionManager.php
│   └── index.php              # Admin entry point
├── User/                       # User interface
│   ├── Api/                   # User API endpoints
│   │   ├── ModuleNameModulesManager.php
│   │   └── ModuleNameActionManager.php
│   └── index.php              # User entry point
├── Common/                     # Shared components
│   └── Model/                 # Data models
│       └── ModuleName.php
├── Rest/                       # REST API endpoints
│   └── ModuleNameRestEndPoint.php
└── meta.json                  # Module metadata
```

### Module Manager Lifecycle

1. **Registration**: Module manager class is registered in `core/modules.php`
2. **Initialization**: `includeModuleManager()` creates instance and calls methods:
   - `initializeUserClasses()` - Define user-scoped tables
   - `initializeFieldMappings()` - Define file field mappings
   - `initializeDatabaseErrorMappings()` - Define error mappings
   - `setupModuleClassDefinitions()` - Register model classes
   - `setupRestEndPoints()` - Register REST API routes
3. **Model Registration**: Models added to `BaseService::$modelClassMap`
4. **REST Registration**: Routes registered via `Macaw` routing
5. **Metadata Loading**: `meta.json` loaded for permissions and UI

### Module Metadata (`meta.json`)

```json
{
  "label": "Module Display Name",
  "name": "module_name",
  "mod_group": "admin",
  "user_levels": ["Admin", "Manager"],
  "user_roles": [],
  "status": "Active",
  "dashboardPosition": 1
}
```

## Extension System

Extensions are self-contained add-ons that extend IceHrm's functionality without modifying core code. They provide a clean way to add new features, modules, and integrations.

### Creating an Extension

Use the CLI command to scaffold a new extension:

```bash
php ice create:extension {name} {type}
```

**Parameters:**
- `{name}`: Extension name (e.g., `insights`, `kudos`)
- `{type}`: Extension type - `admin` or `modules` (determines user access level)

**Example:**
```bash
php ice create:extension insights admin
```

This creates a complete extension structure at `extensions/insights/admin/`.

### Extension Directory Structure

```
extensions/
└── {name}/
    └── {type}/                    # admin or modules
        ├── {name}.php             # Main entry point (loads all classes)
        ├── meta.json              # Extension metadata and configuration
        ├── src/
        │   ├── Extension.php      # Extension manager (extends IceExtension)
        │   ├── Controller.php     # Backend controller (extends IceController)
        │   ├── ApiController.php  # REST API endpoints (extends IceApiController)
        │   ├── Common/
        │   │   └── Model/         # ORM models (extend BaseModel)
        │   └── Migrations/
        │       └── CreateTables.php  # Database migrations
        ├── web/
        │   ├── index.php          # Frontend entry point
        │   └── js/
        │       ├── index.js       # JavaScript initialization
        │       ├── controller.js  # Frontend API controller
        │       ├── module.js      # Module class (extends ExtensionModuleBase)
        │       └── view.js        # React view component
        └── dist/
            └── {name}.js          # Compiled JavaScript bundle
```

### Extension Entry Point (`{name}.php`)

The main entry point loads all required PHP classes:

```php
<?php
require_once __DIR__.'/src/Extension.php';
require_once __DIR__.'/src/Controller.php';
require_once __DIR__.'/src/ApiController.php';

// Migrations
require_once __DIR__.'/src/Migrations/CreateTables.php';
```

### Extension Manager (`src/Extension.php`)

Manages extension initialization, model registration, and REST API setup:

```php
<?php
namespace InsightsAdmin;

use Classes\BaseService;
use Classes\IceExtension;
use InsightsAdmin\Migrations\CreateTables;

class Extension extends IceExtension
{
    public function initialize() {
        // Register database migrations
        // BaseService::getInstance()->registerExtensionMigration(new CreateTables());
    }

    public function setupModuleClassDefinitions() {
        // Register ORM model classes
        // $this->addModelClass('ClassName');
    }

    public function setupRestEndPoints() {
        // Initialize REST API endpoints
        (new ApiController())->registerEndPoints();
    }
}
```

### Extension Metadata (`meta.json`)

Configures extension appearance and access control:

```json
{
  "label": "Insights Admin",
  "menu": ["Insights", "fa-th"],
  "order": "0",
  "icon": "fa-random",
  "user_levels": ["Admin"],
  "dashboardPosition": 0,
  "permissions": [],
  "model_namespace": "\\InsightsAdmin\\Common\\Model",
  "manager": "\\InsightsAdmin\\Extension",
  "controller": "\\InsightsAdmin\\Controller",
  "headless": false,
  "show_in_menu": true
}
```

**Key Properties:**
- `label`: Display name in the menu
- `menu`: Menu group and FontAwesome icon `["Group Name", "fa-icon"]`
- `user_levels`: Array of user levels with access (`["Admin"]`, `["Admin", "Manager"]`)
- `model_namespace`: PHP namespace for ORM models
- `manager`: Fully qualified class name for Extension manager
- `controller`: Fully qualified class name for Controller
- `headless`: If `true`, extension has no UI (API only)
- `show_in_menu`: If `false`, extension is hidden from menu

### Backend Controller (`src/Controller.php`)

Provides backend actions callable from frontend:

```php
<?php
namespace InsightsAdmin;

use Classes\IceController;
use Classes\IceResponse;

class Controller extends IceController
{
    public function testAction($req): IceResponse
    {
        // Access request data via $req object
        return new IceResponse(IceResponse::SUCCESS, 'Echo from server: '.$req->data);
    }

    public function customAction($req): IceResponse
    {
        // Perform business logic
        $result = $this->processData($req);

        if ($result) {
            return new IceResponse(IceResponse::SUCCESS, $result);
        }
        return new IceResponse(IceResponse::ERROR, 'Operation failed');
    }
}
```

**Action Method Naming:**
- Methods must end with `Action` suffix (e.g., `testAction`, `saveAction`)
- Called from frontend as action name without suffix (e.g., `test`, `save`)

### REST API Controller (`src/ApiController.php`)

Exposes extension functionality through REST endpoints:

```php
<?php
namespace InsightsAdmin;

use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;

class ApiController extends IceApiController
{
    public function registerEndPoints() {
        // GET request
        self::register(
            REST_API_PATH . 'insights/echo', self::GET, function ($pathParams = null) {
                (new RestEndPoint())->sendResponse(
                    new IceResponse(IceResponse::SUCCESS, 'Hello from extension')
                );
            }
        );

        // GET request with path parameter
        self::register(
            REST_API_PATH . 'insights/echo/(:any)', self::GET, function ($parameter) {
                (new RestEndPoint())->sendResponse(
                    new IceResponse(IceResponse::SUCCESS, 'Data: '.$parameter)
                );
            }
        );

        // GET request with multiple path parameters
        self::register(
            REST_API_PATH . 'insights/echo/(:any)/(:num)', self::GET,
            function ($param1, $param2) {
                (new RestEndPoint())->sendResponse(
                    new IceResponse(IceResponse::SUCCESS, "Param1: $param1, Param2: $param2")
                );
            }
        );

        // POST request
        self::register(
            REST_API_PATH . 'insights/echo', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $restEndpoint->sendResponse(
                    new IceResponse(IceResponse::SUCCESS, 'POST data: '.json_encode($data))
                );
            }
        );
    }
}
```

**Path Parameter Types:**
- `(:any)` - Matches any string
- `(:num)` - Matches numeric values only
- `(:all)` - Matches everything including slashes

### Database Migrations (`src/Migrations/CreateTables.php`)

Handles database table creation and data initialization:

```php
<?php
namespace InsightsAdmin\Migrations;

use Classes\Migration\AbstractMigration;
use Classes\Migration\MigrationInterface;

class CreateTables extends AbstractMigration implements MigrationInterface
{
    public function getName() {
        return 'insights_create_table';  // Unique identifier
    }

    public function up() {
        $sql = [];

        $sql[] = <<<'SQL'
CREATE TABLE IF NOT EXISTS InsightsData (
    id bigint auto_increment PRIMARY KEY,
    name varchar(100) NOT NULL,
    value text NULL,
    created datetime NULL,
    updated datetime NULL
) charset = utf8mb3;
SQL;

        foreach ($sql as $query) {
            if (!$this->executeQuery($query)) {
                return false;
            }
        }

        return true;
    }

    public function down() {
        return true;
    }
}
```

**Registering Migrations:**
```php
// In Extension.php initialize() method
BaseService::getInstance()->registerExtensionMigration(new CreateTables());
```

### Frontend Entry Point (`web/index.php`)

Initializes the React component with module data:

```php
<?php
use Classes\PermissionManager;

$moduleData = [
    'controller_url' => CLIENT_BASE_URL.'service.php',
    'user_level' => $user->user_level,
];
?>
<div class="span9"><div id="content"></div></div>
<script>
initInsightsAdmin(<?=json_encode($moduleData)?>);
</script>
```

### JavaScript Initialization (`web/js/index.js`)

Sets up the extension module and controller:

```javascript
import React from 'react';
import InsightsAdminExtensionController from './controller';
import InsightsAdminModule from './module';

function init(data) {
  // Required: Expose modJsList and modJs for IceHrm core compatibility
  window.modJsList = [];
  window.modJs = new InsightsAdminModule('InsightsAdmin');
  window.modJsList.push(window.modJs);

  // Define controller for API requests
  window.insightsExtensionController = new InsightsAdminExtensionController(
    'admin=insights',  // Extension identifier
    data.controller_url,
  );
}

window.initInsightsAdmin = init;
```

### Frontend Controller (`web/js/controller.js`)

Handles API communication with the backend:

```javascript
import ExtensionController from '../../../../../web/api/ExtensionController';

class InsightsAdminExtensionController extends ExtensionController {
  // Call backend Controller actions
  handleTestAction() {
    this.handleRequest(
      'testAction',  // Action name (maps to testAction in Controller.php)
      { data: 'message from client' },
    ).then((response) => {
      console.log(response);
    });
  }

  // Call REST API endpoints
  makeSomeTestApiRequests() {
    // GET request
    this.getApiClient().get('insights/echo').then((response) => {
      console.log('GET response:', response);
    });

    // POST request
    this.getApiClient().post('insights/echo', {
      browserTime: (new Date()).getTime()
    }).then((response) => {
      console.log('POST response:', response);
    });
  }
}

export default InsightsAdminExtensionController;
```

### Frontend Module (`web/js/module.js`)

Manages extension view lifecycle:

```javascript
import React from 'react';
import ReactDOM from "react-dom";
import ExtensionModuleBase from "../../../../../web/api/ExtensionModuleBase";
import InsightsAdminExtensionView from "./view";

class InsightsAdminModule extends ExtensionModuleBase {
  // Called after IceHrm core and frontend is loaded
  showExtensionView() {
    // Mount React component
    ReactDOM.render(
      <InsightsAdminExtensionView />,
      document.getElementById('content')
    );

    // Optional: Call controller methods on load
    window.insightsExtensionController.handleTestAction();
    window.insightsExtensionController.makeSomeTestApiRequests();
  }
}

export default InsightsAdminModule;
```

### React View Component (`web/js/view.js`)

The main UI component using React and Ant Design:

```javascript
import React from 'react';
import { Empty, Card, Button } from 'antd';

class InsightsAdminExtensionView extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      data: [],
      loading: false,
    };
  }

  componentDidMount() {
    this.loadData();
  }

  loadData = () => {
    this.setState({ loading: true });
    window.insightsExtensionController.getApiClient()
      .get('insights/data')
      .then((response) => {
        this.setState({
          data: response.data,
          loading: false
        });
      })
      .catch((error) => {
        console.error('Error loading data:', error);
        this.setState({ loading: false });
      });
  };

  render() {
    const { data, loading } = this.state;

    return (
      <Card title="Insights Dashboard" loading={loading}>
        {data.length === 0 ? (
          <Empty description="No data available" />
        ) : (
          // Render data
          <div>{/* Your UI here */}</div>
        )}
      </Card>
    );
  }
}

export default InsightsAdminExtensionView;
```

### Extension Lifecycle

1. **Registration**: Extension is discovered via `meta.json` in extensions directory
2. **Loading**: `{name}.php` loads all PHP classes when extension is accessed
3. **Initialization**: `Extension::initialize()` registers migrations and sets up the extension
4. **Model Setup**: `Extension::setupModuleClassDefinitions()` registers ORM models
5. **REST Setup**: `Extension::setupRestEndPoints()` registers API routes
6. **Frontend Init**: `web/index.php` initializes JavaScript with `init{Name}()`
7. **View Render**: `Module::showExtensionView()` mounts React component

### Building Extension Frontend

Extensions use the same webpack build process as the main application:

```bash
cd web
npm run build:extensions
```

The compiled bundle is output to `extensions/{name}/{type}/dist/{name}.js`.

## Request Flow

### Standard Module Request Flow

This will examine settings module.

```
1. User navigates to: ?g=admin&n=settings
   ↓
2. app/index.php routes to: core/admin/settings/index.php
   ↓
3. Module index.php:
   - Sets MODULE_PATH
   - Includes header.php (loads core, sets up UI)
   - Includes modulejslibs.inc.php (loads JavaScript bundles)
   - Creates ModuleBuilder with tabs
   - Renders HTML tabs and containers
   - Generates JavaScript adapter initialization code
   ↓
4. Browser loads page:
   - HTML tabs rendered
   - JavaScript adapters initialized in modJsList
   - Active tab adapter assigned to modJs
   ↓
5. Adapter.get() called:
   - Constructs API URL (service.php or data.php)
   - Sends POST request with filters, source mapping
   ↓
6. PHP service.php/data.php:
   - Routes to BaseService::get() or custom handler
   - Validates permissions
   - Queries database via BaseModel
   - Applies postProcessGetData() transformations
   - Returns JSON response
   ↓
7. Adapter receives response:
   - Parses JSON data
   - Calls getSuccessCallBack()
   - Updates React component state
   ↓
8. React component re-renders:
   - IceTable displays data
   - User can interact with table
```

### REST API Request Flow

```
1. React component calls: apiClient.post('module/save', data)
   ↓
2. IceApiClient constructs URL:
   - Legacy: api/index.php?token=...&method=post&url=/module/save
   - Modern: /api/module/save (with Bearer token)
   ↓
3. PHP api-rest.php/api-url-based.php:
   - Routes request via Macaw
   - Calls RestEndPoint::process()
   ↓
4. RestEndPoint::process():
   - Validates access token (if required)
   - Sets current user from token/session
   - Calls endpoint method (e.g., save())
   - Returns IceResponse
   ↓
5. RestEndPoint::sendResponse():
   - Converts IceResponse to HTTP response
   - Sets status codes (200, 201, 400, 403, etc.)
   - Returns JSON
   ↓
6. React component receives response:
   - Updates UI
   - Shows success/error message
```

## Data Flow

### PHP → JavaScript Data Flow

1. **Model Layer**: `BaseModel` loads data from database
2. **Service Layer**: `BaseService` processes data:
   - Applies `postProcessGetData()` transformations
   - Populates field mappings (joins)
   - Cleans up ADOdb objects
3. **API Layer**: JSON serialization:
   ```php
   return json_encode([
       'status' => 'SUCCESS',
       'object' => $processedData
   ]);
   ```
4. **Adapter Layer**: Parses JSON and updates React state
5. **React Layer**: Components re-render with new data

### JavaScript → PHP Data Flow

1. **React Component**: User interaction triggers action
2. **Adapter/API Client**: Constructs request:
   ```javascript
   {
     t: 'TableName',
     a: 'save',
     id: 123,
     field1: 'value1',
     field2: 'value2'
   }
   ```
3. **PHP Service**: `BaseService::addElement()`:
   - Validates CSRF token
   - Checks permissions
   - Creates/updates model
   - Calls `validateSave()`, `executePreSaveActions()`
   - Saves to database
   - Calls `executePostSaveActions()`
4. **Response**: Returns updated model or error

## Adapter System

### Adapter Hierarchy

```
ModuleBase (base functionality)
  ↓
AdapterBase (CRUD operations, data fetching)
  ↓
ReactModalAdapterBase (React table/form rendering)
  ↓
CustomAdapter (module-specific logic)
```

### AdapterBase Key Methods

#### Data Fetching
- `get(callBackData)` - Fetch list of records
- `getElement(id, callBackData)` - Fetch single record
- `add(object)` - Create new record
- `delete(id)` - Delete record

#### Form Handling
- `fillForm(object)` - Populate form with data
- `edit(id)` - Load record for editing
- `addNew()` - Initialize new record form

#### Filtering & Sorting
- `setFilter(filter)` - Set filter object
- `getFilter()` - Get current filter
- `setOrderBy(orderBy)` - Set sort order

#### Remote Data
- `getFieldValues(remoteSource, callBackData)` - Load remote data
- `fieldMasterData` - Storage for remote data (key-value pairs)

### ReactModalAdapterBase Extensions

#### Table Rendering
```javascript
initTable() {
  // Renders IceTable component into container
  ReactDOM.render(
    <IceTable adapter={this} reader={this.masterDataReader} />,
    document.getElementById(`${this.tab}Table`)
  );
}
```

#### Form Rendering
```javascript
initForm() {
  // Renders IceFormModal component
  ReactDOM.render(
    <IceFormModal adapter={this} fields={this.getFormFields()} />,
    document.getElementById(`${this.tab}Form`)
  );
}
```

#### Data Pipe Integration
```javascript
setDataPipe(dataPipe) {
  this.dataPipe = dataPipe;
}

// Usage
const dataPipe = new IceDataPipe(this);
this.setDataPipe(dataPipe);
dataPipe.get({ page: 1, limit: 10, filters: this.getFilter() })
  .then(response => {
    // response.items = array of records
    // response.total = total count
  });
```

### Adapter Initialization Pattern

```javascript
// Generated by ModuleBuilder
modJsList['tabCompanySetting'] = new SettingAdapter(
  'Setting',           // table/endpoint
  'CompanySetting',    // tab name
  '{"category":"Company"}', // filter JSON
  'name'               // order by
);

// Manual initialization (if needed)
if (!adapter.dataPipe) {
  adapter.setDataPipe(new IceDataPipe(adapter));
}
adapter.loadRemoteDataForSettings();
adapter.get([]);
```

## ModuleBuilder System

### ModuleBuilder Class

`ModuleBuilder` (`core/src/Classes/ModuleBuilder/ModuleBuilder.php`) generates:
1. **Tab Headers HTML**: Bootstrap tab navigation
2. **Tab Pages HTML**: Tab content containers
3. **JavaScript Code**: Adapter initialization

### ModuleTab Class

`ModuleTab` (`core/src/Classes/ModuleBuilder/ModuleTab.php`) represents a single tab:

```php
new ModuleTab(
    $name,        // Tab identifier (e.g., 'CompanySetting')
    $class,       // Model class (e.g., 'Setting')
    $label,       // Display label (e.g., 'Company')
    $adapterName, // JS adapter class (e.g., 'SettingAdapter')
    $filter,      // Filter JSON string (e.g., '{"category":"Company"}')
    $orderBy,     // Sort field (e.g., 'name')
    $isActive,    // Is active tab (boolean)
    $options      // Additional options array
);
```

### Generated HTML Structure

#### Tab Headers
```html
<li class="active">
  <a id="tabCompanySetting" href="#tabPageCompanySetting">Company</a>
</li>
```

#### Tab Pages
```html
<div class="tab-pane active" id="tabPageCompanySetting">
  <div id="CompanySetting" class="reviewBlock" data-content="List"></div>
  <div id="CompanySettingForm" class="reviewBlock" data-content="Form" style="display:none;"></div>
</div>
```

#### JavaScript Initialization
```javascript
modJsList['tabCompanySetting'] = new SettingAdapter(
  'Setting',
  'CompanySetting',
  '{"category":"Company"}',
  'name'
);
var modJs = modJsList['tabCompanySetting'];
```

### ModuleBuilder Limitations

The old `ModuleBuilder` system has some limitations:
1. **No `setDataPipe` support**: Options like `setDataPipe` aren't processed correctly
2. **Container IDs**: Uses `{tabName}` not `{tabName}Table`
3. **Manual initialization**: Need to manually set dataPipe and call `get()`

**Workaround**:
```javascript
// In module index.php, after ModuleBuilder output
$(window).load(function() {
  // Manually set dataPipe for each tab
  modJsList['tabCompanySetting'].setDataPipe(
    new IceDataPipe(modJsList['tabCompanySetting'])
  );
  // Initialize table
  modJs.get([]);
});
```

## REST API System

### REST Endpoint Structure

```php
namespace ModuleName\Rest;

use Classes\RestEndPoint;
use Users\Common\Model\User;

class ModuleNameRestEndPoint extends RestEndPoint
{
    public function save(User $user)
    {
        // Validate user permissions
        if ($user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, "Permission denied", 403);
        }
        
        // Get request body
        $body = $this->getRequestBody();
        
        // Process request
        $response = BaseService::getInstance()->addElement('ModelName', $body);
        
        // Return response
        if ($response->getStatus() === IceResponse::SUCCESS) {
            return new IceResponse(IceResponse::SUCCESS, $response->getData(), 200);
        }
        
        return new IceResponse(IceResponse::ERROR, $response->getData(), 400);
    }
}
```

### Endpoint Registration

```php
// In ModuleManager::setupRestEndPoints()
\Classes\Macaw::post(
    REST_API_PATH.'module/save',
    function () {
        $restEndPoint = new ModuleNameRestEndPoint();
        $restEndPoint->process('save', [], true); // true = require access token
    }
);
```

### Authentication Methods

#### Token-Based (API Clients)
```php
public function process($method, $params, $requireAccessToken = true)
{
    if ($requireAccessToken) {
        $accessTokenValidation = $this->validateAccessToken();
        // Sets current user from token
        BaseService::getInstance()->setCurrentUser($accessTokenValidation->getData());
    }
    // Call endpoint method
}
```

#### Session-Based (Admin UI)
```php
// In endpoint method
public function save(User $user = null)
{
    // Fallback to session if no token
    if (!$user || empty($user->id)) {
        $sessionUser = SessionUtils::getSessionObject('user');
        if ($sessionUser) {
            $user = $sessionUser;
            BaseService::getInstance()->setCurrentUser($user);
        }
    }
    // Process request
}
```

### Response Format

#### Success Response
```json
{
  "id": 123,
  "name": "Value",
  "status": "Active"
}
```
HTTP Status: 200 or 201

#### Error Response
```json
{
  "error": [[{"code": 400, "message": "Error message here"}]]
}
```
HTTP Status: 400, 403, 404, etc.

**Note**: The error structure is a nested array: `error[0][0].message` contains the message.

#### Frontend Error Handling Pattern

```javascript
const handleApiCall = async () => {
  try {
    const response = await getApiClient().delete(`module/endpoint/${id}`);
    // Check for error in successful HTTP response (200 with error in body)
    if (response.data?.error) {
      const errorMsg = response.data.error[0]?.[0]?.message || 'Operation failed';
      message.error(errorMsg, 5);
      return;
    }
    message.success('Operation successful');
  } catch (error) {
    // Handle HTTP error responses (4xx, 5xx)
    const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Operation failed';
    message.error(errorMsg, 5);
  }
};
```

## File Upload System

### Upload Flow

```
1. User selects file in IceUpload component
   ↓
2. Ant Design Upload component uploads to:
   fileupload-new.php?user={userId}&file_group={group}&file_name={name}
   ↓
3. core/common-upload.php:
   - Validates file extension and size
   - Saves file to BaseService::getDataDirectory()
   - Creates Files table entry
   - Optionally uploads to S3
   ↓
4. Returns JSON response:
   {
     "name": "file_name",      // Files.name column
     "status": "success",
     "url": "file_url"
   }
   ↓
5. IceUpload.handleChange():
   - Extracts file.response.name
   - Calls onChange(fileName)
   ↓
6. SettingFileUploadField.handleFileChange():
   - Updates setting value with file name
   - Triggers save when form is submitted
```

### Files Table Structure

```sql
CREATE TABLE Files (
    id          bigint auto_increment PRIMARY KEY,
    name        varchar(100) NOT NULL UNIQUE,  -- File identifier
    filename    varchar(100) NOT NULL,        -- Physical filename
    employee    bigint NULL,                  -- Owner (or NULL for system)
    file_group  varchar(100) NULL,            -- Group identifier
    size        bigint NULL,                  -- File size in bytes
    size_text   varchar(20) NULL,             -- Human-readable size
    object_type varchar(100) NULL,             -- Related object type
    object_id   bigint NULL                   -- Related object ID
);
```

### File Upload Usage

```javascript
// In component
<IceUpload
  user={userId}                    // Employee ID or '_NONE_'
  fileGroup="Settings"             // Group identifier
  fileName="setting_123"           // Desired file name
  adapter={adapter}                // Adapter for getFile()
  accept="image/*"                 // File type filter
  readOnly={false}                 // Allow upload
  value={currentFileName}         // Current file name
  onChange={handleFileChange}      // Callback with file name
/>
```

### FileService Class

Use `FileService` singleton for file operations:

```php
use Classes\FileService;

// Delete a file by field value (e.g., by 'name' column in Files table)
FileService::getInstance()->deleteFileByField($document->attachment, 'name');

// Get the data directory path
$dataDir = FileService::getInstance()->getDataDirectory();

// Check if file exists
$exists = FileService::getInstance()->fileExists($fileName);
```

**Note**: Use `deleteFileByField()` not `getFilePath()` for file deletion.

## Settings System

### Settings Table Structure

```sql
CREATE TABLE Settings (
    id            bigint auto_increment PRIMARY KEY,
    name          varchar(100) NOT NULL UNIQUE,
    value         text NULL,
    description   text NULL,
    meta          text NULL,        -- JSON field configuration
    category      varchar(15) NOT NULL,
    setting_group varchar(20) NULL, -- Group identifier for organizing settings
    setting_order int NOT NULL DEFAULT 0 -- Display order within category/group
);
```

**Key Fields**:
- `name`: Unique setting identifier (e.g., `"Company: Name"`, `"System: Data Directory"`)
- `value`: The setting value (encrypted for sensitive settings)
- `description`: Help text displayed in the UI
- `meta`: JSON string defining how the setting is rendered in the UI
- `category`: Determines which tab the setting appears in (`"Company"`, `"System"`, `"Email"`, `"Leave"`, `"Attendance"`, `"Files"`, `"LDAP"`, `"SAML"`, `"Other"`)
- `setting_group`: Optional group identifier for further organization
- `setting_order`: Display order (default: 0)

### Meta Column Format

The `meta` column contains JSON that defines field rendering:

```json
["value", {
  "label": "Value",
  "type": "select2",
  "source": [["1", "Yes"], ["0", "No"]],
  "allow-null": false,
  "remote-source": ["Country", "id", "name"]
}]
```

### Settings Rendering Components

Each setting type has a dedicated React component:

- **`SettingTextField`**: Text inputs (`type: "text"`)
- **`SettingTextareaField`**: Textarea inputs (`type: "textarea"`)
- **`SettingSelectField`**: Select dropdowns (`type: "select"` or `"select2"`)
- **`SettingMultiSelectField`**: Multi-select (`type: "select2multi"`)
- **`SettingSwitchField`**: Yes/No toggles (detected from source options)
- **`SettingPlaceholderField`**: Read-only (`type: "placeholder"`)
- **`SettingFileUploadField`**: File uploads (`type: "fileupload"`)

### Settings Value Handling

#### Value Truncation
- `Setting::postProcessGetData()` truncates values > 30 chars for table display
- Use `getElement()` to get full values (not truncated)
- Settings form components fetch full values when needed

#### Value Types
- **Text**: Stored as string
- **Select**: Stored as string (option value)
- **Multi-select**: Stored as JSON string: `["value1","value2"]`
- **Switch**: Stored as "1" or "0" string
- **File Upload**: Stored as Files table `name` column value

### Settings Display and Ordering

#### Setting Order Column
The `setting_order` column controls the display order of settings within each category tab:
- **Non-zero values**: Displayed first, sorted in ascending order (1, 2, 3, ...)
- **Zero or NULL values**: Displayed at the bottom (maintains original order among themselves)
- **Backend ordering**: SQL expression: `CASE WHEN setting_order IS NULL OR setting_order = 0 THEN 1 ELSE 0 END, setting_order ASC`
- **Frontend sorting**: Client-side sorting as backup ensures correct order even if backend ordering fails

#### Settings UI Components
- **Card Wrapper**: Each setting is wrapped in an Ant Design `Card` component for visual separation
- **Description Display**: Descriptions are shown as dimmed text below each setting field (not in tooltips)
- **Search Functionality**: Frontend search filters settings by name and description (client-side only)
- **Hidden Settings**: Specific settings can be filtered out from display (e.g., internal/system settings)

#### Hiding Settings from Frontend
Settings can be hidden from the UI by filtering them out after fetching:
```javascript
const visibleSettings = settings.filter((setting) => {
  const hiddenSettings = [
    'System: Do not pass JSON in request',
    'Api: REST Api Token',
  ];
  return !hiddenSettings.includes(setting.name);
});
```

#### Settings Save Flow

```
1. User changes setting value
   ↓
2. Component calls onChange(settingId, newValue)
   ↓
3. SettingsPage.handleSettingChange():
   - Updates changedSettings state
   - Updates form field value immediately
   ↓
4. User clicks "Save Changes"
   ↓
5. SettingsPage.handleSave():
   - Iterates changedSettings
   - Calls REST API: POST /api/settings/save
   - Sends {id: settingId, value: newValue}
   ↓
6. SettingsRestEndPoint::save():
   - Validates Admin permission
   - Calls BaseService::addElement('Setting', {...})
   ↓
7. Setting model:
   - executePreSaveActions(): Encrypts if needed
   - validateSave(): Validates value
   - Save(): Persists to database
   ↓
8. Response returned, UI updated
```

## Integration Points

### PHP → JavaScript Integration

#### ModuleBuilder JavaScript Generation
```php
// PHP generates JavaScript code
$moduleBuilder->getModJsHTML();
// Output:
// modJsList['tabCompanySetting'] = new SettingAdapter(...);
// var modJs = modJsList['tabCompanySetting'];
```

#### JavaScript Adapter Access
```javascript
// Access adapter from PHP-generated code
const adapter = modJsList['tabCompanySetting'];

// Or from React component
const adapter = this.props.adapter;
```

### JavaScript → PHP Integration

#### Service.php API
```javascript
// Standard CRUD operations
$.post(moduleRelativeURL, {
  t: 'TableName',
  a: 'get',        // or 'add', 'save', 'delete', 'getElement'
  sm: sourceMappingJson,
  ft: filterJson,
  ob: orderBy
}, callback, 'json');
```

#### REST API
```javascript
// Modern REST endpoints
apiClient.post('module/save', {
  id: 123,
  field: 'value'
});
```

### React → Adapter Integration

#### Table Rendering
```javascript
// Adapter provides data to IceTable
<IceTable
  adapter={adapter}
  reader={adapter.masterDataReader}
/>

// Adapter methods called by IceTable
adapter.get([]);           // Load data
adapter.edit(id);          // Edit record
adapter.delete(id);        // Delete record
```

#### Form Rendering
```javascript
// Adapter provides form configuration
<IceFormModal
  adapter={adapter}
  fields={adapter.getFormFields()}
/>

// Adapter methods called by form
adapter.fillForm(object);  // Populate form
adapter.add(object);        // Save new record
```

### Data Pipe Integration

```javascript
// Create data pipe
const dataPipe = new IceDataPipe(adapter);

// Set on adapter
adapter.setDataPipe(dataPipe);

// Use for data fetching
adapter.dataPipe.get({
  page: 1,
  limit: 10,
  filters: adapter.getFilter()
}).then(response => {
  // response.items = array of records
  // response.total = total count
});
```

## Key Concepts

### Source Mapping
Source mapping defines which fields to fetch and how to populate related data:

```javascript
getSourceMapping() {
  return {
    'department': ['CompanyStructure', 'id', 'title'],
    'supervisor': ['Employee', 'id', 'first_name+last_name']
  };
}
```

### Field Master Data
Remote data loaded for dropdowns:

```javascript
// Load remote data
adapter.getFieldValues(['Country', 'id', 'name'], callBackData);

// Access loaded data
const countries = adapter.fieldMasterData['Country_id_name'];
```

### Filter Handling
Filters are JSON objects passed to backend:

```javascript
// Set filter
adapter.setFilter({ category: 'Company' });

// Filter is used in:
// - adapter.get() → sent as 'ft' parameter
// - adapter.dataPipe.get() → used by getDataUrl()
```

### Form Field Configuration
Form fields defined as arrays:

```javascript
getFormFields() {
  return [
    ['id', { label: 'ID', type: 'hidden' }],
    ['name', { label: 'Name', type: 'text', validation: 'required' }],
    ['value', JSON.parse(setting.meta)],  // Dynamic from meta
    ['enabled', { label: 'Enabled', type: 'switch', validation: 'none' }]
  ];
}
```

**Supported Field Types in IceForm:**
- `'hidden'` - Hidden input field
- `'text'` - Text input
- `'password'` - Password input
- `'textarea'` - Multi-line text input
- `'select'` - Dropdown select (static options)
- `'select2'` - Searchable dropdown (static or remote options)
- `'select2multi'` - Multi-select dropdown
- `'switch'` - Toggle switch for Yes/No fields (stores '0'/'1' strings)
- `'date'` - Date picker
- `'datetime'` - Date and time picker
- `'time'` - Time picker
- `'fileupload'` - File upload component
- `'colorpick'` - Color picker
- `'signature'` - Signature pad
- `'datagroup'` - Data group component
- `'placeholder'` - Read-only placeholder
- `'quill'` - Rich text editor
- `'slider'` - Slider input

**Switch Field Type:**
The `switch` type renders an Ant Design Switch component and automatically handles value conversion:
- **Storage**: Always stores '0' (unchecked) or '1' (checked) as strings in form state
- **Database**: Use `tinyint` column type (0/1)
- **Payload**: Form submission sends '0' or '1' strings, never booleans
- **Display**: Shows toggle switch with "Yes"/"No" labels
- **Example**: See PATTERNS.md for detailed switch field usage

## Migration System

### Overview

IceHrm uses a migration system to manage database schema changes and data updates. Migrations are automatically executed when the application loads, ensuring database consistency across deployments.

### Migration File Structure

#### File Naming Convention
- **Location**: `core/migrations/`
- **Format**: `v{YYYYMMDD}_{HHmmss}_{name_with_underscores}.php`
  - `v` - Prefix
  - `{YYYYMMDD}` - Current date (e.g., `20250323`)
  - `{HHmmss}` - Current time in 24-hour format (e.g., `101615`)
  - `{name_with_underscores}` - Descriptive name with spaces replaced by underscores
- **Example**: `v20250323_101615_add_punch_in_out_limits.php`

#### Class Structure

**Pattern 1: Using Array and Loop (Recommended for multiple queries)**
```php
<?php
namespace Classes\Migration;

class v20250323_101615_add_punch_in_out_limits extends AbstractMigration {
    
    public function up() {
        $sql = [];
        
        // Add SQL queries to array
        $sql[] = <<<'SQL'
CREATE TABLE example_table (
    id bigint auto_increment PRIMARY KEY,
    name varchar(100) NOT NULL
) charset = utf8mb3;
SQL;
        
        $sql[] = <<<'SQL'
ALTER TABLE other_table ADD COLUMN new_field VARCHAR(100) NULL;
SQL;
        
        // Execute each query separately
        $result = true;
        foreach ($sql as $query) {
            $result = $result && $this->executeQuery($query);
        }
        
        return $result;
    }
    
    public function down() {
        // For now, always return true
        return true;
    }
}
```

**Pattern 2: Sequential Execution (For simple migrations)**
```php
<?php
namespace Classes\Migration;

class v20241026_340015_file_object extends AbstractMigration {
    
    public function up() {
        $sql = <<<'SQL'
ALTER TABLE Files ADD COLUMN `object_type` varchar(50) NULL;
SQL;
        $this->executeQuery($sql);
        
        $sql = <<<'SQL'
ALTER TABLE Files ADD COLUMN `object_id` bigint(20) NULL;
SQL;
        $this->executeQuery($sql);
        
        return true;
    }
    
    public function down() {
        return true;
    }
}
```

### Migration Execution

#### Key Rules
1. **Multiple Queries**: A migration can contain multiple SQL queries
2. **Separate Execution**: Each query must be executed separately via `$this->executeQuery($query)`
3. **Do NOT combine**: Never pass multiple SQL statements in a single `executeQuery()` call
4. **Return Value**: `up()` returns `true` on success, `false` on failure
5. **Down Method**: Currently, `down()` should always return `true`

#### executeQuery() Method
- **Location**: `AbstractMigration::executeQuery($sql)`
- **Usage**: `$this->executeQuery($sql)` where `$sql` is a single SQL statement
- **Returns**: `true` on success, `false` on error
- **Error Handling**: Errors are logged and stored in `Migration.last_error`

### Migration Registration

#### Adding to list.php
After creating a migration file, it must be registered in `core/migrations/list.php`:

1. Open `core/migrations/list.php`
2. Add migration filename (without `.php` extension) at the **top** of the `$migrationList` array
3. Format: `$migrationList[] = 'v20250323_101615_add_punch_in_out_limits';`

Example:
```php
<?php
$migrationList = [];
$migrationList[] = 'v20250323_101615_add_punch_in_out_limits'; // New migration
$migrationList[] = 'v20200828_270103_remove_country_leave';
$migrationList[] = 'v20241026_340016_update_employee_null_status';
// ... rest of migrations
```

**Important**: Migrations are executed in the order they appear in `list.php`, so new migrations must be added at the top.

### Migration Execution Flow

```
1. Application Load
   ↓
2. MigrationManager::ensureMigrations() called
   ↓
3. Check list.php for latest migration
   ↓
4. Compare with Migrations table
   ↓
5. If new migrations found:
   - Scan core/migrations/ directory
   - Create Migration table entries for new files
   - Set status to 'Pending'
   ↓
6. Run all pending migrations:
   - Load migration class
   - Call up() method
   - Execute each SQL query separately
   - Update status to 'Up' on success, 'UpError' on failure
```

### Migration Status Tracking

The `Migrations` table tracks migration execution:

```sql
CREATE TABLE Migrations (
    id          bigint auto_increment PRIMARY KEY,
    file        varchar(255) NOT NULL,
    version     bigint NULL,
    status      varchar(20) NULL,      -- 'Pending', 'Up', 'UpError', 'Down', 'DownError'
    created     datetime NULL,
    updated     datetime NULL,
    last_error  text NULL
);
```

**Status Values**:
- `'Pending'`: Migration file exists but not yet executed
- `'Up'`: Migration executed successfully
- `'UpError'`: Migration execution failed
- `'Down'`: Migration rolled back (not currently used)
- `'DownError'`: Rollback failed (not currently used)

### MigrationManager Class

`MigrationManager` (`core/src/Classes/Migration/MigrationManager.php`) handles migration execution:

#### Key Methods
- `ensureMigrations()`: Checks for new migrations and runs pending ones
- `queueMigrations()`: Scans directory and creates Migration table entries
- `runPendingMigrations()`: Executes all migrations with status `'Pending'`
- `runMigrationUp($migration)`: Executes a single migration's `up()` method
- `getMigrationObject($fileName)`: Loads and instantiates migration class

#### Automatic Execution
Migrations are automatically executed when:
- Application loads (via `BaseService::initialize()`)
- `MigrationManager::ensureMigrations()` is called
- New migration files are detected in `core/migrations/`

### Adding New Tabs to Settings Module

When adding a new category tab to the settings module:

1. **Add ModuleTab in PHP**: Add a new `ModuleTab` instance in `core/admin/settings/index.php`:
   ```php
   $moduleBuilder->addModuleOrGroup(new ModuleTab(
       'TabNameSetting',        // Tab identifier (e.g., 'FilesSetting')
       'Setting',                // Model class
       'Display Name',          // Tab label (e.g., 'Files')
       'SettingAdapter',        // Adapter class
       '{"category":"Category"}', // Filter JSON (e.g., '{"category":"Files"}')
       'name',                  // Order by field
       false,                   // Is active tab
       $options1                // Options array
   ));
   ```

2. **Update JavaScript Tab Names**: Add the new tab name to the `tabNames` array in the JavaScript initialization:
   ```javascript
   var tabNames = ['CompanySetting', 'SystemSetting', ..., 'NewTabSetting'];
   ```

3. **Category Filter**: The filter JSON determines which settings appear in the tab. Use:
   - Single category: `'{"category":"CategoryName"}'`
   - Multiple categories: `'{"category":["Category1","Category2"]}'`

4. **Tab Order**: Tabs appear in the order they are added to `ModuleBuilder`. Place new tabs logically.

### Common Migration Patterns

#### Pattern 1: Creating Tables
```php
$sql[] = <<<'SQL'
CREATE TABLE new_table (
    id bigint auto_increment PRIMARY KEY,
    name varchar(100) NOT NULL,
    created datetime NULL,
    updated datetime NULL
) charset = utf8mb3;
SQL;
```

#### Pattern 2: Altering Tables
```php
$sql[] = "ALTER TABLE existing_table ADD COLUMN new_field VARCHAR(100) NULL;";
$sql[] = "ALTER TABLE existing_table MODIFY COLUMN existing_field TEXT NULL;";
$sql[] = "ALTER TABLE existing_table DROP COLUMN old_field;";
```

#### Pattern 3: Adding Settings
```php
$metaData = json_encode(['value', [
    'label' => 'Value',
    'type' => 'select',
    'source' => [['1', 'Yes'], ['0', 'No']]
]]);

$sql[] = "REPLACE INTO Settings (`name`, `value`, `description`, `meta`, `category`) 
          VALUES (
              'System: New Setting',
              'default_value',
              'Setting description',
              '$metaData',
              'System'
          )";
```

#### Pattern 4: Data Migrations
```php
// Update existing records
$sql[] = "UPDATE table_name SET field = 'new_value' WHERE condition = 'value';";

// Insert default data
$sql[] = "INSERT INTO table_name (field1, field2) VALUES ('value1', 'value2');";

// Use REPLACE for idempotent inserts
$sql[] = "REPLACE INTO table_name (id, field) VALUES (1, 'value');";
```

#### Pattern 5: Updating Setting Categories
```php
// Update category for settings matching a pattern
$sql[] = "UPDATE Settings SET category = 'NewCategory' WHERE name LIKE 'Prefix:%';";

// Update category for a specific setting
$sql[] = "UPDATE Settings SET category = 'Category' WHERE name = 'Setting: Name';";

// Update multiple categories in one migration
$sql[] = "UPDATE Settings SET category = 'Files' WHERE name LIKE 'Files:%';";
$sql[] = "UPDATE Settings SET category = 'Attendance' WHERE name = 'Attendance: Specific Setting';";
```

#### Pattern 6: Updating Setting Orders and Descriptions
```php
// Update setting_order for multiple settings
$sql[] = "UPDATE Settings SET setting_order = 1 WHERE name = 'System: Allowed Countries';";
$sql[] = "UPDATE Settings SET setting_order = 2 WHERE name = 'System: Allowed Currencies';";
$sql[] = "UPDATE Settings SET setting_order = 3 WHERE name = 'System: Allowed Nationality';";

// Update descriptions
$sql[] = "UPDATE Settings SET description = 'New description text' WHERE name = 'System: Setting Name';";

// Rename a setting
$sql[] = "UPDATE Settings SET name = 'System: New Name' WHERE name = 'System: Old Name';";

// Delete settings
$sql[] = "DELETE FROM Settings WHERE name = 'System: Unused Setting';";
$sql[] = "DELETE FROM Settings WHERE name = 'Api: Deprecated Setting';";
```

### Best Practices

1. **One Change Per Migration**: Keep migrations focused on a single change or related set of changes
2. **Descriptive Names**: Use clear, descriptive names (e.g., `add_punch_in_out_limits`)
3. **Test Queries**: Test SQL queries manually before adding to migration
4. **Idempotent Operations**: Use `REPLACE INTO` or `INSERT IGNORE` when possible
5. **Error Handling**: Check `executeQuery()` return value and stop on failure
6. **Order Matters**: Migrations execute in `list.php` order - ensure dependencies are correct
7. **Always Add to list.php**: New migrations won't execute until added to `list.php`
8. **Single Query Per executeQuery()**: Never combine multiple SQL statements in one call
9. **Use Heredoc**: Use `<<<'SQL'` heredoc syntax for multi-line queries
10. **Version Control**: Commit migration files and `list.php` changes together

### Troubleshooting

#### Migration Not Executing
- **Check**: Is migration added to `list.php` at the top?
- **Check**: Does filename match class name exactly?
- **Check**: Is class namespace correct (`Classes\Migration`)?
- **Check**: Does class extend `AbstractMigration`?

#### Migration Failing
- **Check**: `Migrations.last_error` column for error details
- **Check**: Application logs (`app/data/icehrm.log`)
- **Verify**: SQL syntax is correct (test manually)
- **Verify**: Each query is executed separately

#### Migration Status Issues
- **Pending**: Migration file exists but not executed yet
- **UpError**: Check `last_error` field for details
- **Up**: Migration executed successfully

### Settings Module Frontend Features

#### Search Functionality
The settings module includes client-side search that filters settings by:
- **Setting name**: Case-insensitive partial matching
- **Description**: Case-insensitive partial matching
- **Real-time**: Filters as user types
- **No backend calls**: Works entirely on loaded data

#### Settings Display
- **Card Layout**: Each setting wrapped in Ant Design Card for visual separation
- **Description Display**: Shown as dimmed text below field (not in tooltips)
- **Ordering**: Settings ordered by `setting_order` column (0 values at bottom)
- **Hidden Settings**: Specific settings can be filtered out from display

#### Settings Ordering Implementation
- **Backend**: SQL ordering via `getOrderBy()` override in adapter
- **Frontend**: Client-side sorting as backup
- **Logic**: Non-zero values first (ascending), zero/NULL values last

## Common Issues and Solutions

### Issue: Table Not Loading
**Cause**: Container ID mismatch or dataPipe not initialized
**Solution**: 
- Check container ID matches adapter tab name
- Initialize dataPipe: `adapter.setDataPipe(new IceDataPipe(adapter))`
- Call `adapter.get([])` after initialization

### Issue: Settings Values Truncated
**Cause**: `postProcessGetData()` truncates values > 30 chars
**Solution**: Use `getElement()` to fetch full values, not `get()`

### Issue: Remote Data Not Loading
**Cause**: Field master data not loaded before component renders
**Solution**: Call `adapter.loadRemoteDataForSettings()` before rendering

### Issue: Switch Always On
**Cause**: Form value is string "1"/"0" but Switch expects boolean
**Solution**: Convert to boolean when setting form values:
```javascript
formValues[`setting_${id}`] = value === '1' || value === 1;
```

### Issue: Multi-select Values Duplicated
**Cause**: Values being JSON.stringify'd multiple times
**Solution**: Parse JSON string before setting form value, clean duplicates

## Best Practices

1. **Always initialize dataPipe** before using it
2. **Set adapter filter** before calling dataPipe.get()
3. **Handle value type conversion** (string ↔ boolean, JSON ↔ array)
4. **Fetch full values** when displaying in forms (use getElement)
5. **Clean remote data** before using (remove duplicates, filter invalid)
6. **Validate permissions** in REST endpoints
7. **Handle errors gracefully** with user-friendly messages
8. **Use appropriate HTTP status codes** in REST responses
9. **Follow existing patterns** when creating new components
10. **Test with different user levels** (Admin, Manager, Employee)

## Common Model Namespaces

When referencing models from other modules, use the correct namespaces:

```php
// Job/Position related
use Jobs\Common\Model\JobTitle;       // NOT Job\Common\Model\Job

// Custom fields / Metadata
use Metadata\Common\Model\CustomFieldValue;    // NOT FieldNames\Common\Model\CustomFieldValue
use Metadata\Common\Model\FieldNameMapping;

// Geography
use Employees\Common\Model\Nationality;
use Employees\Common\Model\Province;

// Compensation
use Employees\Common\Model\PayGrade;

// Employees
use Employees\Common\Model\Employee;
```

## Dynamic Field Labels (FieldNameMappings)

The `FieldNameMappings` table stores custom field labels that can be configured per installation:

### Table Structure
```sql
CREATE TABLE FieldNameMappings (
    id bigint auto_increment PRIMARY KEY,
    type varchar(50) NOT NULL,        -- Entity type (e.g., 'Employee')
    name varchar(100) NOT NULL,        -- Field name (e.g., 'first_name')
    textMapped varchar(200) NULL,     -- Custom label (e.g., 'First Name')
    display varchar(50) NULL          -- Display context ('Form', 'Table')
);
```

### Usage Pattern

```php
use Metadata\Common\Model\FieldNameMapping;

// Load all field mappings for Employee forms
$fieldMapping = new FieldNameMapping();
$mappings = $fieldMapping->Find("type = ? AND display = ?", ['Employee', 'Form']);

// Build a lookup map
$labelMap = [];
foreach ($mappings as $mapping) {
    $labelMap[$mapping->name] = $mapping->textMapped;
}

// Use in UI
$label = $labelMap['first_name'] ?? 'First Name';  // Fallback to default
```

### Frontend API Pattern

```php
// ApiController.php - endpoint to fetch field mappings
self::register(
    REST_API_PATH . 'module/employee-field-mappings', self::GET,
    function () {
        $fieldMapping = new FieldNameMapping();
        $mappings = $fieldMapping->Find("type = ? AND display = ?", ['Employee', 'Form']);

        $result = [];
        foreach ($mappings as $mapping) {
            $result[$mapping->name] = $mapping->textMapped;
        }

        (new RestEndPoint())->sendResponse(new IceResponse(IceResponse::SUCCESS, $result));
    }
);
```

## Protecting Records from Deletion

Before deleting a record that may be referenced as a foreign key, check for usage:

### Backend Pattern

```php
// In ApiController.php or RestEndPoint
public function deleteTemplate($id) {
    $restEndpoint = new RestEndPoint();

    // Check if template is in use
    $payroll = new Payroll();
    $payrollsUsingTemplate = $payroll->Find("payslipTemplate = ?", [$id]);

    if (!empty($payrollsUsingTemplate)) {
        $names = array_map(function($p) { return $p->name; }, $payrollsUsingTemplate);
        $restEndpoint->sendResponse(new IceResponse(
            IceResponse::ERROR,
            'Cannot delete template. It is used by payroll(s): ' . implode(', ', $names)
        ));
        return;
    }

    // Safe to delete
    $template = new PayslipTemplate();
    $template->Load("id = ?", [$id]);
    $template->Delete();

    $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, ['deleted' => true]));
}
```

### Frontend Handling

```javascript
const handleDelete = async (id) => {
  try {
    const response = await getApiClient().delete(`module/template/${id}`);

    // Check for error in response body (HTTP 200 but logical error)
    if (response.data?.error) {
      const errorMsg = response.data.error[0]?.[0]?.message || 'Failed to delete';
      message.error(errorMsg, 5);  // Show for 5 seconds
      return;
    }

    message.success('Deleted successfully');
    refreshList();
  } catch (error) {
    const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to delete';
    message.error(errorMsg, 5);
  }
};
```

## Legacy Data Handling

When introducing new data formats while supporting legacy records:

### Detection Pattern

```javascript
// Check if record uses legacy format
const isLegacyRecord = (record) => {
  // Legacy records have 'data' column but no 'design' column
  return record.data && !record.design;
};

// In table columns
{
  title: 'Name',
  dataIndex: 'name',
  render: (text, record) => (
    <Space>
      <span>{text}</span>
      {isLegacyRecord(record) && (
        <Tag color="warning" icon={<WarningOutlined />}>Legacy</Tag>
      )}
    </Space>
  ),
}
```

### Conditional Actions

```javascript
// Only show Edit/Preview for new format records
const columns = [
  {
    title: 'Actions',
    render: (_, record) => {
      const isLegacy = isLegacyRecord(record);
      return (
        <Space>
          {!isLegacy && (
            <>
              <Button onClick={() => handlePreview(record)}>Preview</Button>
              <Button onClick={() => handleEdit(record)}>Edit</Button>
            </>
          )}
          {/* Delete is always available */}
          <Popconfirm title="Delete?" onConfirm={() => handleDelete(record.id)}>
            <Button danger>Delete</Button>
          </Popconfirm>
        </Space>
      );
    },
  },
];
```

### API Support for Legacy

```php
// Include legacy field in API response for detection
public function getTemplates() {
    $template = new PayslipTemplate();
    $templates = $template->Find("1=1", []);

    $result = [];
    foreach ($templates as $t) {
        $result[] = [
            'id' => $t->id,
            'name' => $t->name,
            'design' => $t->design,
            'data' => $t->data,  // Include for legacy detection
            'status' => $t->status,
        ];
    }

    return $result;
}
```
