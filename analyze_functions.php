<?php
/**
 * Function Analysis Script
 * Analyzes obfuscated function_XX to determine their purpose and suggest descriptive names
 */

set_time_limit(0);
ini_set('memory_limit', '512M');

$rootDir = __DIR__;
$outputFile = __DIR__ . '/function_mappings.txt';

// Store function analysis
$functionAnalysis = [];

function analyzeFunctionPurpose($functionName, $filePath, $functionBody) {
    $suggestions = [];
    
    // Analyze based on content patterns
    if (strpos($functionBody, 'mysqli_query') !== false) {
        $suggestions[] = 'database';
    }
    if (strpos($functionBody, 'SELECT') !== false) {
        $suggestions[] = 'query';
    }
    if (strpos($functionBody, 'INSERT') !== false || strpos($functionBody, 'UPDATE') !== false) {
        $suggestions[] = 'save';
    }
    if (strpos($functionBody, 'header(') !== false && strpos($functionBody, 'Location:') !== false) {
        $suggestions[] = 'redirect';
    }
    if (strpos($functionBody, 'return') !== false && strpos($functionBody, 'mysqli_real_escape_string') !== false) {
        $suggestions[] = 'sanitize';
    }
    if (strpos($functionBody, 'md5') !== false || strpos($functionBody, 'sha1') !== false) {
        $suggestions[] = 'hash';
    }
    if (strpos($functionBody, 'mail(') !== false) {
        $suggestions[] = 'email';
    }
    if (strpos($functionBody, '<!DOCTYPE') !== false || strpos($functionBody, '<html') !== false) {
        $suggestions[] = 'html';
    }
    if (strpos($functionBody, 'json_encode') !== false) {
        $suggestions[] = 'json';
    }
    if (strpos($functionBody, '$_COOKIE') !== false) {
        $suggestions[] = 'cookie';
    }
    if (strpos($functionBody, '$_SESSION') !== false) {
        $suggestions[] = 'session';
    }
    if (preg_match('/return\s+["\']/', $functionBody)) {
        $suggestions[] = 'string';
    }
    if (strpos($functionBody, 'echo') !== false || strpos($functionBody, 'print') !== false) {
        $suggestions[] = 'output';
    }
    
    // Special patterns
    if (strpos($functionBody, 'staffcplanguage') !== false) {
        return 'getStaffLanguage';
    }
    if (strpos($functionBody, 'timezone') !== false) {
        return 'setTimezone';
    }
    if (preg_match('/function\s+' . preg_quote($functionName) . '\s*\(\s*\$url\s*\)/', $functionBody) && 
        strpos($functionBody, 'header') !== false) {
        return 'redirectTo';
    }
    
    return implode('_', $suggestions) ?: 'utility';
}

function extractFunctions($filePath) {
    global $functionAnalysis;
    
    $content = file_get_contents($filePath);
    if ($content === false) {
        return;
    }
    
    // Find all function_XX definitions
    preg_match_all('/function\s+(function_\d+)\s*\([^)]*\)\s*\{/s', $content, $matches, PREG_OFFSET_CAPTURE);
    
    foreach ($matches[1] as $idx => $match) {
        $functionName = $match[0];
        $startPos = $match[1];
        
        // Extract function body (simple approach - find matching brace)
        $braceCount = 0;
        $inFunction = false;
        $functionBody = '';
        $bodyStart = strpos($content, '{', $startPos);
        
        for ($i = $bodyStart; $i < strlen($content) && $i < $bodyStart + 2000; $i++) {
            $char = $content[$i];
            if ($char === '{') {
                $braceCount++;
                $inFunction = true;
            } elseif ($char === '}') {
                $braceCount--;
                if ($braceCount === 0 && $inFunction) {
                    $functionBody = substr($content, $bodyStart, $i - $bodyStart + 1);
                    break;
                }
            }
        }
        
        $purpose = analyzeFunctionPurpose($functionName, $filePath, $functionBody);
        
        if (!isset($functionAnalysis[$functionName])) {
            $functionAnalysis[$functionName] = [
                'count' => 0,
                'files' => [],
                'purpose' => $purpose,
                'sample_body' => substr($functionBody, 0, 200)
            ];
        }
        
        $functionAnalysis[$functionName]['count']++;
        $functionAnalysis[$functionName]['files'][] = basename($filePath);
    }
}

function processDirectory($dir) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            extractFunctions($file->getPathname());
        }
    }
}

echo "Analyzing functions...\n";
processDirectory($rootDir);

// Sort by count (most common first)
uasort($functionAnalysis, function($a, $b) {
    return $b['count'] - $a['count'];
});

// Write output
$output = "FUNCTION ANALYSIS AND SUGGESTED MAPPINGS\n";
$output .= "========================================\n\n";

foreach ($functionAnalysis as $funcName => $data) {
    $output .= sprintf("%-20s | Count: %-4d | Suggested: %-30s\n", 
        $funcName, 
        $data['count'], 
        $data['purpose']
    );
    $output .= "  Files: " . implode(', ', array_unique(array_slice($data['files'], 0, 5))) . 
               ($data['count'] > 5 ? ' ...' : '') . "\n";
    $output .= "  Sample: " . substr(str_replace(["\n", "\r", "\t"], ' ', $data['sample_body']), 0, 100) . "...\n\n";
}

file_put_contents($outputFile, $output);

echo "\nAnalysis complete!\n";
echo "Total unique functions: " . count($functionAnalysis) . "\n";
echo "Output saved to: $outputFile\n";

// Show top 20
echo "\nTop 20 most common functions:\n";
$count = 0;
foreach ($functionAnalysis as $funcName => $data) {
    if ($count++ >= 20) break;
    echo sprintf("  %-20s (%3d times) -> %s\n", $funcName, $data['count'], $data['purpose']);
}
