# PHP Active Record
Simple PHP library to access databases using active record pattern.

## Installation
```bash
composer require icehrm/php-active-record
```

## Usage
### Create a model
- Let's assume you have this example table (taken from [IceHrm](https://github.com/gamonoid/icehrm))
```sql
create table `Files` (
 `id` bigint(20) NOT NULL AUTO_INCREMENT,
 `name` varchar(100) NOT NULL,
 `filename` varchar(100) NOT NULL,
 `employee` bigint(20) NULL,
 `file_group` varchar(100) NOT NULL,
 `size` bigint(20) NULL,
 `size_text` varchar(20) NULL,
 primary key  (`id`),
 unique key `filename` (`filename`)
) engine=innodb default charset=utf8;
```

- Create a model class
```php
<?php
use MyORM\MySqlActiveRecord;

class File extends MySqlActiveRecord
{
    public $table = 'Files';
}
```

- Create a connection
```php
$connection = new \MyORM\MySqlActiveRecord();
// Host, Username, Password, Database
$res = $connection->Connect('127.0.0.1:10012', 'root', 'root', 'phpactiverecord');
```

- DB operations
```php
// Save a file
$file = new File();
$file->name = 'Test';
$file->filename = 'Test';
$file->file_group = 'Test';
$file->Save();

// Load saved file
$file = new File();
$file->Load('name = ?', ['Test']);

// Find all files
$files = $file->Find();

// Find first 5 files
$files = $file->Find('1=1 LIMIT 5');

// Find all files having name 'Test'
$files = $file->Find('name = ?', ['Test1']);

// Update file
$file = new File();
$file->Load('name = ?', ['Test']);
$file->file_group = 'New Group';
$file->Save();

// Delete file
$file = new File();
$file->Load('name = ?', ['Test']);
$file->Delete();

// Run row query
$file = new File();
$file->DB()->execute('DELETE FROM Files');

```


## Run tests

### Setup
```bash
wget -O phpunit https://phar.phpunit.de/phpunit-9.phar
chmod +x phpunit
```

```bash
rm -rf ./test/db_data
docker compose up -d
./phpunit --configuration phpunit.xml
docker compose down
```