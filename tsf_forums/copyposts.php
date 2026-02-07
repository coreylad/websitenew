<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "copyposts.php");
require "./global.php";
if ($usergroups["canmassdelete"] != "yes") {
    print_no_permission(true);
}
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
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
($query = sql_query("SELECT \r\n\t\t\tt.subject, t.replies, t.fid as oldforum, f.type, f.fid as currentforumid, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "threads t \t\t\t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.fid=t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.fid=f.pid)\r\n\t\t\tWHERE t.tid='" . $tid . "'")) || sqlerr(__FILE__, 57);
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
if ($action == "do_copyposts") {
    if ($_POST["type"] == "newthread") {
        $newfid = isset($_POST["newfid"]) ? intval($_POST["newfid"]) : 0;
        if (!is_valid_id($newfid)) {
            stderr($lang->global["error"], $lang->tsf_forums["invalidfid"]);
            exit;
        }
        ($_query = sql_query("SELECT \r\n\t\t\tf.type, f.fid as currentforumid, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.fid=f.pid)\r\n\t\t\tWHERE f.fid='" . $newfid . "'")) || sqlerr(__FILE__, 93);
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
        $postids = explode(":", $postids);
        $totalselectedposts = count($postids);
        if ($totalselectedposts <= 1) {
            $replies = 0;
        } else {
            $replies = $totalselectedposts - 1;
        }
        $query = sql_query("SELECT uid, username, dateline, pid FROM " . TSF_PREFIX . "posts WHERE pid IN (0," . implode(",", $postids) . ") AND tid = '" . $tid . "' ORDER BY dateline ASC LIMIT 1");
        $FirstPostData = mysqli_fetch_assoc($query);
        $query = sql_query("SELECT dateline, username, uid FROM " . TSF_PREFIX . "posts WHERE pid IN (0," . implode(",", $postids) . ") AND tid = '" . $tid . "' ORDER BY dateline DESC LIMIT 1");
        $LastPostData = mysqli_fetch_assoc($query);
        sql_query("INSERT INTO " . TSF_PREFIX . "threads (fid, subject, uid, username, dateline, firstpost, lastpost, lastposter, lastposteruid, replies) VALUES ('" . $newfid . "', " . sqlesc($subject) . ", " . sqlesc($FirstPostData["uid"]) . ", " . sqlesc($FirstPostData["username"]) . ", " . sqlesc($FirstPostData["dateline"]) . ", " . sqlesc($FirstPostData["pid"]) . ", " . sqlesc($LastPostData["dateline"]) . ", " . sqlesc($LastPostData["username"]) . ", " . sqlesc($LastPostData["uid"]) . ", '" . $replies . "')") || sqlerr(__FILE__, 136);
        $newtid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
        $query = sql_query("SELECT pid, subject, uid, username, dateline, message, ipaddress, edituid, edittime, modnotice, modnotice_info, iconid FROM " . TSF_PREFIX . "posts WHERE pid IN (0," . implode(",", $postids) . ") AND tid = '" . $tid . "' ORDER BY dateline ASC");
        while ($Posts = mysqli_fetch_assoc($query)) {
            sql_query("INSERT INTO " . TSF_PREFIX . "posts (tid, fid, subject, uid, username, dateline, message, ipaddress, edituid, edittime, modnotice, modnotice_info, iconid) VALUES ('" . $newtid . "', '" . $newfid . "', " . sqlesc($Posts["subject"]) . ", " . sqlesc($Posts["uid"]) . ", " . sqlesc($Posts["username"]) . ", " . sqlesc($Posts["dateline"]) . ", " . sqlesc($Posts["message"]) . ", " . sqlesc($Posts["ipaddress"]) . ", " . sqlesc($Posts["edituid"]) . ", " . sqlesc($Posts["edittime"]) . ", " . sqlesc($Posts["modnotice"]) . ", " . sqlesc($Posts["modnotice_info"]) . ", " . sqlesc($Posts["iconid"]) . ")") || sqlerr(__FILE__, 142);
            $newPid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
            ($aquery = sql_query("SELECT a_name, a_size, a_count FROM " . TSF_PREFIX . "attachments WHERE a_pid = '" . $Posts["pid"] . "'")) || sqerr(__FILE__, 144);
            if (0 < mysqli_num_rows($aquery)) {
                while ($Att = mysqli_fetch_assoc($aquery)) {
                    sql_query("INSERT INTO " . TSF_PREFIX . "attachments (a_name, a_size, a_count, a_tid, a_pid) VALUES (" . sqlesc($Att["a_name"]) . ", " . sqlesc($Att["a_size"]) . ", " . sqlesc($Att["a_count"]) . ", '" . $newtid . "', '" . $newPid . "')") || sqerr(__FILE__, 149);
                }
            }
        }
        ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($newfid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 155);
        $lastpostdata = mysqli_fetch_assoc($query);
        $query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($newfid));
        $Result = mysqli_fetch_assoc($query);
        $totalposts = $Result["totalposts"];
        $query = sql_query("SELECT COUNT(*) as totalthreads FROM " . TSF_PREFIX . "threads WHERE fid = " . sqlesc($newfid));
        $Result = mysqli_fetch_assoc($query);
        $totalthreads = $Result["totalthreads"];
        $dateline = sqlesc($lastpostdata["dateline"]);
        $username = sqlesc($lastpostdata["username"]);
        $uid = sqlesc($lastpostdata["uid"]);
        $tid = sqlesc($lastpostdata["tid"]);
        $subject = sqlesc($lastpostdata["subject"]);
        sql_query("UPDATE " . TSF_PREFIX . "forums SET threads = '" . $totalthreads . "', posts = '" . $totalposts . "', lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . ", lastposttid = " . $tid . ", lastpostsubject = " . $subject . " WHERE fid = " . sqlesc($newfid)) || sqlerr(__FILE__, 172);
        write_log("Posts (" . implode(",", $postids) . ") has been copied from thread: " . $tid . " forum: " . $oldforum . " to new thread " . $newtid . " forum: " . $newfid . " by " . $CURUSER["username"]);
        redirect("tsf_forums/showthread.php?tid=" . $newtid);
        exit;
    }
    $newtid = intval($_POST["dest_thread"]);
    if (!$newtid) {
        stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
    }
    ($__query = sql_query("SELECT \r\n\t\t\tt.fid as newfid, f.type, f.fid as currentforumid, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "threads t \t\t\t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.fid=t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.fid=f.pid)\r\n\t\t\tWHERE t.tid='" . $newtid . "'")) || sqlerr(__FILE__, 194);
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
    $query = sql_query("SELECT pid, subject, uid, username, dateline, message, ipaddress, edituid, edittime, modnotice, modnotice_info, iconid FROM " . TSF_PREFIX . "posts WHERE pid IN (0," . implode(",", $postids) . ") AND tid = '" . $tid . "' ORDER BY dateline ASC");
    while ($Posts = mysqli_fetch_assoc($query)) {
        sql_query("INSERT INTO " . TSF_PREFIX . "posts (tid, fid, subject, uid, username, dateline, message, ipaddress, edituid, edittime, modnotice, modnotice_info, iconid) VALUES ('" . $newtid . "', '" . $newfid . "', " . sqlesc($Posts["subject"]) . ", " . sqlesc($Posts["uid"]) . ", " . sqlesc($Posts["username"]) . ", " . sqlesc($Posts["dateline"]) . ", " . sqlesc($Posts["message"]) . ", " . sqlesc($Posts["ipaddress"]) . ", " . sqlesc($Posts["edituid"]) . ", " . sqlesc($Posts["edittime"]) . ", " . sqlesc($Posts["modnotice"]) . ", " . sqlesc($Posts["modnotice_info"]) . ", " . sqlesc($Posts["iconid"]) . ")") || sqlerr(__FILE__, 218);
        $newPid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
        ($aquery = sql_query("SELECT a_name, a_size, a_count FROM " . TSF_PREFIX . "attachments WHERE a_pid = '" . $Posts["pid"] . "'")) || sqerr(__FILE__, 220);
        if (0 < mysqli_num_rows($aquery)) {
            while ($Att = mysqli_fetch_assoc($aquery)) {
                sql_query("INSERT INTO " . TSF_PREFIX . "attachments (a_name, a_size, a_count, a_tid, a_pid) VALUES (" . sqlesc($Att["a_name"]) . ", " . sqlesc($Att["a_size"]) . ", " . sqlesc($Att["a_count"]) . ", '" . $newtid . "', '" . $newPid . "')") || sqerr(__FILE__, 225);
            }
        }
    }
    ($query = sql_query("SELECT uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE tid = " . sqlesc($newtid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 231);
    $___lastpostdata = mysqli_fetch_assoc($query);
    $___dateline = sqlesc($___lastpostdata["dateline"]);
    $___username = sqlesc($___lastpostdata["username"]);
    $___uid = sqlesc($___lastpostdata["uid"]);
    sql_query("UPDATE " . TSF_PREFIX . "threads SET lastpost = " . $___dateline . ", lastposter = " . $___username . ", lastposteruid = " . $___uid . ", replies = replies + " . $totalselectedposts . " WHERE tid = " . sqlesc($newtid)) || sqlerr(__FILE__, 236);
    ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($newfid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 240);
    $lastpostdata = mysqli_fetch_assoc($query);
    $query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($newfid));
    $Result = mysqli_fetch_assoc($query);
    $totalposts = $Result["totalposts"];
    $query = sql_query("SELECT COUNT(*) as totalthreads FROM " . TSF_PREFIX . "threads WHERE fid = " . sqlesc($newfid));
    $Result = mysqli_fetch_assoc($query);
    $totalthreads = $Result["totalthreads"];
    $dateline = sqlesc($lastpostdata["dateline"]);
    $username = sqlesc($lastpostdata["username"]);
    $uid = sqlesc($lastpostdata["uid"]);
    $tid = sqlesc($lastpostdata["tid"]);
    $subject = sqlesc($lastpostdata["subject"]);
    sql_query("UPDATE " . TSF_PREFIX . "forums SET threads = '" . $totalthreads . "', posts = '" . $totalposts . "', lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . ", lastposttid = " . $tid . ", lastpostsubject = " . $subject . " WHERE fid = " . sqlesc($newfid)) || sqlerr(__FILE__, 257);
    write_log("Posts (" . implode(",", $postids) . ") has been copied from thread: " . $tid . " to existing thread " . $newtid . " by " . $CURUSER["username"]);
    redirect("tsf_forums/showthread.php?tid=" . $newtid);
    exit;
}
if ($action == "copyposts") {
    stdhead($lang->tsf_forums["copyposts"]);
    $query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 275);
    while ($forum = mysqli_fetch_assoc($query)) {
        if ($permissions[$forum["fid"]]["canview"] == "yes") {
            $deepsubforums[$forum["pid"]] = (isset($deepsubforums[$forum["pid"]]) ? $deepsubforums[$forum["pid"]] : "") . "\r\n\t\t\t<option value=\"" . $forum["fid"] . "\">&nbsp; &nbsp;" . $forum["name"] . "</option>";
        }
    }
    $query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 'f' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 'f' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 290);
    while ($forum = mysqli_fetch_assoc($query)) {
        if ($permissions[$forum["fid"]]["canview"] == "yes") {
            $subforums[$forum["pid"]] = (isset($subforums[$forum["pid"]]) ? $subforums[$forum["pid"]] : "") . "\r\n\t\t\t<option value=\"" . $forum["fid"] . "\">-- " . $forum["name"] . "</option>" . (isset($deepsubforums[$forum["fid"]]) ? $deepsubforums[$forum["fid"]] : "");
        }
    }
    ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\tWHERE f.type = 'c' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 304);
    $selectbox = "\r\n\t\t<select name=\"newfid\" onfocus=\"CheckRadioBox('newthread');\" onchange=\"CheckRadioBox('newthread');\">\r\n\t\t\t<optgroup label=\"" . $SITENAME . " Forums\">";
    while ($category = mysqli_fetch_assoc($query)) {
        if ($permissions[$category["fid"]]["canview"] == "yes") {
            $selectbox .= "<optgroup label=\"" . $category["name"] . "\">" . $subforums[$category["fid"]] . "</optgroup>";
        }
    }
    $selectbox .= "\r\n\t\t\t</optgroup>\r\n\t\t\t</select>";
    echo "\r\n\t<script type=\"text/javascript\">\r\n\t\tfunction CheckRadioBox(What)\r\n\t\t{\r\n\t\t\tTSGetID(What).checked = \"checked\";\r\n\t\t}\r\n\t</script>\r\n\t<form action=\"" . $_SERVER["SCRIPT_NAME"] . "?action=do_copyposts&tid=" . $tid . "\" method=\"post\" style=\"margin-top: 0pt; margin-bottom: 0pt;\">\r\n\t<input type=\"hidden\" name=\"action\" id=\"action\" value=\"do_copyposts\" />\r\n\t<input type=\"hidden\" name=\"hash\" value=\"" . $forumtokencode . "\" />\r\n\t<input type=\"hidden\" name=\"postids\" value=\"" . implode(":", $postids) . "\" />\t\t\r\n\t<table border=\"0\" cellpadding=\"5\" cellspacing=\"0\" width=\"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">" . $lang->tsf_forums["copyposts"] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend><input type=\"radio\" name=\"type\" value=\"newthread\" id=\"newthread\" checked=\"checked\" /> " . $lang->tsf_forums["copyposts2"] . "</legend>\r\n\t\t\t\t\t<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"none\"><b>" . $lang->tsf_forums["moveposts5"] . "</b></td>\r\n\t\t\t\t\t\t\t<td class=\"none\"><b>" . $lang->tsf_forums["thread"] . "</b></td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"none\">" . $selectbox . "</td>\r\n\t\t\t\t\t\t\t<td class=\"none\"><input type=\"text\" name=\"subject\" value=\"" . $subject . "\" size=\"50\" onfocus=\"CheckRadioBox('newthread');\" /></td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</fieldset>\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend><input type=\"radio\" name=\"type\" value=\"thread\" id=\"thread\" /> " . $lang->tsf_forums["copyposts3"] . "</legend>\r\n\t\t\t\t\t<div>" . $lang->tsf_forums["moveposts4"] . "</div>\r\n\t\t\t\t\t<input type=\"text\" name=\"dest_thread\" value=\"\" size=\"10\" onfocus=\"CheckRadioBox('thread');\" />\r\n\t\t\t\t</fieldset>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" align=\"center\">\r\n\t\t\t\t<input type=\"submit\" value=\"" . $lang->tsf_forums["copyposts"] . "\" /> <input value=\"" . $lang->tsf_forums["cancel"] . "\" onclick=\"jumpto('showthread.php?tid=" . $tid . "');\" type=\"button\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
    stdfoot();
    exit;
}

?>