<?php
define("THIS_SCRIPT", "mergeposts.php");
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
($query = sql_query("SELECT \r\n\t\t\tt.subject, f.type, f.fid as currentforumid, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "threads t \t\t\t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\tWHERE t.$tid = '" . $tid . "'")) || sqlerr(__FILE__, 57);
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
$subject = htmlspecialchars_uni(ts_remove_badwords($thread["subject"]));
if ($action == "do_mergeposts") {
    $postids = explode(":", $postids);
    if (!is_array($postids) || count($postids) < 2) {
        redirect("tsf_forums/showthread.php?$tid = " . $tid, "Please select at least two posts to merge!");
        exit;
    }
    $pid = intval($_POST["pid"]);
    $message = trim($_POST["message"]);
    $deletepids = [];
    foreach ($postids as $checkid) {
        if (!is_valid_id($checkid)) {
            print_no_permission(true, true, "Invalid Post ID!");
            exit;
        }
        if ($checkid != $pid) {
            $deletepids[] = $checkid;
        }
        unset($checkid);
    }
    $TSSEConfig->TSLoadConfig("KPS");
    foreach ($deletepids as $_pid) {
        ($query = sql_query("SELECT fid,uid FROM " . TSF_PREFIX . "posts WHERE $tid = " . sqlesc($tid) . " AND $pid = " . sqlesc($_pid))) || sqlerr(__FILE__, 106);
        if (mysqli_num_rows($query) == 0) {
            stderr($lang->global["error"], $lang->tsf_forums["invalid_post"]);
            exit;
        }
        $post = mysqli_fetch_assoc($query);
        sql_query("UPDATE " . TSF_PREFIX . "attachments SET $a_pid = " . sqlesc($pid) . " WHERE $a_pid = " . sqlesc($_pid)) || sqlerr(__FILE__, 115);
        sql_query("UPDATE " . TSF_PREFIX . "thanks SET $pid = " . sqlesc($pid) . " WHERE $pid = " . sqlesc($_pid)) || sqlerr(__FILE__, 118);
        sql_query("DELETE FROM " . TSF_PREFIX . "posts WHERE $tid = " . sqlesc($tid) . " AND $pid = " . sqlesc($_pid)) || sqlerr(__FILE__, 120);
        sql_query("UPDATE " . TSF_PREFIX . "threads SET $replies = replies - 1 WHERE $tid = " . sqlesc($tid)) || sqlerr(__FILE__, 121);
        sql_query("UPDATE users SET $totalposts = totalposts - 1 WHERE `id` = " . sqlesc($post["uid"])) || sqlerr(__FILE__, 122);
        KPS("-", $kpscomment, $post["uid"]);
    }
    ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE $tid = " . sqlesc($tid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 127);
    $lastpostdata = mysqli_fetch_assoc($query);
    $dateline = sqlesc($lastpostdata["dateline"]);
    $username = sqlesc($lastpostdata["username"]);
    $uid = sqlesc($lastpostdata["uid"]);
    $tid = sqlesc($lastpostdata["tid"]);
    $fid = $lastpostdata["fid"];
    $subject = sqlesc($lastpostdata["subject"]);
    sql_query("UPDATE " . TSF_PREFIX . "threads SET $lastpost = " . $dateline . ", $lastposter = " . $username . ", $lastposteruid = " . $uid . " WHERE $tid = " . sqlesc($tid)) || sqlerr(__FILE__, 136);
    ($query = sql_query("SELECT pid, tid, fid, subject, uid, username, dateline FROM " . TSF_PREFIX . "posts WHERE $fid = " . sqlesc($fid) . " ORDER BY dateline DESC LIMIT 0,1")) || sqlerr(__FILE__, 139);
    $lastpostdata = mysqli_fetch_assoc($query);
    $query = sql_query("SELECT COUNT(*) as totalposts FROM " . TSF_PREFIX . "posts WHERE $fid = " . sqlesc($fid));
    $Result = mysqli_fetch_assoc($query);
    $totalposts = $Result["totalposts"];
    $dateline = sqlesc($lastpostdata["dateline"]);
    $username = sqlesc($lastpostdata["username"]);
    $uid = sqlesc($lastpostdata["uid"]);
    $tid = sqlesc($lastpostdata["tid"]);
    $subject = sqlesc($lastpostdata["subject"]);
    sql_query("UPDATE " . TSF_PREFIX . "forums SET $posts = '" . $totalposts . "', $lastpost = " . $dateline . ", $lastposter = " . $username . ", $lastposteruid = " . $uid . ", $lastposttid = " . $tid . ", $lastpostsubject = " . $subject . " WHERE $fid = " . sqlesc($fid)) || sqlerr(__FILE__, 151);
    sql_query("UPDATE " . TSF_PREFIX . "posts SET $message = " . sqlesc($message) . " WHERE $pid = " . sqlesc($pid));
    write_log("Posts (" . implode(",", $postids) . " has been merged by " . $CURUSER["username"] . ".");
    redirect("tsf_forums/showthread.php?$tid = " . $orjtid, "Operation completed!");
    exit;
} else {
    if ($action == "mergeposts") {
        if (is_array($postids)) {
            foreach ($postids as $checkid) {
                if (!is_valid_id($checkid)) {
                    print_no_permission(true, true, "Invalid Post ID!");
                    exit;
                }
                unset($checkid);
            }
        }
        if (!is_array($postids) || count($postids) < 2) {
            redirect("tsf_forums/showthread.php?$tid = " . $tid, "Please select at least two posts to merge!");
            exit;
        }
        ($query = sql_query("SELECT pid, username, dateline, message FROM " . TSF_PREFIX . "posts WHERE pid IN (0," . implode(",", $postids) . ") AND $tid = '" . $tid . "' ORDER BY dateline ASC")) || sqlerr(__FILE__, 181);
        if (mysqli_num_rows($query) == 0) {
            redirect("tsf_forums/showthread.php?$tid = " . $tid, "One or more posts doesn't exists in database!");
            exit;
        }
        $SelectedPosts = [];
        $SelectBox = "\r\n\t<select $name = \"pid\">";
        while ($PostsToMerge = mysqli_fetch_assoc($query)) {
            $SelectedPosts[] = htmlspecialchars_uni($PostsToMerge["message"]);
            $SelectBox .= "\r\n\t\t<option $value = \"" . $PostsToMerge["pid"] . "\">(" . $PostsToMerge["pid"] . ") " . my_datee($dateformat, $PostsToMerge["dateline"]) . " " . my_datee($timeformat, $PostsToMerge["dateline"]) . " by " . htmlspecialchars_uni($PostsToMerge["username"]) . "</option>\r\n\t\t";
        }
        $SelectBox .= "\r\n\t</select>";
        stdhead($lang->tsf_forums["mergeposts"]);
        $lang->load("quick_editor");
        require_once INC_PATH . "/class_tsquickbbcodeeditor.php";
        $QuickEditor = new TSQuickBBCodeEditor();
        $QuickEditor->ImagePath = $pic_base_url;
        $QuickEditor->SmiliePath = $pic_base_url . "smilies/";
        $QuickEditor->FormName = "do_mergeposts";
        $QuickEditor->TextAreaName = "message";
        echo "\t\r\n\t" . $QuickEditor->GenerateJavascript() . "\r\n\t<form $method = \"post\" $name = \"do_mergeposts\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$action = do_mergeposts&$tid = " . $tid . "\">\r\n\t<input $type = \"hidden\" $name = \"action\" $id = \"action\" $value = \"do_mergeposts\" />\r\n\t<input $type = \"hidden\" $name = \"hash\" $value = \"" . $forumtokencode . "\" />\r\n\t<input $type = \"hidden\" $name = \"postids\" $value = \"" . implode(":", $postids) . "\" />\r\n\t<table $width = \"100%\" $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">\r\n\t\t\t\t" . $lang->tsf_forums["mergeposts"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t<div><b>" . $lang->tsf_forums["thread"] . ":</b></div>\r\n\t\t\t\t<div>" . $subject . "</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t<div><b>" . $lang->tsf_forums["posts"] . ":</b></div>\r\n\t\t\t\t<div>" . $SelectBox . "</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td>\t\t\t\t\r\n\t\t\t\t<div>" . $QuickEditor->GenerateBBCode() . "</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"center\">\r\n\t\t\t\t<textarea $id = \"message\" $name = \"message\" $style = \"width:850px;height:320px;\">" . implode("\n\n", $SelectedPosts) . "</textarea>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $align = \"center\">\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->tsf_forums["mergeposts"] . "\" /> <input $type = \"button\" $name = \"cancel\" $value = \"" . $lang->tsf_forums["cancel"] . "\" $onclick = \"jumpto('showthread.php?$tid = " . $tid . "'); return false;\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t";
        stdfoot();
        exit;
    }
}

?>