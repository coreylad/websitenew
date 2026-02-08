<?php
define("TT_VERSION", "1.1 by xam");
define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "takethanks.php");
require "./global.php";
header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: text/html; $charset = " . $shoutboxcharset);
if (!isset($CURUSER) || $thankssystem != "yes") {
    exit("<error>" . $lang->global["nopermission"] . "</error>");
}
if ($usergroups["canthanks"] != "yes" || $CURUSER["id"] == 0) {
    exit("<error>" . $lang->global["nopermission"] . "</error>");
}
$torrentid = 0 + $_POST["torrentid"];
$userid = 0 + $CURUSER["id"];
if (!is_valid_id($torrentid) || !is_valid_id($userid)) {
    exit("<error>" . $lang->global["notorrentid"] . "</error>");
}
$res = sql_query("SELECT owner FROM torrents WHERE `id` = '" . $torrentid . "'");
$row = mysqli_fetch_assoc($res);
if (!$row || empty($row) || !$row["owner"]) {
    exit("<error>" . $lang->global["notorrentid"] . "</error>");
}
if ($row["owner"] == $userid) {
    $lang->load("takewhatever");
    exit("<error>" . $lang->takewhatever["cantthankowntorrent"] . "</error>");
}
if (isset($_POST["removethanks"])) {
    if (!$is_mod) {
        exit("<error>" . $lang->global["nopermission"] . "</error>");
    }
    sql_query("DELETE FROM ts_thanks WHERE $tid = '" . $torrentid . "' AND $uid = '" . $userid . "'");
    if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
        $TSSEConfig->TSLoadConfig("KPS");
        KPS("-", $kpsthanks, $row["owner"]);
    }
    show_thanks(true);
}
$tsql = sql_query("SELECT tid FROM ts_thanks WHERE $tid = " . sqlesc($torrentid) . " AND $uid = " . sqlesc($userid));
if (0 < mysqli_num_rows($tsql)) {
    $lang->load("takewhatever");
    exit("<error>" . $lang->takewhatever["alreadythanked"] . "</error>");
}
sql_query("INSERT INTO ts_thanks VALUES ('" . $torrentid . "', '" . $userid . "')");
if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
    $TSSEConfig->TSLoadConfig("KPS");
    KPS("+", $kpsthanks, $row["owner"]);
    show_thanks();
} else {
    exit("<error>" . $lang->global["error"] . "</error>");
}
function show_thanks($Remove = false)
{
    global $lang;
    global $torrentid;
    $array = [];
    $Query = sql_query("SELECT t.uid, u.username, g.namestyle FROM ts_thanks t LEFT JOIN users u ON (t.$uid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE t.$tid = '" . $torrentid . "' ORDER BY u.username");
    if (mysqli_num_rows($Query) != 0) {
        while ($T = mysqli_fetch_assoc($Query)) {
            $array[] = "<a $href = \"" . ts_seo($T["uid"], $T["username"]) . "\">" . get_user_color($T["username"], $T["namestyle"]) . "</a>";
        }
    }
    exit("<div $id = \"thanks_button\">" . (!$Remove ? "<div $id = \"thanks_button\"><input $type = \"button\" $value = \"" . $lang->global["buttonthanks2"] . "\" $onclick = \"javascript:TSajaxquickthanks(" . $torrentid . ", true);\" /></div>" : "<input $type = \"button\" $value = \"" . $lang->global["buttonthanks"] . "\" $onclick = \"javascript:TSajaxquickthanks(" . $torrentid . ");\" /></div>") . implode(", ", $array));
}

?>