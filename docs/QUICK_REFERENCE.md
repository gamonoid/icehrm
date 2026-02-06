# IceHrm Pro - Quick Reference Guide

## Request Flow Summary

```
HTTP Request → app/index.php → Module Router → Module index.php → Core Files → Action Manager → Response
```

## Key Files & Their Purpose

### Entry Points
- **`app/index.php`** - Main router, handles all incoming requests
- **`app/config.php`** - Configuration loader
- **`app/config-dev.php`** - Development configuration

### Core Files
- **`core/include.common.php`** - Autoloading, container setup, input cleaning
- **`core/modules.php`** - Module initialization and permission setup
- **`core/service.php`** - API service handler (get, add, update, delete)
- **`core/server.includes.inc.php`** - Database connection, service initialization

### Core Classes
- **`BaseService`** - Central service class, database operations
- **`SubActionManager`** - Base class for all action managers
- **`AbstractModuleManager`** - Base class for module managers
- **`BaseModel`** - Base model class with access control

## URL Structure

```
?g=group&n=name

Examples:
?g=admin&n=employees     → Admin/employees/index.php
?g=modules&n=attendance  → Modules/attendance/index.php
?g=extension&n=custom    → Extensions/custom/web/index.php
```

## Module Structure Template

```
ModuleName/
├── Admin/
│   ├── Api/
│   │   └── ModuleNameActionManager.php  # Admin API endpoints
│   └── ModuleNameManager.php             # Admin module manager
├── User/
│   ├── Api/
│   │   └── ModuleNameActionManager.php  # User API endpoints
│   └── ModuleNameManager.php             # User module manager
├── Common/
│   └── Model/
│       └── ModuleName.php               # Data model
└── meta.json                            # Module metadata
```

## Common API Actions

### Standard Actions (handled by service.php)
- `a=get` - Retrieve data
- `a=add` - Create record
- `a=update` - Update record
- `a=delete` - Delete record
- `a=getElement` - Get single record

### Custom Actions (handled by ActionManager)
- Define custom methods in your ActionManager class
- Access via `a=methodName` parameter

## Database Operations

### Using BaseService
```php
$baseService = BaseService::getInstance();

// Get data
$data = $baseService->get('TableName', $mapping, $filters, $orderBy);

// Add record
$response = $baseService->addElement('TableName', $data);

// Update record
$response = $baseService->updateElement('TableName', $id, $data);

// Delete record
$response = $baseService->deleteElement('TableName', $id);
```

### Using Models
```php
$model = new ModelName();
$model->Load('id = ?', [$id]);
$model->field = 'value';
$model->Save();
```

## Access Control Methods

### In Models
```php
public function getAdminAccess() {
    return array('get', 'element', 'save', 'delete');
}

public function getManagerAccess() {
    return array('get', 'element', 'save');
}

public function getUserAccess() {
    return array('get');
}

public function getUserOnlyMeAccess() {
    return array('element', 'save', 'delete');
}
```

## Frontend Integration

### React Components
- **`IceForm`** - Form handling with validation
- **`IceTable`** - Data tables
- **`IceUpload`** - File uploads
- **`IceSelect`** - Dropdowns

### API Calls
```javascript
// Example API call
const response = await axios.post('/service.php', {
    a: 'customAction',
    // other parameters
});
```

## Quick Development Checklist

### Creating a New Module
1. ✅ Create module directory structure
2. ✅ Implement ModuleManager class
3. ✅ Implement ActionManager class
4. ✅ Create data models
5. ✅ Add module to database
6. ✅ Set up permissions
7. ✅ Create frontend components

### Debugging
1. ✅ Check `app/data/icehrm.log`
2. ✅ Verify module is loaded in `core/modules.php`
3. ✅ Check database module records
4. ✅ Validate file permissions
5. ✅ Check user roles and permissions

## Common Issues & Solutions

| Issue | Check | Solution |
|-------|-------|----------|
| Module not loading | `core/modules.php` | Verify module metadata and class existence |
| Permission denied | User roles, module permissions | Check access control methods in models |
| Database errors | Connection settings | Verify `config-dev.php` database settings |
| Frontend not working | JavaScript console | Check API endpoints and authentication |

## Environment Variables

```bash
# Set custom config file
export ICE_CONFIG_FILE=config-prod.php

# Default fallback
# config-dev.php
```

## File Locations

- **Logs**: `app/data/icehrm.log`
- **Uploads**: `app/data/`
- **Cache**: Redis or memory (configurable)
- **Templates**: `core/templates/`
- **Languages**: `core/lang/`
- **Migrations**: `core/migrations/`
