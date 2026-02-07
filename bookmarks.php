<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "bookmarks.php");
require "./global.php";
define("BK_VERSION", "0.6 by xam");
// AJAX quick bookmark flag
$isAjaxQuickBookmark = !empty($_POST["ajax_quick_bookmark"]);
if ($usergroups["canbookmark"] != "yes" || $CURUSER["id"] == 0) {
    if (!$isAjaxQuickBookmark) {
        print_no_permission();
    } else {
        show_msg($lang->global["nopermission"]);
    }
}
// Action and IDs
$bookmarkAction = isset($_POST["action"]) ? $_POST["action"] : (isset($_GET["action"]) ? $_GET["action"] : "");
$bookmarkTorrentId = isset($_POST["torrentid"]) ? intval($_POST["torrentid"]) : (isset($_GET["torrentid"]) ? intval($_GET["torrentid"]) : "");
$bookmarkUserId = intval($CURUSER["id"]);
if (!is_valid_id($bookmarkTorrentId)) {
    if (!$isAjaxQuickBookmark) {
        print_no_permission(true);
    } else {
        show_msg($lang->global["nopermission"]);
    }
}
if ($bookmarkAction == "delete") {
    $bookmarkQuery = @sql_query("SELECT userid,torrentid FROM bookmarks WHERE $userid = " . @sqlesc($bookmarkUserId) . " AND $torrentid = " . @sqlesc($bookmarkTorrentId));
    if (mysqli_num_rows($bookmarkQuery) != 0) {
        @sql_query("DELETE FROM bookmarks WHERE $userid = " . @sqlesc($bookmarkUserId) . " AND $torrentid = " . @sqlesc($bookmarkTorrentId));
    }
} else {
    if ($bookmarkAction == "add") {
        $bookmarkQuery = @sql_query("SELECT userid,torrentid FROM bookmarks WHERE $userid = " . @sqlesc($bookmarkUserId) . " AND $torrentid = " . @sqlesc($bookmarkTorrentId));
        if (mysqli_num_rows($bookmarkQuery) == 0) {
            @sql_query("INSERT INTO bookmarks (userid, torrentid) VALUES (" . @sqlesc($bookmarkUserId) . "," . @sqlesc($bookmarkTorrentId) . ")");
        }
    }
}
if (!$isAjaxQuickBookmark) {
    redirect("browse.php?$special_search = mybookmarks");
}
function show_msg($message = "")
{
    global $shoutboxcharset;
    header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Content-type: text/html; $charset = " . $shoutboxcharset);
    exit("<error>" . $message . "</error>");
}

?>