<?php

declare(strict_types=1);

/**
 * Modern Staff Control Panel Helper Functions
 * Provides updated infrastructure for staffcp/tools
 * PHP 8.3+ compatible with strict types
 */

if (!defined('IN_TRACKER')) {
    exit('Direct access not allowed');
}

// Load modern security functions if not already loaded
if (!function_exists('escape_html')) {
    require_once __DIR__ . '/../include/security_functions.php';
}

/**
 * Check if user is authenticated as staff member
 * Modernized version of checkStaffAuthentication()
 * 
 * @return array Staff member details
 * @throws RuntimeException if not authenticated
 */
function checkStaffAuthenticationModern(): array
{
    global $TSDatabase;
    
    if (!defined('IN-TSSE-STAFF-PANEL')) {
        if (!isset($_SESSION['ADMIN_ID']) || !isset($_SESSION['ADMIN_USERNAME'])) {
            redirectTo('../index.php');
            exit;
        }
        
        define('IN-TSSE-STAFF-PANEL', true);
    }
    
    // Verify staff member exists and has permissions
    if ($TSDatabase && method_exists($TSDatabase, 'query')) {
        try {
            $result = $TSDatabase->query(
                'SELECT u.id, u.username, u.usergroup, g.canstaffpanel, g.cansettingspanel 
                 FROM users u 
                 LEFT JOIN usergroups g ON u.usergroup = g.gid 
                 WHERE u.id = ? 
                 LIMIT 1',
                [$_SESSION['ADMIN_ID']]
            );
            
            if ($result) {
                $staff = $result->fetch(PDO::FETCH_ASSOC);
                
                if (!$staff || $staff['canstaffpanel'] !== 'yes') {
                    redirectTo('../index.php');
                    exit;
                }
                
                return $staff;
            }
        } catch (Exception $e) {
            // Fall back to old method if new method fails
        }
    }
    
    // Return minimal array for compatibility
    return [
        'id' => $_SESSION['ADMIN_ID'] ?? 0,
        'username' => $_SESSION['ADMIN_USERNAME'] ?? 'Unknown',
        'canstaffpanel' => 'yes'
    ];
}

/**
 * Get staff language preference
 * 
 * @return string Language code (default: 'english')
 */
function getStaffLanguageModern(): string
{
    if (isset($_COOKIE['staffcplanguage']) && 
        is_dir(__DIR__ . '/languages/' . $_COOKIE['staffcplanguage']) && 
        is_file(__DIR__ . '/languages/' . $_COOKIE['staffcplanguage'] . '/staffcp.lang')) {
        return $_COOKIE['staffcplanguage'];
    }
    
    return 'english';
}

/**
 * Load language file with error handling
 * 
 * @param string $filename Language file name (without .lang extension)
 * @param string $language Language code
 * @return array Language strings
 */
function loadStaffLanguage(string $filename, ?string $language = null): array
{
    $language = $language ?? getStaffLanguageModern();
    $filepath = __DIR__ . '/languages/' . $language . '/' . $filename . '.lang';
    
    if (!file_exists($filepath)) {
        // Fall back to English if language file doesn't exist
        $filepath = __DIR__ . '/languages/english/' . $filename . '.lang';
    }
    
    if (file_exists($filepath)) {
        return file($filepath);
    }
    
    return [];
}

/**
 * Show alert message (error)
 * 
 * @param string $message Message to display
 * @return string HTML for alert
 */
function showAlertErrorModern(string $message): string
{
    return '<div class="alert alert-error">' . escape_html($message) . '</div>';
}

/**
 * Show alert message (success)
 * 
 * @param string $message Message to display
 * @return string HTML for alert
 */
function showAlertSuccessModern(string $message): string
{
    return '<div class="alert alert-success">' . escape_html($message) . '</div>';
}

/**
 * Show alert message (info)
 * 
 * @param string $message Message to display
 * @return string HTML for alert
 */
function showAlertInfoModern(string $message): string
{
    return '<div class="alert alert-info">' . escape_html($message) . '</div>';
}

/**
 * Log staff action to database
 * 
 * @param string $action Action description
 * @return bool Success status
 */
function logStaffActionModern(string $action): bool
{
    global $TSDatabase;
    
    if (!isset($_SESSION['ADMIN_ID']) || !isset($_SESSION['ADMIN_USERNAME'])) {
        return false;
    }
    
    try {
        if ($TSDatabase && method_exists($TSDatabase, 'query')) {
            $TSDatabase->query(
                'INSERT INTO ts_stafflog (date, user, action) VALUES (?, ?, ?)',
                [time(), $_SESSION['ADMIN_USERNAME'], $action]
            );
            return true;
        }
    } catch (Exception $e) {
        // Log error but don't fail
        error_log('Failed to log staff action: ' . $e->getMessage());
    }
    
    return false;
}

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 * @param int $statusCode HTTP status code
 * @return void
 */
function redirectTo(string $url, int $statusCode = 302): void
{
    if (!headers_sent()) {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
    
    // Fallback if headers already sent
    echo '<meta http-equiv="refresh" content="0;url=' . escape_attr($url) . '">';
    echo '<script>window.location.href = ' . escape_js($url) . ';</script>';
    exit;
}

/**
 * Generate a random secret key
 * 
 * @param int $length Length of secret
 * @return string Random secret
 */
function generateSecret(int $length = 20): string
{
    try {
        return bin2hex(random_bytes($length));
    } catch (Exception $e) {
        // Fallback to less secure method
        return md5(uniqid((string)mt_rand(), true));
    }
}

/**
 * Validate email address
 * 
 * @param string $email Email to validate
 * @return bool True if valid
 */
function validateEmail(string $email): bool
{
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate username (alphanumeric only)
 * 
 * @param string $username Username to validate
 * @return bool True if valid
 */
function validateUsername(string $username): bool
{
    return (bool)preg_match('/^[a-zA-Z0-9_-]+$/', $username);
}

/**
 * Generate form token for CSRF protection
 * 
 * @return string Form token
 */
function generateFormToken(): string
{
    if (!isset($_SESSION['form_token'])) {
        $_SESSION['form_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['form_token'];
}

/**
 * Validate form token for CSRF protection
 * 
 * @param string|null $token Token to validate
 * @return bool True if valid
 */
function validateFormToken(?string $token): bool
{
    if (!isset($_SESSION['form_token']) || empty($token)) {
        return false;
    }
    
    return hash_equals($_SESSION['form_token'], $token);
}

/**
 * Get form token input field HTML
 * 
 * @return string HTML input field
 */
function getFormTokenField(): string
{
    $token = generateFormToken();
    return '<input type="hidden" name="form_token" value="' . escape_attr($token) . '">';
}
