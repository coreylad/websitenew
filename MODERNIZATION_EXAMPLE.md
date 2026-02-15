# Modernization Example: Before vs After

## Example from ban_user.php

### BEFORE (Original Code with Security Issues):

```php
<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/ban_user.lang");
$Message = "";
$username = isset($_GET["username"]) ? trim($_GET["username"]) : "";

if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $username = trim($_POST["username"]);
    $usergroup = intval($_POST["usergroup"]);
    $reason = trim($_POST["reason"]);
    
    if ($username && $usergroup && $reason) {
        // ❌ SQL INJECTION VULNERABILITY - Direct string concatenation
        $query = mysqli_query($GLOBALS["DatabaseConnect"], 
            "SELECT ip, g.cansettingspanel FROM users 
             WHERE `username` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'");
        
        if (mysqli_num_rows($query) == 0) {
            $Message = showAlertError($Language[2]);
        } else {
            $User = mysqli_fetch_assoc($query);
            // ❌ More SQL injection vulnerabilities...
            mysqli_query($GLOBALS["DatabaseConnect"], 
                "UPDATE users SET `enabled` = 'no', 
                 $usergroup = '" . $usergroup . "' 
                 WHERE `username` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'");
        }
    }
}

// ❌ NO CSRF PROTECTION - Form vulnerable to CSRF attacks
echo "<form action=\"" . $_SERVER["SCRIPT_NAME"] . "?do=ban_user\" method=\"post\">";
echo "<input type=\"text\" name=\"username\" value=\"" . htmlspecialchars($username) . "\" />";
echo "</form>";

// ❌ NO TYPE HINTS - Function without type safety
function getStaffLanguage()
{
    if (isset($_COOKIE["staffcplanguage"])) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
```

### AFTER (Modernized Code with Security):

```php
<?php
declare(strict_types=1);  // ✅ Strict type enforcement

checkStaffAuthentication();

$pdo = $GLOBALS['DatabaseConnect_PDO'] ?? null;
if (!$pdo) {
    die('Database connection not available');
}

$Language = file("languages/" . getStaffLanguage() . "/ban_user.lang");
$Message = "";
$username = $_GET["username"] ?? "";  // ✅ Null coalescing operator
$username = trim($username);

if (strtoupper($_SERVER["REQUEST_METHOD"]) === "POST") {  // ✅ Strict comparison
    // ✅ CSRF PROTECTION - Validate token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $Message = showAlertError("Invalid CSRF token");
    } else {
        $username = trim($_POST["username"]);
        $usergroup = intval($_POST["usergroup"]);
        $reason = trim($_POST["reason"]);
        
        if ($username && $usergroup && $reason) {
            try {  // ✅ Error handling
                // ✅ PDO PREPARED STATEMENT - No SQL injection possible
                $stmt = $pdo->prepare(
                    "SELECT u.ip, g.cansettingspanel FROM users u 
                     LEFT JOIN usergroups g ON (u.usergroup = g.gid) 
                     WHERE u.username = ?"
                );
                $stmt->execute([$username]);
                
                if ($stmt->rowCount() === 0) {  // ✅ Strict comparison
                    $Message = showAlertError($Language[2]);
                } else {
                    $User = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // ✅ PDO PREPARED STATEMENT - Secure update
                    $stmt = $pdo->prepare(
                        "UPDATE users SET enabled = 'no', usergroup = ?, 
                         notifs = ? WHERE username = ?"
                    );
                    $stmt->execute([$usergroup, $reason, $username]);
                }
            } catch (Exception $e) {  // ✅ Exception handling
                $Message = showAlertError("Error: " . htmlspecialchars($e->getMessage()));
            }
        }
    }
}

// ✅ CSRF PROTECTION - Generate and include token
$csrf_token = generateCSRFToken();

echo "<form action=\"" . htmlspecialchars($_SERVER["SCRIPT_NAME"]) . "?do=ban_user\" method=\"post\">";
echo "<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($csrf_token) . "\" />";
echo "<input type=\"text\" name=\"username\" value=\"" . htmlspecialchars($username) . "\" />";
echo "</form>";

// ✅ TYPE HINTS - Function with explicit types
function getStaffLanguage(): string  // ✅ Return type
{
    if (isset($_COOKIE["staffcplanguage"]) && 
        is_dir("languages/" . $_COOKIE["staffcplanguage"]) && 
        is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}

// ✅ CSRF TOKEN FUNCTIONS
function generateCSRFToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
```

## Key Differences Highlighted:

### ❌ Security Issues in Original Code:
1. **SQL Injection** - String concatenation in queries
2. **No CSRF Protection** - Forms vulnerable to cross-site attacks
3. **Weak Type Safety** - Using `==` instead of `===`
4. **No Error Handling** - Database errors not caught
5. **Missing Type Hints** - No function parameter/return types

### ✅ Improvements in Modernized Code:
1. **SQL Injection Protected** - PDO prepared statements with parameterized queries
2. **CSRF Protection** - Token generation and validation on all forms
3. **Strict Type Safety** - `declare(strict_types=1)` and `===` comparisons
4. **Error Handling** - Try-catch blocks for all database operations
5. **Type Hints** - All functions have explicit parameter and return types
6. **Output Escaping** - All user input properly escaped with `htmlspecialchars()`
7. **Null Safety** - Null coalescing operator (`??`) for safe defaults

## Security Impact:

| Vulnerability Type | Before | After |
|-------------------|--------|-------|
| SQL Injection | ❌ High Risk | ✅ Protected |
| CSRF Attacks | ❌ Vulnerable | ✅ Protected |
| XSS Attacks | ⚠️ Partial | ✅ Protected |
| Type Errors | ⚠️ Possible | ✅ Prevented |
| Database Errors | ❌ Unhandled | ✅ Handled |

This pattern has been applied consistently across all 7 modernized files.
