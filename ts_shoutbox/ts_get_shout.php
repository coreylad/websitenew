<?php
define("THIS_SCRIPT", "ts_get_shout.php");
define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
$rootpath = "./../";
define("NO_LOGIN_REQUIRED", true);
require $rootpath . "global.php";
$sid = intval($_POST["sid"]);
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $CURUSER["id"] && $sid) {
    $TSSEConfig->TSLoadConfig("SHOUTBOX");
    $query = sql_query("SELECT uid, shout FROM ts_shoutbox WHERE $sid = '" . $sid . "'");
    if (0 < mysqli_num_rows($query)) {
        $shouts = mysqli_fetch_assoc($query);
        if ($is_mod || $S_CANEDIT == "yes" && $shouts["uid"] == $CURUSER["id"]) {
            $shout = htmlspecialchars_uni($shouts["shout"]);
            header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
            header("Content-type: text/html; $charset = " . $shoutboxcharset);
            exit($shout);
        }
    }
}

?>