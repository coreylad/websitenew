<?php

declare(strict_types=1);

if (!defined('INC_PATH') || !defined('TSDIR')) {
    exit('Include and TSDIR paths does not defined correctly!');
}

/**
 * Modern TSDatabase class using PDO
 * PHP 8.5+ compatible with strict typing and prepared statements
 * Maintains backward compatibility with legacy code
 */
class TSDatabase
{
    public PDO|null $DatabaseConnect = null;
    private ?PDODatabase $pdoWrapper = null;
    
    /**
     * Connect to database using PDO
     * Maintains backward compatibility while using modern PDO
     */
    public function Connect(): void
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
            
            $this->DatabaseConnect = new PDO(
                $dsn,
                MYSQL_USER,
                MYSQL_PASS,
                $options
            );
            
            // Set global for backward compatibility
            $GLOBALS['DatabaseConnect'] = $this->DatabaseConnect;
            
            // Load PDO wrapper class if available
            if (file_exists(INC_PATH . '/class_pdo_database.php')) {
                require_once INC_PATH . '/class_pdo_database.php';
            }
            
        } catch (PDOException $e) {
            $this->handleConnectionError($e);
        }
    }
    
    /**
     * Get PDO connection instance
     * 
     * @return PDO|null
     */
    public function getConnection(): ?PDO
    {
        return $this->DatabaseConnect;
    }
    
    /**
     * Execute query with optional prepared statement parameters
     * 
     * @param string $query SQL query
     * @param array $params Optional parameters for prepared statements
     * @return PDOStatement|false
     */
    public function query(string $query, array $params = []): PDOStatement|false
    {
        if (!$this->DatabaseConnect) {
            return false;
        }
        
        try {
            if (empty($params)) {
                return $this->DatabaseConnect->query($query);
            } else {
                $stmt = $this->DatabaseConnect->prepare($query);
                $stmt->execute($params);
                return $stmt;
            }
        } catch (PDOException $e) {
            $this->handleQueryError($e, $query);
            return false;
        }
    }
    
    /**
     * Fetch single row from query
     * 
     * @param string $query SQL query
     * @param array $params Optional parameters
     * @return array|false
     */
    public function fetchRow(string $query, array $params = []): array|false
    {
        $stmt = $this->query($query, $params);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }
    
    /**
     * Fetch all rows from query
     * 
     * @param string $query SQL query
     * @param array $params Optional parameters
     * @return array
     */
    public function fetchAll(string $query, array $params = []): array
    {
        $stmt = $this->query($query, $params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    
    /**
     * Get last insert ID
     * 
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->DatabaseConnect ? $this->DatabaseConnect->lastInsertId() : '0';
    }
    
    /**
     * Handle connection errors
     * 
     * @param PDOException $e
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
        
        if (function_exists('write_log')) {
            write_log($errorMsg);
        } else {
            error_log($errorMsg);
        }
    }
    
    /**
     * Legacy method for charset - now handled in PDO constructor
     * Kept for backward compatibility
     */
    public function set_charset(): void
    {
        // Charset is now set in PDO constructor
        // This method is kept for backward compatibility only
    }
}