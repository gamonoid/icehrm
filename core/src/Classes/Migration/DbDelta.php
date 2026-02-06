<?php
/**
 * DbDelta - Database Schema Synchronization
 *
 * Compares provided DDL with existing database schema and generates
 * ALTER statements to sync the database. Inspired by WordPress dbDelta.
 *
 * Safety: This class NEVER deletes tables, columns, or indexes.
 * It only adds missing elements or modifies existing column types.
 */

namespace Classes\Migration;

use Classes\BaseService;
use Utils\LogManager;

class DbDelta
{
    const AUDIT_TYPE = 'Database';

    /** @var mixed */
    private $db;

    /** @var \mysqli */
    private $mysqli;

    /** @var array */
    private $messages = array();

    /** @var array */
    private $errors = array();

    /** @var bool */
    private $auditEnabled = true;

    public function __construct()
    {
        $this->db = BaseService::getInstance()->getDB();
        // Get the raw mysqli connection from the ORM
        $this->mysqli = \MyORM\MySqlActiveRecord::$connection;
    }

    /**
     * Enable or disable audit logging
     *
     * @param bool $enabled
     * @return self
     */
    public function setAuditEnabled($enabled)
    {
        $this->auditEnabled = $enabled;
        return $this;
    }

    /**
     * Get all messages from the last operation
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Get all errors from the last operation
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Execute schema synchronization from DDL statements
     *
     * @param string $ddl One or more CREATE TABLE statements
     * @param bool $execute Whether to execute the queries or just return them
     * @return array Array of ALTER/CREATE statements that were (or would be) executed
     */
    public function delta($ddl, $execute = true)
    {
        $this->messages = array();
        $this->errors = array();
        $queries = array();

        // Split DDL into individual CREATE TABLE statements
        $tables = $this->parseDDL($ddl);

        foreach ($tables as $tableName => $tableDefinition) {
            $tableQueries = $this->processTable($tableName, $tableDefinition, $execute);
            $queries = array_merge($queries, $tableQueries);
        }

        return $queries;
    }

    /**
     * Parse DDL string into individual table definitions
     *
     * @param string $ddl
     * @return array
     */
    private function parseDDL($ddl)
    {
        $tables = array();

        // Normalize the DDL: replace newlines and multiple spaces with single space
        $normalizedDdl = preg_replace('/\s+/', ' ', $ddl);

        // Find each CREATE TABLE statement and extract table name and body
        $pattern = '/create\s+table\s+(?:if\s+not\s+exists\s+)?[`]?(\w+)[`]?\s*\(/i';

        if (preg_match_all($pattern, $normalizedDdl, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $index => $match) {
                $tableName = $matches[1][$index][0];
                $startPos = $match[1] + strlen($match[0]);

                // Find the matching closing parenthesis by counting depth
                $tableBody = $this->extractBalancedParenContent($normalizedDdl, $startPos);

                if ($tableBody !== null) {
                    $tables[$tableName] = $this->parseTableBody($tableBody);
                }
            }
        }

        return $tables;
    }

    /**
     * Extract content between balanced parentheses starting at given position
     *
     * @param string $str
     * @param int $startPos
     * @return string|null
     */
    private function extractBalancedParenContent($str, $startPos)
    {
        $depth = 1;
        $content = '';
        $len = strlen($str);

        for ($i = $startPos; $i < $len && $depth > 0; $i++) {
            $char = $str[$i];
            if ($char === '(') {
                $depth++;
                $content .= $char;
            } elseif ($char === ')') {
                $depth--;
                if ($depth > 0) {
                    $content .= $char;
                }
            } else {
                $content .= $char;
            }
        }

        return $depth === 0 ? $content : null;
    }

    /**
     * Parse table body to extract columns, indexes, and constraints
     *
     * @param string $body
     * @return array
     */
    private function parseTableBody($body)
    {
        $columns = array();
        $indexes = array();
        $constraints = array();
        $primaryKey = null;

        // Split by comma, but respect parentheses
        $parts = $this->splitByComma($body);

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) {
                continue;
            }

            // Check for PRIMARY KEY
            if (preg_match('/^\s*primary\s+key\s*\(([^)]+)\)/i', $part, $m)) {
                $primaryKey = $this->parseColumnList($m[1]);
                continue;
            }

            // Check for UNIQUE constraint
            if (preg_match('/^\s*(?:constraint\s+[`]?(\w+)[`]?\s+)?unique\s+(?:key\s+|index\s+)?[`]?(\w+)?[`]?\s*\(([^)]+)\)/i', $part, $m)) {
                $indexName = !empty($m[2]) ? $m[2] : (!empty($m[1]) ? $m[1] : 'unique_' . count($indexes));
                $indexes[$indexName] = array(
                    'type' => 'UNIQUE',
                    'columns' => $this->parseColumnList($m[3]),
                );
                continue;
            }

            // Check for INDEX/KEY
            if (preg_match('/^\s*(?:key|index)\s+[`]?(\w+)[`]?\s*\(([^)]+)\)/i', $part, $m)) {
                $indexes[$m[1]] = array(
                    'type' => 'INDEX',
                    'columns' => $this->parseColumnList($m[2]),
                );
                continue;
            }

            // Check for FOREIGN KEY constraint
            if (preg_match('/^\s*constraint\s+[`]?(\w+)[`]?\s+foreign\s+key\s*\(([^)]+)\)\s+references\s+[`]?(\w+)[`]?\s*\(([^)]+)\)/i', $part, $m)) {
                $constraints[$m[1]] = array(
                    'type' => 'FOREIGN KEY',
                    'columns' => $this->parseColumnList($m[2]),
                    'references_table' => $m[3],
                    'references_columns' => $this->parseColumnList($m[4]),
                    'full_definition' => $part,
                );
                continue;
            }

            // Must be a column definition
            if (preg_match('/^[`]?(\w+)[`]?\s+(.+)$/i', $part, $m)) {
                $columnName = $m[1];
                $columnDef = trim($m[2]);

                // Skip if this looks like a constraint keyword
                if (in_array(strtolower($columnName), array('primary', 'unique', 'key', 'index', 'constraint', 'foreign'))) {
                    continue;
                }

                $columns[$columnName] = $this->parseColumnDefinition($columnDef);
            }
        }

        return array(
            'columns' => $columns,
            'indexes' => $indexes,
            'constraints' => $constraints,
            'primary_key' => $primaryKey,
        );
    }

    /**
     * Split string by comma, respecting parentheses
     *
     * @param string $str
     * @return array
     */
    private function splitByComma($str)
    {
        $parts = array();
        $current = '';
        $depth = 0;

        for ($i = 0; $i < strlen($str); $i++) {
            $char = $str[$i];

            if ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
            } elseif ($char === ',' && $depth === 0) {
                $parts[] = trim($current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if (!empty(trim($current))) {
            $parts[] = trim($current);
        }

        return $parts;
    }

    /**
     * Parse column list like "col1, col2"
     *
     * @param string $list
     * @return array
     */
    private function parseColumnList($list)
    {
        $columns = array();
        $parts = explode(',', $list);
        foreach ($parts as $part) {
            $part = trim($part, " \t\n\r\0\x0B`");
            if (!empty($part)) {
                $columns[] = $part;
            }
        }
        return $columns;
    }

    /**
     * Parse column definition to extract type, nullable, default, etc.
     *
     * @param string $def
     * @return array
     */
    private function parseColumnDefinition($def)
    {
        $result = array(
            'type' => '',
            'nullable' => true,
            'default' => null,
            'has_default' => false,
            'auto_increment' => false,
            'full_definition' => trim($def),
        );

        // Extract type - handle complex types like enum, decimal with balanced parens
        $result['type'] = $this->extractColumnType($def);

        // Check for NOT NULL
        if (preg_match('/\bnot\s+null\b/i', $def)) {
            $result['nullable'] = false;
        }

        // Check for NULL (explicit) - but not "not null"
        if (preg_match('/(?<!\bnot\s)\bnull\b/i', $def)) {
            $result['nullable'] = true;
        }

        // Check for DEFAULT
        if (preg_match('/\bdefault\s+(\'[^\']*\'|"[^"]*"|\w+)/i', $def, $m)) {
            $result['default'] = trim($m[1], "'\"");
            $result['has_default'] = true;
        }

        // Check for AUTO_INCREMENT
        if (preg_match('/\bauto_increment\b/i', $def)) {
            $result['auto_increment'] = true;
        }

        return $result;
    }

    /**
     * Extract column type handling balanced parentheses for enum, decimal, etc.
     *
     * @param string $def
     * @return string
     */
    private function extractColumnType($def)
    {
        $def = trim($def);

        // Match the type name first
        if (!preg_match('/^(\w+)/i', $def, $m)) {
            return '';
        }

        $typeName = strtolower($m[1]);
        $rest = substr($def, strlen($m[0]));

        // If no opening paren follows, return just the type name
        if (!preg_match('/^\s*\(/', $rest)) {
            return $typeName;
        }

        // Find the matching closing paren
        $rest = ltrim($rest);
        $depth = 0;
        $typeWithParams = $typeName;

        for ($i = 0; $i < strlen($rest); $i++) {
            $char = $rest[$i];
            $typeWithParams .= $char;

            if ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
                if ($depth === 0) {
                    break;
                }
            }
        }

        return strtolower($typeWithParams);
    }

    /**
     * Process a single table - compare and generate ALTER statements
     *
     * @param string $tableName
     * @param array $definition
     * @param bool $execute
     * @return array
     */
    private function processTable($tableName, $definition, $execute)
    {
        $queries = array();

        // Check if table exists
        if (!$this->tableExists($tableName)) {
            $query = $this->generateCreateTable($tableName, $definition);
            $queries[] = $query;
            $description = "DbDelta: Created table '$tableName'";
            $this->messages[] = "Creating table: $tableName";

            if ($execute) {
                $this->executeQuery($query, $description);
            }

            return $queries;
        }

        // Table exists, compare columns
        $existingColumns = $this->getExistingColumns($tableName);
        $existingIndexes = $this->getExistingIndexes($tableName);

        // Process columns
        foreach ($definition['columns'] as $columnName => $columnDef) {
            if (!isset($existingColumns[$columnName])) {
                // Column doesn't exist, add it
                $query = $this->generateAddColumn($tableName, $columnName, $columnDef);
                $queries[] = $query;
                $description = "DbDelta: Added column '$columnName' to table '$tableName'";
                $this->messages[] = "Adding column $columnName to $tableName";

                if ($execute) {
                    $this->executeQuery($query, $description);
                }
            } else {
                // Column exists, check if modification needed
                $modifyQuery = $this->generateModifyColumn($tableName, $columnName, $columnDef, $existingColumns[$columnName]);
                if ($modifyQuery) {
                    $queries[] = $modifyQuery;
                    $description = "DbDelta: Modified column '$columnName' in table '$tableName'";
                    $this->messages[] = "Modifying column $columnName in $tableName";

                    if ($execute) {
                        $this->executeQuery($modifyQuery, $description);
                    }
                }
            }
        }

        // Process indexes (but not drop existing ones)
        foreach ($definition['indexes'] as $indexName => $indexDef) {
            if (!isset($existingIndexes[$indexName])) {
                $query = $this->generateAddIndex($tableName, $indexName, $indexDef);
                $queries[] = $query;
                $description = "DbDelta: Added index '$indexName' to table '$tableName'";
                $this->messages[] = "Adding index $indexName to $tableName";

                if ($execute) {
                    $this->executeQuery($query, $description);
                }
            }
        }

        // Process constraints (foreign keys)
        $existingConstraints = $this->getExistingConstraints($tableName);
        foreach ($definition['constraints'] as $constraintName => $constraintDef) {
            if (!isset($existingConstraints[$constraintName])) {
                $query = $this->generateAddConstraint($tableName, $constraintName, $constraintDef);
                $queries[] = $query;
                $description = "DbDelta: Added constraint '$constraintName' to table '$tableName'";
                $this->messages[] = "Adding constraint $constraintName to $tableName";

                if ($execute) {
                    $this->executeQuery($query, $description);
                }
            }
        }

        return $queries;
    }

    /**
     * Check if a table exists
     *
     * @param string $tableName
     * @return bool
     */
    private function tableExists($tableName)
    {
        $escapedTable = $this->mysqli->real_escape_string($tableName);
        $result = $this->mysqli->query("SHOW TABLES LIKE '$escapedTable'");
        if (!$result) {
            return false;
        }
        $row = $result->fetch_row();
        $result->free();
        return !empty($row);
    }

    /**
     * Get existing columns for a table
     *
     * @param string $tableName
     * @return array
     */
    private function getExistingColumns($tableName)
    {
        $columns = array();
        // Table name cannot be parameterized, but we've already validated it exists
        $escapedTable = $this->mysqli->real_escape_string($tableName);
        $result = $this->mysqli->query("SHOW COLUMNS FROM `$escapedTable`");

        if (!$result) {
            return $columns;
        }

        while ($row = $result->fetch_assoc()) {
            $columns[$row['Field']] = array(
                'type' => strtolower($row['Type']),
                'nullable' => $row['Null'] === 'YES',
                'default' => $row['Default'],
                'has_default' => $row['Default'] !== null || $row['Null'] === 'YES',
                'auto_increment' => strpos($row['Extra'], 'auto_increment') !== false,
                'key' => $row['Key'],
            );
        }
        $result->free();

        return $columns;
    }

    /**
     * Get existing indexes for a table
     *
     * @param string $tableName
     * @return array
     */
    private function getExistingIndexes($tableName)
    {
        $indexes = array();
        $escapedTable = $this->mysqli->real_escape_string($tableName);
        $result = $this->mysqli->query("SHOW INDEX FROM `$escapedTable`");

        if (!$result) {
            return $indexes;
        }

        while ($row = $result->fetch_assoc()) {
            $indexName = $row['Key_name'];
            if (!isset($indexes[$indexName])) {
                $indexes[$indexName] = array(
                    'type' => $row['Non_unique'] == 0 ? 'UNIQUE' : 'INDEX',
                    'columns' => array(),
                );
            }
            $indexes[$indexName]['columns'][] = $row['Column_name'];
        }
        $result->free();

        return $indexes;
    }

    /**
     * Get existing foreign key constraints for a table
     *
     * @param string $tableName
     * @return array
     */
    private function getExistingConstraints($tableName)
    {
        $constraints = array();
        $escapedTable = $this->mysqli->real_escape_string($tableName);

        $sql = "SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = '$escapedTable'
                AND REFERENCED_TABLE_NAME IS NOT NULL";

        $result = $this->mysqli->query($sql);

        if (!$result) {
            return $constraints;
        }

        while ($row = $result->fetch_assoc()) {
            $constraintName = $row['CONSTRAINT_NAME'];
            if (!isset($constraints[$constraintName])) {
                $constraints[$constraintName] = array(
                    'type' => 'FOREIGN KEY',
                    'columns' => array(),
                    'references_table' => $row['REFERENCED_TABLE_NAME'],
                    'references_columns' => array(),
                );
            }
            $constraints[$constraintName]['columns'][] = $row['COLUMN_NAME'];
            $constraints[$constraintName]['references_columns'][] = $row['REFERENCED_COLUMN_NAME'];
        }
        $result->free();

        return $constraints;
    }

    /**
     * Generate CREATE TABLE statement
     *
     * @param string $tableName
     * @param array $definition
     * @return string
     */
    private function generateCreateTable($tableName, $definition)
    {
        $parts = array();

        // Columns
        foreach ($definition['columns'] as $columnName => $columnDef) {
            $parts[] = "`$columnName` " . $columnDef['full_definition'];
        }

        // Primary key
        if (!empty($definition['primary_key'])) {
            $cols = array_map(function ($c) {
                return "`$c`";
            }, $definition['primary_key']);
            $parts[] = "PRIMARY KEY (" . implode(', ', $cols) . ")";
        }

        // Unique indexes
        foreach ($definition['indexes'] as $indexName => $indexDef) {
            $cols = array_map(function ($c) {
                return "`$c`";
            }, $indexDef['columns']);
            if ($indexDef['type'] === 'UNIQUE') {
                $parts[] = "UNIQUE KEY `$indexName` (" . implode(', ', $cols) . ")";
            } else {
                $parts[] = "KEY `$indexName` (" . implode(', ', $cols) . ")";
            }
        }

        // Foreign key constraints
        foreach ($definition['constraints'] as $constraintName => $constraintDef) {
            $parts[] = "CONSTRAINT `$constraintName` " . $this->buildForeignKeyDefinition($constraintDef);
        }

        return "CREATE TABLE IF NOT EXISTS `$tableName` (\n  " . implode(",\n  ", $parts) . "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }

    /**
     * Build FOREIGN KEY definition string
     *
     * @param array $def
     * @return string
     */
    private function buildForeignKeyDefinition($def)
    {
        $cols = array_map(function ($c) {
            return "`$c`";
        }, $def['columns']);
        $refCols = array_map(function ($c) {
            return "`$c`";
        }, $def['references_columns']);

        $sql = "FOREIGN KEY (" . implode(', ', $cols) . ") ";
        $sql .= "REFERENCES `" . $def['references_table'] . "` (" . implode(', ', $refCols) . ")";

        // Extract ON DELETE/UPDATE from full definition if available
        if (isset($def['full_definition'])) {
            if (preg_match('/on\s+delete\s+(cascade|set\s+null|restrict|no\s+action)/i', $def['full_definition'], $m)) {
                $sql .= " ON DELETE " . strtoupper($m[1]);
            }
            if (preg_match('/on\s+update\s+(cascade|set\s+null|restrict|no\s+action)/i', $def['full_definition'], $m)) {
                $sql .= " ON UPDATE " . strtoupper($m[1]);
            }
        }

        return $sql;
    }

    /**
     * Generate ADD COLUMN statement
     *
     * @param string $tableName
     * @param string $columnName
     * @param array $columnDef
     * @return string
     */
    private function generateAddColumn($tableName, $columnName, $columnDef)
    {
        return "ALTER TABLE `$tableName` ADD COLUMN `$columnName` " . $columnDef['full_definition'];
    }

    /**
     * Generate MODIFY COLUMN statement if needed
     *
     * @param string $tableName
     * @param string $columnName
     * @param array $newDef
     * @param array $existingDef
     * @return string|null
     */
    private function generateModifyColumn($tableName, $columnName, $newDef, $existingDef)
    {
        // Never modify ID columns or primary keys
        if (strtolower($columnName) === 'id' || (isset($existingDef['key']) && $existingDef['key'] === 'PRI')) {
            return null;
        }

        // Compare types (normalize for comparison)
        $newType = $this->normalizeType($newDef['type']);
        $existingType = $this->normalizeType($existingDef['type']);

        // Check if modification is needed
        $needsModification = false;

        // Type changed
        if ($newType !== $existingType) {
            $needsModification = true;
        }

        // Nullable changed
        if ($newDef['nullable'] !== $existingDef['nullable']) {
            $needsModification = true;
        }

        if (!$needsModification) {
            return null;
        }

        return "ALTER TABLE `$tableName` MODIFY COLUMN `$columnName` " . $newDef['full_definition'];
    }

    /**
     * Normalize type string for comparison
     *
     * @param string $type
     * @return string
     */
    private function normalizeType($type)
    {
        $type = strtolower(trim($type));

        // Remove all spaces for consistent comparison
        $type = preg_replace('/\s+/', '', $type);

        // Normalize integer types (remove display width)
        $type = preg_replace('/int\(\d+\)/', 'int', $type);
        $type = preg_replace('/bigint\(\d+\)/', 'bigint', $type);
        $type = preg_replace('/tinyint\(\d+\)/', 'tinyint', $type);
        $type = preg_replace('/smallint\(\d+\)/', 'smallint', $type);

        // Remove display width from numeric types (deprecated in MySQL 8)
        $type = preg_replace('/(\w+)\(\d+\)(unsigned)/', '$1$2', $type);

        return $type;
    }

    /**
     * Generate ADD INDEX statement
     *
     * @param string $tableName
     * @param string $indexName
     * @param array $indexDef
     * @return string
     */
    private function generateAddIndex($tableName, $indexName, $indexDef)
    {
        $cols = array_map(function ($c) {
            return "`$c`";
        }, $indexDef['columns']);

        if ($indexDef['type'] === 'UNIQUE') {
            return "ALTER TABLE `$tableName` ADD UNIQUE KEY `$indexName` (" . implode(', ', $cols) . ")";
        }

        return "ALTER TABLE `$tableName` ADD INDEX `$indexName` (" . implode(', ', $cols) . ")";
    }

    /**
     * Generate ADD CONSTRAINT statement for foreign key
     *
     * @param string $tableName
     * @param string $constraintName
     * @param array $constraintDef
     * @return string
     */
    private function generateAddConstraint($tableName, $constraintName, $constraintDef)
    {
        return "ALTER TABLE `$tableName` ADD CONSTRAINT `$constraintName` " . $this->buildForeignKeyDefinition($constraintDef);
    }

    /**
     * Execute a query
     *
     * @param string $query
     * @param string $description
     * @return bool
     */
    private function executeQuery($query, $description = '')
    {
        try {
            $result = $this->mysqli->query($query);
            if (!$result) {
                $error = $this->mysqli->error;
                $this->errors[] = "Query failed: $query - Error: $error";
                LogManager::getInstance()->error("DbDelta query failed: $error - Query: $query");
                return false;
            }

            // Create audit log entry for successful schema changes
            if ($this->auditEnabled) {
                $this->createAuditLog($description ? $description : $query);
            }

            return true;
        } catch (\Throwable $e) {
            $this->errors[] = "Query failed: $query - Error: " . $e->getMessage();
            LogManager::getInstance()->error("DbDelta exception: " . $e->getMessage() . " - Query: $query");
            return false;
        }
    }

    /**
     * Create an audit log entry for a database schema change
     *
     * @param string $details
     * @return void
     */
    private function createAuditLog($details)
    {
        try {
            // Check if AuditLog table exists
            if (!$this->tableExists('AuditLog')) {
                return;
            }

            // Get current user ID (use 1 for system/migration user if not logged in)
            $userId = 1;
            try {
                $currentUser = BaseService::getInstance()->getCurrentUser();
                if ($currentUser && !empty($currentUser->id)) {
                    $userId = (int) $currentUser->id;
                }
            } catch (\Throwable $e) {
                // Use default user ID if we can't get current user
            }

            // Get client IP
            $ip = $this->mysqli->real_escape_string(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1');

            // Truncate and escape details
            if (strlen($details) > 65535) {
                $details = substr($details, 0, 65532) . '...';
            }
            $escapedDetails = $this->mysqli->real_escape_string($details);
            $type = self::AUDIT_TYPE;

            $sql = "INSERT INTO `AuditLog` (`time`, `user`, `ip`, `type`, `employee`, `details`)
                    VALUES (NOW(), $userId, '$ip', '$type', NULL, '$escapedDetails')";

            $this->mysqli->query($sql);
        } catch (\Throwable $e) {
            // Don't fail the migration if audit logging fails, just log the error
            LogManager::getInstance()->error("DbDelta audit log failed: " . $e->getMessage());
        }
    }
}
