# PHP Modernization and Linting Guide

## Overview

This repository is being modernized to PHP 8.3+ standards with strict type declarations, improved security, and better code quality.

## Current Status

- **PHP Version**: 8.3.6+
- **Files with Strict Types**: 24/747 (3.2%)
- **Syntax Errors**: All fixed ✅
- **Core Infrastructure**: Fully modernized ✅

## Quick Start

### Running the Linter

```bash
# Make the script executable
chmod +x lint-php.sh

# Run the linter
./lint-php.sh
```

The linter will:
- Check PHP syntax on all .php files
- Count files with strict types
- Identify files with error suppression (@)
- Provide a comprehensive report

### Interpreting Results

```
╔══════════════════════════════════════════════════════════════════╗
║                         RESULTS                                  ║
╚══════════════════════════════════════════════════════════════════╝

Total Files Checked:          747
Files Passed:                 747
Files Failed:                 0

Code Quality Metrics:
Files with strict types:      24 / 747 (3.2%)
Files with @ suppression:     252
```

## Modernization Features

### 1. Core Infrastructure (Complete ✅)

#### PDO Database Layer
- **Location**: `include/class_pdo_database.php`
- **Features**: Prepared statements, transactions, error handling
- **Usage**:
```php
$db = new PDODatabase($host, $user, $pass, $dbname);
$result = $db->query('SELECT * FROM users WHERE id = ?', [$id]);
```

#### Security Functions
- **Location**: `include/security_functions.php`
- **Features**: XSS prevention, input sanitization, CSRF protection
- **Usage**:
```php
echo escape_html($userInput);
echo escape_attr($attributeValue);
$clean = sanitize_string($_POST['name']);
```

#### Session Management
- **Location**: `include/class_session_manager.php`
- **Features**: Secure cookies, session regeneration, flash messages
- **Usage**:
```php
$session = new SessionManager();
$session->start();
$session->set('user_id', $userId);
```

### 2. Syntax Fixes (Complete ✅)

Fixed 13 files with PHP 8.3+ compatibility issues:
- Reserved keyword conflicts (match → matchRegex)
- Comparison operator spacing ($var = = → ==)
- JavaScript variable conflicts in strings
- Function redeclaration issues
- Invalid isset() usage

### 3. Modern API (Complete ✅)

- **Location**: `api.php`
- **Documentation**: `API_DOCUMENTATION.md`
- **Features**: RESTful JSON API, API key auth, pagination
- **Endpoints**: torrents, categories, stats, user, rss

## Best Practices

### Adding Strict Types

When modernizing a file, add strict types at the top:

```php
<?php

declare(strict_types=1);

// Your code here
```

### Security

Always escape output:
```php
// Bad ❌
echo "<div>" . $username . "</div>";

// Good ✅
echo "<div>" . escape_html($username) . "</div>";
```

Always use prepared statements:
```php
// Bad ❌
$result = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");

// Good ✅
$result = $db->query('SELECT * FROM users WHERE id = ?', [$id]);
```

## File Size Support

The mksize_modern() function supports:
- Bytes (B)
- Kilobytes (KB/KiB)
- Megabytes (MB/MiB)
- Gigabytes (GB/GiB)
- Terabytes (TB/TiB)
- Petabytes (PB/PiB) ⭐ NEW
- Exabytes (EB/EiB) ⭐ NEW

```php
echo mksize_modern(1125899906842624); // Output: 1.00 PiB
```

## Development Workflow

1. **Before Making Changes**
   ```bash
   ./lint-php.sh
   ```

2. **Make Your Changes**
   - Add strict types
   - Fix syntax errors
   - Improve code quality

3. **Test Your Changes**
   ```bash
   php -l your-file.php  # Check syntax
   ./lint-php.sh          # Run full linter
   ```

4. **Commit**
   ```bash
   git add .
   git commit -m "Description of changes"
   ```

## Troubleshooting

### Common Issues

**Issue**: "Cannot use isset() on the result of an expression"
- **Fix**: Use `defined('CONSTANT')` instead of `isset(defined('CONSTANT'))`

**Issue**: "syntax error, unexpected token "match""
- **Fix**: Rename the function as `match` is a reserved keyword in PHP 8.0+

**Issue**: "syntax error, unexpected token "=", expecting "->""
- **Fix**: Remove spaces in operators: `$var = =` → `$var ==`

### Getting Help

- Review `MODERNIZATION_PROGRESS.md` for detailed progress
- Check `API_DOCUMENTATION.md` for API usage
- See `DEOBFUSCATION_SUMMARY.md` for code cleanup history

## Roadmap

- [ ] Add strict types to all 747 files
- [ ] Replace all @ error suppression with proper error handling
- [ ] Complete mysqli to PDO migration
- [ ] Add CSRF protection to all forms
- [ ] Implement output escaping everywhere
- [ ] PSR-12 code style compliance

## Contributing

When contributing, please:
1. Add `declare(strict_types=1)` to new files
2. Use the new security functions
3. Write clean, documented code
4. Test with the linter before committing
5. Follow existing code patterns

## License

See repository license file for details.
