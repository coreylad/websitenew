<?php
checkStaffAuthentication();
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
        redirectTo("../index.php");
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
    $configCodeBuffer = "";
    $configQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'ANNOUNCE'");
    $Result = mysqli_fetch_assoc($configQuery);
    $configData = unserialize($Result["content"]);
    $mainConfig = $configData["xbt_active"];
    $themeConfig = $configData["xbt_announce_url"];
    $uploadConfig = $configData["announce_system"];
    $cacheConfig = $configData["announce_interval"];
    $securityConfig = $configData["bannedclientdetect"];
    $emailConfig = $configData["allowed_clients"];
    $smtpConfig = $configData["announce_actions"];
    $socialConfig = $configData["max_rate"];
    $apiConfig = $configData["announce_wait"];
    $proxyConfig = $configData["aggressivecheat"];
    $searchConfig = $configData["detectbrowsercheats"];
    $trackerConfig = $configData["A_checkconnectable"];
    $bonusConfig = $configData["checkip"];
    $torrentConfig = $configData["banned_ports"];
    $userConfig = $configData["nc"];
    $configCodeBuffer .= "/* ANNOUNCE */";
    $configCodeBuffer .= "\$xbt_active = '" . $mainConfig . "';";
    if ($mainConfig == "yes") {
        $configCodeBuffer .= "\$xbt_announce_url = '" . $themeConfig . "';";
    }
    $configCodeBuffer .= "\$announce_system = '" . $uploadConfig . "';";
    $configCodeBuffer .= "\$announce_interval = '" . $cacheConfig . "';";
    $configCodeBuffer .= "\$bannedclientdetect = '" . $securityConfig . "';";
    if ($securityConfig == "yes") {
        $configCodeBuffer .= "\$allowed_clients = '" . $emailConfig . "';";
    }
    $configCodeBuffer .= "\$announce_actions = '" . $smtpConfig . "';";
    $configCodeBuffer .= "\$max_rate = '" . $socialConfig . "';";
    $configCodeBuffer .= "\$announce_wait = '" . $apiConfig . "';";
    $configCodeBuffer .= "\$aggressivecheat = '" . $proxyConfig . "';";
    $configCodeBuffer .= "\$detectbrowsercheats = '" . $searchConfig . "';";
    $configCodeBuffer .= "\$A_checkconnectable = '" . $trackerConfig . "';";
    $configCodeBuffer .= "\$checkip = '" . $bonusConfig . "';";
    $configCodeBuffer .= "\$banned_ports = '" . $torrentConfig . "';";
    $configCodeBuffer .= "\$nc = '" . $userConfig . "';";
    $configQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
    $Result = mysqli_fetch_assoc($configQuery);
    $formAction = unserialize($Result["content"]);
    $forumConfig = $configData["BASEURL"];
    $chatConfig = $configData["SITENAME"];
    $miscConfig = $configData["cache"];
    $configCodeBuffer .= "/* MAIN */";
    $configCodeBuffer .= "\$BASEURL = '" . $forumConfig . "';";
    $configCodeBuffer .= "\$SITENAME = '" . $chatConfig . "';";
    $configCodeBuffer .= "\$cache = '" . $miscConfig . "';";
    $configQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'TWEAK'");
    $Result = mysqli_fetch_assoc($configQuery);
    $donationConfig = unserialize($Result["content"]);
    $lotteryConfig = $donationConfig["snatchmod"];
    $gameConfig = $donationConfig["gzipcompress"];
    $configCodeBuffer .= "/* TWEAK */";
    $configCodeBuffer .= "\$snatchmod = '" . $lotteryConfig . "';";
    $configCodeBuffer .= "\$gzipcompress = '" . $gameConfig . "';";
    $configQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'SECURITY'");
    $Result = mysqli_fetch_assoc($configQuery);
    $seedConfig = unserialize($Result["content"]);
    $hitRunConfig = $seedConfig["privatetrackerpatch"];
    $announceConfig = $seedConfig["aggressivecheckip"];
    $configCodeBuffer .= "/* SECURITY */";
    $configCodeBuffer .= "\$privatetrackerpatch = '" . $hitRunConfig . "';";
    $configCodeBuffer .= "\$aggressivecheckip = '" . $announceConfig . "';";
    $configQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'THEME'");
    $Result = mysqli_fetch_assoc($configQuery);
    $formMethod = unserialize($Result["content"]);
    $rssConfig = $formMethod["defaultlanguage"];
    $htmlOutput = $formMethod["charset"];
    $configCodeBuffer .= "/* THEME */";
    $configCodeBuffer .= "\$defaultlanguage = '" . $rssConfig . "';";
    $configCodeBuffer .= "\$charset = '" . $htmlOutput . "';";
    $configQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'KPS'");
    $Result = mysqli_fetch_assoc($configQuery);
    $statsConfig = unserialize($Result["content"]);
    $logConfig = $statsConfig["bdayreward"];
    $backupConfig = $statsConfig["bdayrewardtype"];
    $maintenanceConfig = $statsConfig["bonus"];
    $performanceConfig = $statsConfig["kpsseed"];
    $debugConfig = $statsConfig["kpstype"];
    $captchaConfig = $statsConfig["kpsgbamount"];
    $configCodeBuffer .= "/* KPS */";
    $configCodeBuffer .= "\$bdayreward = '" . $logConfig . "';";
    $configCodeBuffer .= "\$bdayrewardtype = '" . $backupConfig . "';";
    $configCodeBuffer .= "\$bonus = '" . $maintenanceConfig . "';";
    $configCodeBuffer .= "\$kpsseed = '" . $performanceConfig . "';";
    $configCodeBuffer .= "\$kpstype = '" . $debugConfig . "';";
    $configCodeBuffer .= "\$kpsgbamount = '" . $captchaConfig . "';";
    if (file_put_contents("../include/config_announce.php", "<?php\r\n/* Please use Setting Panel to Modify this file! */\r\n" . $configCodeBuffer . "\r\n/* Please use Setting Panel to Modify this file! */\r\n?>")) {
        return "";
    }
    return showAlertError("<b>include/config_announce.php</b> isn't writable. Plesae check permissions.");
}

?>