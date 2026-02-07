<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/performance_mode.lang");
$Message = "";
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$RequiredConfig = "'SECURITY','TWEAK','SHOUTBOX','MAIN','CLEANUP','ANNOUNCE'";
if ($Act == "enable") {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `configname`, `content` FROM ts_config WHERE configname IN (" . $RequiredConfig . ")");
    $Configs = mysqli_fetch_assoc($query);
    while ($Config = mysqli_fetch_assoc($query)) {
        $Contents = unserialize($Config["content"]);
        switch ($Config["configname"]) {
            case "SECURITY":
                $Contents["aggressivecheckip"] = "no";
                break;
            case "TWEAK":
                $Contents["logphperrors"] = "no";
                $Contents["where"] = "no";
                $Contents["iplog1"] = "no";
                $Contents["gzipcompress"] = "yes";
                $Contents["cachesystem"] = "yes";
                $Contents["cachetime"] = "30";
                $Contents["torrentspeed"] = "no";
                $Contents["progressbar"] = "no";
                $Contents["checkconnectable"] = "no";
                $Contents["showsmiliartorrents"] = "no";
                $Contents["showsubtitles"] = "no";
                $Contents["ref"] = "no";
                $Contents["ts_perpage"] = "30";
                break;
            case "SHOUTBOX":
                $Contents["S_REFRESHTIME"] = "22";
                break;
            case "CLEANUP":
                $Contents["delete_old_snatched"] = "90";
                break;
            case "ANNOUNCE":
                $Contents["scrape_system"] = "no";
                $Contents["nc"] = "no";
                $Contents["A_checkconnectable"] = "no";
                $Contents["announce_interval"] = "2700";
                $Contents["announce_wait"] = "90";
                break;
            default:
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_config SET `content` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], serialize($Contents)) . "' WHERE `configname` = '" . $Config["configname"] . "'");
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_staffcp_tools SET $options = '" . time() . "' WHERE $filename = 'performance_mode'");
        }
    }
    $Message = function_136();
    if (empty($Message)) {
        $Message = showAlertError(nl2br("Aggressive IP Ban Check: No\r\nLog PHP Errors: No\r\nSave User Location: No\r\nSave User IP: No\r\nGZIP Compress: Enabled\r\nCache System: Enabled (30 Minutes)\r\nTorrent Speed Mod: Disabled\r\nProgress Bar Mod: Disabled\r\nCheck & Show Connectable: No\r\nShow Smiliar Torrents: No\r\nShow Subtitles: No\r\nSave Referrals: No\r\nTS Per Page Limit: 30\r\nShoutbox Refresh Time: 22\r\nDelete OLD Snatched Torrent Data: 90 days\r\nScrape System: Disabled\r\nNot Connectable SYstem Check: Disabled\r\nCheck Connectable Status: Disabled\r\nAnnounce Interval Value: 45 minutes.\r\nMin. Announce Refresh Time: 90 seconds."));
    }
}
if ($Act == "disable") {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `configname`, `content` FROM ts_config WHERE configname IN (" . $RequiredConfig . ")");
    $Configs = mysqli_fetch_assoc($query);
    while ($Config = mysqli_fetch_assoc($query)) {
        $Contents = unserialize($Config["content"]);
        switch ($Config["configname"]) {
            case "SECURITY":
                $Contents["aggressivecheckip"] = "yes";
                break;
            case "TWEAK":
                $Contents["logphperrors"] = "yes";
                $Contents["where"] = "yes";
                $Contents["iplog1"] = "yes";
                $Contents["gzipcompress"] = "yes";
                $Contents["cachesystem"] = "yes";
                $Contents["cachetime"] = "15";
                $Contents["torrentspeed"] = "yes";
                $Contents["progressbar"] = "yes";
                $Contents["checkconnectable"] = "no";
                $Contents["showsmiliartorrents"] = "yes";
                $Contents["showsubtitles"] = "yes";
                $Contents["ref"] = "no";
                $Contents["ts_perpage"] = "15";
                break;
            case "SHOUTBOX":
                $Contents["S_REFRESHTIME"] = "15";
                break;
            case "CLEANUP":
                $Contents["delete_old_snatched"] = "365";
                break;
            case "ANNOUNCE":
                $Contents["scrape_system"] = "yes";
                $Contents["nc"] = "no";
                $Contents["A_checkconnectable"] = "no";
                $Contents["announce_interval"] = "1800";
                $Contents["announce_wait"] = "30";
                break;
            default:
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_config SET `content` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], serialize($Contents)) . "' WHERE `configname` = '" . $Config["configname"] . "'");
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_staffcp_tools SET $options = '' WHERE $filename = 'performance_mode'");
        }
    }
    $Message = function_136();
    if (empty($Message)) {
        $Message = showAlertError(nl2br("Aggressive IP Ban Check: Yes\r\nLog PHP Errors: Yes\r\nSave User Location: Yes\r\nSave User IP: Yes\r\nGZIP Compress: Enabled\r\nCache System: Enabled (15 minutes)\r\nTorrent Speed Mod: Enabled\r\nProgress Bar Mod: Enabled\r\nCheck & Show Connectable: No\r\nShow Smiliar Torrents: Yes\r\nShow Subtitles: Yes\r\nSave Referrals: No\r\nTS Per Page Limit: 15\r\nShoutbox Refresh Time: 15\r\nDelete OLD Snatched Torrent Data: 365 days\r\nScrape System: Enabled\r\nNot Connectable SYstem Check: Disabled\r\nCheck Connectable Status: Disabled\r\nAnnounce Interval Value: 30 minutes.\r\nMin. Announce Refresh Time: 30 seconds."));
    }
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT options FROM ts_staffcp_tools WHERE $filename = 'performance_mode'");
$Result = mysqli_fetch_assoc($query);
$Options = $Result["options"];
echo "\r\n\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\"><b>" . $Language[2] . "</b></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\" $align = \"left\">" . $Language[3] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Language[4] . ": " . (empty($Options) ? "<font $color = \"red\">" . $Language[6] . "</font> <a $href = \"index.php?do=performance_mode&amp;$act = enable\">" . $Language[7] . "</a>" : "<font $color = \"green\">" . $Language[5] . "</font> <a $href = \"index.php?do=performance_mode&amp;$act = disable\">" . $Language[8] . "</a>") . "</td>\r\n\t</tr>\r\n</table>";
function getStaffLanguage()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function checkStaffAuthentication()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        var_236("../index.php");
    }
}
function redirectTo($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_136()
{
    $var_375 = "";
    $var_281 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'ANNOUNCE'");
    $Result = mysqli_fetch_assoc($var_281);
    $var_56 = unserialize($Result["content"]);
    $var_376 = $var_56["xbt_active"];
    $var_377 = $var_56["xbt_announce_url"];
    $var_378 = $var_56["announce_system"];
    $var_379 = $var_56["announce_interval"];
    $var_380 = $var_56["bannedclientdetect"];
    $var_381 = $var_56["allowed_clients"];
    $var_382 = $var_56["announce_actions"];
    $var_383 = $var_56["max_rate"];
    $var_384 = $var_56["announce_wait"];
    $var_385 = $var_56["aggressivecheat"];
    $var_386 = $var_56["detectbrowsercheats"];
    $var_387 = $var_56["A_checkconnectable"];
    $var_388 = $var_56["checkip"];
    $var_389 = $var_56["banned_ports"];
    $var_390 = $var_56["nc"];
    $var_375 .= "/* ANNOUNCE */";
    $var_375 .= "\$xbt_active = '" . $var_376 . "';";
    if ($var_376 == "yes") {
        $var_375 .= "\$xbt_announce_url = '" . $var_377 . "';";
    }
    $var_375 .= "\$announce_system = '" . $var_378 . "';";
    $var_375 .= "\$announce_interval = '" . $var_379 . "';";
    $var_375 .= "\$bannedclientdetect = '" . $var_380 . "';";
    if ($var_380 == "yes") {
        $var_375 .= "\$allowed_clients = '" . $var_381 . "';";
    }
    $var_375 .= "\$announce_actions = '" . $var_382 . "';";
    $var_375 .= "\$max_rate = '" . $var_383 . "';";
    $var_375 .= "\$announce_wait = '" . $var_384 . "';";
    $var_375 .= "\$aggressivecheat = '" . $var_385 . "';";
    $var_375 .= "\$detectbrowsercheats = '" . $var_386 . "';";
    $var_375 .= "\$A_checkconnectable = '" . $var_387 . "';";
    $var_375 .= "\$checkip = '" . $var_388 . "';";
    $var_375 .= "\$banned_ports = '" . $var_389 . "';";
    $var_375 .= "\$nc = '" . $var_390 . "';";
    $var_281 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
    $Result = mysqli_fetch_assoc($var_281);
    $var_27 = unserialize($Result["content"]);
    $var_391 = $var_27["BASEURL"];
    $var_392 = $var_27["SITENAME"];
    $var_393 = $var_27["cache"];
    $var_375 .= "/* MAIN */";
    $var_375 .= "\$BASEURL = '" . $var_391 . "';";
    $var_375 .= "\$SITENAME = '" . $var_392 . "';";
    $var_375 .= "\$cache = '" . $var_393 . "';";
    $var_281 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'TWEAK'");
    $Result = mysqli_fetch_assoc($var_281);
    $var_394 = unserialize($Result["content"]);
    $var_395 = $var_394["snatchmod"];
    $var_396 = $var_394["gzipcompress"];
    $var_375 .= "/* TWEAK */";
    $var_375 .= "\$snatchmod = '" . $var_395 . "';";
    $var_375 .= "\$gzipcompress = '" . $var_396 . "';";
    $var_281 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'SECURITY'");
    $Result = mysqli_fetch_assoc($var_281);
    $var_400 = unserialize($Result["content"]);
    $var_401 = $var_400["privatetrackerpatch"];
    $var_402 = $var_400["aggressivecheckip"];
    $var_375 .= "/* SECURITY */";
    $var_375 .= "\$privatetrackerpatch = '" . $var_401 . "';";
    $var_375 .= "\$aggressivecheckip = '" . $var_402 . "';";
    $var_281 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'THEME'");
    $Result = mysqli_fetch_assoc($var_281);
    $var_28 = unserialize($Result["content"]);
    $var_403 = $var_28["defaultlanguage"];
    $var_10 = $var_28["charset"];
    $var_375 .= "/* THEME */";
    $var_375 .= "\$defaultlanguage = '" . $var_403 . "';";
    $var_375 .= "\$charset = '" . $var_10 . "';";
    $var_281 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'KPS'");
    $Result = mysqli_fetch_assoc($var_281);
    $var_404 = unserialize($Result["content"]);
    $var_405 = $var_404["bdayreward"];
    $var_406 = $var_404["bdayrewardtype"];
    $var_407 = $var_404["bonus"];
    $var_408 = $var_404["kpsseed"];
    $var_409 = $var_404["kpstype"];
    $var_410 = $var_404["kpsgbamount"];
    $var_375 .= "/* KPS */";
    $var_375 .= "\$bdayreward = '" . $var_405 . "';";
    $var_375 .= "\$bdayrewardtype = '" . $var_406 . "';";
    $var_375 .= "\$bonus = '" . $var_407 . "';";
    $var_375 .= "\$kpsseed = '" . $var_408 . "';";
    $var_375 .= "\$kpstype = '" . $var_409 . "';";
    $var_375 .= "\$kpsgbamount = '" . $var_410 . "';";
    if (file_put_contents("../include/config_announce.php", "<?php\r\n/* Please use Setting Panel to Modify this file! */\r\n" . $var_375 . "\r\n/* Please use Setting Panel to Modify this file! */\r\n?>")) {
        return "";
    }
    return showAlertError("<b>include/config_announce.php</b> isn't writable. Plesae check permissions.");
}

?>