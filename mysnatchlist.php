<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "mysnatchlist.php");
require "./global.php";
define("MYSL_VERSION", "0.3 by xam");
if ($usergroups["cansnatch"] != "yes" || $CURUSER["id"] == 0) {
    print_no_permission();
    exit;
}
if (!isset($_GET["uid"])) {
    $uid = intval($CURUSER["id"]);
} else {
    $uid = intval($_GET["uid"]);
}
if (!is_valid_id($uid)) {
    print_no_permission(true);
    exit;
}
if (!$is_mod && $uid != $CURUSER["id"]) {
    print_no_permission(true);
    exit;
}
$TSSEConfig->TSLoadConfig("ANNOUNCE");
if ($xbt_active != "yes") {
    redirect("index.php?$xbte = false");
    exit;
}
$lang->load("mysnatchlist");
$Links = [];
if (!isset($_GET["uid"]) && $is_mod) {
    $WHERE = "";
    $WHERE2 = "";
    if (isset($_GET["tid"]) && is_valid_id($_GET["tid"])) {
        $WHERE = " WHERE t.$id = " . sqlesc(intval($_GET["tid"]));
        $WHERE2 = " AND $fid = " . sqlesc(intval($_GET["tid"]));
        $Links[] = "tid=" . intval($_GET["tid"]);
    }
} else {
    $WHERE = " WHERE x.$uid = " . sqlesc($uid);
    $WHERE2 = " AND $uid = " . sqlesc($uid);
    $Links[] = "uid=" . $uid;
    if ($is_mod && isset($_GET["tid"]) && is_valid_id($_GET["tid"])) {
        $WHERE .= " AND t.$id = " . sqlesc(intval($_GET["tid"]));
        $WHERE2 = " AND $fid = " . sqlesc(intval($_GET["tid"]));
        $Links[] = "tid=" . intval($_GET["tid"]);
    }
}
($Query = sql_query("SELECT x.uid, t.id as torrentid FROM `xbt_files_users` x INNER JOIN `torrents` t ON (x.$fid = t.id)" . $WHERE)) || sqlerr(__FILE__, 86);
$Count = mysqli_num_rows($Query);
list($pagertop, $pagerbottom, $limit) = pager($ts_perpage, $Count, "mysnatchlist.php?" . (0 < count($Links) ? implode("&amp", $Links) . "&amp;" : ""));
($Query = sql_query("SELECT x.*, t.id as torrentid, t.name, t.size, u.username, u.ip, g.namestyle FROM `xbt_files_users` x INNER JOIN `torrents` t ON (x.$fid = t.id) LEFT JOIN `users` u ON (x.$uid = u.id) LEFT JOIN `usergroups` g ON (u.$usergroup = g.gid)" . $WHERE . " ORDER by `mtime` DESC " . $limit)) || sqlerr(__FILE__, 90);
if (!mysqli_num_rows($Query)) {
    stderr($lang->global["error"], $lang->mysnatchlist["error"]);
}
require INC_PATH . "/functions_ratio.php";
$TorrentList = "";
$LastAnnounced = 0;
while ($Torrent = mysqli_fetch_assoc($Query)) {
    $Username = get_user_color($Torrent["username"], $Torrent["namestyle"]);
    if ($LastAnnounced < $Torrent["mtime"]) {
        $LastAnnounced = $Torrent["mtime"];
    }
    $TorrentList .= "\r\n\t<tr>\r\n\t\t" . ($is_mod ? "\r\n\t\t<td><a $href = \"" . ts_seo($Torrent["uid"], $Torrent["username"]) . "\">" . $Username . "</a><br />" . $Torrent["ip"] . "</td>" : "") . "\r\n\t\t<td><a $href = \"" . ts_seo($Torrent["torrentid"], $Torrent["name"], "s") . "\">" . cutename($Torrent["name"], 50) . "</a></td>\r\n\t\t<td $align = \"center\">" . mksize($Torrent["size"]) . "</td>\r\n\t\t<td $align = \"center\">" . mksize($Torrent["downloaded"]) . "</td>\r\n\t\t<td $align = \"center\">" . mksize($Torrent["uploaded"]) . "</td>\r\n\t\t<td $align = \"center\">" . get_user_ratio($Torrent["uploaded"], $Torrent["downloaded"]) . "</td>\r\n\t\t<td $align = \"center\">" . ts_nf($Torrent["announced"]) . " " . $lang->global["times"] . "</td>\r\n\t\t<td $align = \"center\">" . my_datee($dateformat, $Torrent["mtime"]) . " " . my_datee($timeformat, $Torrent["mtime"]) . "</td>\r\n\t\t<td $align = \"center\">" . ($Torrent["active"] ? $lang->global["greenyes"] : $lang->global["redno"]) . "</td>\r\n\t</tr>\r\n\t";
}
$shouldShowList = "\r\n<table $width = \"100%\" $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t<tr>\r\n\t\t<td class=\"thead\" $colspan = \"" . ($is_mod ? "9" : "8") . "\">\r\n\t\t\t" . ($is_mod ? $lang->mysnatchlist["head2"] : $lang->mysnatchlist["head"] . " (" . $Username . ")") . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t" . ($is_mod ? "\r\n\t\t<td class=\"subheader\">\r\n\t\t\t" . $lang->mysnatchlist["username"] . "\r\n\t\t</td>" : "") . "\r\n\t\t<td class=\"subheader\">\r\n\t\t\t" . $lang->mysnatchlist["torrent"] . "\r\n\t\t</td>\r\n\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t" . $lang->mysnatchlist["size"] . "\r\n\t\t</td>\r\n\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t" . $lang->mysnatchlist["downloaded"] . "\r\n\t\t</td>\r\n\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t" . $lang->mysnatchlist["uploaded"] . "\r\n\t\t</td>\r\n\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t" . $lang->mysnatchlist["ratio"] . "\r\n\t\t</td>\r\n\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t" . $lang->mysnatchlist["announced"] . "\r\n\t\t</td>\t\t\r\n\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t" . $lang->mysnatchlist["lastaction"] . "\r\n\t\t</td>\r\n\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t" . $lang->mysnatchlist["active"] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $TorrentList . "\r\n</table>";
$NextAnnounce = $LastAnnounced + $announce_interval;
if ($NextAnnounce < TIMENOW) {
    $NextAnnounce = "---";
}
require INC_PATH . "/functions_mkprettytime.php";
stdhead($is_mod ? $lang->mysnatchlist["head2"] : $lang->mysnatchlist["head"] . " (" . $Username . ")");
echo ($is_mod ? "" : show_notice(sprintf($lang->mysnatchlist["notice"], mkprettytime($announce_interval), $NextAnnounce != "---" ? my_datee($dateformat, $NextAnnounce) . " " . my_datee($timeformat, $NextAnnounce) : $NextAnnounce))) . $pagertop . $shouldShowList . $pagerbottom;
stdfoot();

?>