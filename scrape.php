<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
error_reporting(32759);
require "./include/php_default_timezone_set.php";
define("S_VERSION", "1.5 by xam");
fast_db_connect();
define("IN_TRACKER", true);
require_once "./include/class_config.php";
$TSSEConfig = new TSConfig();
$TSSEConfig->TSLoadConfig(["ANNOUNCE", "TWEAK"]);
if ($scrape_system != "yes") {
    stop("Scrape System is Offline!");
}
$info_hash = isset($_GET["info_hash"]) ? $_GET["info_hash"] : "";
if (!$info_hash) {
    stop("Empty INFO HASH!");
}
if (get_magic_quotes_gpc()) {
    $info_hash = stripslashes($info_hash);
}
$UseMemcached = false;
if (isset($memcached_enabled) && $memcached_enabled == "yes" && isset($memcached_host) && !empty($memcached_host) && isset($memcached_port) && intval($memcached_port) && class_exists("Memcache", false)) {
    require_once "./include/class_ts_memcache.php";
    $TSMemcache = new TSMemcache($memcached_host, $memcached_port, true);
    $UseMemcached = true;
}
$Query = "SELECT seeders, times_completed, leechers FROM torrents WHERE info_hash = " . sqlesc($info_hash) . " OR info_hash = " . sqlesc(preg_replace("/ *\$/s", "", $info_hash));
if ($UseMemcached) {
    $hash = "scrape_" . md5($info_hash);
    if (!($row = $TSMemcache->check($hash))) {
        $res = mysqli_query($GLOBALS["DatabaseConnect"], $Query);
        if (@mysqli_num_rows($res) == 0) {
            stop("Mysql: No result found!");
        }
        $row = mysqli_fetch_assoc($res);
        $TSMemcache->add($hash, $row);
    }
} else {
    $res = mysqli_query($GLOBALS["DatabaseConnect"], $Query);
    if (@mysqli_num_rows($res) == 0) {
        stop("Mysql: No result found!");
    }
    $row = mysqli_fetch_assoc($res);
}
$resp = "d5:filesd20:" . str_pad($info_hash, 20) . "d8:completei" . $row["seeders"] . "e10:downloadedi" . $row["times_completed"] . "e10:incompletei" . $row["leechers"] . "eeee";
header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: text/html; charset=iso-8859-1");
if (isset($_SERVER["HTTP_ACCEPT_ENCODING"]) && $_SERVER["HTTP_ACCEPT_ENCODING"] == "gzip") {
    header("Content-Encoding: gzip");
    exit(gzencode($resp, 2, FORCE_GZIP));
}
exit($resp);
function fast_db_connect()
{
    require "./include/config_database.php";
    $GLOBALS["DatabaseConnect"] = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
    if (!$GLOBALS["DatabaseConnect"]) {
        Stop("Database Connection Error");
    }
}
function sqlesc($value)
{
    if (@get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
    return "'" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $value) . "'";
}
function terminateProcess($msg)
{
    header("Content-Type: text/plain");
    header("Pragma: no-cache");
    exit("d14:failure reason" . strlen($msg) . ":" . $msg . "e");
}

?>