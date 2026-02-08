<?php
define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("TS_AJAX_VERSION", "1.2.2 by xam");
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "ts_ajax8.php");
require "./global.php";
$id = intval($_POST["tid"]);
$page = intval($_POST["page"]);
$_GET["page"] = $page;
if (strtoupper($_SERVER["REQUEST_METHOD"]) != "POST" || !isset($CURUSER) || !$id) {
    exit;
}
$lang->load("quick_editor");
include INC_PATH . "/functions_quick_editor.php";
require INC_PATH . "/commenttable.php";
require INC_PATH . "/functions_ts_ajax_pager.php";
$count = TSRowCount("id", "comments", "torrent=" . $id);
list($pagertop, $pagerbottom, $limit) = TSAjaxPager($ts_perpage, $count, $id);
($subres = sql_query("SELECT c.id, c.torrent as torrentid, c.text, c.user, c.added, c.editedby, c.editedat, c.modnotice, c.modeditid, c.modeditusername, c.modedittime, c.totalvotes, c.visible, uu.username as editedbyuname, gg.namestyle as editbynamestyle, u.added as registered, u.enabled, u.warned, u.leechwarn, u.username, u.title, u.usergroup, u.last_access, u.options, u.donor, u.uploaded, u.downloaded, u.avatar as useravatar, u.signature, g.title as grouptitle, g.namestyle, t.name as torrentname FROM comments c LEFT JOIN users uu ON (c.$editedby = uu.id) LEFT JOIN usergroups gg ON (uu.$usergroup = gg.gid) LEFT JOIN users u ON (c.$user = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) LEFT JOIN torrents t ON (c.$torrent = t.id) WHERE c.$torrent = " . sqlesc($id) . " ORDER BY c.id " . $limit)) || sqlerr(__FILE__, 44);
$torrent = [];
$allrows = [];
while ($subrow = mysqli_fetch_assoc($subres)) {
    if (!isset($torrent["name"])) {
        $torrent["name"] = $subrow["torrentname"];
    }
    $allrows[] = $subrow;
}
$showcommenttable = commenttable($allrows, "", "", false, true, true);
header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: text/html; $charset = " . $shoutboxcharset);
exit($pagertop . $showcommenttable . $pagerbottom);

?>