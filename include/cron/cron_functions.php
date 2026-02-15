<?php
if (!defined("IN_CRON")) {
    exit;
}
function SaveLog($Text)
{
    global $TSDatabase;
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO sitelog VALUES (NULL, NOW(), " . sqlesc($Text) . ")");
}
function TSRowCount($C, $T, $E = "")
{
    global $TSDatabase;
    $R = mysqli_fetch_row(mysqli_query($GLOBALS["DatabaseConnect"], "SELECT COUNT(" . $C . ") FROM " . $T . ($E ? " WHERE " . $E : "")));
    return $R[0];
}
/**
 * Format bytes into human-readable size with petabyte support
 * Fixed bugs: proper logic and division by zero
 * Supports: Bytes, KB, MB, GB, TB, PB, EB
 * 
 * @param int|float $bytes Size in bytes
 * @return string Formatted size string
 */
function mksize($bytes)
{
    $bytes = max(0, $bytes); // Ensure non-negative
    
    // Bytes (< 1 KB)
    if ($bytes < 1024) {
        return number_format($bytes, 2) . " B";
    }
    
    // Kilobytes (< 1000 KB = 1,024,000 bytes)
    if ($bytes < 1024000) {
        return number_format($bytes / 1024, 2) . " KB";
    }
    
    // Megabytes (< 1000 MB = 1,048,576,000 bytes)
    if ($bytes < 1048576000) {
        return number_format($bytes / 1048576, 2) . " MB";
    }
    
    // Gigabytes (< 1000 GB = 1,073,741,824,000 bytes)
    if ($bytes < 1073741824000) {
        return number_format($bytes / 1073741824, 2) . " GB";
    }
    
    // Terabytes (< 1000 TB = 1,099,511,627,776,000 bytes)
    if ($bytes < 1099511627776000) {
        return number_format($bytes / 1099511627776, 2) . " TB";
    }
    
    // Petabytes (< 1000 PB = 1,125,899,906,842,624,000 bytes)
    if ($bytes < 1125899906842624000) {
        return number_format($bytes / 1125899906842624, 2) . " PB";
    }
    
    // Exabytes (anything larger)
    return number_format($bytes / 1152921504606846976, 2) . " EB";
}
function sqlesc($value)
{
    global $TSDatabase;
    if (@get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
    return "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $value) . "'";
}
function LogCronAction($filename, $querycount, $executetime)
{
    global $TSDatabase;
    mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO ts_cron_log (filename, querycount, executetime, runtime) VALUES ('" . $filename . "', '" . $querycount . "', '" . $executetime . "', '" . TIMENOW . "')");
}
function deadtime()
{
    global $announce_interval;
    return TIMENOW - floor($announce_interval * 0);
}
function write_log($Text)
{
    global $TSDatabase;
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO sitelog VALUES (NULL, NOW(), " . sqlesc($Text) . ")");
}

?>