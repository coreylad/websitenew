<?php
// DEAD CODE: Temporary utility script for deobfuscation. Not part of application runtime.
// Blanket deobfuscation script for TSSE V8.0 codebase
// Uses mapping from deobfuscation_progress.md to replace all obfuscated names in all PHP files

$mappingFile = __DIR__ . '/deobfuscation_progress.md';
$rootDir = __DIR__;

function getMapping($mappingFile) {
    $mapping = [];
    $lines = file($mappingFile);
    foreach ($lines as $line) {
        if (preg_match('/\|\s*(_?obfuscated_[^|]+)\s*\|\s*([^|]+)\s*\|/', $line, $matches)) {
            $mapping[$matches[1]] = trim($matches[2]);
        }
    }
    return $mapping;
}

function replaceInFile($file, $mapping) {
    $content = file_get_contents($file);
    $original = $content;
    foreach ($mapping as $obf => $clear) {
        // Replace variable, function, and class names
        $content = preg_replace('/(?<![a-zA-Z0-9_])' . preg_quote($obf, '/') . '(?![a-zA-Z0-9_])/', $clear, $content);
    }
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Deobfuscated: $file\n";
    }
}

function processDirectory($dir, $mapping) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($rii as $file) {
        if ($file->isDir()) continue;
        if (preg_match('/\.php$/i', $file->getFilename())) {
            replaceInFile($file->getPathname(), $mapping);
        }
    }
}

$mapping = getMapping($mappingFile);
processDirectory($rootDir, $mapping);
echo "\nDeobfuscation complete.\n";
