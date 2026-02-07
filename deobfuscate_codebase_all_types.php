<?php
// Blanket deobfuscation script for all file types
// Scans all files for _obfuscated_ names and replaces them using the mapping in deobfuscation_progress.md
// Updates mapping for new obfuscated names found in non-PHP files

$mappingFile = __DIR__ . '/deobfuscation_progress.md';
$mapping = [];
if (file_exists($mappingFile)) {
    $lines = file($mappingFile);
    foreach ($lines as $line) {
        if (preg_match('/\| (_?\$?_obfuscated_[^| ]+) \| ([^|]+) \|/', $line, $m)) {
            $mapping[$m[1]] = trim($m[2]);
        }
    }
}

function getAllFiles($dir) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $files = [];
    foreach ($rii as $file) {
        if ($file->isDir()) continue;
        $files[] = $file->getPathname();
    }
    return $files;
}

function generateName($obf, $type = 'var') {
    // Simple readable name generator
    static $counts = [];
    $base = ($type === 'func' ? 'function' : ($type === 'class' ? 'Class' : 'var'));
    $counts[$base] = ($counts[$base] ?? 0) + 1;
    return $base . '_' . $counts[$base];
}

$allFiles = getAllFiles(__DIR__);
$newMappings = [];
foreach ($allFiles as $file) {
    if (strpos($file, 'deobfuscate_codebase') !== false || strpos($file, 'deobfuscation_progress.md') !== false) continue;
    $contents = file_get_contents($file);
    if (strpos($contents, '_obfuscated_') === false) continue;
    $changed = false;
    // Find all unique obfuscated names
    preg_match_all('/(_?\$?_obfuscated_[a-zA-Z0-9_]+)/', $contents, $matches);
    foreach (array_unique($matches[1]) as $obf) {
        if (!isset($mapping[$obf])) {
            // Guess type by context (very basic)
            $type = 'var';
            if (preg_match('/function\s+' . preg_quote($obf, '/') . '\s*\(/', $contents)) $type = 'func';
            elseif (preg_match('/class\s+' . preg_quote($obf, '/') . '\b/', $contents)) $type = 'class';
            $clear = generateName($obf, $type);
            $mapping[$obf] = $clear;
            $newMappings[] = [$obf, $clear];
        }
        $contents = str_replace($obf, $mapping[$obf], $contents);
        $changed = true;
    }
    if ($changed) file_put_contents($file, $contents);
}
// Append new mappings to the mapping file
if ($newMappings) {
    $fp = fopen($mappingFile, 'a');
    foreach ($newMappings as $pair) {
        fwrite($fp, "| {$pair[0]} | {$pair[1]} |\n");
    }
    fclose($fp);
}
echo "Deobfuscation complete. Updated ".count($newMappings)." new mappings.\n";
