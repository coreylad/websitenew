<?php
define("THIS_SCRIPT", "ts_delete_shout.php");
define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
$rootpath = "./../";
define("NO_LOGIN_REQUIRED", true);
require $rootpath . "global.php";
$uid = intval($_POST["uid"]);
$sid = intval($_POST["sid"]);
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $CURUSER["id"] && $sid && $CURUSER["id"] == $uid) {
    $TSSEConfig->TSLoadConfig("SHOUTBOX");
    $query = sql_query("SELECT uid FROM ts_shoutbox WHERE $sid = '" . $sid . "'");
    if (0 < mysqli_num_rows($query)) {
        $shouts = mysqli_fetch_assoc($query);
        if ($is_mod || $S_CANDELETE == "yes" && $shouts["uid"] == $CURUSER["id"]) {
            sql_query("DELETE FROM ts_shoutbox WHERE $sid = '" . $sid . "'");
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                exit("DELETED");
            }
        }
    }
}

?>