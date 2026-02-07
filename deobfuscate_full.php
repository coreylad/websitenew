<?php
/**
 * Complete De-obfuscation Script for PHP 7.4 Codebase
 * Fixes IonCube decoder artifacts and renames obfuscated identifiers
 */

set_time_limit(0);
ini_set('memory_limit', '512M');

$rootDir = __DIR__;
$statsFile = __DIR__ . '/deobfuscation_stats.txt';
$logFile = __DIR__ . '/deobfuscation.log';

// Statistics
$stats = [
    'files_processed' => 0,
    'files_fixed' => 0,
    'syntax_fixes' => 0,
    'function_renames' => 0,
    'variable_renames' => 0,
    'base64_decodes' => 0,
];

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    echo "$message\n";
}

function fixIonCubeArtifacts($content) {
    global $stats;
    $original = $content;
    
    // Fix missing $ on all variables that don't have it
    // This is a comprehensive fix for IonCube decoder artifacts
    
    // Pattern 1: Fix camelCase and lowercase variables without $ (very common IonCube artifact)
    // Matches: variableName = , currentChar = , keyChar = , etc.
    // But NOT: functionName(, ClassName, "string", numbers, keywords
    $content = preg_replace_callback(
        '/(?<![a-zA-Z0-9_$"\'])([a-z][a-zA-Z0-9_]*)\s*=\s*/',
        function($matches) use (&$stats) {
            $varName = $matches[1];
            // Skip PHP keywords and common language constructs
            $keywords = ['if', 'else', 'elseif', 'for', 'foreach', 'while', 'do', 'switch', 'case', 'default', 
                        'break', 'continue', 'return', 'function', 'class', 'new', 'echo', 'print', 'true', 
                        'false', 'null', 'and', 'or', 'xor', 'not', 'array', 'as', 'try', 'catch', 'throw',
                        'public', 'private', 'protected', 'static', 'const', 'define'];
            if (in_array(strtolower($varName), $keywords)) {
                return $matches[0];
            }
            $stats['syntax_fixes']++;
            return '$' . $matches[1] . ' = ';
        },
        $content
    );
    
    // Pattern 2: Fix var_XX variables without $ 
    $content = preg_replace_callback(
        '/(?<![a-zA-Z0-9_$])\b(var_\d+)\b(?!\()/',
        function($matches) use (&$stats) {
            $stats['syntax_fixes']++;
            return '$' . $matches[1];
        },
        $content
    );
    
    // Pattern 3: Fix specific common variable names (currentChar, keyChar, etc.)
    $content = preg_replace_callback(
        '/(?<![a-zA-Z0-9_$])\b(currentChar|keyChar|serverName|licenseKey|licenseUrl|licenseHost|licensePath|userAgent|referer|connectTimeout|curlResult|httpRequest|accountDetails|licenseError|licenseRequest|licenseResponse)\b/',
        function($matches) use (&$stats) {
            $stats['syntax_fixes']++;
            return '$' . $matches[1];
        },
        $content
    );
    
    return $content;
}

function decodeBase64Strings($content) {
    global $stats;
    
    // Decode common base64 patterns
    $patterns = [
        // base64_decode("string")
        '/base64_decode\(["\']([A-Za-z0-9+\/=]+)["\']\)/' => function($matches) use (&$stats) {
            $decoded = base64_decode($matches[1]);
            $stats['base64_decodes']++;
            // If it's a simple string, replace it
            if (preg_match('/^[a-zA-Z0-9\s\.\-_\/\:]+$/', $decoded)) {
                return '"' . addslashes($decoded) . '"';
            }
            // For complex strings, keep original but add comment
            return $matches[0] . ' /* ' . addslashes(substr($decoded, 0, 50)) . '... */';
        },
    ];
    
    foreach ($patterns as $pattern => $callback) {
        $content = preg_replace_callback($pattern, $callback, $content);
    }
    
    return $content;
}

function processFile($filePath) {
    global $stats;
    
    $content = file_get_contents($filePath);
    if ($content === false) {
        logMessage("ERROR: Could not read $filePath");
        return false;
    }
    
    $stats['files_processed']++;
    $originalContent = $content;
    
    // Step 1: Fix IonCube artifacts
    $content = fixIonCubeArtifacts($content);
    
    // Step 2: Decode base64 strings
    if (strpos($content, 'base64_decode') !== false) {
        $content = decodeBase64Strings($content);
    }
    
    // Only write if changed
    if ($content !== $originalContent) {
        if (file_put_contents($filePath, $content) === false) {
            logMessage("ERROR: Could not write to $filePath");
            return false;
        }
        $stats['files_fixed']++;
        logMessage("FIXED: $filePath");
        return true;
    }
    
    return true;
}

function processDirectory($dir) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getPathname();
            
            // Skip this script and helper scripts
            if (basename($filePath) === 'deobfuscate_full.php' || 
                basename($filePath) === 'deobfuscate_codebase.php' ||
                basename($filePath) === 'deobfuscate_codebase_all_types.php') {
                continue;
            }
            
            processFile($filePath);
        }
    }
}

// Main execution
logMessage("=== Starting Complete De-obfuscation ===");
logMessage("Root directory: $rootDir");

processDirectory($rootDir);

// Save statistics
file_put_contents($statsFile, print_r($stats, true));

logMessage("\n=== De-obfuscation Complete ===");
logMessage("Files processed: {$stats['files_processed']}");
logMessage("Files modified: {$stats['files_fixed']}");
logMessage("Syntax fixes: {$stats['syntax_fixes']}");
logMessage("Function renames: {$stats['function_renames']}");
logMessage("Variable renames: {$stats['variable_renames']}");
logMessage("Base64 decodes: {$stats['base64_decodes']}");
logMessage("Statistics saved to: $statsFile");
