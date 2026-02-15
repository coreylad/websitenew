<?php

declare(strict_types=1);

/**
 * Simple class autoloader for PHP 8.5+
 * Autoloads classes from the include directory
 */

spl_autoload_register(function (string $className): void {
    // Convert class name to file path
    // PDODatabase -> class_pdo_database.php
    // SessionManager -> class_session_manager.php
    // CSRFProtection -> class_csrf_protection.php
    
    // Convert PascalCase to snake_case
    $fileName = preg_replace('/(?<!^)[A-Z]/', '_$0', $className);
    $fileName = strtolower($fileName);
    $fileName = 'class_' . $fileName . '.php';
    
    // Try to load from include directory
    $filePath = INC_PATH . '/' . $fileName;
    
    if (file_exists($filePath)) {
        require_once $filePath;
    }
});
