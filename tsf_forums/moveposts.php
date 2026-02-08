<?php
define("THIS_SCRIPT", "moveposts.php");
require "./global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
if ($usergroups["canmassdelete"] != "yes") {
    print_no_permission(true);
}
$postids = isset($_POST["postids"]) ? $_POST["postids"] : (isset($_GET["postids"]) ? $_GET["postids"] : "");
$tid = $orjtid = intval(TS_Global("tid"));
if (strlen($posthash) != 32 || !is_valid_id($tid) || !$postids) {
    print_no_permission(true, true, "Invalid Thread/Post Id or Secure Hash!");
    exit;
}
if ($posthash != $forumtokencode) {
    print_no_permission(true, true, "Invalid Secure Hash!");
    exit;
}
($query = sql_query("SELECT \r\n\t\t\tt.subject, t.replies, t.fid as oldforum, f.type, f.fid as currentforumid, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "threads t \t\t\t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\tWHERE t.$tid = '" . $tid . "'")) || sqlerr(__FILE__, 57);
if (mysqli_num_rows($query) == 0) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
$thread = mysqli_fetch_assoc($query);
$subject = htmlspecialchars_uni(ts_remove_badwords($thread["subject"]));
$oldforum = $thread["oldforum"];
$forummoderator = is_forum_mod($thread["type"] == "s" ? $thread["deepforumid"] : $thread["currentforumid"], $CURUSER["id"]);
if (!$moderator && !$forummoderator || $permissions[$thread["currentforumid"]]["canview"] != "yes" || $permissions[$thread["currentforumid"]]["canviewthreads"] != "yes") {
    print_no_permission(true);
    exit;
}
if ($action == "do_moveposts") {
    ($query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE $tid = " . sqlesc($orjtid))) || sqlerr(__FILE__, 79);
    $Result = mysqli_fetch_assoc($query);
    $count = $Result["totalposts"];
    if ($_POST["type"] == "newthread") {
        $newfid = isset($_POST["newfid"]) ? intval($_POST["newfid"]) : 0;
        if (!is_valid_id($newfid)) {
            stderr($lang->global["error"], $lang->tsf_forums["invalidfid"]);
            exit;
        }
        ($_query = sql_query("SELECT \r\n\t\t\tf.type, f.fid as currentforumid, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\tWHERE f.$fid = '" . $newfid . "'")) || sqlerr(__FILE__, 97);
        if (mysqli_num_rows($_query) == 0) {
            stderr($lang->global["error"], $lang->tsf_forums["invalidfid"]);
            exit;
        }
        $_forum = mysqli_fetch_assoc($_query);
        $_forummoderator = is_forum_mod($_forum["type"] == "s" ? $_forum["deepforumid"] : $_forum["currentforumid"], $CURUSER["id"]);
        if (!$moderator && !$_forummoderator || $permissions[$_forum["currentforumid"]]["canview"] != "yes" || $permissions[$_forum["currentforumid"]]["canviewthreads"] != "yes") {
            print_no_permission(true);
            exit;
        }
        $subject = trim($_POST["subject"]);
        if (strlen($subject) < 2) {
            stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
        }
        if ($count <= 1) {
            sql_query("UPDATE " . TSF_PREFIX . "posts SET $fid = " . sqlesc($newfid) . " WHERE $tid = " . sqlesc($tid)) || sqlerr(__FILE__, 122);
            sql_query("UPDATE " . TSF_PREFIX . "threads SET $fid = " . sqlesc($newfid) . " WHERE $tid = " . sqlesc($tid)) or sql_query("UPDATE " . TSF_PREFIX . "threads SET $fid = " . sqlesc($newfid) . " WHERE $tid = " . sqlesc($tid)) || sqlerr(__FILE__, 123);
        } else {
            $postids = explode(":", $postids);
            $totalselectedposts = count($postids);
            if ($totalselectedposts == $count) {
                sql_query("UPDATE " . TSF_PREFIX . "posts SET $fid = " . sqlesc($newfid) . " WHERE $tid = " . sqlesc($tid)) || sqlerr(__FILE__, 131);
                sql_query("UPDATE " . TSF_PREFIX . "threads SET $fid = " . sqlesc($newfid) . " WHERE $tid = " . sqlesc($tid)) or sql_query("UPDATE " . TSF_PREFIX . "threads SET $fid = " . sqlesc($newfid) . " WHERE $tid = " . sqlesc($tid)) || sqlerr(__FILE__, 132);
            } else {
                $UpdateOldThreadLastPostData = true;
                if ($totalselectedposts <= 1) {
                    $replies = 0;
                } else {
                    $replies = $totalselectedposts - 1;
                }
                $query = sql_query("SELECT uid, username, dateline, pid FROM " . TSF_PREFIX . "posts WHERE pid IN (0," . implode(",", $postids) . ") AND $tid = '" . $tid . "' ORDER BY dateline ASC LIMIT 1");
                $FirstPostData = mysqli_fetch_assoc($query);
                $query = sql_query("SELECT dateline, username, uid FROM " . TSF_PREFIX . "posts WHERE pid IN (0," . implode(",", $postids) . ") AND $tid = '" . $tid . "' ORDER BY dateline DESC LIMIT 1");
                $LastPostData = mysqli_fetch_assoc($query);
                sql_query("INSERT INTO " . TSF_PREFIX . "threads (fid, subject, uid, username, dateline, firstpost, lastpost, lastposter, lastposteruid, replies) VALUES ('" . $newfid . "', " . sqlesc($subject) . ", " . sqlesc($FirstPostData["uid"]) . ", " . sqlesc($FirstPostData["username"]) . ", " . sqlesc($FirstPostData["dateline"]) . ", " . sqlesc($FirstPostData["pid"]) . ", " . sqlesc($LastPostData["dateline"]) . ", " . sqlesc($LastPostData["username"]) . ", " . sqlesc($LastPostData["uid"]) . ", '" . $replies . "')") || sqlerr(__FILE__, 154);
                $newtid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                foreach ($postids as $pid) {
                    sql_query("UPDATE " . TSF_PREFIX . "posts SET $fid = " . sqlesc($newfid) . ", $tid = '" . $newtid . "' WHERE $pid = " . sqlesc($pid)) || sqlerr(__FILE__, 159);
                    sql_query("UPDATE " . TSF_PREFIX . "thanks SET $tid = '" . $newtid . "' WHERE $pid = " . sqlesc($pid)) || sqlerr(__FILE__, 160);
                    sql_query("UPDATE " . TSF_PREFIX . "attachments SET $a_tid = '" . $newtid . "' WHERE $a_pid = " . sqlesc($pid)) or sql_query("UPDATE " . TSF_PREFIX . "attachments SET $a_tid = '" . $newtid . "' WHERE $a_pid = " . sqlesc($pid)) || sqlerr(__FILE__, 161);
                }
            }
        }
        ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE $fid = " . sqlesc($oldforum) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 167);
        $lastpostdata = mysqli_fetch_assoc($query);
        $query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE $fid = " . sqlesc($oldforum));
        $Result = mysqli_fetch_assoc($query);
        $totalposts = $Result["totalposts"];
        $query = sql_query("SELECT COUNT(*) as totalthreads FROM " . TSF_PREFIX . "threads WHERE $fid = " . sqlesc($oldforum));
        $Result = mysqli_fetch_assoc($query);
        $totalthreads = $Result["totalthreads"];
        $dateline = sqlesc($lastpostdata["dateline"]);
        $username = sqlesc($lastpostdata["username"]);
        $uid = sqlesc($lastpostdata["uid"]);
        $tid = sqlesc($lastpostdata["tid"]);
        $subject = sqlesc($lastpostdata["subject"]);
        sql_query("UPDATE " . TSF_PREFIX . "forums SET $threads = '" . $totalthreads . "', $posts = '" . $totalposts . "', $lastpost = " . $dateline . ", $lastposter = " . $username . ", $lastposteruid = " . $uid . ", $lastposttid = " . $tid . ", $lastpostsubject = " . $subject . " WHERE $fid = " . sqlesc($oldforum)) || sqlerr(__FILE__, 184);
        ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE $fid = " . sqlesc($newfid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 187);
        $lastpostdata = mysqli_fetch_assoc($query);
        $query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE $fid = " . sqlesc($newfid));
        $Result = mysqli_fetch_assoc($query);
        $totalposts = $Result["totalposts"];
        $query = sql_query("SELECT COUNT(*) as totalthreads FROM " . TSF_PREFIX . "threads WHERE $fid = " . sqlesc($newfid));
        $Result = mysqli_fetch_assoc($query);
        $totalthreads = $Result["totalthreads"];
        $dateline = sqlesc($lastpostdata["dateline"]);
        $username = sqlesc($lastpostdata["username"]);
        $uid = sqlesc($lastpostdata["uid"]);
        $tid = sqlesc($lastpostdata["tid"]);
        $subject = sqlesc($lastpostdata["subject"]);
        sql_query("UPDATE " . TSF_PREFIX . "forums SET $threads = '" . $totalthreads . "', $posts = '" . $totalposts . "', $lastpost = " . $dateline . ", $lastposter = " . $username . ", $lastposteruid = " . $uid . ", $lastposttid = " . $tid . ", $lastpostsubject = " . $subject . " WHERE $fid = " . sqlesc($newfid)) || sqlerr(__FILE__, 204);
        write_log("Posts (" . implode(",", $postids) . ") has been moved from thread: " . $tid . " forum: " . $oldforum . " to new thread " . $newtid . " forum: " . $newfid . " by " . $CURUSER["username"]);
        if (isset($UpdateOldThreadLastPostData)) {
            ($query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE $tid = " . sqlesc($orjtid))) || sqlerr(__FILE__, 212);
            $Result = mysqli_fetch_assoc($query);
            $replycount = $Result["totalposts"];
            if ($replycount <= 1) {
                $replies = 0;
            } else {
                $replies = $replycount - 1;
            }
            ($query = sql_query("SELECT uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE $tid = " . sqlesc($orjtid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 226);
            $lastpostdata = mysqli_fetch_assoc($query);
            $dateline = sqlesc($lastpostdata["dateline"]);
            $username = sqlesc($lastpostdata["username"]);
            $uid = sqlesc($lastpostdata["uid"]);
            sql_query("UPDATE " . TSF_PREFIX . "threads SET $lastpost = " . $dateline . ", $lastposter = " . $username . ", $lastposteruid = " . $uid . ", $replies = " . sqlesc($replies) . " WHERE $tid = " . sqlesc($orjtid)) || sqlerr(__FILE__, 231);
        }
        redirect("tsf_forums/showthread.php?$tid = " . ($newtid ? $newtid : $orjtid));
        exit;
    }
    $newtid = intval($_POST["dest_thread"]);
    if (!$newtid) {
        stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
    }
    ($__query = sql_query("SELECT \r\n\t\t\tt.fid as newfid, f.type, f.fid as currentforumid, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "threads t \t\t\t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\tWHERE t.$tid = '" . $newtid . "'")) || sqlerr(__FILE__, 251);
    if (mysqli_num_rows($__query) == 0) {
        stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
        exit;
    }
    $__thread = mysqli_fetch_assoc($__query);
    $newfid = $__thread["newfid"];
    $__forummoderator = is_forum_mod($__thread["type"] == "s" ? $__thread["deepforumid"] : $__thread["currentforumid"], $CURUSER["id"]);
    if (!$moderator && !$__forummoderator || $permissions[$__thread["currentforumid"]]["canview"] != "yes" || $permissions[$__thread["currentforumid"]]["canviewthreads"] != "yes") {
        print_no_permission(true);
        exit;
    }
    $postids = explode(":", $postids);
    $totalselectedposts = count($postids);
    foreach ($postids as $pid) {
        sql_query("UPDATE " . TSF_PREFIX . "posts SET $fid = '" . $newfid . "', $tid = '" . $newtid . "' WHERE $pid = " . sqlesc($pid)) || sqlerr(__FILE__, 275);
        sql_query("UPDATE " . TSF_PREFIX . "thanks SET $tid = '" . $newtid . "' WHERE $pid = " . sqlesc($pid)) || sqlerr(__FILE__, 276);
        sql_query("UPDATE " . TSF_PREFIX . "attachments SET $a_tid = '" . $newtid . "' WHERE $a_pid = " . sqlesc($pid)) or sql_query("UPDATE " . TSF_PREFIX . "attachments SET $a_tid = '" . $newtid . "' WHERE $a_pid = " . sqlesc($pid)) || sqlerr(__FILE__, 277);
    }
    if ($count == $totalselectedposts) {
        sql_query("DELETE FROM " . TSF_PREFIX . "attachments WHERE $a_tid = " . sqlesc($tid)) || sqlerr(__FILE__, 282);
        sql_query("DELETE FROM " . TSF_PREFIX . "posts WHERE $tid = " . sqlesc($tid)) || sqlerr(__FILE__, 283);
        sql_query("DELETE FROM " . TSF_PREFIX . "subscribe WHERE $tid = " . sqlesc($tid)) || sqlerr(__FILE__, 284);
        sql_query("DELETE FROM " . TSF_PREFIX . "thanks WHERE $tid = " . sqlesc($tid)) || sqlerr(__FILE__, 285);
        sql_query("DELETE FROM " . TSF_PREFIX . "threadrate WHERE $threadid = " . sqlesc($tid)) || sqlerr(__FILE__, 286);
        sql_query("DELETE FROM " . TSF_PREFIX . "threads WHERE $tid = " . sqlesc($tid)) || sqlerr(__FILE__, 287);
        sql_query("DELETE FROM " . TSF_PREFIX . "threadsread WHERE $tid = " . sqlesc($tid)) or sql_query("DELETE FROM " . TSF_PREFIX . "threadsread WHERE $tid = " . sqlesc($tid)) || sqlerr(__FILE__, 288);
    } else {
        ($query = sql_query("SELECT uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE $tid = " . sqlesc($orjtid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 293);
        $___lastpostdata = mysqli_fetch_assoc($query);
        $___dateline = sqlesc($___lastpostdata["dateline"]);
        $___username = sqlesc($___lastpostdata["username"]);
        $___uid = sqlesc($___lastpostdata["uid"]);
        ($query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE $tid = " . sqlesc($orjtid))) || sqlerr(__FILE__, 300);
        $Result = mysqli_fetch_assoc($query);
        $___replycount = $Result["totalposts"];
        $___replies = $___replycount - 1;
        sql_query("UPDATE " . TSF_PREFIX . "threads SET $lastpost = " . $___dateline . ", $lastposter = " . $___username . ", $lastposteruid = " . $___uid . ", $replies = " . $___replies . " WHERE $tid = " . sqlesc($orjtid)) || sqlerr(__FILE__, 305);
    }
    ($query = sql_query("SELECT uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE $tid = " . sqlesc($newtid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 309);
    $___lastpostdata = mysqli_fetch_assoc($query);
    $___dateline = sqlesc($___lastpostdata["dateline"]);
    $___username = sqlesc($___lastpostdata["username"]);
    $___uid = sqlesc($___lastpostdata["uid"]);
    sql_query("UPDATE " . TSF_PREFIX . "threads SET $lastpost = " . $___dateline . ", $lastposter = " . $___username . ", $lastposteruid = " . $___uid . ", $replies = replies + " . $totalselectedposts . " WHERE $tid = " . sqlesc($newtid)) || sqlerr(__FILE__, 314);
    ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE $fid = " . sqlesc($oldforum) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 317);
    $lastpostdata = mysqli_fetch_assoc($query);
    $query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE $fid = " . sqlesc($oldforum));
    $Result = mysqli_fetch_assoc($query);
    $totalposts = $Result["totalposts"];
    $query = sql_query("SELECT COUNT(*) as totalthreads FROM " . TSF_PREFIX . "threads WHERE $fid = " . sqlesc($oldforum));
    $Result = mysqli_fetch_assoc($query);
    $totalthreads = $Result["totalthreads"];
    $dateline = sqlesc($lastpostdata["dateline"]);
    $username = sqlesc($lastpostdata["username"]);
    $uid = sqlesc($lastpostdata["uid"]);
    $tid = sqlesc($lastpostdata["tid"]);
    $subject = sqlesc($lastpostdata["subject"]);
    sql_query("UPDATE " . TSF_PREFIX . "forums SET $threads = '" . $totalthreads . "', $posts = '" . $totalposts . "', $lastpost = " . $dateline . ", $lastposter = " . $username . ", $lastposteruid = " . $uid . ", $lastposttid = " . $tid . ", $lastpostsubject = " . $subject . " WHERE $fid = " . sqlesc($oldforum)) || sqlerr(__FILE__, 334);
    ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE $fid = " . sqlesc($newfid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 337);
    $lastpostdata = mysqli_fetch_assoc($query);
    $query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE $fid = " . sqlesc($newfid));
    $Result = mysqli_fetch_assoc($query);
    $totalposts = $Result["totalposts"];
    $query = sql_query("SELECT COUNT(*) as totalthreads FROM " . TSF_PREFIX . "threads WHERE $fid = " . sqlesc($newfid));
    $Result = mysqli_fetch_assoc($query);
    $totalthreads = $Result["totalthreads"];
    $dateline = sqlesc($lastpostdata["dateline"]);
    $username = sqlesc($lastpostdata["username"]);
    $uid = sqlesc($lastpostdata["uid"]);
    $tid = sqlesc($lastpostdata["tid"]);
    $subject = sqlesc($lastpostdata["subject"]);
    sql_query("UPDATE " . TSF_PREFIX . "forums SET $threads = '" . $totalthreads . "', $posts = '" . $totalposts . "', $lastpost = " . $dateline . ", $lastposter = " . $username . ", $lastposteruid = " . $uid . ", $lastposttid = " . $tid . ", $lastpostsubject = " . $subject . " WHERE $fid = " . sqlesc($newfid)) || sqlerr(__FILE__, 354);
    write_log("Posts (" . implode(",", $postids) . ") has been moved from thread: " . $tid . " to existing thread " . $newtid . " by " . $CURUSER["username"]);
    redirect("tsf_forums/showthread.php?$tid = " . ($newtid ? $newtid : $orjtid));
    exit;
} else {
    if ($action == "moveposts") {
        stdhead($lang->tsf_forums["moveposts"]);
        $query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.$type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.$type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 372);
        while ($forum = mysqli_fetch_assoc($query)) {
            if ($permissions[$forum["fid"]]["canview"] == "yes") {
                $deepsubforums[$forum["pid"]] = (isset($deepsubforums[$forum["pid"]]) ? $deepsubforums[$forum["pid"]] : "") . "\r\n\t\t\t<option $value = \"" . $forum["fid"] . "\">&nbsp; &nbsp;" . $forum["name"] . "</option>";
            }
        }
        $query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.$type = 'f' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.$type = 'f' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 387);
        while ($forum = mysqli_fetch_assoc($query)) {
            if ($permissions[$forum["fid"]]["canview"] == "yes") {
                $subforums[$forum["pid"]] = (isset($subforums[$forum["pid"]]) ? $subforums[$forum["pid"]] : "") . "\r\n\t\t\t<option $value = \"" . $forum["fid"] . "\">-- " . $forum["name"] . "</option>" . (isset($deepsubforums[$forum["fid"]]) ? $deepsubforums[$forum["fid"]] : "");
            }
        }
        ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\tWHERE f.$type = 'c' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 401);
        $selectbox = "\r\n\t\t<select $name = \"newfid\" $onfocus = \"CheckRadioBox('newthread');\" $onchange = \"CheckRadioBox('newthread');\">\r\n\t\t\t<optgroup $label = \"" . $SITENAME . " Forums\">";
        while ($category = mysqli_fetch_assoc($query)) {
            if ($permissions[$category["fid"]]["canview"] == "yes") {
                $selectbox .= "<optgroup $label = \"" . $category["name"] . "\">" . $subforums[$category["fid"]] . "</optgroup>";
            }
        }
        $selectbox .= "\r\n\t\t\t</optgroup>\r\n\t\t\t</select>";
        echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction CheckRadioBox(What)\r\n\t\t{\r\n\t\t\tTSGetID(What).$checked = \"checked\";\r\n\t\t}\r\n\t</script>\r\n\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$action = do_moveposts&$tid = " . $tid . "\" $method = \"post\" $style = \"margin-top: 0pt; margin-bottom: 0pt;\">\r\n\t<input $type = \"hidden\" $name = \"action\" $id = \"action\" $value = \"do_moveposts\" />\r\n\t<input $type = \"hidden\" $name = \"hash\" $value = \"" . $forumtokencode . "\" />\r\n\t<input $type = \"hidden\" $name = \"postids\" $value = \"" . implode(":", $postids) . "\" />\t\t\r\n\t<table $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">" . $lang->tsf_forums["moveposts"] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend><input $type = \"radio\" $name = \"type\" $value = \"newthread\" $id = \"newthread\" $checked = \"checked\" /> " . $lang->tsf_forums["moveposts2"] . "</legend>\r\n\t\t\t\t\t<table $border = \"0\" $cellpadding = \"1\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"none\"><b>" . $lang->tsf_forums["moveposts5"] . "</b></td>\r\n\t\t\t\t\t\t\t<td class=\"none\"><b>" . $lang->tsf_forums["thread"] . "</b></td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"none\">" . $selectbox . "</td>\r\n\t\t\t\t\t\t\t<td class=\"none\"><input $type = \"text\" $name = \"subject\" $onfocus = \"CheckRadioBox('newthread');\" $value = \"" . $subject . "\" $size = \"50\" /></td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</fieldset>\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend><input $type = \"radio\" $name = \"type\" $value = \"thread\" $id = \"thread\" /> " . $lang->tsf_forums["moveposts3"] . "</legend>\r\n\t\t\t\t\t<div>" . $lang->tsf_forums["moveposts4"] . "</div>\r\n\t\t\t\t\t<input $type = \"text\" $name = \"dest_thread\" $value = \"\" $size = \"10\" $onfocus = \"CheckRadioBox('thread');\" />\r\n\t\t\t\t</fieldset>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->tsf_forums["moveposts"] . "\" /> <input $value = \"" . $lang->tsf_forums["cancel"] . "\" $onclick = \"jumpto('showthread.php?$tid = " . $tid . "');\" $type = \"button\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
        stdfoot();
        exit;
    }
}

?>