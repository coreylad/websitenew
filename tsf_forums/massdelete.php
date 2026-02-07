<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "massdelete.php");
require "./global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
$TSSEConfig->TSLoadConfig("KPS");
if ($usergroups["canmassdelete"] != "yes") {
    print_no_permission(true);
}
$parentfid = isset($_POST["parentfid"]) ? intval($_POST["parentfid"]) : (isset($_GET["parentfid"]) ? intval($_GET["parentfid"]) : "");
$currentfid = isset($_POST["currentfid"]) ? intval($_POST["currentfid"]) : (isset($_GET["currentfid"]) ? intval($_GET["currentfid"]) : "");
$threadids = isset($_POST["threadids"]) ? $_POST["threadids"] : (isset($_GET["threadids"]) ? explode(":", $_GET["threadids"]) : "");
$postids = isset($_POST["postids"]) ? $_POST["postids"] : (isset($_GET["postids"]) ? explode(":", $_GET["postids"]) : "");
if (!is_valid_id($parentfid) || !is_valid_id($currentfid) || strlen($posthash) != 32) {
    print_no_permission(true, true, "Invalid Thread/Post Id or Secure Hash!");
    exit;
}
if ($posthash != $forumtokencode) {
    print_no_permission(true, true, "Invalid Secure Hash!");
    exit;
}
if (is_array($threadids)) {
    foreach ($threadids as $checkid) {
        if (!is_valid_id($checkid)) {
            print_no_permission(true, true, "Invalid Thread ID!");
            exit;
        }
        unset($checkid);
    }
}
if (is_array($postids)) {
    foreach ($postids as $checkid) {
        if (!is_valid_id($checkid)) {
            print_no_permission(true, true, "Invalid Post ID!");
            exit;
        }
        unset($checkid);
    }
}
if (!is_array($threadids) && !is_array($postids)) {
    redirect("tsf_forums/forumdisplay.php?fid=" . $currentfid, "Please select at least one thread/post to do this action!");
    exit;
}
($query = sql_query("SELECT \r\n\t\t\tt.tid, f.type, f.fid as currentforumid, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "threads t \t\t\t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.fid=t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.fid=f.pid)\r\n\t\t\tWHERE t.tid IN (0," . implode(",", $threadids) . ")")) || sqlerr(__FILE__, 93);
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
if ($action == "deletethreads" && is_array($threadids)) {
    $sure = isset($_GET["sure"]) ? intval($_GET["sure"]) : 0;
    if ($sure != 1) {
        foreach ($threadids as $stid) {
            $showtids[] = "<a href=\"" . $BASEURL . "/tsf_forums/showthread.php?tid=" . intval($stid) . (isset($_GET["page"]) ? "&page=" . intval($_GET["page"]) : "") . "\">" . intval($stid) . "</a>";
        }
        $showtid = implode(" , ", $showtids);
        stderr("Sanity Check", sprintf($lang->tsf_forums["mod_del_thread"], $showtid) . "<br />" . $lang->tsf_forums["mod_del_thread_2"] . "<br /><a href=\"" . $_SERVER["SCRIPT_NAME"] . "?parentfid=" . $parentfid . "&currentfid=" . $currentfid . "&sure=1&hash=" . $forumtokencode . "&threadids=" . implode(":", $threadids) . "&action=deletethreads\">" . $lang->tsf_forums["yes"] . "</a> -- <a href=\"forumdisplay.php?fid=" . $currentfid . "\">" . $lang->tsf_forums["no"] . "</a>", false);
        exit;
    } else {
        foreach ($threadids as $tid) {
            ($query = sql_query("SELECT pid,fid,uid FROM " . TSF_PREFIX . "posts WHERE tid= " . sqlesc($tid))) || sqlerr(__FILE__, 128);
            if (mysqli_num_rows($query)) {
                while ($post = mysqli_fetch_assoc($query)) {
                    delete_attachments($post["pid"], $tid);
                    sql_query("DELETE FROM " . TSF_PREFIX . "thanks WHERE pid = " . $post["pid"]) || sqlerr(__FILE__, 137);
                    if (!isset($fid) || empty($fid)) {
                        $fid = $post["fid"];
                    }
                    sql_query("UPDATE users SET totalposts = totalposts - 1 WHERE id = " . sqlesc($post["uid"])) || sqlerr(__FILE__, 143);
                    KPS("-", $kpscomment, $post["uid"]);
                }
            }
            $query = sql_query("SELECT pollid FROM " . TSF_PREFIX . "threads WHERE tid = '" . $tid . "'");
            if (mysqli_num_rows($query)) {
                while ($delpoll = mysqli_fetch_assoc($query)) {
                    if (isset($delpoll["pollid"]) && $delpoll["pollid"]) {
                        $deletepolls[] = intval($delpoll["pollid"]);
                    }
                }
            }
            if (isset($deletepolls) && count($deletepolls)) {
                sql_query("DELETE FROM " . TSF_PREFIX . "poll WHERE pollid IN (0," . implode(",", $deletepolls) . ")");
                sql_query("DELETE FROM " . TSF_PREFIX . "pollvote WHERE pollid IN (0," . implode(",", $deletepolls) . ")");
            }
            sql_query("DELETE FROM " . TSF_PREFIX . "posts WHERE tid = " . sqlesc($tid)) || sqlerr(__FILE__, 167);
            sql_query("DELETE FROM " . TSF_PREFIX . "threads WHERE tid = " . sqlesc($tid)) || sqlerr(__FILE__, 168);
            sql_query("DELETE FROM " . TSF_PREFIX . "subscribe WHERE tid = " . sqlesc($tid)) || sqlerr(__FILE__, 169);
            $orjtid = $tid;
            ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($fid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 174);
            $lastpostdata = mysqli_fetch_assoc($query);
            $query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($fid));
            $Result = mysqli_fetch_assoc($query);
            $totalposts = $Result["totalposts"];
            $dateline = sqlesc($lastpostdata["dateline"]);
            $username = sqlesc($lastpostdata["username"]);
            $uid = sqlesc($lastpostdata["uid"]);
            $tid = sqlesc($lastpostdata["tid"]);
            $subject = sqlesc($lastpostdata["subject"]);
            sql_query("UPDATE " . TSF_PREFIX . "forums SET threads = threads - 1, posts = '" . $totalposts . "', lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . ", lastposttid = " . $tid . ", lastpostsubject = " . $subject . " WHERE fid = " . sqlesc($fid)) || sqlerr(__FILE__, 187);
            write_log("Mass Delete: Threadid:  " . $orjtid . " has been deleted by " . $CURUSER["username"]);
        }
        redirect("tsf_forums/forumdisplay.php?fid=" . $fid);
    }
} else {
    if ($action == "deleteposts" && is_array($postids)) {
        $sure = isset($_GET["sure"]) ? intval($_GET["sure"]) : 0;
        if ($sure != 1) {
            foreach ($postids as $spid) {
                $showspid[] = "<a href=\"" . $BASEURL . "/tsf_forums/showthread.php?tid=" . intval($threadids[0]) . "&pid=" . $spid . "&scrollto=pid16175" . (isset($_GET["page"]) ? "&page=" . intval($_GET["page"]) : "") . "\">" . intval($spid) . "</a>";
            }
            $showpid = implode(" , ", $showspid);
            stderr("Sanity Check", sprintf($lang->tsf_forums["mod_del_post"], $showpid) . "<br />" . $lang->tsf_forums["mod_del_post_2"] . "<br /><a href=\"" . $_SERVER["SCRIPT_NAME"] . "?parentfid=" . $parentfid . "&currentfid=" . $currentfid . "&sure=1&hash=" . $forumtokencode . "&threadids=" . implode(":", $threadids) . "&postids=" . implode(":", $postids) . "&action=deleteposts" . (isset($_GET["page"]) ? "&page=" . intval($_GET["page"]) : "") . "\">" . $lang->tsf_forums["yes"] . "</a> -- <a href=\"" . $BASEURL . "/tsf_forums/showthread.php?tid=" . intval($threadids[0]) . (isset($_GET["page"]) ? "&page=" . intval($_GET["page"]) : "") . "\">" . $lang->tsf_forums["no"] . "</a>", false);
            exit;
        } else {
            $tid = 0 + $threadids[0];
            $fid = 0 + $currentfid;
            if (count($postids) <= 1) {
                $pid = 0 + $postids[0];
                ($query = sql_query("SELECT fid,uid FROM " . TSF_PREFIX . "posts WHERE tid= " . sqlesc($tid) . " AND pid = " . sqlesc($pid))) || sqlerr(__FILE__, 216);
                if (mysqli_num_rows($query) == 0) {
                    stderr($lang->global["error"], $lang->tsf_forums["invalid_post"]);
                    exit;
                }
                $post = mysqli_fetch_assoc($query);
                delete_attachments($pid, $tid);
                sql_query("DELETE FROM " . TSF_PREFIX . "thanks WHERE pid = " . $pid) || sqlerr(__FILE__, 228);
                sql_query("DELETE FROM " . TSF_PREFIX . "posts WHERE tid= " . sqlesc($tid) . " AND pid = " . sqlesc($pid)) || sqlerr(__FILE__, 230);
                sql_query("UPDATE users SET totalposts = totalposts - 1 WHERE id = " . sqlesc($post["uid"])) || sqlerr(__FILE__, 231);
                KPS("-", $kpscomment, $post["uid"]);
            } else {
                foreach ($postids as $pid) {
                    ($query = sql_query("SELECT fid,uid FROM " . TSF_PREFIX . "posts WHERE tid= " . sqlesc($tid) . " AND pid = " . sqlesc($pid))) || sqlerr(__FILE__, 238);
                    if (mysqli_num_rows($query) == 0) {
                        stderr($lang->global["error"], $lang->tsf_forums["invalid_post"]);
                        exit;
                    }
                    $post = mysqli_fetch_assoc($query);
                    delete_attachments($pid, $tid);
                    sql_query("DELETE FROM " . TSF_PREFIX . "thanks WHERE pid = " . $pid) || sqlerr(__FILE__, 250);
                    sql_query("DELETE FROM " . TSF_PREFIX . "posts WHERE tid= " . sqlesc($tid) . " AND pid = " . sqlesc($pid)) || sqlerr(__FILE__, 252);
                    sql_query("UPDATE " . TSF_PREFIX . "threads SET replies = replies - 1 WHERE tid = " . sqlesc($tid)) || sqlerr(__FILE__, 253);
                    sql_query("UPDATE users SET totalposts = totalposts - 1 WHERE id = " . sqlesc($post["uid"])) || sqlerr(__FILE__, 254);
                    KPS("-", $kpscomment, $post["uid"]);
                }
            }
            ($query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE tid = " . sqlesc($tid))) || sqlerr(__FILE__, 260);
            $Result = mysqli_fetch_assoc($query);
            $count = $Result["totalposts"];
            if (0 < $count) {
                $ThreadContainsPosts = true;
                $orjtid = $tid;
                ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE tid = " . sqlesc($tid) . " ORDER BY dateline DESC LIMIT 1")) || sqlerr(__FILE__, 271);
                $lastpostdata = mysqli_fetch_assoc($query);
                $dateline = sqlesc($lastpostdata["dateline"]);
                $username = sqlesc($lastpostdata["username"]);
                $uid = sqlesc($lastpostdata["uid"]);
                $tid = sqlesc($lastpostdata["tid"]);
                $subject = sqlesc($lastpostdata["subject"]);
                sql_query("UPDATE " . TSF_PREFIX . "threads SET replies = IF(replies > 0, replies - 1, replies), lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . " WHERE tid = " . sqlesc($orjtid)) or sql_query("UPDATE " . TSF_PREFIX . "threads SET replies = IF(replies > 0, replies - 1, replies), lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . " WHERE tid = " . sqlesc($orjtid)) || sqlerr(__FILE__, 279);
            } else {
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
                sql_query("DELETE FROM " . TSF_PREFIX . "threads WHERE tid = " . sqlesc($tid)) || sqlerr(__FILE__, 298);
                sql_query("UPDATE " . TSF_PREFIX . "forums SET threads = threads - 1 WHERE fid = " . sqlesc($fid)) || sqlerr(__FILE__, 299);
                sql_query("DELETE FROM " . TSF_PREFIX . "subscribe WHERE tid = " . sqlesc($tid)) || sqlerr(__FILE__, 300);
            }
            ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($fid) . " ORDER BY dateline DESC LIMIT 1")) || sqlerr(__FILE__, 304);
            $lastpostdata = mysqli_fetch_assoc($query);
            $query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE fid = " . sqlesc($fid));
            $Result = mysqli_fetch_assoc($query);
            $totalposts = $Result["totalposts"];
            $dateline = sqlesc($lastpostdata["dateline"]);
            $username = sqlesc($lastpostdata["username"]);
            $uid = sqlesc($lastpostdata["uid"]);
            $tid = sqlesc($lastpostdata["tid"]);
            $subject = sqlesc($lastpostdata["subject"]);
            sql_query("UPDATE " . TSF_PREFIX . "forums SET posts = '" . $totalposts . "', lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . ", lastposttid = " . $tid . ", lastpostsubject = " . $subject . " WHERE fid = " . sqlesc($fid)) || sqlerr(__FILE__, 316);
            write_log("Mass Delete: Threadid:  " . $tid . " / Postid: " . $pid . " has been deleted by " . $CURUSER["username"]);
            if (isset($ThreadContainsPosts)) {
                redirect("tsf_forums/showthread.php?tid=" . $orjtid . (isset($_GET["page"]) ? "&page=" . intval($_GET["page"]) : ""));
            } else {
                redirect("tsf_forums/forumdisplay.php?fid=" . $fid);
            }
        }
    } else {
        print_no_permission(true, true, "Invalid action!");
    }
}

?>