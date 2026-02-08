<?php
ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
error_reporting(32759);
require "./include/php_default_timezone_set.php";
define("DP_Version", "1.0 by xam");
$peer_id = isset($_GET["peer_id"]) ? $_GET["peer_id"] : "";
if (!$peer_id) {
    Stop("Empty PEER ID!");
}
if (strlen($peer_id) != 20) {
    Stop("Invalid PEER ID!");
}
fast_db_connect();
mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO ts_peerid_list VALUES (" . sqlesc($peer_id) . ")");
Stop("Thank you. Peer ID has been added into database! Please stop the torrent or update the announce url!");
function fast_db_connect()
{
    require "./include/config_database.php";
    $GLOBALS["DatabaseConnect"] = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
    if ($GLOBALS["DatabaseConnect"]) {
    } else {
        exit("Database Connection Error");
    }
}
function sqlesc($value)
{
    if (@get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
    return "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $value) . "'";
}
function Stop($msg)
{
    $announce_interval = 86400;
    header("Content-Type: text/plain");
    header("Pragma: no-cache");
    exit("d14:failure reason" . strlen($msg) . ":" . $msg . "e");
}

?>