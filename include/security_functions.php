<?php

declare(strict_types=1);

/**
 * Security helper functions for output escaping and input sanitization
 * PHP 8.5+ compatible with strict typing
 */

/**
 * Escape output for HTML context
 * 
 * @param string|null $string String to escape
 * @param string $encoding Character encoding
 * @return string Escaped string
 */
function escape_html(?string $string, string $encoding = 'UTF-8'): string
{
    if ($string === null) {
        return '';
    }
    
    return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, $encoding);
}

/**
 * Escape output for HTML attribute context
 * 
 * @param string|null $string String to escape
 * @param string $encoding Character encoding
 * @return string Escaped string
 */
function escape_attr(?string $string, string $encoding = 'UTF-8'): string
{
    if ($string === null) {
        return '';
    }
    
    return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, $encoding);
}

/**
 * Escape output for JavaScript context
 * 
 * @param string|null $string String to escape
 * @return string Escaped string
 */
function escape_js(?string $string): string
{
    if ($string === null) {
        return '';
    }
    
    return json_encode($string, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

/**
 * Escape output for URL context
 * 
 * @param string|null $string String to escape
 * @return string Escaped string
 */
function escape_url(?string $string): string
{
    if ($string === null) {
        return '';
    }
    
    return rawurlencode($string);
}

/**
 * Sanitize input for SQL LIKE patterns
 * 
 * @param string $string String to sanitize
 * @return string Sanitized string
 */
function escape_like_pattern(string $string): string
{
    return addcslashes($string, '%_\\');
}

/**
 * Validate and sanitize integer input
 * 
 * @param mixed $value Value to validate
 * @param int $default Default value if invalid
 * @return int
 */
function sanitize_int(mixed $value, int $default = 0): int
{
    if (is_numeric($value)) {
        return (int)$value;
    }
    
    return $default;
}

/**
 * Validate and sanitize positive integer input
 * 
 * @param mixed $value Value to validate
 * @param int $default Default value if invalid
 * @return int
 */
function sanitize_positive_int(mixed $value, int $default = 0): int
{
    $intValue = sanitize_int($value, $default);
    return max(0, $intValue);
}

/**
 * Validate and sanitize float input
 * 
 * @param mixed $value Value to validate
 * @param float $default Default value if invalid
 * @return float
 */
function sanitize_float(mixed $value, float $default = 0.0): float
{
    if (is_numeric($value)) {
        return (float)$value;
    }
    
    return $default;
}

/**
 * Validate and sanitize boolean input
 * 
 * @param mixed $value Value to validate
 * @return bool
 */
function sanitize_bool(mixed $value): bool
{
    if (is_bool($value)) {
        return $value;
    }
    
    if (is_string($value)) {
        $value = strtolower($value);
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }
    
    return (bool)$value;
}

/**
 * Sanitize email address
 * 
 * @param string $email Email to validate
 * @return string|false Sanitized email or false if invalid
 */
function sanitize_email(string $email): string|false
{
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    if ($email === false) {
        return false;
    }
    
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? $email : false;
}

/**
 * Sanitize URL
 * 
 * @param string $url URL to validate
 * @return string|false Sanitized URL or false if invalid
 */
function sanitize_url_input(string $url): string|false
{
    $url = filter_var($url, FILTER_SANITIZE_URL);
    
    if ($url === false) {
        return false;
    }
    
    return filter_var($url, FILTER_VALIDATE_URL) !== false ? $url : false;
}

/**
 * Sanitize string input (remove control characters)
 * 
 * @param string|null $string String to sanitize
 * @param bool $allowLineBreaks Whether to allow \n and \r
 * @return string
 */
function sanitize_string(?string $string, bool $allowLineBreaks = false): string
{
    if ($string === null) {
        return '';
    }
    
    // Remove null bytes
    $string = str_replace("\0", '', $string);
    
    if (!$allowLineBreaks) {
        // Remove all control characters
        $string = preg_replace('/[\x00-\x1F\x7F]/u', '', $string);
    } else {
        // Remove control characters except \n and \r
        $string = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/u', '', $string);
    }
    
    return trim($string);
}

/**
 * Validate IP address
 * 
 * @param string $ip IP address to validate
 * @param int $flags Validation flags (FILTER_FLAG_IPV4, FILTER_FLAG_IPV6, etc.)
 * @return string|false Valid IP or false
 */
function validate_ip(string $ip, int $flags = 0): string|false
{
    $validIp = filter_var($ip, FILTER_VALIDATE_IP, $flags);
    return $validIp !== false ? $validIp : false;
}

/**
 * Strip HTML and PHP tags with allowed tags
 * 
 * @param string|null $string String to strip
 * @param array $allowedTags Array of allowed tags (e.g., ['p', 'br', 'strong'])
 * @return string
 */
function strip_tags_safe(?string $string, array $allowedTags = []): string
{
    if ($string === null) {
        return '';
    }
    
    if (empty($allowedTags)) {
        return strip_tags($string);
    }
    
    $allowed = '<' . implode('><', $allowedTags) . '>';
    return strip_tags($string, $allowed);
}

/**
 * Prevent header injection attacks
 * 
 * @param string $string String to check
 * @return string Safe string
 */
function prevent_header_injection(string $string): string
{
    // Remove newlines and carriage returns
    return str_replace(["\r", "\n", "%0a", "%0d"], '', $string);
}

/**
 * Generate a secure random token
 * 
 * @param int $length Token length in bytes
 * @return string Hexadecimal token
 */
function generate_secure_token(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}

/**
 * Hash password securely using modern algorithms
 * 
 * @param string $password Password to hash
 * @return string Hashed password
 */
function hash_password_secure(string $password): string
{
    return password_hash($password, PASSWORD_ARGON2ID);
}

/**
 * Verify password against hash
 * 
 * @param string $password Password to verify
 * @param string $hash Hash to verify against
 * @return bool
 */
function verify_password_secure(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/**
 * Check if password hash needs rehashing
 * 
 * @param string $hash Hash to check
 * @return bool
 */
function password_needs_rehash_secure(string $hash): bool
{
    return password_needs_rehash($hash, PASSWORD_ARGON2ID);
}
