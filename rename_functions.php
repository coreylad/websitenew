<?php
/**
 * Function Renaming Script - Phase 1
 * Renames the most common obfuscated functions (10+ occurrences)
 * 
 * DEAD CODE: This is a temporary utility script for renaming obfuscated functions.
 * Not part of the application runtime. Can be removed after renaming is complete.
 */

set_time_limit(0);
ini_set('memory_limit', '512M');

$rootDir = __DIR__;

// Manual mappings based on analysis (top functions that appear 10+ times)
$functionMappings = [
    // Top 20 functions - manually analyzed
    'function_75' => 'getStaffLanguage',
    'function_76' => 'showAlertError',
    'function_77' => 'checkStaffAuthentication', 
    'function_78' => 'redirectTo',
    'function_79' => 'logStaffAction',
    'function_81' => 'showAlertMessage',
    'function_84' => 'formatTimestamp',
    'function_88' => 'formatBytes',
    'function_83' => 'applyUsernameStyle',
    'function_86' => 'validatePerPage',
    'function_87' => 'calculatePagination',
    'function_82' => 'buildPaginationLinks',
    'function_90' => 'loadTinyMCEEditor',
    'function_80' => 'sendPrivateMessage',
    'function_169' => 'getBencodeEnd',
    
    // Additional common patterns
    'function_102' => 'generateSecret',  // Common in password/hash functions
    'function_16' => 'buildDashboard',   // From staffcp/index.php analysis
    'function_15' => 'showFatalError',   // From staffcp/index.php analysis  
    'function_29' => 'getStaffLanguage', // Duplicate of 75 in different context
    'function_30' => 'setDefaultTimezone',
    'function_31' => 'loadEditor',
    'function_32' => 'outputHTMLHeader',
    'function_33' => 'outputHTMLFooter',
    'function_34' => 'outputCopyrightFooter',
    'function_35' => 'redirectTo',  // Duplicate of 78 in different context
    'function_36' => 'showAlert',
    'function_37' => 'checkBrandingLicense',
];

$stats = [
    'files_processed' => 0,
    'files_modified' => 0,
    'renames' => 0,
];

function renameInFile($filePath, $mappings) {
    global $stats;
    
    $content = file_get_contents($filePath);
    if ($content === false) {
        return false;
    }
    
    $originalContent = $content;
    $stats['files_processed']++;
    
    foreach ($mappings as $oldName => $newName) {
        // Replace function definitions: function oldName( -> function newName(
        $pattern = '/\bfunction\s+' . preg_quote($oldName, '/') . '\s*\(/';
        $replacement = 'function ' . $newName . '(';
        $content = preg_replace($pattern, $replacement, $content, -1, $count);
        $stats['renames'] += $count;
        
        // Replace function calls: oldName( -> newName(
        $pattern = '/\b' . preg_quote($oldName, '/') . '\s*\(/';
        $replacement = $newName . '(';
        $content = preg_replace($pattern, $replacement, $content, -1, $count);
        $stats['renames'] += $count;
    }
    
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        $stats['files_modified']++;
        return true;
    }
    
    return false;
}

function processDirectory($dir, $mappings) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    $count = 0;
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getPathname();
            
            // Skip helper scripts
            if (strpos($filePath, 'deobfuscate') !== false || 
                strpos($filePath, 'analyze_functions') !== false) {
                continue;
            }
            
            if (renameInFile($filePath, $mappings)) {
                $count++;
                if ($count % 50 == 0) {
                    echo "Processed $count files...\n";
                }
            }
        }
    }
}

echo "=== Starting Function Renaming (Phase 1) ===\n";
echo "Renaming " . count($functionMappings) . " common functions\n\n";

processDirectory($rootDir, $functionMappings);

echo "\n=== Renaming Complete ===\n";
echo "Files processed: {$stats['files_processed']}\n";
echo "Files modified: {$stats['files_modified']}\n";
echo "Total renames: {$stats['renames']}\n";
