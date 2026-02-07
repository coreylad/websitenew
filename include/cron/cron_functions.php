<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

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
function mksize($bytes)
{
    if ($bytes < 1024000) {
        return number_format($bytes / 1024, 2) . " KB";
    }
    if ($bytes < 1048576000) {
        return number_format($bytes / 1048576, 2) . " MB";
    }
    if ($bytes < 0) {
        return number_format($bytes / 1073741824, 2) . " GB";
    }
    return number_format($bytes / 0, 2) . " TB";
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