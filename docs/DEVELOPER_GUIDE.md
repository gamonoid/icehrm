# IceHrm Pro - Developer Guide

## Project Overview

IceHrm Pro is a comprehensive Human Resource Management System built with PHP (backend) and React (frontend). The system follows a modular architecture with clear separation between admin and user interfaces.

## Project Structure

```
icehrm-pro/
├── app/                    # Client-side application entry point
│   ├── index.php          # Main entry point for all requests
│   ├── config.php         # Configuration loader
│   ├── config-dev.php     # Development configuration
│   └── data/              # Data storage and logs
├── core/                   # Core application logic
│   ├── src/               # Source code (open source)
│   ├── admin/             # Admin interface modules
│   ├── modules/           # User interface modules
│   ├── modules.php        # Module initialization
│   ├── service.php        # API service handler
│   └── server.includes.inc.php # Server-side includes
├── web/                    # Frontend React application
│   ├── components/         # Reusable React components
│   ├── admin/             # Admin interface components
│   └── modules/           # User interface components
└── extensions/             # Custom extensions
```

## Request Flow Architecture

### 1. Entry Point (`app/index.php`)

All requests first land in `app/index.php`, which acts as the main router:

```php
// Request format: ?g=group&n=name
$group = $_REQUEST['g'];  // 'admin', 'modules', or 'extension'
$name = $_REQUEST['n'];    // Module name

// Routes to appropriate module
if ($group == 'admin' || $group == 'modules') {
    include APP_BASE_PATH.'/'.$group.'/'.$name.'/index.php';
} else if ($group == 'extension') {
    include APP_BASE_PATH.'extensions/wrapper.php';
}
```

### 2. Module Routing

Each module has its own `index.php` that:
- Sets the module path
- Includes core files
- Initializes the module manager
- Routes to appropriate action managers

### 3. Core Initialization (`core/include.common.php`)

- Sets up autoloading via Composer
- Initializes the IceContainer (dependency injection)
- Sets up language management
- Cleans input parameters

### 4. Module Management (`core/modules.php`)

- Dynamically loads module managers
- Creates permissions for each module
- Initializes module action managers

### 5. Service Layer (`core/service.php`)

Handles API requests with actions like:
- `get` - Retrieve data
- `add` - Create new records
- `update` - Modify existing records
- `delete` - Remove records
- Custom module actions

## Core Architecture Components

### 1. BaseService (`core/src/Classes/BaseService.php`)

The central service class that provides:
- Database operations
- User management
- Module management
- File handling
- Caching services

**Key Methods:**
- `get()` - Retrieve data with filtering and mapping
- `addElement()` - Create new records
- `updateElement()` - Update existing records
- `deleteElement()` - Remove records

### 2. SubActionManager (`core/src/Classes/SubActionManager.php`)

Base class for all action managers that provides:
- User context management
- Base service access
- Email template handling
- Profile management

### 3. AbstractModuleManager (`core/src/Classes/AbstractModuleManager.php`)

Base class for module managers that defines:
- User class initialization
- File field mappings
- Database error mappings
- Model class definitions

### 4. BaseModel (`core/src/Model/BaseModel.php`)

Extends `MySqlActiveRecord` and provides:
- Role-based access control
- Module access management
- Custom field support
- Audit trail capabilities

## Module Structure

Each module follows a consistent structure:

```
ModuleName/
├── Admin/                  # Admin interface
│   ├── Api/               # Admin API endpoints
│   │   └── ModuleNameActionManager.php
│   └── ModuleNameManager.php
├── User/                   # User interface
│   ├── Api/               # User API endpoints
│   │   └── ModuleNameActionManager.php
│   └── ModuleNameManager.php
├── Common/                 # Shared components
│   └── Model/             # Data models
└── meta.json              # Module metadata
```

### Example: Attendance Module

```php
// AttendanceModulesManager.php
class AttendanceModulesManager extends AbstractModuleManager
{
    public function initializeUserClasses()
    {
        if (defined('MODULE_TYPE') && MODULE_TYPE != 'admin') {
            $this->addUserClass("Attendance");
        }
    }
    
    public function setupModuleClassDefinitions()
    {
        // Define models used by this module
    }
}

// AttendanceActionManager.php
class AttendanceActionManager extends SubActionManager
{
    public function punchIn($req)
    {
        // Handle punch-in logic
    }
    
    public function punchOut($req)
    {
        // Handle punch-out logic
    }
}
```

## Frontend Architecture

### React Components

The frontend uses React with Ant Design components:

**Core Components:**
- `IceForm` - Form handling with validation
- `IceTable` - Data table with sorting/filtering
- `IceUpload` - File upload handling
- `IceSelect` - Dropdown selection

**Key Dependencies:**
- React 16.13.1
- Ant Design 5.24.3
- Axios for API calls
- Moment.js for date handling

### API Communication

Frontend components communicate with backend via:
- RESTful API endpoints
- JSON request/response format
- Session-based authentication
- Role-based access control

## Database Architecture

### ORM Layer

Uses custom `MySqlActiveRecord` implementation:
- Active Record pattern
- Automatic table mapping
- Relationship handling
- Query building

### Key Models

- **User** - Authentication and user management
- **Employee** - Employee information
- **Module** - Module definitions and permissions
- **Permission** - Role-based access control

## Security Features

### 1. Input Sanitization
- Domain-aware input cleaning
- SQL injection prevention
- XSS protection

### 2. Authentication
- Session-based authentication
- JWT token support
- Role-based access control

### 3. Authorization
- Module-level permissions
- Record-level access control
- Subordinate data access

## Extension System

### Creating Extensions

Extensions can be added in the `extensions/` directory:

```
extensions/
└── custom_module/
    ├── web/               # Frontend components
    ├── src/               # Backend logic
    └── meta.json          # Extension metadata
```

#### Creating Admin Extensions

Use the IceHrm CLI tool to create extensions:

```bash
# Create an admin extension
php ice create:extension sample admin

# Refresh IceHrm to see the new menu item
```

The extension code will be created under `extensions/sample/admin/`.

For more details, refer to: [https://icehrm.com/explore/docs/extensions/](https://icehrm.com/explore/docs/extensions/)

### Extension Types

- **Admin Extensions** - Extend admin functionality
- **User Extensions** - Extend user functionality
- **API Extensions** - Add new API endpoints

## Development Workflow

### 1. Setting Up Development Environment

#### Using Docker (Recommended)

```bash
# Clone the repository
git clone https://github.com/gamonoid/icehrm.git
cd icehrm

# Start development environment
docker-compose up -d
```

- Visit [http://localhost:9080/](http://localhost:9080/) and login using `admin` as username and password
- Watch detailed instructions: [https://www.youtube.com/watch?v=sz8OV_ON6S8](https://www.youtube.com/watch?v=sz8OV_ON6S8)

#### Manual Setup

```bash
# Clone the repository
git clone https://github.com/gamonoid/icehrm.git
cd icehrm

# Install frontend dependencies
cd web
npm install
cd ..
npm install

# Install PHP dependencies
cd core
composer install
```

### 2. Configuration

Copy and modify configuration files:
- `app/config-dev.php` - Development settings
- Database connection settings
- File upload configurations

### 3. Module Development

1. **Create Module Structure:**
   - Add module directory under `core/src/`
   - Create Admin and User subdirectories
   - Implement ModuleManager and ActionManager classes

2. **Define Models:**
   - Extend BaseModel
   - Implement access control methods
   - Define table relationships

3. **Create Frontend Components:**
   - React components in `web/` directory
   - API integration
   - Form handling

### 4. Frontend Asset Building

When you make changes to JavaScript or CSS files in `web/`, you need to rebuild the frontend:

```bash
# Install dependencies (first time only)
cd web
npm install
cd ..
npm install

# Build assets during development
gulp clean
gulp

# Build assets for production
gulp clean
gulp --eprod

# Build extensions
gulp ejs --xextension_name/admin
```

### 5. Testing

- Unit tests in `test/unit/`
- Integration tests in `test/integration/`
- Use PHPUnit for PHP testing

#### Running Tests (Docker)

```bash
# Run e2e (cypress) tests
docker-compose -f docker-compose-testing.yaml up --exit-code-from cypress

# Or with rebuild
docker-compose -f docker-compose-testing.yaml up --exit-code-from cypress --build --force-recreate
```

### 6. Debugging

#### Using Psysh Console

You can debug code interactively using psysh inside the Docker container:

```bash
# Start container and access shell
docker compose up -d
docker exec -it icehrm-icehrm-1 /bin/sh

# Start psysh console
./psysh -c ./.config/psysh/config.php
```

Example debugging session:
```php
$emp = new \Employees\Common\Model\Employee();
$emp->Load('id = ?',[1]);
var_dump($emp);
```

### 7. Production Deployment

When ready to push changes to production:

```bash
# Build production Docker images
docker-compose -f docker-compose-prod.yaml up -d --build
```

## Key Design Patterns

### 1. Singleton Pattern
- BaseService uses singleton for global access
- SettingsManager for configuration management

### 2. Factory Pattern
- ModuleManager creation
- ActionManager instantiation

### 3. Observer Pattern
- Notification system
- Event handling

### 4. Strategy Pattern
- Different access control strategies
- Multiple authentication methods

## Performance Considerations

### 1. Caching
- Redis cache support
- Memory cache fallback
- Query result caching

### 2. Database Optimization
- Indexed queries
- Relationship optimization
- Lazy loading

### 3. Frontend Optimization
- Component lazy loading
- Bundle splitting
- Image optimization

## Troubleshooting

### Common Issues

1. **Module Not Loading:**
   - Check module metadata
   - Verify file permissions
   - Check database module records

2. **Permission Errors:**
   - Verify user roles
   - Check module permissions
   - Validate access control methods

3. **Database Connection:**
   - Verify connection settings
   - Check database server status
   - Validate credentials

### Debug Tools

- Error logging in `app/data/icehrm.log`
- PHP error reporting
- Browser developer tools
- Database query logging

## Best Practices

### 1. Code Organization
- Follow PSR-4 autoloading standards
- Use namespaces consistently
- Implement proper error handling

### 2. Security
- Always validate user input
- Implement proper access control
- Use prepared statements for queries

### 3. Performance
- Minimize database queries
- Implement proper caching
- Optimize frontend bundle size

### 4. Maintainability
- Write clear documentation
- Use consistent naming conventions
- Implement proper logging

## Conclusion

IceHrm Pro provides a robust, modular architecture for building HR management systems. Understanding the request flow, module structure, and core components is essential for effective development and customization.

For additional information, refer to:
- API documentation in `docs/openapi/`
- Postman collections in `docs/postman-collection/`
- Unit tests for implementation examples
- Existing modules for reference implementations
