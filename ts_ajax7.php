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
define("THIS_SCRIPT", "ts_ajax7.php");
require "./global.php";
define("TS_AJAX_VERSION", "1.2.0 by xam");
if (strtoupper($_SERVER["REQUEST_METHOD"]) != "POST" || !isset($CURUSER)) {
    exit;
}
$lang->load("ts_blog");
if (!$is_mod && (!BlogPermission("canpost") || !BlogPermission("canview"))) {
    show_msg($lang->global["nopermission"], true);
}
// Blog ID and comment text
$blogId = intval($_POST["bid"]);
$commentText = fixAjaxText($_POST["message"]);
if (strlen($commentText) < 2) {
    show_msg($lang->global["dontleavefieldsblank"], true);
}
$commentText = strval($commentText);
if (strtolower($shoutboxcharset) != "utf-8") {
    if (function_exists("iconv")) {
        $commentText = iconv("UTF-8", $shoutboxcharset, $commentText);
    } else {
        if (function_exists("mb_convert_encoding")) {
            $commentText = mb_convert_encoding($commentText, $shoutboxcharset, "UTF-8");
        } else {
            if (strtolower($shoutboxcharset) == "iso-8859-1") {
                $commentText = utf8_decode($commentText);
            }
        }
    }
}
if ($_POST["do"] == "save_comment" && is_valid_id($blogId)) {
    $blogQuery = sql_query("SELECT uid, allowcomments FROM ts_blogs WHERE `bid` = " . sqlesc($blogId));
    if (mysqli_num_rows($blogQuery) == 0) {
        show_msg($lang->ts_blog["disabled"], true);
    } else {
        $blogData = mysqli_fetch_assoc($blogQuery);
    }
    if (!$is_mod && $blogData["allowcomments"] == 0) {
        show_msg($lang->ts_blog["disabled"], true);
    }
    $commentTime = TIMENOW;
    sql_query("INSERT INTO ts_blogs_comments (bid, uid, date, descr) VALUES (" . sqlesc($blogId) . ", " . sqlesc($CURUSER["id"]) . ", " . sqlesc($commentTime) . ", " . sqlesc($commentText) . ")");
    $commentId = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
    if ($commentId) {
        if ($blogData["uid"] != $CURUSER["id"]) {
            require_once INC_PATH . "/functions_pm.php";
            send_pm($blogData["uid"], sprintf($lang->ts_blog["s4"], $BASEURL . "/ts_blog.php?do=show_blog&$bid = " . $blogId . "&$cid = " . $commentId . "#show_comments" . $commentId), $lang->ts_blog["s5"]);
        }
        sql_query("UPDATE ts_blogs SET $comments = comments + 1, $lastposter = " . sqlesc($CURUSER["id"]) . ", $lastpostdate = " . sqlesc($commentTime) . " WHERE `bid` = " . sqlesc($blogId));
        $subscriptionQuery = sql_query("SELECT uid FROM ts_blogs_subscribe WHERE `bid` = " . sqlesc($blogId) . " AND uid != " . sqlesc($CURUSER["id"]));
        if (0 < mysqli_num_rows($subscriptionQuery)) {
            require_once INC_PATH . "/functions_pm.php";
            while ($subscriber = mysqli_fetch_assoc($subscriptionQuery)) {
                send_pm($subscriber["uid"], sprintf($lang->ts_blog["s6"], $BASEURL . "/ts_blog.php?do=show_blog&$bid = " . $blogId . "&$cid = " . $commentId . "#show_comments" . $commentId), $lang->ts_blog["s5"]);
            }
        }
    }
    $posterHtml = "<a $href = \"" . ts_seo($CURUSER["id"], $CURUSER["username"]) . "\">" . get_user_color($CURUSER["username"], $usergroups["namestyle"]) . "</a>";
    show_msg("\t\r\n\t<table $align = \"center\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" $align = \"left\" class=\"subheader\">\r\n\t\t\t\t<span $style = \"float: right;\">" . (BlogPermission("caneditc") || $is_mod ? "<a $href = \"" . $BASEURL . "/ts_blog.php?do=edit_comment&amp;$cid = " . $commentId . "&amp;$bid = " . $blogId . "\"><u><i>" . $lang->ts_blog["edit2"] . "</i></u></a> |" : "") . (BlogPermission("candeletec") || $is_mod ? " <a $href = \"" . $BASEURL . "/ts_blog.php?do=delete_comment&amp;$cid = " . $commentId . "&amp;$bid = " . $blogId . "\" $onclick = \"return AreYouSure('" . $lang->ts_blog["sure2"] . "');\"><u><i>" . $lang->ts_blog["delete2"] . "</i></u></a>" : "") . "</span>\r\n\t\t\t\t" . sprintf($lang->ts_blog["posted"], my_datee($dateformat, $commentTime), my_datee($timeformat, $commentTime), $posterHtml) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"left\" $width = \"1%\" $height = \"50\">" . get_user_avatar($CURUSER["avatar"], false, 50, 50) . "</td>\r\n\t\t\t<td $valign = \"top\" $width = \"99%\">\r\n\t\t\t\t" . format_comment($commentText) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>");
} else {
    show_msg($lang->global["dontleavefieldsblank"], true);
}
function show_msg($message = "", $error = false)
{
    global $shoutboxcharset;
    header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Content-type: text/html; $charset = " . $shoutboxcharset);
    if ($error) {
        exit("<error>" . $message . "</error>");
    }
    exit($message);
}
function BlogPermission($Option)
{
    global $usergroups;
    $Options = ["canview" => "0", "cancreate" => "1", "caneditb" => "2", "candeleteb" => "3", "canpost" => "4", "caneditc" => "5", "candeletec" => "6", "candisablec" => "7"];
    $What = isset($Options[$Option]) ? $Options[$Option] : 0;
    return $usergroups["blogperms"][$What] == "1" ? true : false;
}

?>