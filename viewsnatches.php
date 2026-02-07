<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "viewsnatches.php");
require "./global.php";
include_once INC_PATH . "/functions_ratio.php";
define("VS_VERSION", "1.5.2 by xam");
if ($usergroups["cansnatch"] != "yes" || !isset($CURUSER) || isset($CURUSER["id"]) && $CURUSER["id"] == 0) {
    print_no_permission();
    exit;
}
$TSSEConfig->TSLoadConfig("ANNOUNCE");
if ($snatchmod == "no" && !$is_mod || $xbt_active == "yes") {
    stderr($lang->global["error"], $lang->global["notavailable"]);
}
$lang->load("viewsnatches");
// Refactored for PSR-12 compliance and descriptive naming
$torrentId = intval($_GET["id"]);
int_check($torrentId);
if (isset($_GET["delete"]) && $usergroups["cansettingspanel"] == "yes") {
    $userId = intval($_GET["userid"]);
    if (is_valid_id($userId)) {
        sql_query("DELETE FROM snatched WHERE userid = '" . $userId . "' AND torrentid = '" . $torrentId . "'");
    }
}
($snatchCountResult = sql_query("select count(snatched.id) from snatched inner join users on snatched.userid = users.id inner join torrents on snatched.torrentid = torrents.id where snatched.finished='yes' AND snatched.torrentid = " . sqlesc($torrentId))) || sqlerr(__FILE__, 48);
$snatchCountRow = mysqli_fetch_array($snatchCountResult);
$snatchCount = $snatchCountRow[0];
$torrentsPerPage = $CURUSER["torrentsperpage"] != 0 ? intval($CURUSER["torrentsperpage"]) : $ts_perpage;
$torrentInfoResult = sql_query("SELECT torrents.name, torrents.ts_external, categories.canview FROM torrents LEFT JOIN categories ON torrents.category = categories.id WHERE torrents.id = " . sqlesc($torrentId));
$torrentInfo = mysqli_fetch_array($torrentInfoResult);
if ($torrentInfo["canview"] != "[ALL]" && !in_array($CURUSER["usergroup"], explode(",", $torrentInfo["canview"]))) {
    print_no_permission();
}
if ($torrentInfo["ts_external"] == "yes") {
    stderr($lang->global["error"], $lang->viewsnatches["external"]);
}
stdhead($lang->viewsnatches["headmessage"]);
if ($is_mod) {
    if (isset($_GET["do"]) && $_GET["do"] == "fix_ratio" && ($uid = intval($_GET["userid"]))) {
        sql_query("UPDATE snatched SET uploaded = downloaded WHERE torrentid = '" . $id . "' AND userid = '" . $uid . "'") || sqlerr(__FILE__, 70);
    }
    $modsearch = "\r\n\t<form method=\"post\" action=\"" . $_SERVER["SCRIPT_NAME"] . "?id=" . $id . "\">\r\n\t<table class=\"main\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">\r\n\t\t\t\tSearch User in Snatchlist (Moderator Tool)\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\">\r\n\t\t\t\tUse this tool to search a specific user in Snatch List. Note: Min. 3 chars allowed to search an user.\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t<span style=\"float: right;\"><a href=\"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=hit_and_run&torrentid=" . $id . "\" onmouseout=\"window.status=''; return true;\" onMouseOver=\"window.status=''; return true;\">Hit & Run</a> - <a href=\"" . $BASEURL . "/takereseed.php?reseedid=" . $id . "\">Click here to Request a Reseed</a></span>\r\n\t\t\t\tUsername: <input type=\"text\" name=\"username\" size=\"15\"> <input type=\"checkbox\" value=\"yes\" name=\"showunfinished\"> Search Unfinished? <input type=\"submit\" value=\"search\">\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t";
    echo $modsearch . "<br />";
}
// Sorting and pagination variables
$sortType = "DESC";
$orderBy = "snatched.completedat";
$typeLink = "&amp;type=ASC";
if (isset($_GET["type"]) && $_GET["type"] == "DESC") {
    $sortType = "ASC";
    $typeLink = "&amp;type=DESC";
}
$orderLink = "";
if (isset($_GET["order"])) {
    $orderField = $_GET["order"];
    $allowedFields = ["username", "uploaded", "downloaded", "completedat", "last_action", "seeder", "seedtime", "leechtime", "connectable"];
    if (in_array($orderField, $allowedFields)) {
        if ($orderField == "username") {
            $orderBy = "users.username";
        } else {
            $orderBy = "snatched." . $orderField;
        }
        $orderLink = "&amp;order=" . $orderField;
    }
}
$quickLink = $_SERVER["SCRIPT_NAME"] . "?id=" . $torrentId . "&amp;type=" . $sortType . "&amp;order=";
list($pagerTop, $pagerBottom, $limit) = pager($torrentsPerPage, $snatchCount, $_SERVER["SCRIPT_NAME"] . "?id=" . $torrentId . $typeLink . $orderLink . "&amp;");
$finishquery = "snatched.finished='yes' AND ";
$showpager = true;
$extraquery = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && $is_mod) {
    function validusername($username)
    {
        if (!preg_match("|[^a-z\\|A-Z\\|0-9]|", $username)) {
            return true;
        }
        return false;
    }
    if (!empty($_POST["username"]) && 2 < strlen($_POST["username"]) && validusername($_POST["username"])) {
        $username = trim($_POST["username"]);
        $orderby = "users.username";
        $type = "ASC";
        $extraquery = " AND users.username LIKE '%" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "%'";
        if ($_POST["showunfinished"] == "yes") {
            $finishquery = "";
        }
        $showpager = false;
    }
}
if ($showpager) {
    echo $pagerTop;
}
echo "<table border=1 cellspacing=0 cellpadding=5 align=center width=100%>\n";
echo "<tr><td class=\"thead\" colspan=\"13\">" . sprintf($lang->viewsnatches["snatchdetails"], "<a href=details.php?id=" . $torrentId . ">" . $torrentInfo["name"]) . "</td></tr>";
echo "<tr><td class=subheader align=center><a href='" . $quickLink . "username'>" . $lang->viewsnatches["username"] . "</a></td><td class=subheader align=center><a href='" . $quickLink . "uploaded'>" . $lang->viewsnatches["uploaded"] . "</a></td><td class=subheader align=center><a href='" . $quickLink . "downloaded'>" . $lang->viewsnatches["downloaded"] . "</a></td><td class=subheader align=center>" . $lang->viewsnatches["ratio"] . "</td><td class=subheader align=center><a href='" . $quickLink . "completedat'>" . $lang->viewsnatches["finished"] . "</a></td><td class=subheader align=center><a href='" . $quickLink . "last_action'>" . $lang->viewsnatches["lastaction"] . "</a></td><td class=subheader align=center><a href='" . $quickLink . "seeder'>" . $lang->viewsnatches["seeding"] . "</a></td><td class=subheader align=center><a href='" . $quickLink . "seedtime'>" . $lang->viewsnatches["seedtime"] . "</a></td><td class=subheader align=center><a href='" . $quickLink . "leechtime'>" . $lang->viewsnatches["leechtime"] . "</a></td><td class=subheader align=center><a href='" . $quickLink . "connectable'>" . $lang->viewsnatches["connectable"] . "</a></td>\r\n<td class=subheader align=center colspan=3></td></tr>";
$res = sql_query("select users.donor, users.enabled, users.warned, users.leechwarn, users.options, users.last_login, users.last_access, users.username, p.canupload, p.candownload, p.cancomment, p.canmessage, p.canshout, snatched.seedtime, snatched.leechtime, snatched.upspeed, snatched.downspeed, snatched.connectable, snatched.port, snatched.completedat, snatched.last_action, snatched.agent, snatched.seeder, snatched.userid, snatched.uploaded, snatched.downloaded, usergroups.namestyle from snatched inner join users on snatched.userid = users.id LEFT JOIN ts_u_perm p ON (users.id=p.userid) inner join torrents on snatched.torrentid = torrents.id inner join usergroups on users.usergroup = usergroups.gid where " . $finishquery . "snatched.torrentid = " . sqlesc($torrentId) . $extraquery . " ORDER BY " . $orderBy . " " . $sortType . " " . $limit);
include_once INC_PATH . "/functions_icons.php";
$dt = TIMENOW - TS_TIMEOUT;
require_once INC_PATH . "/functions_mkprettytime.php";
while ($arr = mysqli_fetch_array($res)) {
    if ($arr["connectable"] == "yes" && $arr["seeder"] == "yes") {
        $connectable = $lang->global["greenyes"];
    } else {
        $connectable = "<font color=red>" . $lang->viewsnatches["waiting"] . "</font>";
    }
    $port = 0 + $arr["port"];
    if (0 < $arr["downloaded"]) {
        $OrjRatio = $arr["uploaded"] / $arr["downloaded"];
        $ratio2 = number_format($arr["uploaded"] / $arr["downloaded"], 2);
        $ratio2 = "<font color=" . get_ratio_color($ratio2) . ">" . $ratio2 . "</font>";
    } else {
        if (0 < $arr["uploaded"]) {
            $ratio2 = "Inf.";
            $OrjRatio = 0;
        } else {
            $OrjRatio = 0;
            $ratio2 = "---";
        }
    }
    $uploaded2 = mksize($arr["uploaded"]);
    $downloaded2 = mksize($arr["downloaded"]);
    $highlight = $CURUSER["id"] == $arr["userid"] || isset($uid) && $arr["userid"] == $uid ? " class=\"highlight\"" : "";
    $last_access = $arr["last_access"];
    $userid = 0 + $arr["userid"];
    if (TS_Match($arr["options"], "B1") && !$is_mod && $userid != $CURUSER["id"]) {
        $last_access = $arr["last_login"];
        $onoffpic = "<img src='" . $pic_base_url . "friends/offline.png' border='0' />";
    } else {
        if ($dt < TS_MTStoUTS($last_access) || $userid == $CURUSER["id"]) {
            $onoffpic = "<img src='" . $pic_base_url . "friends/online.png' border='0' />";
        } else {
            $onoffpic = "<img src='" . $pic_base_url . "friends/offline.png' border='0' />";
        }
    }
    $username = get_user_color($arr["username"], $arr["namestyle"]);
    $seedtime = mkprettytime($arr["seedtime"]);
    $leechtime = mkprettytime($arr["leechtime"]);
    $last_action = my_datee($dateformat, $arr["last_action"]) . "<br />" . my_datee($timeformat, $arr["last_action"]);
    $completedat = $arr["completedat"] != "0000-00-00 00:00:00" ? my_datee($dateformat, $arr["completedat"]) . "<br />" . my_datee($timeformat, $arr["completedat"]) : "Unfinished";
    $upspeed = 0 < $arr["upspeed"] ? mksize($arr["upspeed"]) : (0 < $arr["seedtime"] ? mksize($arr["uploaded"] / ($arr["seedtime"] + $arr["leechtime"])) : mksize(0));
    $downspeed = 0 < $arr["downspeed"] ? mksize($arr["downspeed"]) : (0 < $arr["leechtime"] ? mksize($arr["downloaded"] / $arr["leechtime"]) : mksize(0));
    echo "<tr" . $highlight . "><a id='user_" . $userid . "' name='user_" . $userid . "'></a><td align='center' class='smalltext'><a href='" . ts_seo($userid, $arr["username"]) . "'><b>" . $username . "</b></a>" . get_user_icons($arr) . " " . ($OrjRatio < 0 && $is_mod ? "<div align=\"center\"><a href=\"viewsnatches.php?do=fix_ratio&amp;userid=" . $userid . "&amp;id=" . $id . $typelink . $orderlink . (isset($_GET["page"]) ? "&amp;page=" . intval($_GET["page"]) : "") . "#user_" . $userid . "\"><img border=\"0\" src=\"" . $pic_base_url . "up.png\" alt=\"Fix Ratio\" title=\"Fix Ratio\"></a></div>" : "") . "</td><td align=center class=smalltext>" . $uploaded2 . "<br />" . $upspeed . "/s</td><td align=center class=smalltext>" . $downloaded2 . "<br />" . $downspeed . "/s</td><td align=center class=smalltext>" . $ratio2 . "</td><td align=center class=smalltext>" . $completedat . "</td><td align=center class=smalltext>" . $last_action . "</td><td align=center class=smalltext>" . ($arr["seeder"] == "yes" ? $lang->global["greenyes"] . " " . $lang->viewsnatches["port"] . " " . $port : $lang->global["redno"]) . "<br />" . htmlspecialchars_uni(substr($arr["agent"], 0, 20)) . "</td><td align=center class=smalltext>" . $seedtime . "</td><td align=center class=smalltext>" . $leechtime . "</td><td align=center class=smalltext>" . $connectable . "</td>\r\n\t<td align=center colspan=3 class=smalltext width=50>" . $onoffpic . " <a href='sendmessage.php?receiver=" . $userid . "'><img src='" . $pic_base_url . "friends/pm.png' border='0' alt='" . $lang->global["sendmessageto"] . $arr["username"] . "' title='" . $lang->global["sendmessageto"] . $arr["username"] . "' /></a> <a href=\"javascript:void(0);\" onclick=\"TSOpenPopup('" . $BASEURL . "/report.php?type=1&reporting=" . $userid . "', 'report', 500, 300); return false;\"><img border='0' src='" . $pic_base_url . "friends/block.png' alt='" . $lang->global["buttonreport"] . "' title='" . $lang->global["buttonreport"] . "'></a>" . ($usergroups["cansettingspanel"] == "yes" ? "<a href=\"" . $_SERVER["SCRIPT_NAME"] . "?id=" . $id . "&userid=" . $arr["userid"] . "&delete=true\"><img border=\"0\" src=\"" . $pic_base_url . "friends/remove.gif\" alt=\"" . $lang->global["buttondelete"] . "\" title=\"" . $lang->global["buttondelete"] . "\"></a>" : "") . "</td></tr>\n";
}
echo "</table>\n";
if ($showpager) {
    echo $pagerBottom;
}
stdfoot();

?>