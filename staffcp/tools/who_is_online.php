<?php

declare(strict_types=1);

require_once __DIR__ . '/../staffcp_modern.php';

checkStaffAuthentication();

$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Language = loadStaffLanguage('who_is_online');
$Message = "";
$Found = "";
if (isset($_GET["ip"]) && !empty($_GET["ip"])) {
    $IP = escape_html($_GET["ip"]);
    $Host = gethostbyaddr($IP);
    if (!$Host || $IP == $Host) {
        $Host = $Language[12];
    }
    $Message = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>" . $Language[9] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . $Language[4] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . $Language[11] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $IP . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $Host . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
}
if ($Act == "today") {
    try {
        $result = $TSDatabase->query(
            "SELECT u.username, u.ip, u.last_access, u.page, u.uploaded, u.downloaded, g.namestyle 
             FROM users u 
             LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) 
             WHERE UNIX_TIMESTAMP(u.last_access) > ? 
             ORDER BY u.last_access DESC, u.username ASC",
            [time() - 86400]
        );
        
        $users = $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
        $totalCount = count($users);
        
        $Found .= "\r\n\t" . showAlertMessage("<a $href = \"index.php?do=who_is_online\">" . $Language[2] . "</a>") . "\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"5\"><b>" . $Language[8] . " (" . number_format($totalCount) . ")</b></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\"><b>" . $Language[3] . "</b></td>\r\n\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t<td class=\"alt2\"><b>" . $Language[13] . "</b></td>\r\n\t\t<td class=\"alt2\"><b>" . $Language[14] . "</b></td>\r\n\t\t<td class=\"alt2\"><b>" . $Language[7] . "</b></td>\r\n\t</tr>\r\n\t";
        
        $Count = 0;
        foreach ($users as $User) {
            $class = $Count % 2 == 1 ? "alt2" : "alt1";
            $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . escape_attr($User["username"]) . "\">" . applyUsernameStyle($User["username"], $User["namestyle"]) . "</a>\r\n\t\t\t\t\t<br />\r\n\t\t\t\t\t<small>" . $Language[4] . ": <a $href = \"index.php?do=who_is_online&amp;$act = today&amp;$ip = " . escape_attr($User["ip"]) . "\">" . escape_html($User["ip"]) . "</a></small>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t" . formatTimestamp($User["last_access"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t" . formatBytes($User["uploaded"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t" . formatBytes($User["downloaded"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t<a $href = \"" . str_replace("&amp;", "&", escape_html($User["page"])) . "\">" . identifyUserLocation($User["page"]) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
            $Count++;
        }
        
        $Found .= "\r\n\t</table>";
        echo $Found;
    } catch (Exception $e) {
        error_log('Who is online (today) error: ' . $e->getMessage());
        echo showAlertErrorModern('Failed to fetch user data');
    }
}
if (!$Found) {
    try {
        $result = $TSDatabase->query(
            "SELECT s.*, u.username, g.namestyle 
             FROM ts_sessions s 
             LEFT JOIN users u ON (s.`userid` = u.id) 
             LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) 
             WHERE s.lastactivity > ? 
             GROUP BY u.id 
             ORDER BY s.lastactivity DESC, u.username ASC",
            [time() - 3600]
        );
        
        $sessions = $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
        $Count = 0;
        
        foreach ($sessions as $User) {
            $class = $Count % 2 == 1 ? "alt2" : "alt1";
            $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t" . ($User["userid"] ? "<a $href = \"index.php?do=edit_user&amp;$username = " . escape_attr($User["username"]) . "\">" . applyUsernameStyle($User["username"], $User["namestyle"]) . "</a>" : identifyUserAgentBot($User["useragent"])) . "\r\n\t\t\t\t\t<br />\r\n\t\t\t\t\t<small>" . $Language[4] . ": <a $href = \"index.php?do=who_is_online&amp;$ip = " . escape_attr($User["host"]) . "\">" . escape_html($User["host"]) . "</a></small>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t" . escape_html($User["useragent"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t" . formatTimestamp($User["lastactivity"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t<a $href = \"" . str_replace("&amp;", "&", escape_html($User["location"])) . "\">" . identifyUserLocation($User["location"]) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
            $Count++;
        }
        
        $totalCount = count($sessions);
        echo "\r\n\t" . showAlertMessage("<a $href = \"index.php?do=who_is_online&amp;$act = today\">" . $Language[8] . "</a>") . "\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"4\"><b>" . $Language[2] . " (" . number_format($totalCount) . ")</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[3] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[7] . "</b></td>\r\n\t\t</tr>\r\n\t\t" . $Found . "\r\n\t</table>";
    } catch (Exception $e) {
        error_log('Who is online error: ' . $e->getMessage());
        echo showAlertErrorModern('Failed to fetch session data');
    }
}

function formatTimestamp($timestamp = "")
{
    $dateFormatPattern = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($dateFormatPattern, $timestamp);
}
function applyUsernameStyle($username, $namestyle)
{
    return str_replace("{username}", $username, $namestyle);
}
function identifyUserLocation($location)
{
    global $Language, $TSDatabase;
    
    try {
        $result = $TSDatabase->query(
            "SELECT `content` FROM `ts_config` WHERE `configname` = ?",
            ['MAIN']
        );
        $configRow = $result ? $result->fetch(PDO::FETCH_ASSOC) : null;
        $configData = $configRow ? unserialize($configRow["content"]) : ['staffcp_path' => 'staffcp'];
    } catch (Exception $e) {
        error_log('Config fetch error: ' . $e->getMessage());
        $configData = ['staffcp_path' => 'staffcp'];
    }
    $userLocationDescription = "<font $color = \"red\"><b>Unknown Location!</b></font>";
    if (strstr($location, "tsf_forums") && !strstr($location, "returnto")) {
        preg_match_all("#\\/tsf_forums\\/(.*)\\.php#U", $location, $results, PREG_SET_ORDER);
        switch ($results[0][1]) {
            case "index":
                $userLocationDescription = "Viewing Index.";
                break;
            case "forumdisplay":
                $userLocationDescription = "Viewing Forum.";
                break;
            case "showthread":
                $userLocationDescription = "Viewing Thread.";
                break;
            case "announcement":
                $userLocationDescription = "Viewing Announcement.";
                break;
            case "deletepost":
            case "massdelete":
                $userLocationDescription = "Deleting Post.";
                break;
            case "editpost":
                $userLocationDescription = "Editing Post.";
                break;
            case "moderation":
                $userLocationDescription = "Moderating Thread/Post.";
                break;
            case "newreply":
                $userLocationDescription = "Posting Reply.";
                break;
            case "newthread":
                $userLocationDescription = "Creating Thread.";
                break;
            case "poll":
                $userLocationDescription = "Voting Poll.";
                break;
            case "subscription":
                $userLocationDescription = "Subscription Thread.";
                break;
            case "threadrate":
                $userLocationDescription = "Rating Thread.";
                break;
            case "tsf_search":
                $userLocationDescription = "Searching Forums.";
                break;
            case "attachment":
                $userLocationDescription = "Viewing Attachment.";
                break;
            case "top_stats":
                $userLocationDescription = "Viewing Top 10 Forum Stats.";
                break;
            case "syndication":
                $userLocationDescription = "TSF Forums RSS Feeds";
                break;
            default:
                $userLocationDescription = "<b>Forum:</b> " . $userLocationDescription;
        }
    } else {
        if (strstr($location, "/ts_games/")) {
            $userLocationDescription = "<b>Viewing/Playing Arcade.</b>";
        } else {
            if (strstr($location, "/ts_shoutbox/")) {
                $userLocationDescription = "<b>Viewing Shoutbox.</b>";
            } else {
                if (strstr($location, "/admin/")) {
                    $userLocationDescription = "<b>Viewing Admin Panel.</b>";
                } else {
                    if (strstr($location, "/" . $configData["staffcp_path"] . "/")) {
                        $userLocationDescription = "<b>Viewing Staff Control Panel.</b>";
                    } else {
                        if (strstr($location, "/shoutcast/")) {
                            $userLocationDescription = "<b>Shoutcast:</b> Listening Music.";
                        } else {
                            if (strstr($location, "/pbar/")) {
                                $userLocationDescription = "Viewing Donation Status.";
                            } else {
                                preg_match_all("#\\/(.*)\\.php#U", $location, $results, PREG_SET_ORDER);
                                switch ($results[0][1]) {
                                    case "ts_error":
                                        $userLocationDescription = "<strong>Viewing Error Message</strong>";
                                        break;
                                    case "listen":
                                        $userLocationDescription = "Listening Image Verification Code";
                                        break;
                                    case "ok":
                                        $userLocationDescription = "Viewing Confirmation Page.";
                                        break;
                                    case "index":
                                        $userLocationDescription = "Viewing Index Page.";
                                        break;
                                    case "browse":
                                        $userLocationDescription = "Viewing Browse Page.";
                                        break;
                                    case "comment":
                                        $userLocationDescription = "Viewing Comment Page.";
                                        break;
                                    case "donate":
                                        $userLocationDescription = "Viewing Donation Page.";
                                        break;
                                    case "edit":
                                        $userLocationDescription = "Editing Torrent.";
                                        break;
                                    case "faq":
                                        $userLocationDescription = "Viewing FAQ Page.";
                                        break;
                                    case "finduser":
                                        $userLocationDescription = "Searching User.";
                                        break;
                                    case "friends":
                                        $userLocationDescription = "Viewing Friends Page.";
                                        break;
                                    case "getrss":
                                    case "rss":
                                        $userLocationDescription = "Viewing RSS Page.";
                                        break;
                                    case "invite":
                                        $userLocationDescription = "Viewing Invite Page.";
                                        break;
                                    case "logout":
                                        $userLocationDescription = "Logout.";
                                        break;
                                    case "messages":
                                        $userLocationDescription = "Viewing Messages.";
                                        break;
                                    case "sendmessage":
                                        $userLocationDescription = "Sending PM.";
                                        break;
                                    case "mybonus":
                                        $userLocationDescription = "Viewing Bonus Page.";
                                        break;
                                    case "referrals":
                                        $userLocationDescription = "Viewing Referrals Page.";
                                        break;
                                    case "topten":
                                        $userLocationDescription = "Viewing TOPTEN Page.";
                                        break;
                                    case "viewsnatches":
                                        $userLocationDescription = "Viewing Snatches Page.";
                                        break;
                                    case "userdetails":
                                        $userLocationDescription = "Viewing Userdetails Page.";
                                        break;
                                    case "details":
                                        $userLocationDescription = "Viewing Torrent Details.";
                                        break;
                                    case "upload":
                                        $userLocationDescription = "Uploading Torrent.";
                                        break;
                                    case "ts_subtitles":
                                        $userLocationDescription = "Viewing Subtitles Page.";
                                        break;
                                    case "download":
                                        $userLocationDescription = "Downloading Torrent.";
                                        break;
                                    case "badusers":
                                        $userLocationDescription = "Viewing BadUsers Page.";
                                        break;
                                    case "usercp":
                                        $userLocationDescription = "Viewing User Control Panel.";
                                        break;
                                    case "bookmarks":
                                        $userLocationDescription = "Viewing Bookmarks Page.";
                                        break;
                                    case "users":
                                        $userLocationDescription = "Viewing Member List.";
                                        break;
                                    case "rules":
                                        $userLocationDescription = "Viewing Rules Page.";
                                        break;
                                    case "takerate":
                                        $userLocationDescription = "Rating Torrent.";
                                        break;
                                    case "image":
                                        $userLocationDescription = "Showing Image Verification String.";
                                        break;
                                    case "login":
                                    case "takelogin":
                                        $userLocationDescription = "Logging.";
                                        break;
                                    case "signup":
                                        $userLocationDescription = "Registering.";
                                        break;
                                    case "recover":
                                    case "recoverhint":
                                        $userLocationDescription = "Recovering Password.";
                                        break;
                                    case "confirm":
                                        $userLocationDescription = "Confirming account.";
                                        break;
                                    case "staff":
                                        $userLocationDescription = "Viewing Staff Page.";
                                        break;
                                    case "contactstaff":
                                        $userLocationDescription = "Sending Message to Staff.";
                                        break;
                                    case "contactus":
                                        $userLocationDescription = "Viewing Contact Us Page.";
                                        break;
                                    case "links":
                                        $userLocationDescription = "Viewing Useful Links Page.";
                                        break;
                                    case "redirector_footer":
                                        $userLocationDescription = "Redirecting.";
                                        break;
                                    case "stats":
                                        $userLocationDescription = "Viewing Tracker Statistics Page.";
                                        break;
                                    case "ts_applications":
                                        $userLocationDescription = "Viewing Applications Page";
                                        break;
                                    case "ts_social_groups":
                                        $userLocationDescription = "Viewing Social Groups";
                                        break;
                                    case "viewrequests":
                                        $userLocationDescription = "Viewing Request Page";
                                        break;
                                    case "ts_blog":
                                        $userLocationDescription = "Viewing Blogs";
                                        break;
                                    case "ts_tags":
                                        $userLocationDescription = "Viewing Search Cloud";
                                        break;
                                    case "report":
                                        $userLocationDescription = "Reporting...";
                                        break;
                                    case "ts_albums":
                                        $userLocationDescription = "Viewing Albums";
                                        break;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return $userLocationDescription;
}
function formatBytes($bytes = 0)
{
    if ($bytes < 1024000) {
        return number_format($bytes / 1024, 2) . " KB";
    }
    if ($bytes < 1048576000) {
        return number_format($bytes / 1048576, 2) . " MB";
    }
    if ($bytes < 0) {
        return number_format($bytes / 1073741824, 2) . " GB";
    }
    return number_format($bytes / 0, 2) . " TB";
}
function identifyUserAgentBot($Data = "")
{
    global $Language;
    $currentVersion = ["msnbot" => "MSN Bot", "google" => "Google Bot", "yahoo" => "Yahoo! Bot", "alexa" => "AleXa Bot", "sogou" => "Sogou Web Spider", "baiduspider" => "Baidu Spider", "w3c_validator" => "W3C Validator", "mlbot" => "MLBoT", "yandex" => "YanDeX"];
    foreach ($currentVersion as $latestVersion => $versionCheck) {
        if (preg_match("@" . $latestVersion . "@Uis", strtolower($Data))) {
            return "<b><i><font $color = \"#FF6633\">" . $versionCheck . "</font></i></b>";
        }
    }
    return "<b><i><font $color = \"#990066\">" . $Language[10] . "</font></i></b>";
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>