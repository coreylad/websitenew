<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "approveposts.php");
require "./global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
if ($usergroups["canmassdelete"] != "yes") {
    print_no_permission(true);
}
$postids = isset($_POST["postids"]) ? $_POST["postids"] : (isset($_GET["postids"]) ? $_GET["postids"] : "");
$tid = intval(TS_Global("tid"));
if (strlen($posthash) != 32 || !is_valid_id($tid) || !$postids) {
    print_no_permission(true, true, "Invalid Thread/Post Id or Secure Hash!");
    exit;
}
if ($posthash != $forumtokencode) {
    print_no_permission(true, true, "Invalid Secure Hash!");
    exit;
}
($query = sql_query("SELECT \r\n\t\t\tt.subject, t.replies, t.fid as oldforum, f.type, f.fid as currentforumid, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "threads t \t\t\t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.fid=t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.fid=f.pid)\r\n\t\t\tWHERE t.tid='" . $tid . "'")) || sqlerr(__FILE__, 57);
if (mysqli_num_rows($query) == 0) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
$thread = mysqli_fetch_assoc($query);
$forummoderator = is_forum_mod($thread["type"] == "s" ? $thread["deepforumid"] : $thread["currentforumid"], $CURUSER["id"]);
if (!$moderator && !$forummoderator || $permissions[$thread["currentforumid"]]["canview"] != "yes" || $permissions[$thread["currentforumid"]]["canviewthreads"] != "yes") {
    print_no_permission(true);
    exit;
}
foreach ($postids as $pid) {
    if (!is_valid_id($pid)) {
        stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    }
}
if ($action == "approve") {
    sql_query("UPDATE " . TSF_PREFIX . "posts SET visible = '1' WHERE pid IN (0," . implode(",", $postids) . ") AND tid = '" . $tid . "'") || sqlerr(__FILE__, 84);
    write_log("Posts: (" . implode(",", $postids) . ") has been approved by " . $CURUSER["username"]);
} else {
    if ($action == "unapprove") {
        sql_query("UPDATE " . TSF_PREFIX . "posts SET visible = '0' WHERE pid IN (0," . implode(",", $postids) . ") AND tid = '" . $tid . "'") || sqlerr(__FILE__, 89);
        write_log("Posts: (" . implode(",", $postids) . ") has been un-approved by " . $CURUSER["username"]);
    } else {
        if ($action == "approveattachments") {
            sql_query("UPDATE " . TSF_PREFIX . "attachments SET visible = '1' WHERE a_pid IN (0," . implode(",", $postids) . ") AND a_tid = '" . $tid . "'") || sqlerr(__FILE__, 94);
            write_log("Attachments: (" . implode(",", $postids) . ") has been approved by " . $CURUSER["username"]);
        } else {
            if ($action == "unapproveattachments") {
                sql_query("UPDATE " . TSF_PREFIX . "attachments SET visible = '0' WHERE a_pid IN (0," . implode(",", $postids) . ") AND a_tid = '" . $tid . "'") || sqlerr(__FILE__, 99);
                write_log("Attachments: (" . implode(",", $postids) . ") has been un-approved by " . $CURUSER["username"]);
            }
        }
    }
}
$return = "tsf_forums/showthread.php?tid=" . $tid . "&nolastpage=true" . (isset($_GET["page"]) ? "&page=" . intval($_GET["page"]) : "");
redirect($return);

?>