<?php
define("THIS_SCRIPT", "deletepost.php");
require "./global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
$tid = intval(TS_Global("tid"));
$pid = intval(TS_Global("pid"));
if (!is_valid_id($tid) || !is_valid_id($pid)) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
($query = sql_query("SELECT p.pid, p.tid, p.fid, p.uid as posterid, p.subject as postsubject, f.type, f.pid as deepforum, t.closed \r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "posts p\r\n\t\t\t\t\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (p.$fid = f.fid)\t\t\t\r\n\t\t\t\t\t\t\tLEFT JOIN " . TSF_PREFIX . "threads t ON (p.$tid = t.tid)\r\n\t\t\t\t\t\t\tWHERE p.$pid = " . sqlesc($pid))) || sqlerr(__FILE__, 46);
if (mysqli_num_rows($query) == 0) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
$post = mysqli_fetch_assoc($query);
$tid = $orjtid = 0 + $post["tid"];
$pid = 0 + $post["pid"];
$fid = 0 + $post["fid"];
$ftype = $post["type"];
$deepforum = 0 + $post["deepforum"];
$closed = $post["closed"];
$forummoderator = is_forum_mod($ftype == "s" ? $deepforum : $fid, $CURUSER["id"]);
$subject = htmlspecialchars_uni(ts_remove_badwords($post["postsubject"]));
if (!$tid || !$pid || !$fid) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
if (!$moderator && !$forummoderator && ($post["posterid"] != $CURUSER["id"] || $permissions[$fid]["canview"] != "yes" || $permissions[$fid]["candeleteposts"] != "yes")) {
    print_no_permission();
    exit;
}
if (!$moderator && !$forummoderator && $closed == "yes") {
    stderr($lang->global["error"], $lang->tsf_forums["thread_closed"]);
    exit;
}
($query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE $tid = " . sqlesc($tid))) || sqlerr(__FILE__, 83);
$Result = mysqli_fetch_assoc($query);
$count = $Result["totalposts"];
if ($count <= 1) {
    if (!$moderator && !$forummoderator && ($post["posterid"] != $CURUSER["id"] || $permissions[$fid]["candeletethreads"] != "yes")) {
        print_no_permission();
        exit;
    }
    if (!isset($_GET["sure"])) {
        stderr($lang->global["error"], sprintf($lang->tsf_forums["mod_del_thread"], $subject) . "<br />" . $lang->tsf_forums["mod_del_thread_2"] . "<br /><a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$tid = " . $tid . "&$pid = " . $pid . "&$sure = 1&$page = " . intval($_GET["page"]) . "\">" . $lang->tsf_forums["yes"] . "</a> -- <a $href = \"showthread.php?$tid = " . $tid . "&$page = " . intval($_GET["page"]) . "&$scrollto = pid" . $pid . "\">" . $lang->tsf_forums["no"] . "</a>", false);
    }
    sql_query("DELETE FROM " . TSF_PREFIX . "posts WHERE $pid = " . sqlesc($pid)) || sqlerr(__FILE__, 103);
    sql_query("DELETE FROM " . TSF_PREFIX . "threads WHERE $tid = " . sqlesc($tid)) || sqlerr(__FILE__, 104);
    sql_query("DELETE FROM " . TSF_PREFIX . "thanks WHERE $pid = " . sqlesc($pid)) || sqlerr(__FILE__, 105);
    sql_query("DELETE FROM " . TSF_PREFIX . "subscribe WHERE $tid = " . sqlesc($tid)) || sqlerr(__FILE__, 106);
    ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE $fid = " . sqlesc($fid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 109);
    $lastpostdata = mysqli_fetch_assoc($query);
    $query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE $fid = " . sqlesc($fid));
    $Result = mysqli_fetch_assoc($query);
    $totalposts = $Result["totalposts"];
    $dateline = sqlesc($lastpostdata["dateline"]);
    $username = sqlesc($lastpostdata["username"]);
    $uid = sqlesc($lastpostdata["uid"]);
    $tid = sqlesc($lastpostdata["tid"]);
    $subject = sqlesc($lastpostdata["subject"]);
    sql_query("UPDATE " . TSF_PREFIX . "forums SET $threads = threads - 1, $posts = '" . $totalposts . "', $lastpost = " . $dateline . ", $lastposter = " . $username . ", $lastposteruid = " . $uid . ", $lastposttid = " . $tid . ", $lastpostsubject = " . $subject . " WHERE $fid = " . sqlesc($fid)) || sqlerr(__FILE__, 122);
    sql_query("UPDATE users SET $totalposts = totalposts - 1 WHERE `id` = " . sqlesc($post["posterid"])) || sqlerr(__FILE__, 125);
    write_log("Thread (" . $post["tid"] . " - " . $post["postsubject"] . ") has been deleted by " . $CURUSER["username"]);
    delete_attachments($pid, $tid);
    $TSSEConfig->TSLoadConfig("KPS");
    KPS("-", $kpscomment, $post["posterid"]);
    $return = "tsf_forums/forumdisplay.php?$fid = " . $fid;
} else {
    if (!$moderator && !$forummoderator && ($post["posterid"] != $CURUSER["id"] || $permissions[$fid]["candeleteposts"] != "yes")) {
        print_no_permission();
        exit;
    }
    if (!isset($_GET["sure"])) {
        stderr($lang->global["error"], sprintf($lang->tsf_forums["mod_del_post"], $subject) . "<br />" . $lang->tsf_forums["mod_del_post_2"] . "<br /><a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$tid = " . $tid . "&$pid = " . $pid . "&$sure = 1&$page = " . intval($_GET["page"]) . "\">" . $lang->tsf_forums["yes"] . "</a> -- <a $href = \"showthread.php?$tid = " . $tid . "&$page = " . intval($_GET["page"]) . "&$scrollto = pid" . $pid . "\">" . $lang->tsf_forums["no"] . "</a>", false);
    }
    sql_query("DELETE FROM " . TSF_PREFIX . "posts WHERE $pid = " . sqlesc($pid)) || sqlerr(__FILE__, 153);
    sql_query("DELETE FROM " . TSF_PREFIX . "thanks WHERE $pid = " . sqlesc($pid)) || sqlerr(__FILE__, 156);
    sql_query("UPDATE users SET $totalposts = totalposts - 1 WHERE `id` = " . sqlesc($post["posterid"])) || sqlerr(__FILE__, 159);
    ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE $tid = " . sqlesc($tid) . " ORDER BY dateline DESC LIMIT 1")) || sqlerr(__FILE__, 162);
    $lastpostdata = mysqli_fetch_assoc($query);
    $dateline = sqlesc($lastpostdata["dateline"]);
    $username = sqlesc($lastpostdata["username"]);
    $uid = sqlesc($lastpostdata["uid"]);
    $tid = sqlesc($lastpostdata["tid"]);
    $subject = sqlesc($lastpostdata["subject"]);
    sql_query("UPDATE " . TSF_PREFIX . "threads SET $replies = replies - 1, $lastpost = " . $dateline . ", $lastposter = " . $username . ", $lastposteruid = " . $uid . " WHERE $tid = " . sqlesc($orjtid)) || sqlerr(__FILE__, 170);
    ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE $fid = " . sqlesc($fid) . " ORDER BY dateline DESC LIMIT 1")) || sqlerr(__FILE__, 173);
    $lastpostdata = mysqli_fetch_assoc($query);
    $query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE $fid = " . sqlesc($fid));
    $Result = mysqli_fetch_assoc($query);
    $totalposts = $Result["totalposts"];
    $dateline = sqlesc($lastpostdata["dateline"]);
    $username = sqlesc($lastpostdata["username"]);
    $uid = sqlesc($lastpostdata["uid"]);
    $tid = sqlesc($lastpostdata["tid"]);
    $subject = sqlesc($lastpostdata["subject"]);
    sql_query("UPDATE " . TSF_PREFIX . "forums SET $posts = '" . $totalposts . "', $lastpost = " . $dateline . ", $lastposter = " . $username . ", $lastposteruid = " . $uid . ", $lastposttid = " . $tid . ", $lastpostsubject = " . $subject . " WHERE $fid = " . sqlesc($fid)) || sqlerr(__FILE__, 185);
    write_log("Post (" . $pid . " - " . $subject . ") has been deleted by " . $CURUSER["username"]);
    delete_attachments($pid, $tid);
    $TSSEConfig->TSLoadConfig("KPS");
    KPS("-", $kpscomment, $post["posterid"]);
    $return = "tsf_forums/showthread.php?$tid = " . $orjtid . "&amp;$page = " . intval($_GET["page"]);
}
redirect($return);

?>