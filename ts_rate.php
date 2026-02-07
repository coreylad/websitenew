<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "ts_rate.php");
define("TS_RATE_VERSION", "v.0.6 by xam");
define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("NO_LOGIN_REQUIRED", true);
define("CSRF_PROTECTION", true);
define("IN_AJAX", true);
require "./global.php";
if (strtoupper($_SERVER["REQUEST_METHOD"]) != "POST" || $usergroups["canrate"] != "yes") {
    exit;
}
$ratingid = trim($_POST["ratingid"]);
$userid = intval($_POST["userid"]);
$score = intval($_POST["score"]);
$maxRatings = 10;
$Result = sql_query("SELECT COUNT(*) as todayVotes FROM ts_ratings WHERE userid = " . sqlesc($CURUSER["id"]) . " AND ratedate >= " . (TIMENOW - 86400));
if (mysqli_num_rows($Result)) {
    $Row = mysqli_fetch_assoc($Result);
    if ($maxRatings <= $Row["todayVotes"]) {
        show_msg($lang->global["dailyvotingreached"], true);
    }
}
require INC_PATH . "/class_ts_rating.php";
$TSRating = new TS_Rating($ratingid, $userid);
if ($TSRating->Score($score)) {
    $TSSEConfig->TSLoadConfig("KPS");
    KPS("+", $kpsrate, $CURUSER["id"]);
}
$lang->load("details");
show_msg($TSRating->GetScore($lang->details["ratedetails"]) . "<div style=\"padding-top: 10px;\"><small><i>" . $lang->details["newrating"] . " <b><font size=\"4\">" . $score . "</font></b></i></small></div>");
function show_msg($message = "", $error = false)
{
    global $shoutboxcharset;
    header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Content-type: text/html; charset=" . $shoutboxcharset);
    if ($error) {
        exit("<error>" . $message . "</error>");
    }
    exit($message);
}

?>