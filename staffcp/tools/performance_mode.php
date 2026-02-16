<?php
declare(strict_types=1);

checkStaffAuthentication();

try {
    $Language = file("languages/" . getStaffLanguage() . "/performance_mode.lang");
    if ($Language === false) {
        throw new RuntimeException("Failed to load language file");
    }
    
    $Message = "";
    $Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
    
    if ($Act === "enable" || $Act === "disable") {
        if (!isset($_SESSION['csrf_token']) || !isset($_GET['csrf_token']) || 
            !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
            throw new RuntimeException("CSRF token validation failed");
        }
    }
    
    $pdo = getPDOConnection();
    $RequiredConfigs = ['SECURITY', 'TWEAK', 'SHOUTBOX', 'MAIN', 'CLEANUP', 'ANNOUNCE'];
    
    if ($Act === "enable") {
        $stmt = $pdo->prepare("SELECT `configname`, `content` FROM ts_config WHERE configname IN (?, ?, ?, ?, ?, ?)");
        $stmt->execute($RequiredConfigs);
        
        while ($Config = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Contents = unserialize($Config["content"]);
            if ($Contents === false) {
                continue;
            }
            
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
            }
            
            $updateStmt = $pdo->prepare("UPDATE ts_config SET `content` = ? WHERE `configname` = ?");
            $updateStmt->execute([serialize($Contents), $Config["configname"]]);
        }
        
        $toolsStmt = $pdo->prepare("UPDATE ts_staffcp_tools SET options = ? WHERE filename = ?");
        $toolsStmt->execute([time(), 'performance_mode']);
        
        $Message = function_136($pdo);
        if (empty($Message)) {
            $Message = showAlertError(nl2br("Aggressive IP Ban Check: No\r\nLog PHP Errors: No\r\nSave User Location: No\r\nSave User IP: No\r\nGZIP Compress: Enabled\r\nCache System: Enabled (30 Minutes)\r\nTorrent Speed Mod: Disabled\r\nProgress Bar Mod: Disabled\r\nCheck & Show Connectable: No\r\nShow Smiliar Torrents: No\r\nShow Subtitles: No\r\nSave Referrals: No\r\nTS Per Page Limit: 30\r\nShoutbox Refresh Time: 22\r\nDelete OLD Snatched Torrent Data: 90 days\r\nScrape System: Disabled\r\nNot Connectable SYstem Check: Disabled\r\nCheck Connectable Status: Disabled\r\nAnnounce Interval Value: 45 minutes.\r\nMin. Announce Refresh Time: 90 seconds."));
        }
    }
    
    if ($Act === "disable") {
        $stmt = $pdo->prepare("SELECT `configname`, `content` FROM ts_config WHERE configname IN (?, ?, ?, ?, ?, ?)");
        $stmt->execute($RequiredConfigs);
        
        while ($Config = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Contents = unserialize($Config["content"]);
            if ($Contents === false) {
                continue;
            }
            
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
            }
            
            $updateStmt = $pdo->prepare("UPDATE ts_config SET `content` = ? WHERE `configname` = ?");
            $updateStmt->execute([serialize($Contents), $Config["configname"]]);
        }
        
        $toolsStmt = $pdo->prepare("UPDATE ts_staffcp_tools SET options = ? WHERE filename = ?");
        $toolsStmt->execute(['', 'performance_mode']);
        
        $Message = function_136($pdo);
        if (empty($Message)) {
            $Message = showAlertError(nl2br("Aggressive IP Ban Check: Yes\r\nLog PHP Errors: Yes\r\nSave User Location: Yes\r\nSave User IP: Yes\r\nGZIP Compress: Enabled\r\nCache System: Enabled (15 minutes)\r\nTorrent Speed Mod: Enabled\r\nProgress Bar Mod: Enabled\r\nCheck & Show Connectable: No\r\nShow Smiliar Torrents: Yes\r\nShow Subtitles: Yes\r\nSave Referrals: No\r\nTS Per Page Limit: 15\r\nShoutbox Refresh Time: 15\r\nDelete OLD Snatched Torrent Data: 365 days\r\nScrape System: Enabled\r\nNot Connectable SYstem Check: Disabled\r\nCheck Connectable Status: Disabled\r\nAnnounce Interval Value: 30 minutes.\r\nMin. Announce Refresh Time: 30 seconds."));
        }
    }
    
    $stmt = $pdo->prepare("SELECT options FROM ts_staffcp_tools WHERE filename = ?");
    $stmt->execute(['performance_mode']);
    $Result = $stmt->fetch(PDO::FETCH_ASSOC);
    $Options = $Result["options"] ?? "";
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrfToken = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
    
    echo "\r\n\r\n" . $Message . "\r\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" align=\"center\"><b>" . htmlspecialchars($Language[2], ENT_QUOTES, 'UTF-8') . "</b></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\" align=\"left\">" . htmlspecialchars($Language[3], ENT_QUOTES, 'UTF-8') . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . htmlspecialchars($Language[4], ENT_QUOTES, 'UTF-8') . ": " . (empty($Options) ? "<font color=\"red\">" . htmlspecialchars($Language[6], ENT_QUOTES, 'UTF-8') . "</font> <a href=\"index.php?do=performance_mode&amp;act=enable&amp;csrf_token=" . $csrfToken . "\">" . htmlspecialchars($Language[7], ENT_QUOTES, 'UTF-8') . "</a>" : "<font color=\"green\">" . htmlspecialchars($Language[5], ENT_QUOTES, 'UTF-8') . "</font> <a href=\"index.php?do=performance_mode&amp;act=disable&amp;csrf_token=" . $csrfToken . "\">" . htmlspecialchars($Language[8], ENT_QUOTES, 'UTF-8') . "</a>") . "</td>\r\n\t</tr>\r\n</table>";
    
} catch (Exception $e) {
    error_log("Performance Mode Error: " . $e->getMessage());
    echo showAlertError("An error occurred: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

function getPDOConnection(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $configPath = __DIR__ . "/../include/config_database.php";
        if (!is_file($configPath)) {
            throw new RuntimeException("Database config file not found");
        }
        
        require $configPath;
        
        if (!defined('MYSQL_HOST') || !defined('MYSQL_USER') || !defined('MYSQL_PASS') || !defined('MYSQL_DB')) {
            throw new RuntimeException("Database configuration constants not defined");
        }
        
        try {
            $pdo = new PDO(
                "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DB . ";charset=utf8mb4",
                MYSQL_USER,
                MYSQL_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("PDO Connection Error: " . $e->getMessage());
            throw new RuntimeException("Failed to connect to database");
        }
    }
    return $pdo;
}

function getStaffLanguage(): string
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}

function checkStaffAuthentication(): void
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        redirectTo("../index.php");
    }
}

function redirectTo(string $url): void
{
    $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\twindow.location.href = \"" . $safeUrl . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-equiv=\"refresh\" content=\"0;url=" . $safeUrl . "\" />\r\n\t\t</noscript>";
    }
    exit;
}

function showAlertError(string $Error): string
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}

function logStaffAction(PDO $pdo, string $log): void
{
    try {
        $stmt = $pdo->prepare("INSERT INTO ts_staffcp_logs (uid, date, log) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION["ADMIN_ID"], time(), $log]);
    } catch (PDOException $e) {
        error_log("Failed to log staff action: " . $e->getMessage());
    }
}

function function_136(PDO $pdo): string
{
    try {
        $configCodeBuffer = "";
        
        $stmt = $pdo->prepare("SELECT `content` FROM `ts_config` WHERE `configname` = ?");
        $stmt->execute(['ANNOUNCE']);
        $Result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$Result || !isset($Result["content"])) {
            throw new RuntimeException("Failed to fetch ANNOUNCE config");
        }
        
        $configData = unserialize($Result["content"]);
        if ($configData === false) {
            throw new RuntimeException("Failed to unserialize ANNOUNCE config");
        }
        
        $mainConfig = $configData["xbt_active"] ?? "";
        $themeConfig = $configData["xbt_announce_url"] ?? "";
        $uploadConfig = $configData["announce_system"] ?? "";
        $cacheConfig = $configData["announce_interval"] ?? "";
        $securityConfig = $configData["bannedclientdetect"] ?? "";
        $emailConfig = $configData["allowed_clients"] ?? "";
        $smtpConfig = $configData["announce_actions"] ?? "";
        $socialConfig = $configData["max_rate"] ?? "";
        $apiConfig = $configData["announce_wait"] ?? "";
        $proxyConfig = $configData["aggressivecheat"] ?? "";
        $searchConfig = $configData["detectbrowsercheats"] ?? "";
        $trackerConfig = $configData["A_checkconnectable"] ?? "";
        $bonusConfig = $configData["checkip"] ?? "";
        $torrentConfig = $configData["banned_ports"] ?? "";
        $userConfig = $configData["nc"] ?? "";
        
        $configCodeBuffer .= "/* ANNOUNCE */";
        $configCodeBuffer .= "\$xbt_active = '" . addslashes($mainConfig) . "';";
        if ($mainConfig === "yes") {
            $configCodeBuffer .= "\$xbt_announce_url = '" . addslashes($themeConfig) . "';";
        }
        $configCodeBuffer .= "\$announce_system = '" . addslashes($uploadConfig) . "';";
        $configCodeBuffer .= "\$announce_interval = '" . addslashes($cacheConfig) . "';";
        $configCodeBuffer .= "\$bannedclientdetect = '" . addslashes($securityConfig) . "';";
        if ($securityConfig === "yes") {
            $configCodeBuffer .= "\$allowed_clients = '" . addslashes($emailConfig) . "';";
        }
        $configCodeBuffer .= "\$announce_actions = '" . addslashes($smtpConfig) . "';";
        $configCodeBuffer .= "\$max_rate = '" . addslashes($socialConfig) . "';";
        $configCodeBuffer .= "\$announce_wait = '" . addslashes($apiConfig) . "';";
        $configCodeBuffer .= "\$aggressivecheat = '" . addslashes($proxyConfig) . "';";
        $configCodeBuffer .= "\$detectbrowsercheats = '" . addslashes($searchConfig) . "';";
        $configCodeBuffer .= "\$A_checkconnectable = '" . addslashes($trackerConfig) . "';";
        $configCodeBuffer .= "\$checkip = '" . addslashes($bonusConfig) . "';";
        $configCodeBuffer .= "\$banned_ports = '" . addslashes($torrentConfig) . "';";
        $configCodeBuffer .= "\$nc = '" . addslashes($userConfig) . "';";
        
        $stmt = $pdo->prepare("SELECT `content` FROM `ts_config` WHERE `configname` = ?");
        $stmt->execute(['MAIN']);
        $Result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$Result || !isset($Result["content"])) {
            throw new RuntimeException("Failed to fetch MAIN config");
        }
        
        $formAction = unserialize($Result["content"]);
        if ($formAction === false) {
            throw new RuntimeException("Failed to unserialize MAIN config");
        }
        
        $forumConfig = $formAction["BASEURL"] ?? "";
        $chatConfig = $formAction["SITENAME"] ?? "";
        $miscConfig = $formAction["cache"] ?? "";
        
        $configCodeBuffer .= "/* MAIN */";
        $configCodeBuffer .= "\$BASEURL = '" . addslashes($forumConfig) . "';";
        $configCodeBuffer .= "\$SITENAME = '" . addslashes($chatConfig) . "';";
        $configCodeBuffer .= "\$cache = '" . addslashes($miscConfig) . "';";
        
        $stmt = $pdo->prepare("SELECT `content` FROM `ts_config` WHERE `configname` = ?");
        $stmt->execute(['TWEAK']);
        $Result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$Result || !isset($Result["content"])) {
            throw new RuntimeException("Failed to fetch TWEAK config");
        }
        
        $donationConfig = unserialize($Result["content"]);
        if ($donationConfig === false) {
            throw new RuntimeException("Failed to unserialize TWEAK config");
        }
        
        $lotteryConfig = $donationConfig["snatchmod"] ?? "";
        $gameConfig = $donationConfig["gzipcompress"] ?? "";
        
        $configCodeBuffer .= "/* TWEAK */";
        $configCodeBuffer .= "\$snatchmod = '" . addslashes($lotteryConfig) . "';";
        $configCodeBuffer .= "\$gzipcompress = '" . addslashes($gameConfig) . "';";
        
        $stmt = $pdo->prepare("SELECT `content` FROM `ts_config` WHERE `configname` = ?");
        $stmt->execute(['SECURITY']);
        $Result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$Result || !isset($Result["content"])) {
            throw new RuntimeException("Failed to fetch SECURITY config");
        }
        
        $seedConfig = unserialize($Result["content"]);
        if ($seedConfig === false) {
            throw new RuntimeException("Failed to unserialize SECURITY config");
        }
        
        $hitRunConfig = $seedConfig["privatetrackerpatch"] ?? "";
        $announceConfig = $seedConfig["aggressivecheckip"] ?? "";
        
        $configCodeBuffer .= "/* SECURITY */";
        $configCodeBuffer .= "\$privatetrackerpatch = '" . addslashes($hitRunConfig) . "';";
        $configCodeBuffer .= "\$aggressivecheckip = '" . addslashes($announceConfig) . "';";
        
        $stmt = $pdo->prepare("SELECT `content` FROM `ts_config` WHERE `configname` = ?");
        $stmt->execute(['THEME']);
        $Result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$Result || !isset($Result["content"])) {
            throw new RuntimeException("Failed to fetch THEME config");
        }
        
        $formMethod = unserialize($Result["content"]);
        if ($formMethod === false) {
            throw new RuntimeException("Failed to unserialize THEME config");
        }
        
        $rssConfig = $formMethod["defaultlanguage"] ?? "";
        $htmlOutput = $formMethod["charset"] ?? "";
        
        $configCodeBuffer .= "/* THEME */";
        $configCodeBuffer .= "\$defaultlanguage = '" . addslashes($rssConfig) . "';";
        $configCodeBuffer .= "\$charset = '" . addslashes($htmlOutput) . "';";
        
        $stmt = $pdo->prepare("SELECT `content` FROM `ts_config` WHERE `configname` = ?");
        $stmt->execute(['KPS']);
        $Result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$Result || !isset($Result["content"])) {
            throw new RuntimeException("Failed to fetch KPS config");
        }
        
        $statsConfig = unserialize($Result["content"]);
        if ($statsConfig === false) {
            throw new RuntimeException("Failed to unserialize KPS config");
        }
        
        $logConfig = $statsConfig["bdayreward"] ?? "";
        $backupConfig = $statsConfig["bdayrewardtype"] ?? "";
        $maintenanceConfig = $statsConfig["bonus"] ?? "";
        $performanceConfig = $statsConfig["kpsseed"] ?? "";
        $debugConfig = $statsConfig["kpstype"] ?? "";
        $captchaConfig = $statsConfig["kpsgbamount"] ?? "";
        
        $configCodeBuffer .= "/* KPS */";
        $configCodeBuffer .= "\$bdayreward = '" . addslashes($logConfig) . "';";
        $configCodeBuffer .= "\$bdayrewardtype = '" . addslashes($backupConfig) . "';";
        $configCodeBuffer .= "\$bonus = '" . addslashes($maintenanceConfig) . "';";
        $configCodeBuffer .= "\$kpsseed = '" . addslashes($performanceConfig) . "';";
        $configCodeBuffer .= "\$kpstype = '" . addslashes($debugConfig) . "';";
        $configCodeBuffer .= "\$kpsgbamount = '" . addslashes($captchaConfig) . "';";
        
        $fileContent = "<?php\r\n/* Please use Setting Panel to Modify this file! */\r\n" . $configCodeBuffer . "\r\n/* Please use Setting Panel to Modify this file! */\r\n?>";
        
        if (file_put_contents("../include/config_announce.php", $fileContent) === false) {
            return showAlertError("<b>include/config_announce.php</b> isn't writable. Please check permissions.");
        }
        
        return "";
        
    } catch (Exception $e) {
        error_log("function_136 Error: " . $e->getMessage());
        return showAlertError("Failed to write config file: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
}

?>