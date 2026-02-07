# Dead Code Analysis Summary

This document summarizes all dead code identified in the PHP 7.4 legacy codebase.

**Analysis Date:** 2026-02-07  
**Scope:** Entire workspace  
**Method:** Static analysis - no code deletion, only annotation

---

## 1. Legacy TSSE/TemplateShares Code

### 1.1 License Validation System (staffcp/check_tool.php)

**Lines 13-43:** `Class_4` encryption class
- **Status:** DEAD - Only instantiated in unused `function_72()`
- **Purpose:** XOR cipher for encrypting license data
- **Reason:** Part of abandoned license validation system

**Lines 53-84:** `function_65()` - IP detection
- **Status:** DEAD - Only called by `function_67()` to define unused `INSTALL_IP` constant
- **Purpose:** Attempts to fetch server IP from templateshares.biz
- **Reason:** Legacy remote endpoint likely defunct

**Lines 88-91:** `function_66()` - IP validator
- **Status:** DEAD - Only called by unused `function_65()`

**Lines 100-113:** `function_68()` - License file validator
- **Status:** DEAD - Never called
- **Purpose:** Checks for license.php files and validates ADMIN_CACHE

**Lines 119-135:** `function_70()` - License response parser
- **Status:** DEAD - Never called
- **Purpose:** Parses license key response and validates MD5 hash

**Lines 137-177:** `function_72()` - Remote license check
- **Status:** DEAD - Never called
- **Purpose:** Makes HTTP POST to templateshares.info for license validation
- **Reason:** Domain likely defunct, entire validation system abandoned

**Lines 178-186:** `function_71()` - License key format validator
- **Status:** DEAD - Only called by unused `function_70()`

**Lines 193-196:** `function_74()` - URL sanitizer
- **Status:** DEAD - Never called

**Lines 197-200:** `function_73()` - Encryption wrapper
- **Status:** DEAD - Only called by unused `function_72()`

### 1.2 Remote Endpoint Functions

**staffcp/tools/version_check.php (Lines 61-77):** `function_269()`
- **Status:** DEAD - Attempts to fetch from templateshares.info/versioncheck/version.txt
- **Reason:** Remote endpoint unreachable

**staffcp/tools/latest_news.php (Lines 46-79):** `function_268()`
- **Status:** DEAD - Attempts to fetch from templateshares.info/tsnews/tsnews.txt
- **Reason:** Remote endpoint unreachable

### 1.3 Unused TSSE Constants

**staffcp/index.php (Line 212):** `TSSE2020CHECKTOOLPHP` check
- **Status:** DEAD - Constant only used for validation, never referenced elsewhere
- **Defined in:** staffcp/check_tool.php:95
- **Purpose:** Validates check_tool.php was included

---

## 2. Unused Function Files (include/)

**include/functions_find_post.php** (All)
- **Status:** DEAD - Entire file never included/required
- **Function:** `find_post($pid)` - Forum post URL resolver
- **Line 12:** Function defined but never called

**include/functions_resolve_url.php** (All)
- **Status:** DEAD - Entire file never included/required
- **Functions:** 
  - `resolve_request_url()` - URL parser
  - `fetch_server_value()` - Server variable helper
  - `strip_sessionhash()` - Session ID stripper

**include/functions_navigation.php** (All)
- **Status:** DEAD - Entire file never included/required
- **Functions:**
  - `TS_fetch_start_end_total_array()` - Pagination helper
  - `ts_number_format()` - Number formatter
  - `TS_construct_page_nav()` - Page navigation builder
  - `sanitize_maxposts()` - Max posts sanitizer

---

## 3. Unused Functions in Tools

**staffcp/tools/restore_database.php (Line 140):** `function_319()`
- **Status:** DEAD - Never called
- **Purpose:** Generates backup filename with timestamp

**staffcp/tools/plugins.php (Line 175):** `function_316()`
- **Status:** DEAD - Never called
- **Purpose:** Formats plugin positions for JSON output

**staffcp/tools/themes.php (Line 360):** `function_321($inXmlset, $needle)`
- **Status:** DEAD - Never called
- **Purpose:** XML parser to extract values by tag name

---

## 4. Temporary Utility Scripts

All marked as dead code - not part of application runtime:

1. **deobfuscate_full.php**
   - Lines 19-20: Unused stat counters (`function_renames`, `variable_renames`)
   
2. **deobfuscate_codebase_all_types.php**
   - Lines 27-32: `generateName()` function never called

3. **deobfuscate_codebase.php**
   - Entire script is temporary utility

4. **analyze_functions.php**
   - Line 16: `analyzeFunctionPurpose()` defined but `getAllFiles()` commented as dead

5. **rename_functions.php**
   - Entire script is temporary utility

6. **fix_review_issues.php**
   - Entire script is temporary utility

---

## 5. Unreachable Code Patterns

### 5.1 Always-True Conditions

**staffcp/tools/manage_polls.php (Lines 113-119)**
```php
if (true) {
    $show["additional_option1"] = $pollinfo["numberoptions"] < 10;
    $show["additional_option2"] = $pollinfo["numberoptions"] < 9;
} else {
    // DEAD CODE: This else block never executes
    $show["additional_option1"] = true;
    $show["additional_option2"] = true;
}
```

### 5.2 Empty Code Blocks

**usercp.php (Lines 298-299)**
```php
if (true) {
    // DEAD CODE: Empty block, leftover from deobfuscation
}
```

---

## Summary Statistics

| Category | Count | Files Affected |
|----------|-------|----------------|
| Entire files never included | 3 | include/ |
| Unused functions | 15 | staffcp/check_tool.php, tools/ |
| Temporary utility scripts | 6 | Root directory |
| Unreachable code blocks | 2 | manage_polls.php, usercp.php |
| Dead constants | 1 | TSSE2020CHECKTOOLPHP |
| Legacy remote endpoints | 2 | version_check, latest_news |

---

## Recommendations

1. **Remove unused include files:** The 3 files in include/ can be safely deleted
2. **Remove legacy TSSE license system:** All functions in check_tool.php marked as dead
3. **Remove temporary scripts:** All 6 deobfuscation/utility scripts can be deleted
4. **Fix unreachable code:** Remove always-true conditions and empty blocks
5. **Remove unused tool functions:** Clean up function_316, function_319, function_321

---

## Notes

- All annotations use format: `// DEAD CODE: reason`
- No code was deleted - only annotated per requirements
- No runtime behavior changes
- All dead code is clearly marked for future cleanup
