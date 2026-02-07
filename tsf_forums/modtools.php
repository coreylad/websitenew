<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "modtools.php");
require "./global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
$parentfid = isset($_POST["parentfid"]) ? intval($_POST["parentfid"]) : "";
$currentfid = isset($_POST["currentfid"]) ? intval($_POST["currentfid"]) : "";
$threadids = isset($_POST["threadids"]) && is_array($_POST["threadids"]) ? $_POST["threadids"] : (isset($_POST["threadids"]) && $_POST["threadids"] ? explode(",", $_POST["threadids"]) : "");
$postids = isset($_POST["postids"]) ? $_POST["postids"] : "";
if (is_array($threadids)) {
    foreach ($threadids as $checkid) {
        if (!is_valid_id($checkid)) {
            print_no_permission(true, true, "Invalid Thread ID!");
            exit;
        }
    }
    if (!is_valid_id($parentfid) || !is_valid_id($currentfid) || strlen($posthash) != 32) {
        print_no_permission(true, true, "Invalid Thread/Post Id or Secure Hash!");
        exit;
    }
    if ($posthash != $forumtokencode) {
        print_no_permission(true, true, "Invalid Secure Hash!");
        exit;
    }
    if (!$moderator) {
        ($query = sql_query("SELECT p.tid, t.closed, f.type, f.fid as currentforumid, ff.fid as deepforumid\r\n\t\t\tFROM " . TSF_PREFIX . "posts p\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "threads t ON (p.tid=t.tid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.fid=t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.fid=f.pid)\r\n\t\t\tWHERE p.tid IN (0," . implode(",", $threadids) . ") LIMIT 1")) || sqlerr(__FILE__, 72);
        $thread = mysqli_fetch_assoc($query);
        $fid = 0 + $thread["currentforumid"];
        $ftype = $thread["type"];
        $deepforum = $thread["deepforumid"];
        $forummoderator = is_forum_mod($ftype == "s" ? $deepforum : $fid, $CURUSER["id"]);
        if (!$forummoderator) {
            print_no_permission(true);
            exit;
        }
    } else {
        if (empty($action)) {
            print_no_permission(true);
            exit;
        }
    }
    if ($action == "approve") {
        sql_query("UPDATE " . TSF_PREFIX . "threads SET visible = '1' WHERE tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 92);
        sql_query("UPDATE " . TSF_PREFIX . "posts SET visible = '1' WHERE tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 93);
        write_log("Threads: (" . implode(",", $threadids) . ") has been approved by " . $CURUSER["username"]);
        redirect("tsf_forums/forumdisplay.php?fid=" . $currentfid);
        exit;
    }
    if ($action == "unapprove") {
        sql_query("UPDATE " . TSF_PREFIX . "threads SET visible = '0' WHERE tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 100);
        sql_query("UPDATE " . TSF_PREFIX . "posts SET visible = '0' WHERE tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 101);
        write_log("Threads: (" . implode(",", $threadids) . ") has been un-approved by " . $CURUSER["username"]);
        redirect("tsf_forums/forumdisplay.php?fid=" . $currentfid);
        exit;
    }
    if ($action == "approveattachments") {
        sql_query("UPDATE " . TSF_PREFIX . "attachments SET visible = '1' WHERE a_tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 108);
        write_log("Attachments: (" . implode(",", $threadids) . ") has been approved by " . $CURUSER["username"]);
        redirect("tsf_forums/forumdisplay.php?fid=" . $currentfid);
        exit;
    }
    if ($action == "unapproveattachments") {
        sql_query("UPDATE " . TSF_PREFIX . "attachments SET visible = '0' WHERE a_tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 115);
        write_log("Attachments: (" . implode(",", $threadids) . ") has been un-approved by " . $CURUSER["username"]);
        redirect("tsf_forums/forumdisplay.php?fid=" . $currentfid);
        exit;
    }
    if ($action == "open") {
        sql_query("UPDATE " . TSF_PREFIX . "threads SET closed = 'no' WHERE tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 122);
        write_log("Threads: (" . implode(",", $threadids) . ") has been opened by " . $CURUSER["username"]);
        redirect("tsf_forums/forumdisplay.php?fid=" . $currentfid);
        exit;
    }
    if ($action == "close") {
        sql_query("UPDATE " . TSF_PREFIX . "threads SET closed = 'yes' WHERE tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 129);
        write_log("Threads: (" . implode(",", $threadids) . ") has been closed by " . $CURUSER["username"]);
        redirect("tsf_forums/forumdisplay.php?fid=" . $currentfid);
        exit;
    }
    if ($action == "sticky") {
        sql_query("UPDATE " . TSF_PREFIX . "threads SET sticky = '1' WHERE tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 136);
        write_log("Threads: (" . implode(",", $threadids) . ") has been set to sticky by " . $CURUSER["username"]);
        redirect("tsf_forums/forumdisplay.php?fid=" . $currentfid);
        exit;
    }
    if ($action == "unsticky") {
        sql_query("UPDATE " . TSF_PREFIX . "threads SET sticky = '0' WHERE tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 143);
        write_log("Threads: (" . implode(",", $threadids) . ") has been set to un-sticky by " . $CURUSER["username"]);
        redirect("tsf_forums/forumdisplay.php?fid=" . $currentfid);
        exit;
    }
    if ($action == "do_movethreads") {
        $approve = isset($_POST["approve_threads"]) && isset($_POST["approve_threads"]) == "yes" ? true : false;
        $newfid = isset($_POST["newfid"]) ? intval($_POST["newfid"]) : 0;
        if (!is_valid_id($newfid)) {
            stderr($lang->global["error"], $lang->tsf_forums["invalidfid"]);
            exit;
        }
        ($query = sql_query("SELECT type,pid FROM " . TSF_PREFIX . "forums WHERE fid = " . sqlesc($newfid))) || sqlerr(__FILE__, 158);
        if (mysqli_num_rows($query) == 0) {
            stderr($lang->global["error"], $lang->tsf_forums["invalidfid"]);
            exit;
        }
        $Result = mysqli_fetch_assoc($query);
        $type = $Result["type"];
        $pid = $Result["pid"];
        ($query = sql_query("SELECT fid as oldforum FROM " . TSF_PREFIX . "threads WHERE tid IN (0," . implode(",", $threadids) . ")")) || sqlerr(__FILE__, 176);
        if (mysqli_num_rows($query) == 0) {
            stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
            exit;
        }
        $Result = mysqli_fetch_assoc($query);
        $oldforum = $Result["oldforum"];
        sql_query("UPDATE " . TSF_PREFIX . "posts SET fid = " . sqlesc($newfid) . " WHERE tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 187);
        sql_query("UPDATE " . TSF_PREFIX . "threads SET fid = " . sqlesc($newfid) . " WHERE tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 188);
        if ($approve) {
            sql_query("UPDATE " . TSF_PREFIX . "threads SET visible = '1' WHERE tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 192);
            sql_query("UPDATE " . TSF_PREFIX . "posts SET visible = '1' WHERE tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 193);
        }
        ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($oldforum) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 197);
        $lastpostdata = mysqli_fetch_assoc($query);
        $query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($oldforum));
        $Result = mysqli_fetch_assoc($query);
        $totalposts = $Result["totalposts"];
        $query = sql_query("SELECT COUNT(*) as totalthreads FROM " . TSF_PREFIX . "threads WHERE fid = " . sqlesc($oldforum));
        $Result = mysqli_fetch_assoc($query);
        $totalthreads = $Result["totalthreads"];
        $dateline = sqlesc($lastpostdata["dateline"]);
        $username = sqlesc($lastpostdata["username"]);
        $uid = sqlesc($lastpostdata["uid"]);
        $tid = sqlesc($lastpostdata["tid"]);
        $subject = sqlesc($lastpostdata["subject"]);
        sql_query("UPDATE " . TSF_PREFIX . "forums SET threads = '" . $totalthreads . "', posts = '" . $totalposts . "', lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . ", lastposttid = " . $tid . ", lastpostsubject = " . $subject . " WHERE fid = " . sqlesc($oldforum)) || sqlerr(__FILE__, 214);
        ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($newfid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 217);
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
        sql_query("UPDATE " . TSF_PREFIX . "forums SET threads = '" . $totalthreads . "', posts = '" . $totalposts . "', lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . ", lastposttid = " . $tid . ", lastpostsubject = " . $subject . " WHERE fid = " . sqlesc($newfid)) || sqlerr(__FILE__, 234);
        write_log("Thread (" . implode(",", $threadids) . " has been moved from FORUM: " . $oldforum . " to FORUM: " . $newfid . " by " . $CURUSER["username"] . ($approve ? " (Threads has been approved too!)" : ""));
        redirect("tsf_forums/forumdisplay.php?fid=" . $newfid);
        exit;
    }
    if ($action == "movethreads") {
        stdhead($lang->tsf_forums["mod_options_m"]);
        $query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 249);
        while ($forum = mysqli_fetch_assoc($query)) {
            if ($permissions[$forum["fid"]]["canview"] == "yes") {
                $deepsubforums[$forum["pid"]] = (isset($deepsubforums[$forum["pid"]]) ? $deepsubforums[$forum["pid"]] : "") . "\r\n\t\t\t<option value=\"" . $forum["fid"] . "\">&nbsp; &nbsp;" . $forum["name"] . "</option>";
            }
        }
        ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 'f' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 264);
        $str = "\r\n\t\t\t<form action=\"" . $BASEURL . "/tsf_forums/modtools.php\" method=\"POST\" style=\"margin-top: 0pt; margin-bottom: 0pt;\">\r\n\t\t\t<input type=\"hidden\" name=\"action\" value=\"do_movethreads\">\r\n\t\t\t<input type=\"hidden\" name=\"parentfid\" value=\"" . $parentfid . "\">\r\n\t\t\t<input type=\"hidden\" name=\"currentfid\" value=\"" . $currentfid . "\">\r\n\t\t\t<input type=\"hidden\" name=\"threadids\" value=\"" . implode(",", $threadids) . "\">\r\n\t\t\t<input type=\"hidden\" name=\"hash\" value=\"" . $forumtokencode . "\">\r\n\t\t\t<span class=\"smalltext\">\r\n\t\t\t<strong>" . $lang->tsf_forums["mod_move"] . "</strong></span><br />\r\n\t\t\t<select name=\"newfid\">\r\n\t\t\t<optgroup label=\"" . $SITENAME . " Forums\">\t";
        while ($forum = mysqli_fetch_assoc($query)) {
            if ($permissions[$forum["fid"]]["canview"] == "yes") {
                $subforums[$forum["pid"]] = (isset($subforums[$forum["pid"]]) ? $subforums[$forum["pid"]] : "") . "\r\n\t\t\t<option value=\"" . $forum["fid"] . "\">-- " . $forum["name"] . "</option>" . (isset($deepsubforums[$forum["fid"]]) ? $deepsubforums[$forum["fid"]] : "");
            }
        }
        $query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 'c' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 'c' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 291);
        while ($category = mysqli_fetch_assoc($query)) {
            if ($permissions[$category["fid"]]["canview"] == "yes") {
                $str .= "<optgroup label=\"" . $category["name"] . "\">" . $subforums[$category["fid"]] . "</optgroup>";
            }
        }
        $str .= "\r\n\t\t\t</optgroup>\r\n\t\t\t</select>\r\n\t\t\t<input type=\"checkbox\" name=\"approve_threads\" value=\"yes\" class=\"inlineimg\" checked=\"checked\" /> " . $lang->tsf_forums["moderatemsg3"] . "\r\n\t\t\t<input type=\"submit\" value=\"" . $lang->tsf_forums["mod_options_m"] . "\" />\r\n\t\t\t" . (isset($tid) ? "<input value=\"" . $lang->tsf_forums["cancel"] . "\" onclick=\"jumpto('showthread.php?tid=" . $tid . "');\" type=\"button\" />" : "") . "\r\n\t\t\t</form>";
        echo "\r\n\t<table class=\"tborder\" border=\"0\" cellpadding=\"4\" cellspacing=\"0\">\r\n\t<tbody><tr><td>" . $str . "</td></tr></tbody></table>";
        stdfoot();
        exit;
    }
    if ($action == "mergethreads") {
        if (count($threadids) < 2) {
            stderr($lang->global["error"], $lang->tsf_forums["mergeerror"]);
        }
        ($query = sql_query("SELECT tid,subject FROM " . TSF_PREFIX . "threads WHERE tid IN (0," . implode(",", $threadids) . ") ORDER by dateline DESC")) || sqlerr(__FILE__, 321);
        if (mysqli_num_rows($query) == 0) {
            print_no_permission(true, true, "Invalid Thread ID!");
        }
        $merge = "\r\n\t<fieldset>\r\n\t<legend>" . $lang->tsf_forums["mop6"] . "</legend>\r\n\t<div style=\"padding: 3px;\">\r\n\t<select name=\"newtid\">\r\n\t";
        while ($thread = mysqli_fetch_assoc($query)) {
            $merge .= "<option value=\"" . $thread["tid"] . "\">[" . $thread["tid"] . "] " . htmlspecialchars_uni($thread["subject"]) . "</option>";
        }
        $merge .= "\r\n\t</select>\r\n\t</div>\r\n\t</fieldset>";
        $query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 346);
        while ($forum = mysqli_fetch_assoc($query)) {
            if ($permissions[$forum["fid"]]["canview"] == "yes") {
                $deepsubforums[$forum["pid"]] = $deepsubforums[$forum["pid"]] . "\r\n\t\t\t<option value=\"" . $forum["fid"] . "\"" . ($forum["fid"] == $currentfid ? " selected=\"selected\"" : "") . ">&nbsp; &nbsp;" . $forum["name"] . "</option>";
            }
        }
        ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 'f' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 361);
        $formopen = "\r\n\t\t\t<form action=\"" . $BASEURL . "/tsf_forums/modtools.php\" method=\"POST\" style=\"margin-top: 0pt; margin-bottom: 0pt;\">\r\n\t\t\t<input type=\"hidden\" name=\"action\" value=\"do_mergethreads\">\r\n\t\t\t<input type=\"hidden\" name=\"parentfid\" value=\"" . $parentfid . "\">\r\n\t\t\t<input type=\"hidden\" name=\"currentfid\" value=\"" . $currentfid . "\">\r\n\t\t\t<input type=\"hidden\" name=\"threadids\" value=\"" . implode(",", $threadids) . "\">\r\n\t\t\t<input type=\"hidden\" name=\"hash\" value=\"" . $forumtokencode . "\">";
        $formclose = "\r\n\t\t\t<input type=\"submit\" value=\"" . $lang->tsf_forums["mop5"] . "\">\r\n\t\t\t<input value=\"" . $lang->tsf_forums["cancel"] . "\" onclick=\"jumpto('showthread.php?tid=" . $tid . "');\" type=\"button\">\r\n\t\t\t</form>";
        $move = "\r\n\t\t\t<fieldset>\r\n\t\t\t<legend>" . $lang->tsf_forums["mod_move"] . "</legend>\r\n\t\t\t<div style=\"padding: 3px;\">\r\n\t\t\t<select name=\"newfid\">\r\n\t\t\t<optgroup label=\"" . $SITENAME . " Forums\">\t";
        while ($forum = mysqli_fetch_assoc($query)) {
            if ($permissions[$forum["fid"]]["canview"] == "yes") {
                $subforums[$forum["pid"]] = $subforums[$forum["pid"]] . "\r\n\t\t\t<option value=\"" . $forum["fid"] . "\"" . ($forum["fid"] == $currentfid ? " selected=\"selected\"" : "") . ">-- " . $forum["name"] . "</option>" . $deepsubforums[$forum["fid"]];
            }
        }
        $query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 'c' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 'c' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 395);
        while ($category = mysqli_fetch_assoc($query)) {
            if ($permissions[$category["fid"]]["canview"] == "yes") {
                $move .= "<optgroup label=\"" . $category["name"] . "\">" . $subforums[$category["fid"]] . "</optgroup>";
            }
        }
        $move .= "\r\n\t\t\t</optgroup>\r\n\t\t\t</select>\r\n\t\t\t</div>\r\n\t\t\t</fieldset>";
        stdhead($lang->tsf_forums["mop5"]);
        echo $formopen . "\r\n\t<table border=\"0\" cellpadding=\"4\" cellspacing=\"0\" width=\"100%\" align=\"center\">\r\n\t<tbody>\r\n\t<tr>\r\n\t<td>\r\n\t" . $merge . $move . "\r\n\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t<td>\r\n\t" . $formclose . "\r\n\t</td>\r\n\t</tr>\r\n\t</tbody>\r\n\t</table>";
        stdfoot();
        exit;
    }
    if ($action == "do_mergethreads") {
        $newtid = intval($_POST["newtid"]);
        $newfid = intval($_POST["newfid"]);
        if (!is_valid_id($newtid) || !is_valid_id($newfid)) {
            print_no_permission();
        }
        foreach ($threadids as $index => $checkid) {
            if (!is_valid_id($checkid) || $newtid == $checkid) {
                unset($threadids[$index]);
            }
        }
        $views = $replies = $totalposts = $totalthreads = 0;
        $Query = sql_query("SELECT views FROM " . TSF_PREFIX . "threads WHERE tid IN (0," . implode(",", $threadids) . ")") or ($Query = sql_query("SELECT views FROM " . TSF_PREFIX . "threads WHERE tid IN (0," . implode(",", $threadids) . ")")) || sqlerr(__FILE__, 447);
        while ($threadarray = mysqli_fetch_assoc($Query)) {
            $views += $threadarray["views"];
        }
        sql_query("UPDATE " . TSF_PREFIX . "attachments SET a_tid = " . $newtid . " WHERE a_tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 454);
        sql_query("UPDATE " . TSF_PREFIX . "posts SET tid = " . $newtid . ", fid = " . $newfid . " WHERE tid IN (" . $newtid . "," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 455);
        sql_query("UPDATE " . TSF_PREFIX . "subscribe SET tid = " . $newtid . " WHERE tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 456);
        sql_query("UPDATE " . TSF_PREFIX . "threadrate SET threadid = " . $newtid . " WHERE threadid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 457);
        sql_query("UPDATE " . TSF_PREFIX . "threads SET views = views + " . $views . ", fid = '" . $newfid . "' WHERE tid = '" . $newtid . "'") || sqlerr(__FILE__, 458);
        sql_query("DELETE FROM " . TSF_PREFIX . "threads WHERE tid IN (0," . implode(",", $threadids) . ")") || sqlerr(__FILE__, 459);
        ($query = sql_query("SELECT dateline,username,uid,tid,subject FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($currentfid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 463);
        $lastpostdata = mysqli_fetch_assoc($query);
        $query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($currentfid));
        $Result = mysqli_fetch_assoc($query);
        $totalposts = $Result["totalposts"];
        $query = sql_query("SELECT COUNT(*) as totalthreads FROM " . TSF_PREFIX . "threads WHERE fid = " . sqlesc($currentfid));
        $Result = mysqli_fetch_assoc($query);
        $totalthreads = $Result["totalthreads"];
        $dateline = sqlesc($lastpostdata["dateline"]);
        $username = sqlesc($lastpostdata["username"]);
        $uid = sqlesc($lastpostdata["uid"]);
        $tid = sqlesc($lastpostdata["tid"]);
        $subject = sqlesc($lastpostdata["subject"]);
        sql_query("UPDATE " . TSF_PREFIX . "forums SET threads = '" . $totalthreads . "', posts = '" . $totalposts . "', lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . ", lastposttid = " . $tid . ", lastpostsubject = " . $subject . " WHERE fid = " . sqlesc($currentfid)) || sqlerr(__FILE__, 479);
        ($query = sql_query("SELECT dateline,username,uid,tid,subject FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($newfid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 482);
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
        sql_query("UPDATE " . TSF_PREFIX . "forums SET threads = '" . $totalthreads . "', posts = '" . $totalposts . "', lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . ", lastposttid = " . $tid . ", lastpostsubject = " . $subject . " WHERE fid = " . sqlesc($newfid)) || sqlerr(__FILE__, 498);
        ($query = sql_query("SELECT dateline,username,uid,tid,subject FROM " . TSF_PREFIX . "posts WHERE tid = " . sqlesc($newtid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 501);
        $lastpostdata = mysqli_fetch_assoc($query);
        $dateline = sqlesc($lastpostdata["dateline"]);
        $username = sqlesc($lastpostdata["username"]);
        $uid = sqlesc($lastpostdata["uid"]);
        $tid = sqlesc($lastpostdata["tid"]);
        $subject = sqlesc($lastpostdata["subject"]);
        $query = sql_query("SELECT COUNT(*) as totalreplies FROM " . TSF_PREFIX . "posts WHERE tid = " . sqlesc($newtid));
        $Result = mysqli_fetch_assoc($query);
        $totalreplies = $Result["totalreplies"];
        if (0 < $totalreplies) {
            $totalreplies = $totalreplies - 1;
        }
        sql_query("UPDATE " . TSF_PREFIX . "threads SET replies = " . $totalreplies . ", lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . " WHERE tid = " . sqlesc($newtid)) || sqlerr(__FILE__, 517);
        redirect("tsf_forums/showthread.php?tid=" . $newtid);
    }
} else {
    redirect("tsf_forums/forumdisplay.php?fid=" . $currentfid, "Please select at least one thread to do this action!");
    exit;
}

?>