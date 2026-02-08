<?php
define("THIS_SCRIPT", "subscription.php");
require "./global.php";
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
$tid = intval(TS_Global("tid"));
$do = htmlspecialchars_uni(TS_Global("do"));
$userid = intval($CURUSER["id"]);
if (!is_valid_id($tid)) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
$query = sql_query("SELECT fid FROM " . TSF_PREFIX . "threads WHERE $tid = " . sqlesc($tid));
if (mysqli_num_rows($query) == 0) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
$thread = mysqli_fetch_assoc($query);
if (!$moderator && ($permissions[$thread["fid"]]["canview"] != "yes" || $permissions[$thread["fid"]]["canviewthreads"] != "yes")) {
    print_no_permission(true);
    exit;
}
if ($do == "addsubscription") {
    $query = sql_query("SELECT userid FROM " . TSF_PREFIX . "subscribe WHERE $tid = " . sqlesc($tid) . " AND $userid = " . sqlesc($userid));
    if (mysqli_num_rows($query) != 0) {
        redirect("tsf_forums/showthread.php?$tid = " . $tid, $lang->tsf_forums["dsubs"]);
        exit;
    }
    sql_query("INSERT INTO " . TSF_PREFIX . "subscribe (tid,userid) VALUES (" . sqlesc($tid) . "," . sqlesc($userid) . ")");
    redirect("tsf_forums/showthread.php?$tid = " . $tid, $lang->tsf_forums["dsubs"]);
    exit;
}
if ($do == "removesubscription") {
    sql_query("DELETE FROM " . TSF_PREFIX . "subscribe WHERE `userid` = " . sqlesc($userid) . " AND $tid = " . sqlesc($tid));
    redirect("tsf_forums/showthread.php?$tid = " . $tid, $lang->tsf_forums["rsubs"]);
    exit;
}
print_no_permission();
exit;

?>