# Staff Control Panel Modernization Guide

## Overview

This guide documents the modernization of staffcp/tools to use the new PHP 8.3+ infrastructure with PDO, security functions, and strict types.

## New Infrastructure

### staffcp_modern.php

Core helper functions for modernized staffcp/tools:

- `checkStaffAuthenticationModern()` - Secure authentication check
- `loadStaffLanguage()` - Load language files with error handling
- `showAlertErrorModern()` / `showAlertSuccessModern()` - Alert messages with XSS protection
- `logStaffActionModern()` - Log staff actions with prepared statements
- `generateFormToken()` / `validateFormToken()` - CSRF protection
- `validateEmail()` / `validateUsername()` - Input validation
- `escape_html()` / `escape_attr()` - Output escaping (from security_functions.php)

## Modernization Checklist

### Required Changes for Each File

1. **Add strict types declaration**
   ```php
   <?php
   
   declare(strict_types=1);
   ```

2. **Include modern helpers**
   ```php
   require_once __DIR__ . '/../staffcp_modern.php';
   ```

3. **Replace authentication**
   ```php
   // Old
   checkStaffAuthentication();
   
   // New (optional, more secure)
   $staffMember = checkStaffAuthenticationModern();
   ```

4. **Replace language loading**
   ```php
   // Old
   $Language = file("languages/" . getStaffLanguage() . "/filename.lang");
   
   // New
   $Language = loadStaffLanguage('filename');
   ```

5. **Replace database queries**
   ```php
   // Old - vulnerable to SQL injection
   mysqli_query($GLOBALS["DatabaseConnect"], 
       "SELECT * FROM users WHERE id = '$userid'");
   
   // New - prepared statement
   $result = $TSDatabase->query(
       'SELECT * FROM users WHERE id = ?',
       [$userid]
   );
   ```

6. **Replace INSERT/UPDATE queries**
   ```php
   // Old
   mysqli_query($GLOBALS["DatabaseConnect"], 
       "INSERT INTO table (col1, col2) VALUES ('$val1', '$val2')");
   $id = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
   
   // New
   $TSDatabase->query(
       'INSERT INTO table (col1, col2) VALUES (?, ?)',
       [$val1, $val2]
   );
   $id = $TSDatabase->lastInsertId();
   ```

7. **Add CSRF protection**
   ```php
   // In form HTML
   <?php echo getFormTokenField(); ?>
   
   // On form processing
   if (!validateFormToken($_POST['form_token'] ?? '')) {
       $Message = showAlertError('Invalid form token');
   }
   ```

8. **Add output escaping**
   ```php
   // Old - vulnerable to XSS
   echo "<div>" . $username . "</div>";
   echo "<input value=\"" . $email . "\">";
   
   // New - safe
   echo "<div>" . escape_html($username) . "</div>";
   echo '<input value="' . escape_attr($email) . '">';
   ```

9. **Replace HTML attribute syntax**
   ```php
   // Old (causes parse errors in PHP 8)
   echo "<input $type = \"text\" $name = \"username\">";
   
   // New
   echo '<input type="text" name="username">';
   ```

10. **Add input validation**
    ```php
    // Validate email
    if (!validateEmail($email)) {
        $Message = showAlertError('Invalid email');
    }
    
    // Validate username
    if (!validateUsername($username)) {
        $Message = showAlertError('Invalid username');
    }
    ```

11. **Replace helper functions**
    ```php
    // Old
    showAlertError($message);
    logStaffAction($message);
    
    // New (with XSS protection)
    showAlertErrorModern($message);
    logStaffActionModern($message);
    ```

## Example Conversion

### Before (add_user.php - old)
```php
<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/add_user.lang");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $query = mysqli_query($GLOBALS["DatabaseConnect"], 
        "SELECT id FROM users WHERE username = '" . 
        mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'");
    
    if (mysqli_num_rows($query) > 0) {
        $Message = showAlertError($Language[1]);
    }
}

echo "<input $type = \"text\" $name = \"username\" $value = \"" . 
     htmlspecialchars($username) . "\">";
```

### After (add_user.php - modern)
```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthentication();
$Language = loadStaffLanguage('add_user');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateFormToken($_POST['form_token'] ?? '')) {
        $Message = showAlertErrorModern('Invalid form token');
    }
    else {
        $username = trim($_POST['username'] ?? '');
        
        $result = $TSDatabase->query(
            'SELECT id FROM users WHERE username = ? LIMIT 1',
            [$username]
        );
        
        if ($result && $result->fetch()) {
            $Message = showAlertErrorModern($Language[1]);
        }
    }
}

echo '<input type="text" name="username" value="' . 
     escape_attr($username) . '">';
```

## Priority Files for Modernization

### Critical (User Management)
1. ✅ add_user.php - COMPLETE
2. edit_user.php
3. delete_user.php
4. banned_users.php
5. manage_inactive_users.php

### High Priority (Content Management)
6. manage_settings.php
7. manage_torrents.php
8. manage_category.php
9. manage_news.php
10. manage_announcements.php

### Medium Priority (Reports & Logs)
11. manage_reports.php
12. show_logs.php
13. tracker_logs.php
14. cheat_attempts.php
15. announce_actions.php

### Lower Priority (Utilities)
16. cache.php
17. calculator.php
18. server_info.php
19. stats.php
20. version_check.php

## Testing Checklist

For each modernized file:

- [ ] PHP syntax check: `php -l filename.php`
- [ ] No mysqli_ calls remaining
- [ ] All output escaped
- [ ] CSRF tokens added to forms
- [ ] Input validation added
- [ ] Error handling in place
- [ ] Test form submission
- [ ] Test error cases
- [ ] Test with actual database

## Common Issues

### Issue: undefined $TSDatabase
**Solution**: Ensure global.php loads the database properly and TSDatabase is available

### Issue: Headers already sent
**Solution**: Make sure strict_types declaration is immediately after <?php with no whitespace before

### Issue: Method not found on TSDatabase
**Solution**: Use correct method names: query(), lastInsertId(), beginTransaction()

### Issue: Form token validation fails
**Solution**: Ensure session is started and form token is in $_POST

## Performance Considerations

- Prepared statements are cached and reused
- PDO is generally faster than mysqli
- Output escaping has minimal overhead
- CSRF tokens require session storage

## Security Benefits

1. **SQL Injection**: Eliminated via prepared statements
2. **XSS**: Eliminated via output escaping
3. **CSRF**: Protected via form tokens
4. **Input Validation**: Enforced at entry points
5. **Error Handling**: No information disclosure

## Status

- **Completed**: 1/108 files (add_user.php)
- **In Progress**: Phase 2 - more critical files
- **Remaining**: 107 files to modernize
