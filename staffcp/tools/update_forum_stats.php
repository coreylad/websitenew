<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

@set_time_limit(0);
var_235();
$Language = file("languages/" . function_75() . "/update_forum_stats.lang");
$Message = "";
$TotalQueryCount = 0;
define("TSF_PREFIX", "tsf_");
echo "<table cellpadding=\"0\" cellspacing=\"0\" class=\"mainTable\"><tr><td class=\"alt1\">";
echo $Language[3];
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "threads WHERE tid = 0 OR fid = 0");
$TotalQueryCount++;
mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "posts WHERE pid = 0 OR tid = 0 OR fid = 0");
$TotalQueryCount++;
echo $Language[2];
echo $Language[4];
$TotalReplies = [];
$Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT tid FROM " . TSF_PREFIX . "posts");
$TotalQueryCount++;
while ($Posts = mysqli_fetch_assoc($Query)) {
    if (isset($Posts["tid"]) && $Posts["tid"]) {
        if (isset($TotalReplies[$Posts["tid"]])) {
            $TotalReplies[$Posts["tid"]]++;
        } else {
            $TotalReplies[$Posts["tid"]] = 1;
        }
    }
}
foreach ($TotalReplies as $Tid => $ReplyCount) {
    $ReplyCount = $ReplyCount - 1;
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE " . TSF_PREFIX . "threads SET replies = " . $ReplyCount . " WHERE tid = " . $Tid);
    $TotalQueryCount++;
}
echo $Language[2];
echo $Language[5];
$TotalThreads = [];
$Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT fid FROM " . TSF_PREFIX . "threads");
$TotalQueryCount++;
while ($Threads = mysqli_fetch_assoc($Query)) {
    if (isset($Threads["fid"]) && $Threads["fid"]) {
        if (isset($TotalThreads[$Threads["fid"]])) {
            $TotalThreads[$Threads["fid"]]++;
        } else {
            $TotalThreads[$Threads["fid"]] = 1;
        }
    }
}
foreach ($TotalThreads as $Fid => $ThreadCount) {
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE " . TSF_PREFIX . "forums SET threads = " . $ThreadCount . " WHERE fid = " . $Fid);
    $TotalQueryCount++;
}
echo $Language[2];
echo $Language[6];
$TotalPosts = [];
$Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT fid FROM " . TSF_PREFIX . "posts");
$TotalQueryCount++;
while ($Posts = mysqli_fetch_assoc($Query)) {
    if (isset($Posts["fid"]) && $Posts["fid"]) {
        if (isset($TotalPosts[$Posts["fid"]])) {
            $TotalPosts[$Posts["fid"]]++;
        } else {
            $TotalPosts[$Posts["fid"]] = 1;
        }
    }
}
foreach ($TotalPosts as $Fid => $PostCount) {
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE " . TSF_PREFIX . "forums SET posts = " . $PostCount . " WHERE fid = " . $Fid);
    $TotalQueryCount++;
}
echo $Language[2];
echo $Language[7] . " " . $TotalQueryCount;
echo "</td></tr></table>";
function function_75()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function function_77()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        var_236("../index.php");
    }
}
function function_78($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\twindow.location.href=\"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-equiv=\"refresh\" content=\"0;url=" . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function function_76($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function function_79($log)
{
    global $TotalQueryCount;
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
    $TotalQueryCount++;
}

?>