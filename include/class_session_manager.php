<?php

declare(strict_types=1);

/**
 * Modern session manager with security features for PHP 8.5+
 * Implements secure session handling with HttpOnly, Secure, and SameSite flags
 */
class SessionManager
{
    private bool $started = false;
    private array $config = [];
    
    /**
     * Initialize session manager with secure defaults
     * 
     * @param array $config Optional configuration overrides
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'name' => 'TSSE_SESSION',
            'lifetime' => 3600,
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ], $config);
    }
    
    /**
     * Start a secure session
     * 
     * @return bool True on success
     */
    public function start(): bool
    {
        if ($this->started) {
            return true;
        }
        
        // Configure session security settings
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_secure', $this->config['secure'] ? '1' : '0');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_samesite', $this->config['samesite']);
        
        // Set session cookie parameters
        session_set_cookie_params([
            'lifetime' => $this->config['lifetime'],
            'path' => $this->config['path'],
            'domain' => $this->config['domain'],
            'secure' => $this->config['secure'],
            'httponly' => $this->config['httponly'],
            'samesite' => $this->config['samesite']
        ]);
        
        // Set custom session name
        session_name($this->config['name']);
        
        // Start the session
        if (session_status() === PHP_SESSION_NONE) {
            $this->started = session_start();
            
            // Regenerate session ID periodically for security
            if ($this->shouldRegenerateId()) {
                $this->regenerateId();
            }
            
            return $this->started;
        }
        
        return true;
    }
    
    /**
     * Set a session variable
     * 
     * @param string $key Session key
     * @param mixed $value Value to store
     */
    public function set(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get a session variable
     * 
     * @param string $key Session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if a session variable exists
     * 
     * @param string $key Session key
     * @return bool
     */
    public function has(string $key): bool
    {
        $this->ensureStarted();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove a session variable
     * 
     * @param string $key Session key
     */
    public function remove(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }
    
    /**
     * Clear all session data
     */
    public function clear(): void
    {
        $this->ensureStarted();
        $_SESSION = [];
    }
    
    /**
     * Destroy the session completely
     * 
     * @return bool
     */
    public function destroy(): bool
    {
        $this->ensureStarted();
        
        $_SESSION = [];
        
        // Delete session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        $this->started = false;
        return session_destroy();
    }
    
    /**
     * Regenerate session ID for security
     * 
     * @param bool $deleteOldSession Whether to delete old session data
     * @return bool
     */
    public function regenerateId(bool $deleteOldSession = false): bool
    {
        $this->ensureStarted();
        $this->set('_session_last_regeneration', time());
        return session_regenerate_id($deleteOldSession);
    }
    
    /**
     * Get session ID
     * 
     * @return string
     */
    public function getId(): string
    {
        return session_id();
    }
    
    /**
     * Set session ID
     * 
     * @param string $id Session ID
     */
    public function setId(string $id): void
    {
        if (!$this->started) {
            session_id($id);
        }
    }
    
    /**
     * Flash a message for the next request
     * 
     * @param string $key Flash message key
     * @param mixed $value Message value
     */
    public function flash(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION['_flash'][$key] = $value;
    }
    
    /**
     * Get a flash message and remove it
     * 
     * @param string $key Flash message key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public function getFlash(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
    
    /**
     * Check if session has a flash message
     * 
     * @param string $key Flash message key
     * @return bool
     */
    public function hasFlash(string $key): bool
    {
        $this->ensureStarted();
        return isset($_SESSION['_flash'][$key]);
    }
    
    /**
     * Ensure session is started
     * 
     * @throws RuntimeException
     */
    private function ensureStarted(): void
    {
        if (!$this->started) {
            throw new RuntimeException('Session not started. Call start() first.');
        }
    }
    
    /**
     * Check if session ID should be regenerated
     * 
     * @return bool
     */
    private function shouldRegenerateId(): bool
    {
        $lastRegeneration = $this->get('_session_last_regeneration', 0);
        return (time() - $lastRegeneration) > 300; // Regenerate every 5 minutes
    }
    
    /**
     * Set a secure cookie
     * 
     * @param string $name Cookie name
     * @param string $value Cookie value
     * @param int $expires Expiration time (0 for session cookie)
     * @param string $path Cookie path
     * @param string $domain Cookie domain
     * @param bool $secure HTTPS only
     * @param bool $httponly HTTP only (no JavaScript access)
     * @param string $samesite SameSite attribute
     * @return bool
     */
    public function setCookie(
        string $name,
        string $value,
        int $expires = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = true,
        bool $httponly = true,
        string $samesite = 'Strict'
    ): bool {
        return setcookie($name, $value, [
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite
        ]);
    }
}
