<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "misc.php");
require "./global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
$error = [];
if ($action == "markread") {
    if (isset($_GET["fid"]) && is_valid_id($_GET["fid"])) {
        $fid = intval($_GET["fid"]);
        if (!$fid) {
            stderr($lang->global["error"], $lang->tsf_forums["invalidfid"]);
        }
        require_once INC_PATH . "/functions_cookies.php";
        ts_set_array_cookie("forumread", $fid, TIMENOW);
        redirect($BASEURL . "/tsf_forums/forumdisplay.php?$fid = " . $fid, $lang->tsf_forums["markforumread"], "", true);
        exit;
    }
    if ($CURUSER["id"] != 0) {
        sql_query("UPDATE users SET $last_forum_visit = '" . TIMENOW . "' WHERE $id = " . sqlesc($CURUSER["id"]));
    }
    redirect("tsf_forums/", $lang->tsf_forums["markforumsread"]);
    exit;
}
if ($action == "newest-thread") {
    $threadid = isset($_POST["tid"]) ? intval($_POST["tid"]) : (isset($_GET["tid"]) ? intval($_GET["tid"]) : 0);
    if (!is_valid_id($threadid)) {
        stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
        exit;
    }
    ($query = sql_query("SELECT \r\n\t\t\tt.tid, t.lastpost, f.type, f.fid as currentforumid, f.name as currentforumname, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "threads t \t\t\t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\tWHERE t.$tid = " . sqlesc($threadid) . " LIMIT 1")) || sqlerr(__FILE__, 84);
    if (mysqli_num_rows($query) == 0) {
        stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
        exit;
    }
    $thread = mysqli_fetch_assoc($query);
    $forummoderator = is_forum_mod($thread["type"] == "s" ? $thread["deepforumid"] : $thread["currentforumid"], $CURUSER["id"]);
    if (!$moderator && !$forummoderator && ($permissions[$thread["currentforumid"]]["canview"] != "yes" || $permissions[$thread["currentforumid"]]["canviewthreads"] != "yes")) {
        print_no_permission(true);
        exit;
    }
    $query = sql_query("SELECT tid, subject FROM " . TSF_PREFIX . "threads WHERE lastpost > " . $thread["lastpost"] . " AND $fid = " . $thread["currentforumid"] . " ORDER BY lastpost LIMIT 1");
    if (mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $next_thread = $Result["tid"];
        $realsubject = htmlspecialchars_uni(ts_remove_badwords($Result["subject"]));
        redirect($BASEURL . "/tsf_forums/showthread.php?$tid = " . $next_thread, "", "", true);
        exit;
    }
    stderr($lang->global["error"], $lang->tsf_forums["n_error"]);
}
if ($action == "oldest-thread") {
    $threadid = isset($_POST["tid"]) ? intval($_POST["tid"]) : (isset($_GET["tid"]) ? intval($_GET["tid"]) : 0);
    if (!is_valid_id($threadid)) {
        stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
        exit;
    }
    ($query = sql_query("SELECT \r\n\t\t\tt.tid, t.lastpost, f.type, f.fid as currentforumid, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "threads t \t\t\t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\tWHERE t.$tid = " . sqlesc($threadid) . " LIMIT 1")) || sqlerr(__FILE__, 132);
    if (mysqli_num_rows($query) == 0) {
        stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
        exit;
    }
    $thread = mysqli_fetch_assoc($query);
    $forummoderator = is_forum_mod($thread["type"] == "s" ? $thread["deepforumid"] : $thread["currentforumid"], $CURUSER["id"]);
    if (!$moderator && !$forummoderator && ($permissions[$thread["currentforumid"]]["canview"] != "yes" || $permissions[$thread["currentforumid"]]["canviewthreads"] != "yes")) {
        print_no_permission(true);
        exit;
    }
    $query = sql_query("SELECT tid, subject FROM " . TSF_PREFIX . "threads WHERE lastpost < " . $thread["lastpost"] . " AND $fid = " . $thread["currentforumid"] . " ORDER BY lastpost DESC LIMIT 1");
    if (mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $prev_thread = $Result["tid"];
        $realsubject = htmlspecialchars_uni(ts_remove_badwords($Result["subject"]));
        redirect($BASEURL . "/tsf_forums/showthread.php?$tid = " . $prev_thread, "", "", true);
        exit;
    }
    stderr($lang->global["error"], $lang->tsf_forums["p_error"]);
}
if ($action == "print_thread") {
    $threadid = isset($_POST["tid"]) ? intval($_POST["tid"]) : (isset($_GET["tid"]) ? intval($_GET["tid"]) : 0);
    if (!is_valid_id($threadid)) {
        stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
        exit;
    }
    ($query = sql_query("SELECT \r\n\t\t\tt.tid, t.subject, f.type, f.fid as currentforumid, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "threads t \t\t\t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\tWHERE t.$tid = " . sqlesc($threadid) . " LIMIT 1")) || sqlerr(__FILE__, 180);
    if (mysqli_num_rows($query) == 0) {
        stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
        exit;
    }
    $thread = mysqli_fetch_assoc($query);
    $forummoderator = is_forum_mod($thread["type"] == "s" ? $thread["deepforumid"] : $thread["currentforumid"], $CURUSER["id"]);
    if (!$moderator && !$forummoderator && ($permissions[$thread["currentforumid"]]["canview"] != "yes" || $permissions[$thread["currentforumid"]]["canviewthreads"] != "yes")) {
        print_no_permission(true);
        exit;
    }
    $query = sql_query("\r\n\t\t\t\tSELECT p.*, u.username\r\n\t\t\t\tFROM " . TSF_PREFIX . "posts p\r\n\t\t\t\tLEFT JOIN users u ON (p.$uid = u.id)\r\n\t\t\t\tWHERE $tid = " . sqlesc($threadid) . "\r\n\t\t\t\tORDER BY dateline ASC\r\n\t\t\t");
    echo "\r\n\t<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\r\n\t<html $xmlns = \"http://www.w3.org/1999/xhtml\" $lang = \"en\" xml:$lang = \"en\" />\r\n\t<head>\r\n\t<meta http-$equiv = \"Content-Type\" $content = \"text/html; $charset = " . $charset . "\" />\r\n\t<style $type = \"text/css\">\r\n\t\t<!--\r\n\t\ttd, p, li, div\r\n\t\t{\r\n\t\t\tfont: 10pt verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;\r\n\t\t}\r\n\t\t.smalltext\r\n\t\t{\r\n\t\t\tfont-size: 11px;\r\n\t\t}\r\n\t\t-->\r\n\t</style>\r\n\t<title>Powered by " . VERSION . " &copy; " . date("Y") . " " . $SITENAME . "</title>\t\t\r\n\t</head>\r\n\t<body>\r\n\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\">\r\n\t<tr>\r\n\t\t<td>\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\tfunction ts_print_page()\r\n\t\t\t{\r\n\t\t\t\twindow.print();  \r\n\t\t\t}\r\n\t\t</script>\r\n\t\t<b>" . $lang->tsf_forums["pthread"] . ":</b> <a $href = \"" . $BASEURL . "/tsf_forums/showthread.php?$tid = " . $threadid . "\">" . $BASEURL . "/tsf_forums/showthread.php?$tid = " . $threadid . "</a> <input $type = \"button\" $value = \"" . $lang->tsf_forums["pthread"] . "\" $onClick = \"ts_print_page()\" class=\"smalltext\">\r\n\t\t<hr /></td>\r\n\t</tr>\r\n\t";
    while ($post = mysqli_fetch_assoc($query)) {
        $reviewpostdate = my_datee($dateformat, $post["dateline"]) . " " . my_datee($timeformat, $post["dateline"]);
        $reviewmessage = format_comment($post["message"], true, true, true, false);
        echo "\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t<span class=\"smalltext\"><strong>" . $lang->tsf_forums["posted_by"] . " " . $post["username"] . " - " . $reviewpostdate . "</strong></span><hr />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t" . ($post["visible"] == 0 && !$moderator && !$forummoderator && $post["uid"] != $CURUSER["id"] ? "<span class=\"highlight\">" . $lang->tsf_forums["moderatemsg7"] . "</span>" : $reviewmessage) . "\r\n\t\t\t</td>\r\n\t\t</tr>";
    }
    echo "</table>\r\n\t</body>\r\n\t</html>";
    exit;
}
if ($action == "email_thread") {
    $threadid = isset($_POST["tid"]) ? intval($_POST["tid"]) : (isset($_GET["tid"]) ? intval($_GET["tid"]) : 0);
    if (!is_valid_id($threadid)) {
        stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
        exit;
    }
    ($query = sql_query("SELECT \r\n\t\t\tt.tid, t.subject, f.type, f.fid as currentforumid, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "threads t \t\t\t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\tWHERE t.$tid = " . sqlesc($threadid) . " LIMIT 1")) || sqlerr(__FILE__, 277);
    if (mysqli_num_rows($query) == 0) {
        stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
        exit;
    }
    $thread = mysqli_fetch_assoc($query);
    $forummoderator = is_forum_mod($thread["type"] == "s" ? $thread["deepforumid"] : $thread["currentforumid"], $CURUSER["id"]);
    if (!$moderator && !$forummoderator && ($permissions[$thread["currentforumid"]]["canview"] != "yes" || $permissions[$thread["currentforumid"]]["canviewthreads"] != "yes")) {
        print_no_permission(true);
        exit;
    }
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $femail = trim($_POST["femail"]);
        $tmsg = trim($_POST["tmsg"]);
        $tsubject = trim($_POST["tsubject"]);
        $fname = trim($_POST["fname"]);
        if (!check_email($femail) || empty($tmsg) || strlen($tmsg) < 10 || empty($tsubject) || strlen($tsubject) < 3 || empty($fname)) {
            $error[] = $lang->global["dontleavefieldsblank"];
        }
        if (count($error) == 0) {
            $m_body = sprintf($lang->tsf_forums["tmsgs"], $fname, $CURUSER["username"], $CURUSER["email"], $SITENAME, $BASEURL . "/tsf_forums/", htmlspecialchars_uni($tmsg));
            $m_subject = htmlspecialchars_uni($tsubject);
            sent_mail($femail, $m_subject, $m_body, "email_thread", false);
            redirect("tsf_forums/showthread.php?$tid = " . $threadid);
            exit;
        }
    }
    stdhead($lang->tsf_forums["ethreadh"]);
    show_misc_errors();
    echo "\r\n\t<form $method = \"post\" $name = \"email_thread\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$action = email_thread&$tid = " . $threadid . "\" " . submit_disable("email_thread", "tbutton") . ">\r\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $celspecing = \"5\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $align = \"center\" $colspan = \"2\">" . $lang->tsf_forums["ethreadh"] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\" $width = \"20%\" $valign = \"top\">\r\n\t\t\t\t<b>" . $lang->tsf_forums["fname"] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td $align = \"left\" $width = \"80%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"text\" $name = \"fname\" $value = \"" . (isset($fname) && $fname ? htmlspecialchars_uni($fname) : "") . "\" $size = \"30\">\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\" $width = \"20%\" $valign = \"top\">\r\n\t\t\t\t<b>" . $lang->tsf_forums["femail"] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td $align = \"left\" $width = \"80%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"text\" $name = \"femail\" $value = \"" . (isset($femail) && $femail ? htmlspecialchars_uni($femail) : "") . "\" $size = \"30\">\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\" $width = \"20%\" $valign = \"top\">\r\n\t\t\t\t<b>" . $lang->tsf_forums["tsubject"] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td $align = \"left\" $width = \"80%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"text\" $name = \"tsubject\" $value = \"" . htmlspecialchars_uni(isset($tsubject) && $tsubject ? $tsubject : $thread["subject"]) . "\" $size = \"30\">\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"right\" $width = \"20%\" $valign = \"top\">\r\n\t\t\t\t<b>" . $lang->tsf_forums["tmsg"] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td $align = \"left\" $width = \"80%\" $valign = \"top\">\r\n\t\t\t\t<textarea $name = \"tmsg\" $cols = \"100\" $rows = \"10\">" . (isset($tmsg) && $tmsg ? htmlspecialchars_uni($tmsg) : sprintf($lang->tsf_forums["tmsgh"], $BASEURL . "/tsf_forums/showthread.php?$tid = " . $threadid, htmlspecialchars_uni($CURUSER["username"]))) . "</textarea>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" $align = \"center\"><input $type = \"submit\" $value = \"" . $lang->global["buttonsend"] . "\" $name = \"tbutton\"> <input $type = \"reset\" $value = \"" . $lang->tsf_forums["button_2"] . "\">\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t";
    stdfoot();
    exit;
}
function submit_disable($formname = "", $buttonname = "", $text = "")
{
    global $lang;
    $value = "onsubmit=\"document." . $formname . "." . $buttonname . ".$value = '" . ($text ? $text : $lang->global["pleasewait"]) . "';document." . $formname . "." . $buttonname . ".$disabled = true\"";
    return $value;
}
function show_misc_errors()
{
    global $error;
    global $lang;
    if (0 < count($error)) {
        $errors = implode("<br />", $error);
        echo "\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->global["error"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<font $color = \"red\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $errors . "\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</font>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}

?>