<?php
@error_reporting(32759);
@ini_set("display_errors", 0);
@ini_set("display_startup_errors", 0);
@ini_set("pcre.backtrack_limit", -1);
@set_time_limit(0);
@ini_set("memory_limit", "256M");
@ini_set("output_buffering", false);
while (ob_get_level()) {
    @ob_end_clean();
}
define("TSCR_VERSION", "1.7 by xam");
define("IN_CRON", true);
define("IN_TRACKER", true);
define("THIS_PATH", dirname(__FILE__));
define("CRON_PATH", THIS_PATH . "/include/cron/");
define("INC_PATH", THIS_PATH . "/include");
define("TSDIR", THIS_PATH);
require INC_PATH . "/php_default_timezone_set.php";
require INC_PATH . "/init.php";
define("TIMENOW", time());
$_FileData = base64_decode("R0lGODlhAQABAIAAAMDAwAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==") /* GIF89a\0\0\0\0\0\0\0!\0\0\0\0,\0\0\0\0\0\0\0D\0;... */ /* GIF89a\0\0\0\0\0\0\0!\0\0\0\0,\0\0\0\0\0\0\0D\0;... */ /* GIF89a\0\0\0\0\0\0\0!\0\0\0\0,\0\0\0\0\0\0\0D\0;... */;
header("Content-type: image/gif");
if (!(strpos($_SERVER["SERVER_SOFTWARE"], "Microsoft-IIS") !== false && strpos(php_sapi_name(), "cgi") !== false)) {
    header("Content-Length: " . strlen($_FileData));
    header("Connection: Close");
}
echo $_FileData;
flush();
require INC_PATH . "/class_ts_database.php";
$TSDatabase = new TSDatabase();
$TSDatabase->connect();
$cronIsRunning = false;
$checkCron = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT content, lastupdate FROM ts_cache WHERE $cachename = \"cronIsRunning\"");
if ($checkCron) {
    $checkCron = mysqli_fetch_assoc($checkCron);
    if ($checkCron["content"] == "yes") {
        if ($checkCron["lastupdate"] + 3600 >= TIMENOW) {
            $cronIsRunning = true;
        }
    }
}
if (!$cronIsRunning) {
    _shutdown("yes");
    require INC_PATH . "/class_config.php";
    $TSSEConfig = new TSConfig();
    $TSSEConfig->TSLoadConfig(["MAIN", "ANNOUNCE", "THEME", "CLEANUP", "KPS", "DATETIME", "TWEAK"]);
    require CRON_PATH . "cron_functions.php";
    require INC_PATH . "/class_language.php";
    $lang = new trackerlanguage();
    $lang->set_path(INC_PATH . "/languages");
    $lang->set_language(isset($_COOKIE["ts_language"]) && file_exists(INC_PATH . "/languages/" . $_COOKIE["ts_language"]) ? $_COOKIE["ts_language"] : $defaultlanguage);
    $lang->load("cronjobs");
    $is_mod = false;
    require_once INC_PATH . "/ts_error_handler.php";
    set_error_handler("TSSEErrorHandler");
    $_CQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `cronid`, `minutes`, `filename`, `loglevel` FROM `ts_cron` WHERE `nextrun` <= " . TIMENOW . " AND `active` = 1");
    if (mysqli_num_rows($_CQuery)) {
        while ($_RunCron = mysqli_fetch_assoc($_CQuery)) {
            if (file_exists(CRON_PATH . $_RunCron["filename"])) {
                $CQueryCount = 0;
                $_CStart = array_sum(explode(" ", microtime()));
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_cron SET $nextrun = " . (TIMENOW + $_RunCron["minutes"]) . " WHERE $cronid = " . $_RunCron["cronid"]);
                require CRON_PATH . $_RunCron["filename"];
                if ($_RunCron["loglevel"] == 1) {
                    LogCronAction($_RunCron["filename"], $CQueryCount, round(array_sum(explode(" ", microtime())) - $_CStart, 4));
                }
            }
        }
    }
    register_shutdown_function("performShutdown");
}
function performShutdown($_content = "no")
{
    mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO ts_cache VALUES(\"cronIsRunning\", \"" . $_content . "\", \"" . TIMENOW . "\")");
}

?>