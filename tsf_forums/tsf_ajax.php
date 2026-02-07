<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "tsf_ajax.php");
require "./global.php";
$ajax_action = TS_Global("action");
if (!$ajax_action || !$CURUSER["id"]) {
    xmlhttp_error($lang->global["nopermission"]);
    exit;
}
if ($ajax_action == "thanks" && strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    if (!isset($CURUSER) || $CURUSER["id"] == 0 || $thankssystem != "yes" || $usergroups["canthanks"] != "yes") {
        xmlhttp_error($lang->global["nopermission"]);
    }
    $tid = isset($_POST["tid"]) ? intval($_POST["tid"]) : 0;
    $pid = isset($_POST["pid"]) ? intval($_POST["pid"]) : 0;
    if (!is_valid_id($tid) || !is_valid_id($pid)) {
        xmlhttp_error($lang->tsf_forums["invalid_tid"]);
    }
    ($query = sql_query("SELECT p.uid as posterid, t.closed, f.type, f.fid as currentforumid, ff.fid as deepforumid, u.username, g.namestyle\r\n\t\t\tFROM " . TSF_PREFIX . "posts p\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "threads t ON (p.$tid = t.tid)\t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\tLEFT JOIN users u ON (p.$uid = u.id)\r\n\t\t\tLEFT JOIN usergroups g ON (u.`usergroup` = g.gid)\r\n\t\t\tWHERE p.$tid = " . sqlesc($tid) . " AND p.$pid = " . sqlesc($pid) . " LIMIT 1")) || sqlerr(__FILE__, 63);
    if (mysqli_num_rows($query) == 0) {
        xmlhttp_error($lang->tsf_forums["invalid_tid"]);
    }
    $thread = mysqli_fetch_assoc($query);
    $forummoderator = is_forum_mod($thread["type"] == "s" ? $thread["deepforumid"] : $thread["currentforumid"], $CURUSER["id"]);
    if (!$moderator && !$forummoderator && $permissions[$thread["currentforumid"]]["canview"] != "yes") {
        xmlhttp_error($lang->tsf_forums["noperm"]);
    }
    $posterforthanks = get_user_color($thread["username"], $thread["namestyle"]);
    $kpsuserid = $thread["posterid"];
    if ($kpsuserid == $CURUSER["id"]) {
        xmlhttp_error($lang->tsf_forums["noperm"]);
    }
    if (isset($_POST["removethanks"])) {
        sql_query("DELETE FROM " . TSF_PREFIX . "thanks WHERE $tid = '" . $tid . "' AND $pid = '" . $pid . "' AND $uid = '" . $CURUSER["id"] . "'");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $TSSEConfig->TSLoadConfig("KPS");
            KPS("-", $kpsthanks, $kpsuserid);
        }
        show_thanks(true);
    } else {
        $query = sql_query("SELECT uid FROM " . TSF_PREFIX . "thanks WHERE $tid = '" . $tid . "' AND $pid = '" . $pid . "' AND $uid = '" . $CURUSER["id"] . "'");
        if (0 < mysqli_num_rows($query)) {
            xmlhttp_error($lang->tsf_forums["thanked"]);
        }
        sql_query("INSERT INTO " . TSF_PREFIX . "thanks VALUES ('" . $tid . "', '" . $pid . "', '" . $CURUSER["id"] . "')");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $TSSEConfig->TSLoadConfig("KPS");
            KPS("+", $kpsthanks, $kpsuserid);
            show_thanks();
        } else {
            xmlhttp_error($lang->global["error"]);
        }
    }
}
if ($ajax_action == "save_quick_edit" && strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    if (!isset($CURUSER) || $CURUSER["id"] == 0) {
        xmlhttp_error($lang->tsf_forums["noperm"]);
    }
    $tid = isset($_POST["tid"]) ? intval($_POST["tid"]) : 0;
    $pid = isset($_POST["pid"]) ? intval($_POST["pid"]) : 0;
    if (!is_valid_id($tid) || !is_valid_id($pid)) {
        xmlhttp_error($lang->tsf_forums["invalid_tid"]);
    }
    ($query = sql_query("SELECT p.uid as posterid,  p.message, t.closed, f.type, f.fid as currentforumid, f.moderate, ff.fid as deepforumid, ff.moderate as moderaterf \r\n\t\t\tFROM " . TSF_PREFIX . "posts p\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "threads t ON (p.$tid = t.tid)\t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\tWHERE p.$tid = " . sqlesc($tid) . " AND p.$pid = " . sqlesc($pid) . " LIMIT 1")) || sqlerr(__FILE__, 135);
    if (mysqli_num_rows($query) == 0) {
        xmlhttp_error($lang->tsf_forums["invalid_tid"]);
    }
    $thread = mysqli_fetch_assoc($query);
    $forummoderator = is_forum_mod($thread["type"] == "s" ? $thread["deepforumid"] : $thread["currentforumid"], $CURUSER["id"]);
    if (($thread["moderate"] == 1 || $thread["moderaterf"] == 1) && ($forummoderator || $moderator)) {
        $thread["moderate"] = 0;
        $thread["moderaterf"] = 0;
    }
    $visible = $thread["moderate"] == 1 || $thread["moderaterf"] == 1 ? 0 : 1;
    if (!$moderator && !$forummoderator && ($permissions[$thread["currentforumid"]]["caneditposts"] != "yes" || $permissions[$thread["currentforumid"]]["canview"] != "yes" || $permissions[$thread["currentforumid"]]["canpostreplys"] != "yes")) {
        xmlhttp_error($lang->tsf_forums["noperm"]);
    } else {
        if (!$moderator && !$forummoderator && $thread["closed"] == "yes") {
            xmlhttp_error($lang->tsf_forums["thread_closed"]);
        } else {
            if (!$moderator && !$forummoderator && $thread["posterid"] != $CURUSER["id"]) {
                xmlhttp_error($lang->tsf_forums["noperm"]);
            }
        }
    }
    $text = fixAjaxText($_POST["text"]);
    if ($text != $thread["message"]) {
        $uid = sqlesc($CURUSER["id"]);
        $dateline = sqlesc(TIMENOW);
        $text = strval($text);
        if (strtolower($shoutboxcharset) != "utf-8") {
            if (function_exists("iconv")) {
                $text = iconv("UTF-8", $shoutboxcharset, $text);
            } else {
                if (function_exists("mb_convert_encoding")) {
                    $text = mb_convert_encoding($text, $shoutboxcharset, "UTF-8");
                } else {
                    if (strtolower($shoutboxcharset) == "iso-8859-1") {
                        $text = utf8_decode($text);
                    }
                }
            }
        }
        if (strlen($text) < $f_minmsglength) {
            xmlhttp_error($lang->tsf_forums["too_short"]);
        }
        ($query = sql_query("SELECT dateline FROM " . TSF_PREFIX . "posts WHERE `uid` = " . $uid . " ORDER by dateline DESC LIMIT 1")) || sqlerr(__FILE__, 194);
        if (0 < mysqli_num_rows($query)) {
            $Result = mysqli_fetch_assoc($query);
            $last_post = $Result["dateline"];
            $floodcheck = flood_check($lang->tsf_forums["a_post"], $last_post, true);
            if ($floodcheck != "") {
                xmlhttp_error($floodcheck);
            }
        }
        $eq0 = "";
        if ($usergroups["cansettingspanel"] != "yes") {
            $eq0 = ", $edituid = " . $uid . ", $edittime = " . $dateline;
        }
        @sql_query("UPDATE " . TSF_PREFIX . "posts SET $visible = " . $visible . ", $message = " . @sqlesc($text) . $eq0 . " WHERE $tid = " . @sqlesc($tid) . " AND $pid = " . @sqlesc($pid)) || sqlerr(__FILE__, 211);
    }
    if ($thread["moderate"] == 0 && $thread["moderaterf"] == 0) {
        define("IS_THIS_USER_POSTED", true);
        xmlhttp_show(format_comment($text));
    } else {
        xmlhttp_show(show_notice($lang->tsf_forums["moderatemsg1"]) . "<hr />" . format_comment($text));
    }
}
if ($ajax_action == "quick_edit" && strtoupper($_SERVER["REQUEST_METHOD"]) == "GET") {
    if (!isset($CURUSER) || $CURUSER["id"] == 0) {
        xmlhttp_error($lang->tsf_forums["noperm"]);
    }
    $tid = isset($_GET["tid"]) ? intval($_GET["tid"]) : 0;
    $pid = isset($_GET["pid"]) ? intval($_GET["pid"]) : 0;
    if (!is_valid_id($tid) || !is_valid_id($pid)) {
        xmlhttp_error($lang->tsf_forums["invalid_tid"]);
    }
    ($query = sql_query("SELECT p.uid as posterid,  p.message, t.closed, f.type, f.fid as currentforumid, ff.fid as deepforumid \r\n\t\t\tFROM " . TSF_PREFIX . "posts p\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "threads t ON (p.$tid = t.tid)\t\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\tWHERE p.$tid = " . sqlesc($tid) . " AND p.$pid = " . sqlesc($pid) . " LIMIT 1")) || sqlerr(__FILE__, 242);
    if (mysqli_num_rows($query) == 0) {
        xmlhttp_error($lang->tsf_forums["invalid_tid"]);
    }
    $thread = mysqli_fetch_assoc($query);
    $forummoderator = is_forum_mod($thread["type"] == "s" ? $thread["deepforumid"] : $thread["currentforumid"], $CURUSER["id"]);
    if (!$moderator && !$forummoderator && ($permissions[$thread["currentforumid"]]["caneditposts"] != "yes" || $permissions[$thread["currentforumid"]]["canview"] != "yes" || $permissions[$thread["currentforumid"]]["canpostreplys"] != "yes")) {
        xmlhttp_error($lang->tsf_forums["noperm"]);
    } else {
        if (!$moderator && !$forummoderator && $thread["closed"] == "yes") {
            xmlhttp_error($lang->tsf_forums["thread_closed"]);
        } else {
            if (!$moderator && !$forummoderator && $thread["posterid"] != $CURUSER["id"]) {
                xmlhttp_error($lang->tsf_forums["noperm"]);
            }
        }
    }
    xmlhttp_show(htmlspecialchars_uni($thread["message"]));
}
if ($ajax_action == "edit_subject" && strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    if (!isset($CURUSER) || $CURUSER["id"] == 0) {
        xmlhttp_error($lang->tsf_forums["noperm"]);
    }
    $ajax_tid = isset($_POST["tid"]) ? intval($_POST["tid"]) : "";
    if (!is_valid_id($ajax_tid)) {
        xmlhttp_error($lang->tsf_forums["invalid_tid"]);
    }
    ($query = sql_query("SELECT t.subject, t.fid as ofid, t.closed, t.uid as posterid, t.firstpost, f.type, f.name as currentforum, f.fid as currentforumid, ff.name as deepforum, ff.fid as deepforumid \r\n\t\t\t\tFROM " . TSF_PREFIX . "threads t\r\n\t\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid)\r\n\t\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\t\tWHERE t.$tid = " . sqlesc($ajax_tid) . " LIMIT 1")) || sqlerr(__FILE__, 284);
    if (mysqli_num_rows($query) == 0) {
        xmlhttp_error($lang->tsf_forums["invalid_tid"]);
    }
    $thread = mysqli_fetch_assoc($query);
    $forummoderator = is_forum_mod($thread["type"] == "s" ? $thread["deepforumid"] : $thread["currentforumid"], $CURUSER["id"]);
    if (!$moderator && !$forummoderator && ($permissions[$thread["currentforumid"]]["caneditposts"] != "yes" || $permissions[$thread["currentforumid"]]["canview"] != "yes" || $permissions[$thread["currentforumid"]]["canpostreplys"] != "yes")) {
        xmlhttp_error($lang->tsf_forums["noperm"]);
    } else {
        if (!$moderator && !$forummoderator && $thread["closed"] == "yes") {
            xmlhttp_error($lang->tsf_forums["thread_closed"]);
        } else {
            if (!$moderator && !$forummoderator && $thread["posterid"] != $CURUSER["id"]) {
                xmlhttp_error($lang->tsf_forums["noperm"]);
            }
        }
    }
    if (strlen($_POST["value"]) < $f_minmsglength) {
        xmlhttp_error($lang->tsf_forums["too_short"]);
    }
    ($query = sql_query("SELECT dateline FROM " . TSF_PREFIX . "posts WHERE `uid` = " . sqlesc($CURUSER["id"]) . " ORDER by dateline DESC LIMIT 1")) || sqlerr(__FILE__, 313);
    if (0 < mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $last_post = $Result["dateline"];
        $floodcheck = flood_check($lang->tsf_forums["a_post"], $last_post, true);
        if ($floodcheck != "") {
            xmlhttp_error($floodcheck);
        }
    }
    $subject = $_POST["value"];
    if (strtolower($shoutboxcharset) != "utf-8") {
        if (function_exists("iconv")) {
            $subject = iconv("UTF-8", $shoutboxcharset, $subject);
        } else {
            if (function_exists("mb_convert_encoding")) {
                $subject = mb_convert_encoding($subject, $shoutboxcharset, "UTF-8");
            } else {
                if (strtolower($shoutboxcharset) == "iso-8859-1") {
                    $subject = utf8_decode($subject);
                }
            }
        }
    }
    sql_query("UPDATE " . TSF_PREFIX . "threads SET $subject = " . sqlesc($subject) . " WHERE $tid = " . sqlesc($ajax_tid));
    sql_query("UPDATE " . TSF_PREFIX . "posts SET $subject = " . sqlesc($subject) . ", $edituid = " . $CURUSER["id"] . ", $edittime = " . TIMENOW . " WHERE $tid = " . sqlesc($ajax_tid) . " AND $pid = " . sqlesc($thread["firstpost"]));
    sql_query("UPDATE " . TSF_PREFIX . "forums SET $lastpostsubject = " . sqlesc($subject) . " WHERE $lastposttid = " . sqlesc($ajax_tid) . " AND $fid = " . sqlesc($thread["ofid"]));
    xmlhttp_show($_POST["value"]);
}
function xmlhttp_show($message)
{
    global $shoutboxcharset;
    header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Content-type: text/html; $charset = " . $shoutboxcharset);
    echo $message;
    exit;
}
function xmlhttp_error($message)
{
    global $shoutboxcharset;
    header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Content-type: text/html; $charset = " . $shoutboxcharset);
    echo "<error>" . $message . "</error>";
    exit;
}
function show_thanks($Remove = false)
{
    global $lang;
    global $tid;
    global $pid;
    global $posterforthanks;
    $array = [];
    $Query = sql_query("SELECT t.uid, u.username, g.namestyle FROM tsf_thanks t LEFT JOIN users u ON (t.$uid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE t.$tid = '" . $tid . "' AND t.$pid = '" . $pid . "' ORDER BY u.username");
    if (mysqli_num_rows($Query) == 0) {
        exit;
    }
    while ($T = mysqli_fetch_assoc($Query)) {
        $array[] = "<a $href = \"" . ts_seo($T["uid"], $T["username"]) . "\">" . get_user_color($T["username"], $T["namestyle"]) . "</a>";
    }
    $ThanksCount = count($array);
    exit("\r\n\t<table $width = \"100%\" $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $style = \"clear: both;\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\" $style = \"padding: 0px;\">\r\n\t\t\t\t\t<strong>" . (1 < $ThanksCount ? sprintf($lang->tsf_forums["thanks"], ts_nf($ThanksCount), $posterforthanks) : sprintf($lang->tsf_forums["thank"], $posterforthanks)) . "</strong>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<div>\r\n\t\t\t\t\t\t" . implode(", ", $array) . "\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>");
}

?>