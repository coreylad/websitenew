<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "moderation.php");
require "./global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
$tid = intval(TS_Global("tid"));
if ($action != "deletethread" && (empty($posthash) || strlen($posthash) != 32 || $posthash != $forumtokencode)) {
    print_no_permission(true, true, "Invalid HASH!");
    exit;
}
if (!is_valid_id($tid)) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
if (!$moderator) {
    ($query = sql_query("SELECT p.tid, t.closed, f.type, f.fid as currentforumid, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "posts p\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "threads t ON (p.tid=t.tid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.fid=t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.fid=f.pid)\r\n\t\t\tWHERE p.tid = " . sqlesc($tid) . " LIMIT 1")) || sqlerr(__FILE__, 54);
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
if ($action == "sticky") {
    sql_query("UPDATE " . TSF_PREFIX . "threads SET sticky = IF(sticky=1,0,1) WHERE tid=" . sqlesc($tid)) || sqlerr(__FILE__, 74);
    write_log("Thread (" . $tid . ") has been updated (stickey/unsicky) by " . $CURUSER["username"]);
    redirect("tsf_forums/showthread.php?tid=" . $tid);
} else {
    if ($action == "openclosethread") {
        sql_query("UPDATE " . TSF_PREFIX . "threads SET closed = IF(closed='yes','no','yes') WHERE tid=" . sqlesc($tid)) || sqlerr(__FILE__, 80);
        write_log("Thread (" . $tid . ") has been updated (Open/Close) by " . $CURUSER["username"]);
        redirect("tsf_forums/showthread.php?tid=" . $tid);
    } else {
        if ($action == "deletethread") {
            ($query = sql_query("SELECT subject FROM " . TSF_PREFIX . "threads WHERE tid = " . sqlesc($tid))) || sqlerr(__FILE__, 86);
            if (mysqli_num_rows($query) == 0) {
                stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
                exit;
            }
            $Result = mysqli_fetch_assoc($query);
            $subject = $Result["subject"];
            $subject = htmlspecialchars_uni(ts_remove_badwords($subject));
            if (!isset($_GET["sure"])) {
                stderr($lang->global["error"], sprintf($lang->tsf_forums["mod_del_thread"], $subject) . "<br />" . $lang->tsf_forums["mod_del_thread_2"] . "<br /><a href=\"" . $_SERVER["SCRIPT_NAME"] . "?tid=" . $tid . "&action=deletethread&sure=1\">" . $lang->tsf_forums["yes"] . "</a> -- <a href=\"showthread.php?tid=" . $tid . "\">" . $lang->tsf_forums["no"] . "</a>", false);
            }
            ($query = sql_query("SELECT pid,fid,uid FROM " . TSF_PREFIX . "posts WHERE tid= " . sqlesc($tid))) || sqlerr(__FILE__, 102);
            if (mysqli_num_rows($query) == 0) {
                stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
                exit;
            }
            $TSSEConfig->TSLoadConfig("KPS");
            while ($post = mysqli_fetch_assoc($query)) {
                delete_attachments($post["pid"], $tid);
                sql_query("DELETE FROM " . TSF_PREFIX . "thanks WHERE pid = " . $post["pid"]) || sqlerr(__FILE__, 115);
                if (!isset($fid) || empty($fid)) {
                    $fid = $post["fid"];
                }
                sql_query("UPDATE users SET totalposts = totalposts - 1 WHERE id = " . sqlesc($post["uid"])) || sqlerr(__FILE__, 121);
                KPS("-", $kpscomment, $post["uid"]);
            }
            $query = sql_query("SELECT pollid FROM " . TSF_PREFIX . "threads WHERE tid = '" . $tid . "'");
            while ($delpoll = mysqli_fetch_assoc($query)) {
                if ($delpoll["pollid"]) {
                    $deletepolls[] = intval($delpoll["pollid"]);
                }
            }
            if (count($deletepolls)) {
                sql_query("DELETE FROM " . TSF_PREFIX . "poll WHERE pollid IN (0," . implode(",", $deletepolls) . ")");
                sql_query("DELETE FROM " . TSF_PREFIX . "pollvote WHERE pollid IN (0," . implode(",", $deletepolls) . ")");
            }
            sql_query("DELETE FROM " . TSF_PREFIX . "posts WHERE tid = " . sqlesc($tid)) || sqlerr(__FILE__, 142);
            sql_query("DELETE FROM " . TSF_PREFIX . "threads WHERE tid = " . sqlesc($tid)) || sqlerr(__FILE__, 143);
            sql_query("DELETE FROM " . TSF_PREFIX . "subscribe WHERE tid = " . sqlesc($tid)) || sqlerr(__FILE__, 144);
            $orjtid = $tid;
            ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($fid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 149);
            $lastpostdata = mysqli_fetch_assoc($query);
            $query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($fid));
            $Result = mysqli_fetch_assoc($query);
            $totalposts = $Result["totalposts"];
            $dateline = sqlesc($lastpostdata["dateline"]);
            $username = sqlesc($lastpostdata["username"]);
            $uid = sqlesc($lastpostdata["uid"]);
            $tid = sqlesc($lastpostdata["tid"]);
            $subject = sqlesc($lastpostdata["subject"]);
            sql_query("UPDATE " . TSF_PREFIX . "forums SET threads = threads - 1, posts = '" . $totalposts . "', lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . ", lastposttid = " . $tid . ", lastpostsubject = " . $subject . " WHERE fid = " . sqlesc($fid)) || sqlerr(__FILE__, 162);
            write_log("Thread (" . $orjtid . ") has been deleted by " . $CURUSER["username"]);
            redirect("tsf_forums/forumdisplay.php?fid=" . $fid);
        } else {
            if ($action == "movethread") {
                stdhead("Move Thread");
                $query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 177);
                while ($forum = mysqli_fetch_assoc($query)) {
                    if ($permissions[$forum["fid"]]["canview"] == "yes") {
                        $deepsubforums[$forum["pid"]] = (isset($deepsubforums[$forum["pid"]]) ? $deepsubforums[$forum["pid"]] : "") . "\r\n\t\t\t<option value=\"" . $forum["fid"] . "\">&nbsp; &nbsp;" . $forum["name"] . "</option>";
                    }
                }
                ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.type = 'f' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 192);
                $str = "\r\n\t\t\t<form action=\"" . $BASEURL . "/tsf_forums/moderation.php\" method=\"get\" style=\"margin-top: 0pt; margin-bottom: 0pt;\">\r\n\t\t\t<input type=\"hidden\" name=\"action\" value=\"do_move\">\r\n\t\t\t<input type=\"hidden\" name=\"tid\" value=\"" . $tid . "\">\t\r\n\t\t\t<input type=\"hidden\" name=\"hash\" value=\"" . $forumtokencode . "\">\r\n\t\t\t<span class=\"smalltext\">\r\n\t\t\t<strong>" . $lang->tsf_forums["mod_move"] . "</strong></span><br />\r\n\t\t\t<select name=\"newfid\">\r\n\t\t\t<optgroup label=\"" . $SITENAME . " Forums\">\t";
                while ($forum = mysqli_fetch_assoc($query)) {
                    if ($permissions[$forum["fid"]]["canview"] == "yes") {
                        $subforums[$forum["pid"]] = (isset($subforums[$forum["pid"]]) ? $subforums[$forum["pid"]] : "") . "\r\n\t\t\t<option value=\"" . $forum["fid"] . "\">-- " . $forum["name"] . "</option>" . (isset($deepsubforums[$forum["fid"]]) ? $deepsubforums[$forum["fid"]] : "");
                    }
                }
                $query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\tWHERE f.type = 'c' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\tWHERE f.type = 'c' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 217);
                while ($category = mysqli_fetch_assoc($query)) {
                    if ($permissions[$category["fid"]]["canview"] == "yes") {
                        $str .= "<optgroup label=\"" . $category["name"] . "\">" . $subforums[$category["fid"]] . "</optgroup>";
                    }
                }
                $str .= "\r\n\t\t\t</optgroup>\r\n\t\t\t</select> \r\n\t\t\t<input type=\"submit\" value=\"" . $lang->tsf_forums["mod_options_m"] . "\">\r\n\t\t\t<input value=\"" . $lang->tsf_forums["cancel"] . "\" onclick=\"jumpto('showthread.php?tid=" . $tid . "');\" type=\"button\">\r\n\t\t\t</form>";
                echo "\r\n\t<table class=\"tborder\" border=\"0\" cellpadding=\"4\" cellspacing=\"0\">\r\n\t<tbody><tr><td>" . $str . "</td></tr></tbody></table>";
                stdfoot();
            } else {
                if ($action == "do_move") {
                    $newfid = isset($_GET["newfid"]) ? intval($_GET["newfid"]) : 0;
                    if (!is_valid_id($newfid)) {
                        stderr($lang->global["error"], $lang->tsf_forums["invalidfid"]);
                        exit;
                    }
                    ($query = sql_query("SELECT type,pid FROM " . TSF_PREFIX . "forums WHERE fid = " . sqlesc($newfid))) || sqlerr(__FILE__, 248);
                    if (mysqli_num_rows($query) == 0) {
                        stderr($lang->global["error"], $lang->tsf_forums["invalidfid"]);
                        exit;
                    }
                    $Result = mysqli_fetch_assoc($query);
                    $type = $Result["type"];
                    $pid = $Result["pid"];
                    ($query = sql_query("SELECT fid as oldforum FROM " . TSF_PREFIX . "threads WHERE tid = " . sqlesc($tid))) || sqlerr(__FILE__, 266);
                    if (mysqli_num_rows($query) == 0) {
                        stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
                        exit;
                    }
                    $Result = mysqli_fetch_assoc($query);
                    $oldforum = $Result["oldforum"];
                    $orjtid = $tid;
                    sql_query("UPDATE " . TSF_PREFIX . "posts SET fid = " . sqlesc($newfid) . " WHERE tid = " . sqlesc($tid)) || sqlerr(__FILE__, 277);
                    sql_query("UPDATE " . TSF_PREFIX . "threads SET fid = " . sqlesc($newfid) . " WHERE tid = " . sqlesc($tid)) || sqlerr(__FILE__, 278);
                    ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($oldforum) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 281);
                    $lastpostdata = mysqli_fetch_assoc($query);
                    $query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($oldforum));
                    $Result = mysqli_fetch_assoc($query);
                    $totalposts = $Result["totalposts"];
                    $dateline = sqlesc($lastpostdata["dateline"]);
                    $username = sqlesc($lastpostdata["username"]);
                    $uid = sqlesc($lastpostdata["uid"]);
                    $tid = sqlesc($lastpostdata["tid"]);
                    $subject = sqlesc($lastpostdata["subject"]);
                    sql_query("UPDATE " . TSF_PREFIX . "forums SET threads = threads - 1, posts = '" . $totalposts . "', lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . ", lastposttid = " . $tid . ", lastpostsubject = " . $subject . " WHERE fid = " . sqlesc($oldforum)) || sqlerr(__FILE__, 294);
                    ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($newfid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 297);
                    $lastpostdata = mysqli_fetch_assoc($query);
                    $query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($newfid));
                    $Result = mysqli_fetch_assoc($query);
                    $totalposts = $Result["totalposts"];
                    $dateline = sqlesc($lastpostdata["dateline"]);
                    $username = sqlesc($lastpostdata["username"]);
                    $uid = sqlesc($lastpostdata["uid"]);
                    $tid = sqlesc($lastpostdata["tid"]);
                    $subject = sqlesc($lastpostdata["subject"]);
                    sql_query("UPDATE " . TSF_PREFIX . "forums SET threads = threads + 1, posts = '" . $totalposts . "', lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . ", lastposttid = " . $tid . ", lastpostsubject = " . $subject . " WHERE fid = " . sqlesc($newfid)) || sqlerr(__FILE__, 310);
                    write_log("Thread (" . $orjtid . " has been moved from FORUM: " . $oldforum . " to FORUM: " . $newfid . " by " . $CURUSER["username"]);
                    redirect("tsf_forums/showthread.php?tid=" . $orjtid);
                }
            }
        }
    }
}

?>