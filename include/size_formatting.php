<?php

declare(strict_types=1);

/**
 * Modern file size formatting functions for PHP 8.5+
 * Supports from Bytes up to Exabytes with proper type safety
 */

/**
 * Format bytes into human-readable size with proper unit support
 * Supports: Bytes, KB, MB, GB, TB, PB, EB
 * 
 * @param int|float $bytes Size in bytes
 * @param int $precision Number of decimal places
 * @param bool $binary Use binary (1024) or decimal (1000) units
 * @return string Formatted size string
 */
function mksize_modern(int|float $bytes, int $precision = 2, bool $binary = true): string
{
    if ($bytes < 0) {
        return '0 B';
    }
    
    $base = $binary ? 1024 : 1000;
    $units = $binary 
        ? ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB']
        : ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    
    if ($bytes == 0) {
        return '0 ' . $units[0];
    }
    
    $exp = (int)floor(log($bytes) / log($base));
    $exp = min($exp, count($units) - 1);
    
    $size = $bytes / pow($base, $exp);
    
    return number_format($size, $precision) . ' ' . $units[$exp];
}

/**
 * Legacy-compatible mksize function with petabyte support
 * Maintains backward compatibility while fixing bugs
 * 
 * @param int|float $bytes Size in bytes
 * @return string Formatted size string
 */
function mksize_legacy_compatible(int|float $bytes): string
{
    $bytes = max(0, $bytes); // Ensure non-negative
    
    // Bytes
    if ($bytes < 1024) {
        return number_format($bytes, 2) . ' B';
    }
    
    // Kilobytes (< 1000 KB = 1,024,000 bytes)
    if ($bytes < 1024000) {
        return number_format($bytes / 1024, 2) . ' KB';
    }
    
    // Megabytes (< 1000 MB = 1,048,576,000 bytes)
    if ($bytes < 1048576000) {
        return number_format($bytes / 1048576, 2) . ' MB';
    }
    
    // Gigabytes (< 1000 GB = 1,073,741,824,000 bytes)
    if ($bytes < 1073741824000) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    }
    
    // Terabytes (< 1000 TB = 1,099,511,627,776,000 bytes)
    if ($bytes < 1099511627776000) {
        return number_format($bytes / 1099511627776, 2) . ' TB';
    }
    
    // Petabytes (< 1000 PB = 1,125,899,906,842,624,000 bytes)
    if ($bytes < 1125899906842624000) {
        return number_format($bytes / 1125899906842624, 2) . ' PB';
    }
    
    // Exabytes (anything larger)
    return number_format($bytes / 1152921504606846976, 2) . ' EB';
}

/**
 * Parse human-readable size string to bytes
 * Supports: B, KB, MB, GB, TB, PB, EB
 * 
 * @param string $size Size string (e.g., "5.2 GB", "100MB")
 * @return int Size in bytes
 */
function parse_size_to_bytes(string $size): int
{
    $size = trim($size);
    $unit = strtoupper(substr($size, -2));
    $value = (float)$size;
    
    $units = [
        'B'  => 1,
        'KB' => 1024,
        'MB' => 1048576,
        'GB' => 1073741824,
        'TB' => 1099511627776,
        'PB' => 1125899906842624,
        'EB' => 1152921504606846976
    ];
    
    // Check for single letter units (K, M, G, T, P, E)
    if (!isset($units[$unit])) {
        $unit = strtoupper(substr($size, -1));
        if ($unit === 'K') $unit = 'KB';
        elseif ($unit === 'M') $unit = 'MB';
        elseif ($unit === 'G') $unit = 'GB';
        elseif ($unit === 'T') $unit = 'TB';
        elseif ($unit === 'P') $unit = 'PB';
        elseif ($unit === 'E') $unit = 'EB';
    }
    
    $multiplier = $units[$unit] ?? 1;
    return (int)($value * $multiplier);
}

/**
 * Format transfer speed (bytes per second)
 * 
 * @param int|float $bytesPerSecond Speed in bytes per second
 * @param int $precision Number of decimal places
 * @return string Formatted speed string
 */
function format_speed(int|float $bytesPerSecond, int $precision = 2): string
{
    return mksize_modern($bytesPerSecond, $precision) . '/s';
}

/**
 * Calculate percentage of storage used
 * 
 * @param int|float $used Used storage in bytes
 * @param int|float $total Total storage in bytes
 * @param int $precision Number of decimal places
 * @return string Percentage string
 */
function calculate_storage_percentage(int|float $used, int|float $total, int $precision = 2): string
{
    if ($total <= 0) {
        return '0.00%';
    }
    
    $percentage = ($used / $total) * 100;
    return number_format($percentage, $precision) . '%';
}
