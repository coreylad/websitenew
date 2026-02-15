<?php

declare(strict_types=1);

if (!defined('INC_PATH') || !defined('TSDIR')) {
    exit('Include and TSDIR paths does not defined correctly!');
}

/**
 * Modern PDO-based database abstraction layer for PHP 8.5+
 * Replaces legacy mysqli procedural functions with prepared statements
 */
class PDODatabase
{
    private ?PDO $connection = null;
    private array $queryLog = [];
    private int $queryCount = 0;
    private bool $debugMode = false;
    
    /**
     * Initialize database connection with PDO
     * 
     * @throws PDOException
     */
    public function connect(): void
    {
        require_once INC_PATH . '/config_database.php';
        
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                MYSQL_HOST,
                MYSQL_DB,
                MYSQL_CHARSET ?: 'utf8mb4'
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . (MYSQL_CHARSET ?: 'utf8mb4')
            ];
            
            $this->connection = new PDO($dsn, MYSQL_USER, MYSQL_PASS, $options);
            $GLOBALS['DatabaseConnect'] = $this->connection;
            
        } catch (PDOException $e) {
            $this->handleConnectionError($e);
        }
    }
    
    /**
     * Get the PDO connection instance
     */
    public function getConnection(): ?PDO
    {
        return $this->connection;
    }
    
    /**
     * Execute a query with optional parameters (prepared statement)
     * 
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return PDOStatement|false
     */
    public function query(string $query, array $params = []): PDOStatement|false
    {
        if (!$this->connection) {
            throw new RuntimeException('Database connection not established');
        }
        
        $startTime = $this->debugMode ? microtime(true) : 0;
        
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            
            if ($this->debugMode) {
                $this->logQuery($query, $params, microtime(true) - $startTime);
            }
            
            $this->queryCount++;
            return $stmt;
            
        } catch (PDOException $e) {
            $this->handleQueryError($e, $query);
            return false;
        }
    }
    
    /**
     * Fetch a single row from query results
     * 
     * @param string $query SQL query
     * @param array $params Parameters to bind
     * @return array|false
     */
    public function fetchRow(string $query, array $params = []): array|false
    {
        $stmt = $this->query($query, $params);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }
    
    /**
     * Fetch all rows from query results
     * 
     * @param string $query SQL query
     * @param array $params Parameters to bind
     * @return array
     */
    public function fetchAll(string $query, array $params = []): array
    {
        $stmt = $this->query($query, $params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    
    /**
     * Fetch a single column value
     * 
     * @param string $query SQL query
     * @param array $params Parameters to bind
     * @return mixed
     */
    public function fetchColumn(string $query, array $params = []): mixed
    {
        $stmt = $this->query($query, $params);
        return $stmt ? $stmt->fetchColumn() : false;
    }
    
    /**
     * Get count of rows matching criteria
     * 
     * @param string $table Table name
     * @param string $column Column to count
     * @param string $where WHERE clause (without WHERE keyword)
     * @param array $params Parameters for WHERE clause
     * @return int
     */
    public function count(string $table, string $column = '*', string $where = '', array $params = []): int
    {
        $query = "SELECT COUNT($column) FROM $table";
        if (!empty($where)) {
            $query .= " WHERE $where";
        }
        
        $result = $this->fetchColumn($query, $params);
        return $result !== false ? (int)$result : 0;
    }
    
    /**
     * Execute an INSERT query
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int|false Last insert ID or false on failure
     */
    public function insert(string $table, array $data): int|false
    {
        if (empty($data)) {
            return false;
        }
        
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($data), '?');
        
        $query = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $stmt = $this->query($query, array_values($data));
        return $stmt ? (int)$this->connection->lastInsertId() : false;
    }
    
    /**
     * Execute an UPDATE query
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @param string $where WHERE clause (without WHERE keyword)
     * @param array $whereParams Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        if (empty($data)) {
            return 0;
        }
        
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "$column = ?";
        }
        
        $query = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $setParts),
            $where
        );
        
        $params = array_merge(array_values($data), $whereParams);
        $stmt = $this->query($query, $params);
        
        return $stmt ? $stmt->rowCount() : 0;
    }
    
    /**
     * Execute a DELETE query
     * 
     * @param string $table Table name
     * @param string $where WHERE clause (without WHERE keyword)
     * @param array $params Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $query = "DELETE FROM $table WHERE $where";
        $stmt = $this->query($query, $params);
        return $stmt ? $stmt->rowCount() : 0;
    }
    
    /**
     * Begin a database transaction
     */
    public function beginTransaction(): bool
    {
        return $this->connection ? $this->connection->beginTransaction() : false;
    }
    
    /**
     * Commit a database transaction
     */
    public function commit(): bool
    {
        return $this->connection ? $this->connection->commit() : false;
    }
    
    /**
     * Rollback a database transaction
     */
    public function rollback(): bool
    {
        return $this->connection ? $this->connection->rollBack() : false;
    }
    
    /**
     * Get the last insert ID
     */
    public function lastInsertId(): string
    {
        return $this->connection ? $this->connection->lastInsertId() : '0';
    }
    
    /**
     * Enable debug mode to log queries
     */
    public function enableDebug(): void
    {
        $this->debugMode = true;
    }
    
    /**
     * Get query statistics
     * 
     * @return array
     */
    public function getQueryStats(): array
    {
        return [
            'count' => $this->queryCount,
            'log' => $this->queryLog
        ];
    }
    
    /**
     * Handle connection errors
     * 
     * @param PDOException $e
     * @throws PDOException
     */
    private function handleConnectionError(PDOException $e): void
    {
        $errorCode = (int)$e->getCode();
        
        switch ($errorCode) {
            case 1040:
                define('errorid', 1040);
                break;
            case 2002:
                define('errorid', 2002);
                break;
            default:
                define('errorid', 5);
                if (file_exists(TSDIR . '/ts_error.php')) {
                    include_once TSDIR . '/ts_error.php';
                }
                exit;
        }
    }
    
    /**
     * Handle query errors
     * 
     * @param PDOException $e
     * @param string $query
     */
    private function handleQueryError(PDOException $e, string $query): void
    {
        $errorMsg = sprintf(
            'MySQL Error: %s {Error No: %s - File: %s}.. The query was: %s',
            $e->getMessage(),
            $e->getCode(),
            $_SERVER['SCRIPT_NAME'] ?? 'unknown',
            htmlspecialchars($query, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );
        
        // Log to database if possible, otherwise to PHP error log
        if (function_exists('write_log')) {
            write_log($errorMsg);
        } else {
            error_log($errorMsg);
        }
    }
    
    /**
     * Log query for debugging
     * 
     * @param string $query
     * @param array $params
     * @param float $executionTime
     */
    private function logQuery(string $query, array $params, float $executionTime): void
    {
        $this->queryLog[] = [
            'query' => $query,
            'params' => $params,
            'time' => round($executionTime, 4)
        ];
    }
}
