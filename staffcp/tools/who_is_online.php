<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Language = file("languages/" . getStaffLanguage() . "/who_is_online.lang");
$Message = "";
$Found = "";
if (isset($_GET["ip"]) && !empty($_GET["ip"])) {
    $IP = htmlspecialchars($_GET["ip"]);
    $Host = gethostbyaddr($IP);
    if (!$Host || $IP == $Host) {
        $Host = $Language[12];
    }
    $Message = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>" . $Language[9] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . $Language[4] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . $Language[11] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $IP . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $Host . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
}
if ($Act == "today") {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.username, u.ip, u.last_access, u.page, u.uploaded, u.downloaded, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE UNIX_TIMESTAMP(u.last_access) > " . (time() - 86400) . " ORDER BY u.last_access DESC, u.username ASC");
    $Found .= "\r\n\t" . showAlertMessage("<a $href = \"index.php?do=who_is_online\">" . $Language[2] . "</a>") . "\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"5\"><b>" . $Language[8] . " (" . number_format(mysqli_num_rows($query)) . ")</b></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\"><b>" . $Language[3] . "</b></td>\r\n\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t<td class=\"alt2\"><b>" . $Language[13] . "</b></td>\r\n\t\t<td class=\"alt2\"><b>" . $Language[14] . "</b></td>\r\n\t\t<td class=\"alt2\"><b>" . $Language[7] . "</b></td>\r\n\t</tr>\r\n\t";
    for ($Count = 0; $User = mysqli_fetch_assoc($query); $Count++) {
        $class = $Count % 2 == 1 ? "alt2" : "alt1";
        $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $User["username"] . "\">" . applyUsernameStyle($User["username"], $User["namestyle"]) . "</a>\r\n\t\t\t\t\t<br />\r\n\t\t\t\t\t<small>" . $Language[4] . ": <a $href = \"index.php?do=who_is_online&amp;$act = today&amp;$ip = " . htmlspecialchars($User["ip"]) . "\">" . htmlspecialchars($User["ip"]) . "</a></small>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t" . formatTimestamp($User["last_access"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t" . var_238($User["uploaded"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t" . var_238($User["downloaded"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t<a $href = \"" . str_replace("&amp;", "&", htmlspecialchars($User["page"])) . "\">" . function_259($User["page"]) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
    }
    $Found .= "\r\n\t</table>";
    echo $Found;
}
if (!$Found) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT s.*, u.username, g.namestyle FROM ts_sessions s LEFT JOIN users u ON (s.$userid = u.id) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE s.lastactivity > '" . (time() - 3600) . "' GROUP BY u.id ORDER BY s.lastactivity DESC, u.username ASC");
    for ($Count = 0; $User = mysqli_fetch_assoc($query); $Count++) {
        $class = $Count % 2 == 1 ? "alt2" : "alt1";
        $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t" . ($User["userid"] ? "<a $href = \"index.php?do=edit_user&amp;$username = " . $User["username"] . "\">" . applyUsernameStyle($User["username"], $User["namestyle"]) . "</a>" : function_260($User["useragent"])) . "\r\n\t\t\t\t\t<br />\r\n\t\t\t\t\t<small>" . $Language[4] . ": <a $href = \"index.php?do=who_is_online&amp;$ip = " . htmlspecialchars($User["host"]) . "\">" . htmlspecialchars($User["host"]) . "</a></small>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t" . htmlspecialchars($User["useragent"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t" . formatTimestamp($User["lastactivity"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"" . $class . "\">\r\n\t\t\t\t\t<a $href = \"" . str_replace("&amp;", "&", htmlspecialchars($User["location"])) . "\">" . function_259($User["location"]) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
    }
    echo "\r\n\t" . showAlertMessage("<a $href = \"index.php?do=who_is_online&amp;$act = today\">" . $Language[8] . "</a>") . "\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"4\"><b>" . $Language[2] . " (" . number_format(mysqli_num_rows($query)) . ")</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[3] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[7] . "</b></td>\r\n\t\t</tr>\r\n\t\t" . $Found . "\r\n\t</table>";
}
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
function formatTimestamp($timestamp = "")
{
    $var_265 = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($var_265, $timestamp);
}
function applyUsernameStyle($username, $namestyle)
{
    return str_replace("{username}", $username, $namestyle);
}
function function_259($location)
{
    $var_281 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'MAIN'");
    $var_20 = mysqli_fetch_assoc($var_281);
    $var_27 = unserialize($var_20["content"]);
    $function_259 = "<font $color = \"red\"><b>Unknown Location!</b></font>";
    if (strstr($location, "tsf_forums") && !strstr($location, "returnto")) {
        preg_match_all("#\\/tsf_forums\\/(.*)\\.php#U", $location, $results, PREG_SET_ORDER);
        switch ($results[0][1]) {
            case "index":
                $function_259 = "Viewing Index.";
                break;
            case "forumdisplay":
                $function_259 = "Viewing Forum.";
                break;
            case "showthread":
                $function_259 = "Viewing Thread.";
                break;
            case "announcement":
                $function_259 = "Viewing Announcement.";
                break;
            case "deletepost":
            case "massdelete":
                $function_259 = "Deleting Post.";
                break;
            case "editpost":
                $function_259 = "Editing Post.";
                break;
            case "moderation":
                $function_259 = "Moderating Thread/Post.";
                break;
            case "newreply":
                $function_259 = "Posting Reply.";
                break;
            case "newthread":
                $function_259 = "Creating Thread.";
                break;
            case "poll":
                $function_259 = "Voting Poll.";
                break;
            case "subscription":
                $function_259 = "Subscription Thread.";
                break;
            case "threadrate":
                $function_259 = "Rating Thread.";
                break;
            case "tsf_search":
                $function_259 = "Searching Forums.";
                break;
            case "attachment":
                $function_259 = "Viewing Attachment.";
                break;
            case "top_stats":
                $function_259 = "Viewing Top 10 Forum Stats.";
                break;
            case "syndication":
                $function_259 = "TSF Forums RSS Feeds";
                break;
            default:
                $function_259 = "<b>Forum:</b> " . $function_259;
        }
    } else {
        if (strstr($location, "/ts_games/")) {
            $function_259 = "<b>Viewing/Playing Arcade.</b>";
        } else {
            if (strstr($location, "/ts_shoutbox/")) {
                $function_259 = "<b>Viewing Shoutbox.</b>";
            } else {
                if (strstr($location, "/admin/")) {
                    $function_259 = "<b>Viewing Admin Panel.</b>";
                } else {
                    if (strstr($location, "/" . $var_27["staffcp_path"] . "/")) {
                        $function_259 = "<b>Viewing Staff Control Panel.</b>";
                    } else {
                        if (strstr($location, "/shoutcast/")) {
                            $function_259 = "<b>Shoutcast:</b> Listening Music.";
                        } else {
                            if (strstr($location, "/pbar/")) {
                                $function_259 = "Viewing Donation Status.";
                            } else {
                                preg_match_all("#\\/(.*)\\.php#U", $location, $results, PREG_SET_ORDER);
                                switch ($results[0][1]) {
                                    case "ts_error":
                                        $function_259 = "<strong>Viewing Error Message</strong>";
                                        break;
                                    case "listen":
                                        $function_259 = "Listening Image Verification Code";
                                        break;
                                    case "ok":
                                        $function_259 = "Viewing Confirmation Page.";
                                        break;
                                    case "index":
                                        $function_259 = "Viewing Index Page.";
                                        break;
                                    case "browse":
                                        $function_259 = "Viewing Browse Page.";
                                        break;
                                    case "comment":
                                        $function_259 = "Viewing Comment Page.";
                                        break;
                                    case "donate":
                                        $function_259 = "Viewing Donation Page.";
                                        break;
                                    case "edit":
                                        $function_259 = "Editing Torrent.";
                                        break;
                                    case "faq":
                                        $function_259 = "Viewing FAQ Page.";
                                        break;
                                    case "finduser":
                                        $function_259 = "Searching User.";
                                        break;
                                    case "friends":
                                        $function_259 = "Viewing Friends Page.";
                                        break;
                                    case "getrss":
                                    case "rss":
                                        $function_259 = "Viewing RSS Page.";
                                        break;
                                    case "invite":
                                        $function_259 = "Viewing Invite Page.";
                                        break;
                                    case "logout":
                                        $function_259 = "Logout.";
                                        break;
                                    case "messages":
                                        $function_259 = "Viewing Messages.";
                                        break;
                                    case "sendmessage":
                                        $function_259 = "Sending PM.";
                                        break;
                                    case "mybonus":
                                        $function_259 = "Viewing Bonus Page.";
                                        break;
                                    case "referrals":
                                        $function_259 = "Viewing Referrals Page.";
                                        break;
                                    case "topten":
                                        $function_259 = "Viewing TOPTEN Page.";
                                        break;
                                    case "viewsnatches":
                                        $function_259 = "Viewing Snatches Page.";
                                        break;
                                    case "userdetails":
                                        $function_259 = "Viewing Userdetails Page.";
                                        break;
                                    case "details":
                                        $function_259 = "Viewing Torrent Details.";
                                        break;
                                    case "upload":
                                        $function_259 = "Uploading Torrent.";
                                        break;
                                    case "ts_subtitles":
                                        $function_259 = "Viewing Subtitles Page.";
                                        break;
                                    case "download":
                                        $function_259 = "Downloading Torrent.";
                                        break;
                                    case "badusers":
                                        $function_259 = "Viewing BadUsers Page.";
                                        break;
                                    case "usercp":
                                        $function_259 = "Viewing User Control Panel.";
                                        break;
                                    case "bookmarks":
                                        $function_259 = "Viewing Bookmarks Page.";
                                        break;
                                    case "users":
                                        $function_259 = "Viewing Member List.";
                                        break;
                                    case "rules":
                                        $function_259 = "Viewing Rules Page.";
                                        break;
                                    case "takerate":
                                        $function_259 = "Rating Torrent.";
                                        break;
                                    case "image":
                                        $function_259 = "Showing Image Verification String.";
                                        break;
                                    case "login":
                                    case "takelogin":
                                        $function_259 = "Logging.";
                                        break;
                                    case "signup":
                                        $function_259 = "Registering.";
                                        break;
                                    case "recover":
                                    case "recoverhint":
                                        $function_259 = "Recovering Password.";
                                        break;
                                    case "confirm":
                                        $function_259 = "Confirming account.";
                                        break;
                                    case "staff":
                                        $function_259 = "Viewing Staff Page.";
                                        break;
                                    case "contactstaff":
                                        $function_259 = "Sending Message to Staff.";
                                        break;
                                    case "contactus":
                                        $function_259 = "Viewing Contact Us Page.";
                                        break;
                                    case "links":
                                        $function_259 = "Viewing Useful Links Page.";
                                        break;
                                    case "redirector_footer":
                                        $function_259 = "Redirecting.";
                                        break;
                                    case "stats":
                                        $function_259 = "Viewing Tracker Statistics Page.";
                                        break;
                                    case "ts_applications":
                                        $function_259 = "Viewing Applications Page";
                                        break;
                                    case "ts_social_groups":
                                        $function_259 = "Viewing Social Groups";
                                        break;
                                    case "viewrequests":
                                        $function_259 = "Viewing Request Page";
                                        break;
                                    case "ts_blog":
                                        $function_259 = "Viewing Blogs";
                                        break;
                                    case "ts_tags":
                                        $function_259 = "Viewing Search Cloud";
                                        break;
                                    case "report":
                                        $function_259 = "Reporting...";
                                        break;
                                    case "ts_albums":
                                        $function_259 = "Viewing Albums";
                                        break;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return $function_259;
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
function function_260($Data = "")
{
    global $Language;
    $var_562 = ["msnbot" => "MSN Bot", "google" => "Google Bot", "yahoo" => "Yahoo! Bot", "alexa" => "AleXa Bot", "sogou" => "Sogou Web Spider", "baiduspider" => "Baidu Spider", "w3c_validator" => "W3C Validator", "mlbot" => "MLBoT", "yandex" => "YanDeX"];
    foreach ($var_562 as $var_563 => $var_564) {
        if (preg_match("@" . $var_563 . "@Uis", strtolower($Data))) {
            return "<b><i><font $color = \"#FF6633\">" . $var_564 . "</font></i></b>";
        }
    }
    return "<b><i><font $color = \"#990066\">" . $Language[10] . "</font></i></b>";
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>