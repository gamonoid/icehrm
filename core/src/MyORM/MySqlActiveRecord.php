<?php
namespace MyORM;

class MySqlActiveRecord
{
    static $connection;
    static $isConnected = false;
    static $schema;
    static $columnData = [];

    protected static $columnNames = [];
    protected static $columnKeys = [];
    protected static $primaryKey = [];
    protected static $columnMeta = [];
    protected $lastError = '';

    public function __construct()
    {
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getColumns()
    {
        if (empty(self::$columnNames[$this->getTable()])) {
            $this->updateColumnNames($this->getTable(), self::$schema);
        }

        return self::$columnNames[$this->getTable()];
    }

    public function getColumnKeys()
    {
        if (empty(self::$columnNames[$this->getTable()])) {
            $this->updateColumnNames($this->getTable(), self::$schema);
        }

        return self::$columnKeys[$this->getTable()];
    }


    public function Connect($host, $user, $password, $schema)
    {
        $hostData = explode(':', $host);
        if (! self::$isConnected) {
            if (1 === count($hostData)) {
                self::$connection = new \mysqli($host, $user, $password, $schema);
            } else {
                self::$connection = new \mysqli($hostData[0], $user, $password, $schema, intval($hostData[1]));
            }

            if (self::$connection->connect_errno) {
                $this->lastError = "Failed to connect to MySQL: " . self::$connection->connect_error;
            } else {
                self::$schema = $schema;
                self::$isConnected = true;
            }
        }

        return self::$isConnected;
    }

    public function getServerInfo()
    {
        return self::$connection->server_info;
    }

    protected function updateColumnNames($table, $schema)
    {
        self::$columnNames[$table] = [];
        self::$columnKeys[$table] = [];
        self::$columnMeta[$table] = [];
        self::$primaryKey[$table] = false;
        $sql = sprintf(
            "SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_KEY, EXTRA FROM information_schema.columns WHERE TABLE_NAME = '%s' and TABLE_SCHEMA = '%s'",
            $table,
            $schema
        );
        $stmt = $this->connection()->prepare($sql);
        try {
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $raw_column_data = $result->fetch_all(MYSQLI_ASSOC);
                foreach ($raw_column_data as $outer_key => $array) {
                    $name = $array['COLUMN_NAME'];
                    self::$columnNames[$table][] = $name;
                    self::$columnKeys[$table][$name] = $name;
                    self::$columnMeta[$table][$name] = [
                        'type' => $array['COLUMN_TYPE'],
                        'primary' => $array['COLUMN_KEY'] === 'PRI',
                        'auto_increment' => $array['EXTRA'] === 'auto_increment',
                    ];

                    if (self::$columnMeta[$table][$name]['primary']) {
                        self::$primaryKey[$table] = $name;
                    }
                }
            }
        } catch (\Exception $e) {
            self::$columnNames[$table] = [];
            throw $e;
        }
    }

    public function isConnected()
    {
        return self::$isConnected;
    }

    public function getSchema()
    {
        return self::$schema;
    }

    public function DB()
    {
        return $this;
    }

    protected function connection()
    {
        return self::$connection;
    }

    public function checkPreConditions()
    {
        if (!$this->isConnected()) {
            throw new \Exception('Database is not connected');
        }

        if (empty($this->getTable())) {
            throw new \Exception('No table defined');
        }
    }

    protected function syncWithObject($object, $data, $columns)
    {
        foreach ($columns as $column) {
            $object->{$column} = $data[$column];
        }

        return $object;
    }

    protected function bind($stmt, $bindarr)
    {
        if (empty($bindarr)) {
            return;
        }
        $stmt->bind_param(str_repeat('s', count($bindarr)), ...$bindarr);
    }

    public function Load($where = null, $bindarr = false)
    {
        if (!$bindarr) {
            $bindarr = [];
        }
        $this->checkPreConditions();
        $sql = sprintf('SELECT * FROM %s WHERE %s', $this->getTable(), $where);
        $stmt = $this->connection()->prepare($sql);

        if (!$stmt) {
            $this->lastError = $this->connection()->error;
            return $this;
        }

        $this->bind($stmt, $bindarr);

        if (!$stmt->execute()) {
            $this->lastError = $stmt->error;
            return $this;
        }
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $columns = $this->getColumns();

        $this->syncWithObject($this, $data, $columns);

        return true;
    }

    public function Find($whereOrderBy, $bindarr = false)
    {
        if (!$bindarr) {
            $bindarr = [];
        }
        $this->checkPreConditions();
        $sql = sprintf('SELECT * FROM %s WHERE %s', $this->getTable(), $whereOrderBy);
        $stmt = $this->connection()->prepare($sql);
        if (!$stmt) {
            $this->lastError = $this->connection()->error;
            return [];
        }
        $this->bind($stmt, $bindarr);

        if (!$stmt->execute()) {
            $this->lastError = $stmt->error;
            return false;
        }
        $result = $stmt->get_result();
        $columns = $this->getColumns();

        $objects = [];
        $all_data = $result->fetch_all(MYSQLI_ASSOC);
        $calledClass = get_called_class();
        foreach ($all_data as $data) {
            $objects[] = $this->syncWithObject(new $calledClass(), $data, $columns);
        }

        return $objects;
    }

    public function Count($where, $bindarr = false)
    {
        if (!$bindarr) {
            $bindarr = [];
        }
        $this->checkPreConditions();
        $sql = sprintf('SELECT COUNT(*) as cnt FROM %s WHERE %s', $this->getTable(), $where);
        $stmt = $this->connection()->prepare($sql);
        if (!$stmt) {
            $this->lastError = $this->connection()->error;
            return 0;
        }
        $this->bind($stmt, $bindarr);

        if (!$stmt->execute()) {
            $this->lastError = $stmt->error;
            return false;
        }
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return intval($data['cnt']);
    }

    public function Save()
    {
        $this->checkPreConditions();
        if (!empty($this->{self::$primaryKey[$this->getTable()]})) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    protected function insert()
    {
        $sql = 'INSERT INTO %s (%s) VALUES (%s)';
        $columnNames = [];
        $valuePlaceholders = [];
        $params = [];
        foreach ($this->getColumns() as $col) {
            if (self::$primaryKey[$this->getTable()] === $col) {
                continue;
            }
            $columnNames[] = $this->addQuotes($col);
            $valuePlaceholders[] = '?';
            $params[] = $this->{$col};
        }

        $sql = sprintf(
            $sql,
            $this->addQuotes($this->getTable()),
            join(',', $columnNames),
            join(',', $valuePlaceholders)
        );

        $stmt = $this->connection()->prepare($sql);
        if (!$stmt) {
            $this->lastError = $this->connection()->error;
            return false;
        }

        $this->bind($stmt, $params);

        if ($stmt->execute()) {
            $this->{self::$primaryKey[$this->getTable()]} = $stmt->insert_id;
            return $this;
        }

        $this->lastError = $stmt->error;

        return false;
    }

    protected function update()
    {
        $sql = 'UPDATE %s SET %s WHERE %s';
        $valueSet = [];
        $where = '';
        $parameters = [];

        foreach ($this->getColumns() as $col) {
            if (self::$primaryKey[$this->getTable()] === $col) {
                $where = $col.'= ? ';
            } else {
                $valueSet[] = $col.'= ? ';
                $parameters[] = $this->{$col};
            }
        }

        // Add the where parameter as the last one
        $parameters[] = $this->{self::$primaryKey[$this->getTable()]};

        $sql = sprintf(
            $sql,
            $this->addQuotes($this->getTable()),
            join(',', $valueSet),
            $where
        );

        $stmt = $this->connection()->prepare($sql);

        if (!$stmt) {
            $this->lastError = $this->connection()->error;
            return false;
        }

        $this->bind($stmt, $parameters);

        if ($stmt->execute()) {
            return $this;
        }

        $this->lastError = $stmt->error;

        return false;
    }

    public function Delete()
    {
        $this->checkPreConditions();
        if (empty($this->{self::$primaryKey[$this->getTable()]})) {
            $this->lastError = 'Object is not persisted';
            return false;
        }

        $sql = 'DELETE FROM %s WHERE %s';
        $where = self::$primaryKey[$this->getTable()].'= ? ';

        $sql = sprintf(
            $sql,
            $this->addQuotes($this->getTable()),
            $where
        );

        $stmt = $this->connection()->prepare($sql);
        if (empty($stmt)) {
            $this->lastError = $this->connection()->error;
            return false;
        }

        if (!$stmt) {
            $this->lastError = $this->connection()->error;
            return false;
        }

        $this->bind($stmt, [$this->{self::$primaryKey[$this->getTable()]}]);

        if ($stmt->execute()) {
            return true;
        }

        $this->lastError = $stmt->error;

        return false;
    }

    public function Execute($sql, $parameters = false)
    {
        if (false === $parameters) {
            $parameters = [];
        }

        $stmt = $this->connection()->prepare($sql);

        if (!$stmt) {
            $this->lastError = $this->connection()->error;
            return false;
        }

        $this->bind($stmt, $parameters);

        if (!$stmt->execute()) {
            $this->lastError = $stmt->error;
            return false;
        }


        $result = $stmt->get_result();

        if (false === $result) {
            // For successful table creations result will be false
            return true;
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * @return string
     */
    public function ErrorMsg()
    {
        return $this->lastError;
    }

    protected function addQuotes($value)
    {
        return '`'.$value.'`';
    }


    // Junk methods for backward compatibility

    public static function SetDatabaseAdapter($db)
    {
    }

    public function SetFetchMode($val)
    {
    }
}
