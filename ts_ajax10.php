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
define("THIS_SCRIPT", "ts_ajax10.php");
require "./global.php";
define("TS_AJAX_VERSION", "1.2.0 by xam");
if (strtoupper($_SERVER["REQUEST_METHOD"]) != "POST" || !isset($CURUSER) || $CURUSER["id"] == 0) {
    exit($lang->global["nopermission"]);
}
$lang->load("ts_blog");
if (!$is_mod && $usergroups["cancomment"] == "no") {
    show_msg($lang->global["nopermission"], true);
}
$tid = intval($_POST["tid"]);
$text = fixAjaxText($_POST["message"]);
if (strlen($text) < 2) {
    show_msg($lang->global["dontleavefieldsblank"], true);
}
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
if ($_POST["do"] == "save_comment" && is_valid_id($tid)) {
    $TIME = TIMENOW;
    sql_query("INSERT INTO ts_tutorials_comments (tid, uid, date, descr) VALUES (" . sqlesc($tid) . ", " . sqlesc($CURUSER["id"]) . ", " . sqlesc($TIME) . ", " . sqlesc($text) . ")");
    $CID = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
    $Poster = "<a $href = \"" . ts_seo($CURUSER["id"], $CURUSER["username"]) . "\">" . get_user_color($CURUSER["username"], $usergroups["namestyle"]) . "</a>";
    displayMessage("\t\r\n\t<table $align = \"center\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" $align = \"left\" class=\"subheader\">\r\n\t\t\t\t<span $style = \"float: right;\">" . ($is_mod ? "<a $href = \"" . $BASEURL . "/ts_tutorials.php?do=edit_comment&amp;$cid = " . $CID . "&amp;$tid = " . $tid . "\"><u><i>" . $lang->ts_blog["edit2"] . "</i></u></a> |" : "") . ($is_mod ? " <a $href = \"" . $BASEURL . "/ts_tutorials.php?do=delete_comment&amp;$cid = " . $CID . "&amp;$tid = " . $tid . "\" $onclick = \"return AreYouSure('" . $lang->ts_blog["sure2"] . "');\"><u><i>" . $lang->ts_blog["delete2"] . "</i></u></a>" : "") . "</span>\r\n\t\t\t\t" . sprintf($lang->ts_blog["posted"], my_datee($dateformat, $TIME), my_datee($timeformat, $TIME), $Poster) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $align = \"left\" $width = \"1%\" $height = \"50\">" . get_user_avatar($CURUSER["avatar"], false, 50, 50) . "</td>\r\n\t\t\t<td $valign = \"top\" $width = \"99%\">\r\n\t\t\t\t" . format_comment($text) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>");
} else {
    displayMessage($lang->global["dontleavefieldsblank"], true);
}
function displayMessage($message = "", $error = false)
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

?>