<?php
define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "ts_ajax5.php");
require "./global.php";
define("TS_AJAX_VERSION", "1.2.0 by xam");
define("TU_VERSION", "2.6.6 by xam");
if (strtoupper($_SERVER["REQUEST_METHOD"]) != "POST" || !isset($CURUSER) || !$is_mod) {
    exit;
}
if (!isset($_POST["tid"]) || !is_valid_id($_POST["tid"])) {
    show_msg($lang->global["notorrentid"], true);
}
$id = intval($_POST["tid"]);
$Query = sql_query("SELECT t_link FROM torrents WHERE `id` = '" . $id . "'");
if (mysqli_num_rows($Query) == 0) {
    show_msg($lang->global["notorrentid"], true);
}
$Result = mysqli_fetch_assoc($Query);
$oldt_link = $Result["t_link"];
if (!$oldt_link) {
    show_msg($lang->global["notorrentid"], true);
}
preg_match("@<a $href = '(.*)'@U", $oldt_link, $imdblink);
$t_link = $imdblink[1];
if ($t_link) {
    include_once INC_PATH . "/ts_imdb.php";
}
if ($t_link) {
    sql_query("UPDATE torrents SET $t_link = " . sqlesc($t_link) . " WHERE `id` = '" . $id . "'");
    require_once INC_PATH . "/functions_imdb_rating.php";
    if ($IMDBRating = TSSEGetIMDBRatingImage($t_link)) {
        $t_link = str_replace("<b>User Rating:</b>", "<b>User Rating:</b> " . $IMDBRating["image"], $t_link);
    }
    show_msg($t_link);
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

?>