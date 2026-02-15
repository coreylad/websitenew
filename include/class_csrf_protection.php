<?php

declare(strict_types=1);

/**
 * CSRF (Cross-Site Request Forgery) protection class
 * Generates and validates CSRF tokens for forms
 */
class CSRFProtection
{
    private const TOKEN_NAME = 'csrf_token';
    private const TOKEN_LENGTH = 32;
    
    /**
     * Generate a CSRF token and store it in session
     * 
     * @return string The generated token
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::TOKEN_NAME] = $token;
        
        return $token;
    }
    
    /**
     * Get the current CSRF token or generate a new one
     * 
     * @return string
     */
    public static function getToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION[self::TOKEN_NAME])) {
            return self::generateToken();
        }
        
        return $_SESSION[self::TOKEN_NAME];
    }
    
    /**
     * Validate a CSRF token
     * 
     * @param string|null $token Token to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($token === null || !isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }
        
        return hash_equals($_SESSION[self::TOKEN_NAME], $token);
    }
    
    /**
     * Validate CSRF token from request
     * Checks both POST and headers
     * 
     * @return bool True if valid, false otherwise
     */
    public static function validateRequest(): bool
    {
        // Check POST data first
        $token = $_POST[self::TOKEN_NAME] ?? null;
        
        // If not in POST, check custom header
        if ($token === null && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        
        return self::validateToken($token);
    }
    
    /**
     * Generate a hidden input field with CSRF token
     * 
     * @return string HTML input field
     */
    public static function getTokenField(): string
    {
        $token = htmlspecialchars(self::getToken(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::TOKEN_NAME,
            $token
        );
    }
    
    /**
     * Generate a meta tag with CSRF token (for AJAX requests)
     * 
     * @return string HTML meta tag
     */
    public static function getTokenMeta(): string
    {
        $token = htmlspecialchars(self::getToken(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return sprintf(
            '<meta name="csrf-token" content="%s">',
            $token
        );
    }
    
    /**
     * Validate request or die with error
     * 
     * @param string $errorMessage Error message to display
     * @throws RuntimeException
     */
    public static function validateOrDie(string $errorMessage = 'CSRF token validation failed'): void
    {
        if (!self::validateRequest()) {
            if (function_exists('stderr')) {
                stderr('Security Error', $errorMessage, false);
            } else {
                throw new RuntimeException($errorMessage);
            }
            exit;
        }
    }
    
    /**
     * Regenerate CSRF token (use after important actions)
     * 
     * @return string New token
     */
    public static function regenerateToken(): string
    {
        return self::generateToken();
    }
}
