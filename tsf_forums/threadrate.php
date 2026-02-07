<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "threadrate.php");
require "./global.php";
$page = intval(TS_Global("page"));
$threadid = intval(TS_Global("threadid"));
$userid = intval($CURUSER["id"]);
$vote = intval(TS_Global("vote"));
$ipaddress = htmlspecialchars($CURUSER["ip"]);
$posthash = TS_Global("posthash");
if (!is_valid_id($threadid)) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
if (empty($posthash) || $posthash != sha1($threadid . $securehash . $threadid)) {
    stderr($lang->global["error"], $lang->tsf_forums["rateresult4"]);
    exit;
}
if (!is_valid_id($vote) || $vote < 1 || 5 < $vote) {
    stderr($lang->global["error"], $lang->tsf_forums["rateresult3"]);
    exit;
}
($query = sql_query("SELECT \r\n\t\t\tt.tid, t.closed, t.pollid, f.type, f.fid as currentforumid, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "threads t \t\t\t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.fid=t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.fid=f.pid)\r\n\t\t\tWHERE t.tid = " . sqlesc($threadid) . " LIMIT 1")) || sqlerr(__FILE__, 58);
if (mysqli_num_rows($query) == 0) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
$thread = mysqli_fetch_assoc($query);
$forummoderator = is_forum_mod($thread["type"] == "s" ? $thread["deepforumid"] : $thread["currentforumid"], $CURUSER["id"]);
if (!$moderator && !$forummoderator && ($permissions[$thread["currentforumid"]]["canview"] != "yes" || $permissions[$thread["currentforumid"]]["canviewthreads"] != "yes" || $usergroups["canrate"] != "yes")) {
    print_no_permission(true);
    exit;
}
if ($thread["closed"] == "yes" && !$moderator && !$forummoderator) {
    stderr($lang->global["error"], $lang->tsf_forums["thread_closed"]);
    exit;
}
$query1 = sql_query("SELECT userid FROM " . TSF_PREFIX . "threadrate WHERE userid = '" . $userid . "' AND threadid = '" . $threadid . "'");
if (0 < mysqli_num_rows($query1)) {
    stderr($lang->global["error"], $lang->tsf_forums["rateresult2"]);
    exit;
}
sql_query("INSERT INTO " . TSF_PREFIX . "threadrate (threadid,userid,vote,ipaddress) VALUES (" . $threadid . "," . $userid . "," . $vote . "," . sqlesc($ipaddress) . ")");
sql_query("UPDATE " . TSF_PREFIX . "threads SET votenum = votenum + 1, votetotal = votetotal + " . $vote . " WHERE tid = '" . $threadid . "'");
$TSSEConfig->TSLoadConfig("KPS");
KPS("+", $kpsrate, $userid);
redirect("tsf_forums/showthread.php?tid=" . $threadid . "&amp;page=" . $page . "&amp;nolastpage=true", $lang->tsf_forums["rateresult1"]);

?>