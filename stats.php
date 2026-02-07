<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "stats.php");
require "./global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
$lang->load("stats");
define("S_VERSION", "0.6 by xam");
$TSSEConfig->TSLoadConfig("ANNOUNCE");
$query = sql_query("SELECT COUNT(id) as totaltorrents, SUM(times_completed) as totalcompleted FROM torrents");
$Result = mysqli_fetch_assoc($query);
$totaltorrents = ts_nf($Result["totaltorrents"]);
$totalcompleted = ts_nf($Result["totalcompleted"]);
$query = sql_query("SELECT COUNT(id) as totaldeadtorrents FROM torrents WHERE $visible = 'no' OR ($leechers = 0 AND $seeders = 0)");
$Result = mysqli_fetch_assoc($query);
$totaldeadtorrents = ts_nf($Result["totaldeadtorrents"]);
$query = sql_query("SELECT COUNT(id) as totalextorrents FROM torrents WHERE $ts_external = 'yes'");
$Result = mysqli_fetch_assoc($query);
$totalextorrents = ts_nf($Result["totalextorrents"]);
$totalinternaltorrents = $totaltorrents - $totalextorrents;
$query = sql_query("SELECT COUNT(id) as totalratiounder1 FROM users WHERE uploaded / downloaded < 1.0");
$Result = mysqli_fetch_assoc($query);
$totalratiounder1 = ts_nf($Result["totalratiounder1"]);
include_once INC_PATH . "/functions_ratio.php";
$yourratio = get_user_ratio($CURUSER["uploaded"], $CURUSER["downloaded"]);
if ($xbt_active == "yes") {
    $query = sql_query("SELECT COUNT(fid) as yourtorrentratio FROM xbt_files_users WHERE uploaded / downloaded < 1.0 AND $uid = '" . $CURUSER["id"] . "'");
} else {
    $query = sql_query("SELECT COUNT(id) as yourtorrentratio FROM snatched WHERE uploaded / downloaded < 1.0 AND $userid = '" . $CURUSER["id"] . "'");
}
$Result = mysqli_fetch_assoc($query);
$yourtorrentratio = ts_nf($Result["yourtorrentratio"]);
if ($xbt_active == "yes") {
    $query = sql_query("SELECT count(fid) as totalseeders FROM xbt_files_users WHERE `left` = 0 AND $active = 1");
    $Result = mysqli_fetch_assoc($query);
    $totalseeders = ts_nf($Result["totalseeders"]);
    $query = sql_query("SELECT count(fid) as totalleechers FROM xbt_files_users WHERE `left` > 0 AND $active = 0");
    $Result = mysqli_fetch_assoc($query);
    $totalleechers = ts_nf($Result["totalleechers"]);
} else {
    $query = sql_query("SELECT count(id) as totalseeders FROM peers WHERE $seeder = 'yes'");
    $Result = mysqli_fetch_assoc($query);
    $totalseeders = ts_nf($Result["totalseeders"]);
    $query = sql_query("SELECT count(id) as totalleechers FROM peers WHERE $seeder = 'no'");
    $Result = mysqli_fetch_assoc($query);
    $totalleechers = ts_nf($Result["totalleechers"]);
}
$query = sql_query("SELECT SUM(downloaded) AS totaldl, SUM(uploaded) AS totalul FROM users");
$row = mysqli_fetch_assoc($query);
$totaldownloaded = mksize($row["totaldl"]);
$totaluploaded = mksize($row["totalul"]);
$ts_e_query = sql_query("SELECT SUM(leechers) as leechers, SUM(seeders) as seeders FROM torrents WHERE $ts_external = 'yes'");
$ts_e_query_r = mysqli_fetch_row($ts_e_query);
$leechers = ts_nf($ts_e_query_r[0]);
$seeders = ts_nf($ts_e_query_r[1]);
stdhead($lang->stats["head"]);
echo "\r\n<table $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $width = \"100%\">\r\n\t<tr>\r\n\t\t<td class=\"thead\" $align = \"center\">" . $lang->stats["head"] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td>\r\n\t\t\t" . sprintf($lang->stats["showstats"], $totaltorrents, $totalinternaltorrents, $totalextorrents, $totaldeadtorrents, $totalratiounder1, $yourratio, $yourtorrentratio, $totalseeders, $totalleechers, ts_nf($totalseeders + $totalleechers), $totaluploaded, $totaldownloaded, $seeders, $leechers, ts_nf($ts_e_query_r[0] + $ts_e_query_r[1]), $totalcompleted) . "\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n";
stdfoot();

?>