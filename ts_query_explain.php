<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (strtoupper($_SERVER["REQUEST_METHOD"]) != "POST") {
    header("Location: index.php");
    exit;
}
define("TQE_VERSION", "0.7 by xam");
define("SKIP_SHOW_QUERIES", true);
define("THIS_SCRIPT", "ts_query_explain.php");
require "./global.php";
if ($usergroups["cansettingspanel"] != "yes") {
    print_no_permission(true);
}
$memory_usage = " - <b>Memory Usage:</b> " . mksize(memory_get_usage());
if (is_array($_POST["queries"])) {
    $str = "";
    $id = 1;
    $querytime = 0;
    foreach ($_POST["queries"] as $q => $v) {
        $v = explode(",", base64_decode($v));
        $query_explain = explain_query(base64_decode($v[1]));
        $calcTime = @calcTime($v[0]);
        $str .= "\r\n\t\t<table width=\"100%\" align=\"center\" cellspacing=\"0\" cellpadding=\"5\" border=\"0\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td  class=\"thead\" width=\"100%\" align=\"left\"><span style=\"float: right;\"><i>Query Time: " . $calcTime . "</i></span>Query Debug (" . $id . ")</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td  align=\"left\">\r\n\t\t\t\t\t" . $query_explain . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t<br />";
        $id++;
        $querytime += $v[0];
    }
    $phptime = $_POST["totaltime"] - $querytime;
    $percentphp = @number_format($phptime / $_POST["totaltime"] * 100, 2);
    $percentsql = @number_format($querytime / $_POST["totaltime"] * 100, 2);
    $get_included_files = str_replace("\\", "/", get_included_files());
    $included_files = [];
    foreach ($get_included_files as $incfile) {
        $included_files[] = "<strong>" . basename($incfile) . "</strong>";
    }
    sort($included_files);
    $str .= "\r\n\t<table width=\"100%\" align=\"center\" cellspacing=\"0\" cellpadding=\"5\" border=\"0\">\r\n\t\t<tr>\r\n\t\t\t<td  class=\"thead\" width=\"100%\" align=\"left\">System Debug</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td align=\"left\">\r\n\t\t\t\t<b>Generated in</b> " . htmlspecialchars_uni($_POST["totaltime"]) . " seconds (" . $percentphp . "% PHP / " . $percentsql . "% MySQL)<br />\r\n\t\t\t\t<b>MySQL Queries:</b> " . ($id - 1) . " / <b>Global Parsing Time:</b> " . $querytime . $memory_usage . "<br />\r\n\t\t\t\t<b>PHP version:</b> " . phpversion() . " / <b>Server Load:</b> " . server_load() . " / <b>GZip Compression:</b> " . ($gzipcompress == "yes" ? "Enabled" : "Disabled") . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t<br />\r\n\t<table width=\"100%\" align=\"center\" cellspacing=\"0\" cellpadding=\"5\" border=\"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" width=\"100%\" align=\"left\">Included Files (" . ts_nf(count($included_files)) . ")</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td align=\"left\">\r\n\t\t\t\t" . implode(", ", $included_files) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t</table>";
    if ($UseMemcached) {
        $str .= "\r\n\t\t<br />\r\n\t\t<table width=\"100%\" align=\"center\" cellspacing=\"0\" cellpadding=\"5\" border=\"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" width=\"100%\" align=\"left\" colspan=\"2\">Memcached Status</td>\r\n\t\t</tr>\r\n\t\t\t" . printDetails($TSMemcache->stats()) . "\r\n\t\t</table>";
    }
    stdhead("DEBUG MODE");
    echo $str;
} else {
    echo "There is no query to show..";
}
stdfoot();
function server_load()
{
    if (strtolower(substr(PHP_OS, 0, 3)) === "win") {
        if (class_exists("COM")) {
            $wmi = new COM("WinMgmts:\\\\.");
            $cpus = $wmi->InstancesOf("Win32_Processor");
            $cpuload = 0;
            $i = 0;
            foreach ($cpus as $cpu) {
                $cpuload += $cpu->LoadPercentage;
                $i++;
            }
            $cpuload = round($cpuload / $i, 2);
            return $cpuload . "%";
        } else {
            return "Unknown";
        }
    } else {
        if (file_exists("/proc/loadavg") && ($load = file_get_contents("/proc/loadavg"))) {
            $load = explode(" ", $load);
            return $load[0] . " - " . $load[1] . " - " . $load[2];
        }
        if (function_exists("exec") && ($loadresult = @exec("uptime"))) {
            preg_match("/averages?: ([0-9\\.]+),[\\s]+([0-9\\.]+),[\\s]+([0-9\\.]+)/", $loadresult, $avgs);
            $uptime = explode(" up ", $loadresult);
            $uptime = explode(",", $uptime[1]);
            $uptime = $uptime[0] . ", " . $uptime[1];
            $data = "Server Load Averages " . $avgs[1] . ", " . $avgs[2] . ", " . $avgs[3] . "\n";
            $data .= "Server Uptime " . $uptime;
            return $data;
        }
        return "Unknown";
    }
}
function calcTime($time)
{
    $stat = round($time * 100 / 1, 3);
    if ($stat <= 40) {
        return $time . " (<font color='green'>Excellent</font>)";
    }
    if (40 < $stat && $stat <= 70) {
        return $time . " (<font color='darkgreen'>Good</font>)";
    }
    if (70 < $stat && $stat <= 98) {
        return $time . " (<font color='red'>Regular</font>) ";
    }
    if (98 < $stat) {
        return $time . " (<font color='darkred'>Bad</font>) ";
    }
}
function explain_query($sql)
{
    $output = "\r\n\t<table width=\"100%\" cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\r\n\t\t<tr>\r\n\t\t\t<td colspan=\"10\" class=\"thead\">\r\n\t\t\t\tExplain Query\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\">id</td>\r\n\t\t\t<td class=\"subheader\">select_type</td>\r\n\t\t\t<td class=\"subheader\">table</td>\r\n\t\t\t<td class=\"subheader\">type</td>\r\n\t\t\t<td class=\"subheader\">possible_keys</td>\r\n\t\t\t<td class=\"subheader\">key</td>\r\n\t\t\t<td class=\"subheader\">key_len</td>\r\n\t\t\t<td class=\"subheader\">ref</td>\r\n\t\t\t<td class=\"subheader\">rows</td>\r\n\t\t\t<td class=\"subheader\">Extra</td>\r\n\t\t</tr>";
    if (TS_Match($sql, "SELECT")) {
        $explain = @sql_query("EXPLAIN " . $sql);
        while ($results = @mysqli_fetch_assoc($explain)) {
            $output .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td>" . $results["id"] . "</td>\r\n\t\t\t\t<td>" . $results["select_type"] . "</td>\r\n\t\t\t\t<td>" . $results["table"] . "</td>\r\n\t\t\t\t<td>" . $results["type"] . "</td>\r\n\t\t\t\t<td>" . $results["possible_keys"] . "</td>\r\n\t\t\t\t<td>" . $results["key"] . "</td>\r\n\t\t\t\t<td>" . $results["key_len"] . "</td>\r\n\t\t\t\t<td>" . $results["ref"] . "</td>\r\n\t\t\t\t<td>" . $results["rows"] . "</td>\r\n\t\t\t\t<td>" . $results["Extra"] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    } else {
        $output .= "<tr><td colspan=\"10\">I can't explain this query.</td></tr>";
    }
    $output .= "</table><br />";
    $output .= format_comment("[sql]" . $sql . "[/sql]");
    return $output;
}
function printDetails($status)
{
    require INC_PATH . "/functions_mkprettytime.php";
    $output = "<tr><td>Memcache Server version:</td><td > " . $status["version"] . "</td></tr>";
    $output .= "<tr><td class='subheader'>Process id of this server process </td><td class='subheader'>" . $status["pid"] . "</td></tr>";
    $output .= "<tr><td>This Server has been running </td><td>" . mkprettytime($status["uptime"]) . "</td></tr>";
    $output .= "<tr><td class='subheader'>Accumulated user time for this process </td><td class='subheader'>" . (isset($status["rusage_user"]) ? $status["rusage_user"] : "--") . " seconds</td></tr>";
    $output .= "<tr><td>Accumulated system time for this process </td><td>" . (isset($status["rusage_system"]) ? $status["rusage_system"] : "--") . " seconds</td></tr>";
    $output .= "<tr><td class='subheader'>Total number of items stored by this server ever since it started </td><td class='subheader'>" . $status["total_items"] . "</td></tr>";
    $output .= "<tr><td>Number of open connections </td><td>" . $status["curr_connections"] . "</td></tr>";
    $output .= "<tr><td class='subheader'>Total number of connections opened since the server started running </td><td class='subheader'>" . $status["total_connections"] . "</td></tr>";
    $output .= "<tr><td>Number of connection structures allocated by the server </td><td>" . $status["connection_structures"] . "</td></tr>";
    $output .= "<tr><td class='subheader'>Cumulative number of retrieval requests </td><td class='subheader'>" . $status["cmd_get"] . "</td></tr>";
    $output .= "<tr><td> Cumulative number of storage requests </td><td>" . $status["cmd_set"] . "</td></tr>";
    $percCacheHit = (double) $status["get_hits"] / (double) $status["cmd_get"] * 100;
    $percCacheHit = round($percCacheHit, 3);
    $percCacheMiss = 100 - $percCacheHit;
    $output .= "<tr><td class='subheader'>Number of keys that have been requested and found present </td><td class='subheader'>" . $status["get_hits"] . " (" . $percCacheHit . "%)</td></tr>";
    $output .= "<tr><td >Number of items that have been requested and not found </td><td>" . $status["get_misses"] . "(" . $percCacheMiss . "%)</td></tr>";
    $MBRead = (double) $status["bytes_read"] / 1048576;
    $output .= "<tr><td class='subheader'>Total number of bytes read by this server from network </td><td class='subheader'>" . $MBRead . " Mega Bytes</td></tr>";
    $MBWrite = (double) $status["bytes_written"] / 1048576;
    $output .= "<tr><td>Total number of bytes sent by this server to network </td><td>" . $MBWrite . " Mega Bytes</td></tr>";
    $MBSize = (double) $status["limit_maxbytes"] / 1048576;
    $output .= "<tr><td class='subheader'>Number of bytes this server is allowed to use for storage.</td><td class='subheader'>" . $MBSize . " Mega Bytes</td></tr>";
    $output .= "<tr><td>Number of valid items removed from cache to free memory for new items.</td><td>" . $status["evictions"] . "</td></tr>";
    return $output;
}

?>