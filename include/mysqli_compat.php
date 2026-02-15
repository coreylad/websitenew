<?php

declare(strict_types=1);

/**
 * Backward compatibility layer for mysqli_* functions
 * Wraps PDO calls to maintain compatibility with legacy code
 * PHP 8.5+ compatible with strict typing
 */

if (!function_exists('mysqli_fetch_assoc_compat')) {
    /**
     * Compatibility wrapper for mysqli_fetch_assoc using PDO
     * 
     * @param PDOStatement|false $result PDO statement result
     * @return array|false|null
     */
    function mysqli_fetch_assoc_compat(PDOStatement|false $result): array|false|null
    {
        if (!$result instanceof PDOStatement) {
            return false;
        }
        
        return $result->fetch(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('mysqli_fetch_row_compat')) {
    /**
     * Compatibility wrapper for mysqli_fetch_row using PDO
     * 
     * @param PDOStatement|false $result PDO statement result
     * @return array|false|null
     */
    function mysqli_fetch_row_compat(PDOStatement|false $result): array|false|null
    {
        if (!$result instanceof PDOStatement) {
            return false;
        }
        
        return $result->fetch(PDO::FETCH_NUM);
    }
}

if (!function_exists('mysqli_num_rows_compat')) {
    /**
     * Compatibility wrapper for mysqli_num_rows using PDO
     * Note: rowCount() is not reliable for SELECT queries
     * This should be replaced with count queries where possible
     * 
     * @param PDOStatement|false $result PDO statement result
     * @return int
     */
    function mysqli_num_rows_compat(PDOStatement|false $result): int
    {
        if (!$result instanceof PDOStatement) {
            return 0;
        }
        
        // For SELECT statements, this may not work reliably
        // Better to use COUNT(*) queries instead
        return $result->rowCount();
    }
}

if (!function_exists('mysqli_affected_rows_compat')) {
    /**
     * Compatibility wrapper for mysqli_affected_rows using PDO
     * 
     * @param PDO|null $connection PDO connection
     * @return int
     */
    function mysqli_affected_rows_compat(?PDO $connection): int
    {
        // This needs to be called on the statement, not the connection
        // Legacy code will need refactoring to use statement->rowCount()
        return 0;
    }
}

if (!function_exists('mysqli_insert_id_compat')) {
    /**
     * Compatibility wrapper for mysqli_insert_id using PDO
     * 
     * @param PDO|null $connection PDO connection
     * @return string
     */
    function mysqli_insert_id_compat(?PDO $connection): string
    {
        if (!$connection instanceof PDO) {
            return '0';
        }
        
        return $connection->lastInsertId();
    }
}

if (!function_exists('mysqli_error_compat')) {
    /**
     * Compatibility wrapper for mysqli_error using PDO
     * 
     * @param PDO|null $connection PDO connection
     * @return string
     */
    function mysqli_error_compat(?PDO $connection): string
    {
        if (!$connection instanceof PDO) {
            return '';
        }
        
        $errorInfo = $connection->errorInfo();
        return $errorInfo[2] ?? '';
    }
}

if (!function_exists('mysqli_errno_compat')) {
    /**
     * Compatibility wrapper for mysqli_errno using PDO
     * 
     * @param PDO|null $connection PDO connection
     * @return int
     */
    function mysqli_errno_compat(?PDO $connection): int
    {
        if (!$connection instanceof PDO) {
            return 0;
        }
        
        $errorInfo = $connection->errorInfo();
        return (int)($errorInfo[1] ?? 0);
    }
}

if (!function_exists('mysqli_real_escape_string_compat')) {
    /**
     * Compatibility wrapper for mysqli_real_escape_string
     * Note: With PDO prepared statements, this should not be needed
     * Use prepared statements with parameters instead
     * 
     * @param PDO|null $connection PDO connection
     * @param string $string String to escape
     * @return string
     */
    function mysqli_real_escape_string_compat(?PDO $connection, string $string): string
    {
        if (!$connection instanceof PDO) {
            return addslashes($string);
        }
        
        // Remove quotes added by PDO::quote()
        $quoted = $connection->quote($string);
        return substr($quoted, 1, -1);
    }
}

/**
 * Enhanced sql_query function that uses PDO
 * Maintains backward compatibility with existing code
 * 
 * @param string $query SQL query to execute
 * @param array $params Optional parameters for prepared statements
 * @return PDOStatement|false
 */
function sql_query_pdo(string $query, array $params = []): PDOStatement|false
{
    global $TSDatabase;
    
    if (!defined('DEBUGMODE')) {
        $query_start = array_sum(explode(' ', microtime()));
    }
    
    try {
        $pdo = $GLOBALS['DatabaseConnect'] ?? null;
        
        if (!$pdo instanceof PDO) {
            write_log('Database connection not available');
            return false;
        }
        
        if (empty($params)) {
            $stmt = $pdo->query($query);
        } else {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
        }
        
        if (!defined('DEBUGMODE')) {
            $query_end = round(array_sum(explode(' ', microtime())) - $query_start, 4);
            
            if (!isset($GLOBALS['queries'])) {
                $GLOBALS['queries'] = '';
            }
            
            if (isset($GLOBALS['totalqueries'])) {
                $GLOBALS['totalqueries']++;
            } else {
                $GLOBALS['totalqueries'] = 1;
            }
            
            $GLOBALS['queries'] .= '<input type="hidden" name="queries[]" value="' . 
                base64_encode(substr((string)$query_end, 0, 8) . ',' . base64_encode(trim($query))) . '" />';
        }
        
        return $stmt;
        
    } catch (PDOException $e) {
        write_log('MySQL Error: ' . $e->getMessage() . 
            ' {Error No: ' . $e->getCode() . 
            ' - File: ' . ($_SERVER['SCRIPT_NAME'] ?? 'unknown') . 
            '}.. The query was: ' . htmlspecialchars($query, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        return false;
    }
}
