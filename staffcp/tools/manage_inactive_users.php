<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Language = file("languages/" . function_75() . "/manage_inactive_users.lang");
$Message = "";
$show_per_page = 40;
$maxdays = 60;
$deleteafter = 15;
$maxmails = 500;
$waitlimit = 5;
$usergroups = [];
$Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE configname = 'MAIN'");
$Result = mysqli_fetch_assoc($Q);
$MAIN = unserialize($Result["content"]);
$Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE configname = 'THEME'");
$Result = mysqli_fetch_assoc($Q);
$THEME = unserialize($Result["content"]);
$Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE configname = 'SMTP'");
$Result = mysqli_fetch_assoc($Q);
$SMTP = unserialize($Result["content"]);
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $Act == "save_config") {
    $show_per_page = intval($_POST["show_per_page"]);
    $maxdays = intval($_POST["maxdays"]);
    $deleteafter = intval($_POST["deleteafter"]);
    $maxmails = intval($_POST["maxmails"]);
    $waitlimit = intval($_POST["waitlimit"]);
    $usergroups = isset($_POST["usergroups"]) ? $_POST["usergroups"] : [];
    if ($show_per_page && $maxdays && $deleteafter && $maxmails && $waitlimit && isset($usergroups[0]) && $usergroups[0] != "") {
        $ShowList = true;
    }
} else {
    if (isset($_GET["show_per_page"]) && isset($_GET["maxdays"]) && isset($_GET["deleteafter"]) && isset($_GET["maxmails"]) && isset($_GET["waitlimit"]) && isset($_GET["usergroups"])) {
        $show_per_page = intval($_GET["show_per_page"]);
        $maxdays = intval($_GET["maxdays"]);
        $deleteafter = intval($_GET["deleteafter"]);
        $maxmails = intval($_GET["maxmails"]);
        $waitlimit = intval($_GET["waitlimit"]);
        $usergroups = explode(",", $_GET["usergroups"]);
        if ($show_per_page && $maxdays && $deleteafter && $maxmails && $waitlimit && isset($usergroups[0]) && $usergroups[0] != "") {
            $ShowList = true;
        }
    }
}
if (isset($ShowList) && $ShowList) {
    $usergroups = implode(",", $usergroups);
    $count_query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT COUNT(id) as count FROM users WHERE enabled = 'yes' AND status = 'confirmed' AND UNIX_TIMESTAMP(last_access) < UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL - " . $maxdays . " DAY)) AND usergroup IN (" . $usergroups . ")");
    $Result = mysqli_fetch_assoc($count_query);
    $total_count = $Result["count"];
    list($pagertop, $limit) = function_82($show_per_page, $total_count, "index.php?do=manage_inactive_users&amp;show_per_page=" . $show_per_page . "&amp;maxdays=" . $maxdays . "&amp;deleteafter=" . $deleteafter . "&amp;maxmails=" . $maxmails . "&amp;waitlimit=" . $waitlimit . "&amp;usergroups=" . $usergroups . "&amp;");
    $query = ["inactive" => mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id,u.username,u.email,u.uploaded,u.downloaded,u.last_access,u.added,i.inactivitytag,g.namestyle FROM users u LEFT JOIN ts_inactivity i ON (u.id=i.userid) LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE u.enabled = 'yes' AND u.status = 'confirmed' AND UNIX_TIMESTAMP(u.last_access) < UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL - " . $maxdays . " DAY)) AND u.usergroup IN (" . $usergroups . ") ORDER BY i.inactivitytag DESC, u.last_access DESC " . $limit), "warn" => mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id,u.username,u.email,u.uploaded,u.downloaded,u.last_access,u.added,i.inactivitytag,g.namestyle FROM users u LEFT JOIN ts_inactivity i ON (u.id=i.userid) LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE u.enabled = 'yes' AND u.status = 'confirmed' AND IF (i.inactivitytag>0,i.inactivitytag=0,u.id>0) AND UNIX_TIMESTAMP(u.last_access) < UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL - " . $maxdays . " DAY)) AND u.usergroup IN (" . $usergroups . ") ORDER BY u.last_access")];
    if ($Act == "warn") {
        if (mysqli_num_rows($query["warn"])) {
            $subject = str_replace("{1}", $MAIN["SITENAME"], $Language[27]);
            echo "\r\n\t\t\t<div id=\"sending\" name=\"sending\">\r\n\t\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\">" . $Language[2] . " - " . $Language[8] . " (" . number_format($total_count) . ")</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\">";
            $Totalwarn = 0;
            while ($user = mysqli_fetch_array($query["warn"])) {
                if ($maxmails > $Totalwarn) {
                    echo "\r\n\t\t\t\t\t\t\t\t<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\" width=\"100%\">\r\n\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td width=\"50%\">\r\n\t\t\t\t\t\t\t\t\t\t\t" . $Language[11] . "\r\n\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t\t<td width=\"35%\">\r\n\t\t\t\t\t\t\t\t\t\t\t<b>" . htmlspecialchars($user["email"]) . "</b>\r\n\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t\t<td width=\"15%\">";
                    $message = str_replace(["{1}", "{2}", "{3}", "{4}", "{5}"], [$user["username"], $MAIN["SITENAME"], $maxdays, $deleteafter, $MAIN["BASEURL"]], $Language[30]);
                    $sendmail = var_283($user["email"], $subject, nl2br($message));
                    if ($sendmail) {
                        echo "<font color=\"green\">" . $Language[25] . "</font>";
                    } else {
                        echo "<font color=\"red\">" . $Language[29] . "</font>";
                    }
                    echo "\r\n\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t</table>";
                    if ($user["id"] && $sendmail) {
                        $update = mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO ts_inactivity (userid, inactivitytag) VALUES ('" . $user["id"] . "', '" . time() . "')");
                        $Totalwarn++;
                    }
                }
            }
            echo "\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t</div>";
            $SysMsg = str_replace("{1}", number_format($Totalwarn), $Language[32]);
            function_79($SysMsg);
            echo function_76($SysMsg);
            var_284("index.php?do=manage_inactive_users&amp;show_per_page=" . $show_per_page . "&amp;maxdays=" . $maxdays . "&amp;deleteafter=" . $deleteafter . "&amp;maxmails=" . $maxmails . "&amp;waitlimit=" . $waitlimit . "&amp;usergroups=" . $usergroups . "&amp;act=warn", $waitlimit);
        } else {
            echo "\r\n\t\t\t" . function_76($Language[31]);
        }
    } else {
        if ($Act == "check_delete") {
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT i.userid, u.id, u.username, u.email FROM ts_inactivity i LEFT JOIN users u ON (i.userid=u.id) WHERE i.inactivitytag != 0 AND UNIX_TIMESTAMP(u.last_access) < i.inactivitytag AND i.inactivitytag < UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL - " . $deleteafter . " DAY))");
            if (mysqli_num_rows($query)) {
                $Users = [];
                while ($user = mysqli_fetch_assoc($query)) {
                    $Users[] = $user["id"];
                }
                if (count($Users) && $Users[0] != "") {
                    define("IN_TRACKER", true);
                    require "../include/init.php";
                    $Work = implode(",", $Users);
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM users WHERE id IN (0, " . $Work . ")");
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_inactivity WHERE userid IN (0, " . $Work . ")");
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_support WHERE userid IN (0, " . $Work . ")");
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_u_perm WHERE userid IN (0, " . $Work . ")");
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_social_group_members WHERE userid IN (0, " . $Work . ")");
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_profilevisitor WHERE userid IN (0, " . $Work . ")");
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM addedrequests WHERE userid IN (0, " . $Work . ")");
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM bookmarks WHERE userid IN (0, " . $Work . ")");
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM comments WHERE user IN (0, " . $Work . ")");
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM friends WHERE userid IN (0, " . $Work . ") OR friendid IN (0, " . $Work . ")");
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_visitor_messages WHERE userid IN (0, " . $Work . ") OR visitorid IN (0, " . $Work . ")");
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_thanks WHERE uid IN (0, " . $Work . ")");
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_shoutbox WHERE uid IN (0, " . $Work . ")");
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_secret_questions WHERE userid IN (0, " . $Work . ")");
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_blogs_comments WHERE uid IN (0, " . $Work . ")");
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_awards_users WHERE uid IN (0, " . $Work . ")");
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "subscribe WHERE userid IN (0, " . $Work . ")");
                }
                $SysMsg = str_replace("{1}", number_format(count($Users)), $Language[33]);
                function_79($SysMsg);
                echo function_76($SysMsg);
            } else {
                echo function_76($Language[31]);
            }
        } else {
            echo "\r\n\t\t" . function_81("<a href=\"index.php?do=manage_inactive_users&amp;show_per_page=" . $show_per_page . "&amp;maxdays=" . $maxdays . "&amp;deleteafter=" . $deleteafter . "&amp;maxmails=" . $maxmails . "&amp;waitlimit=" . $waitlimit . "&amp;usergroups=" . $usergroups . "&amp;act=warn\"><small>" . $Language[8] . "</small></a> | <a href=\"index.php?do=manage_inactive_users&amp;show_per_page=" . $show_per_page . "&amp;maxdays=" . $maxdays . "&amp;deleteafter=" . $deleteafter . "&amp;maxmails=" . $maxmails . "&amp;waitlimit=" . $waitlimit . "&amp;usergroups=" . $usergroups . "&amp;act=check_delete\"><small>" . $Language[9] . "</small></a>") . "";
            if (mysqli_num_rows($query["inactive"])) {
                echo "\r\n\t\t\t" . $pagertop . "\r\n\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" colspan=\"7\" align=\"center\">\r\n\t\t\t\t\t\t" . $Language[2] . " (" . number_format($total_count) . ")\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[3] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[4] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[5] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[6] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[23] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[24] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[7] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                while ($user = mysqli_fetch_assoc($query["inactive"])) {
                    echo "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . $user["username"] . "\">" . str_replace("{username}", $user["username"], $user["namestyle"]) . "</a>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t" . $user["email"] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t" . function_84($user["added"]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t" . function_84($user["last_access"]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t" . var_238($user["uploaded"]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t" . var_238($user["downloaded"]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t" . ($user["inactivitytag"] != 0 ? str_replace(["{1}", "{2}"], [function_84($user["inactivitytag"]), function_84($user["inactivitytag"] + $deleteafter * 24 * 60 * 60)], $Language[21]) : $Language[22]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t";
                }
                echo "\r\n\t\t\t\t</table>\r\n\t\t\t\t" . $pagertop;
            } else {
                echo function_76($Language[31]);
            }
        }
    }
} else {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE u.id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
    $LoggedAdminDetails = mysqli_fetch_assoc($query);
    $count = 0;
    $showusergroups = "\r\n\t<table>\r\n\t\t<tr>\t";
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups ORDER by disporder ASC");
    while ($UG = mysqli_fetch_assoc($query)) {
        if (!($UG["cansettingspanel"] == "yes" && $LoggedAdminDetails["cansettingspanel"] != "yes" || $UG["canstaffpanel"] == "yes" && $LoggedAdminDetails["canstaffpanel"] != "yes" || $UG["issupermod"] == "yes" && $LoggedAdminDetails["issupermod"] != "yes")) {
            if ($count && $count % 8 == 0) {
                $showusergroups .= "</tr><tr>";
            }
            $showusergroups .= "<td><input type=\"checkbox\" name=\"usergroups[]\" value=\"" . $UG["gid"] . "\"" . (is_array($usergroups) && count($usergroups) && in_array($UG["gid"], $usergroups) ? " checked=\"checked\"" : "") . " /></td><td>" . str_replace("{username}", $UG["title"], $UG["namestyle"]) . "</td>";
            $count++;
        }
    }
    $showusergroups .= "</tr></table>";
    echo "\r\n\t<form method=\"post\" action=\"index.php?do=manage_inactive_users&act=save_config\">\r\n\t\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\r\n\t\t\t\t" . $Language[2] . " - " . $Language[12] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $Language[13] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<input type=\"text\" name=\"show_per_page\" value=\"" . intval($show_per_page) . "\" dir=\"ltr\" tabindex=\"1\" size=\"10\" /> <small>" . $Language[14] . "</small>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . $Language[15] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<input type=\"text\" name=\"maxdays\" value=\"" . intval($maxdays) . "\" dir=\"ltr\" tabindex=\"1\" size=\"10\" /> <small>" . $Language[16] . "</small>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $Language[17] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<input type=\"text\" name=\"deleteafter\" value=\"" . intval($deleteafter) . "\" dir=\"ltr\" tabindex=\"1\" size=\"10\" /> <small>" . $Language[18] . "</small>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . $Language[35] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<input type=\"text\" name=\"maxmails\" value=\"" . intval($maxmails) . "\" dir=\"ltr\" tabindex=\"1\" size=\"10\" /> <small>" . $Language[36] . "</small>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $Language[37] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<input type=\"text\" name=\"waitlimit\" value=\"" . intval($waitlimit) . "\" dir=\"ltr\" tabindex=\"1\" size=\"10\" /> <small>" . $Language[38] . "</small>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" valign=\"top\">\r\n\t\t\t\t" . $Language[34] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . $showusergroups . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t<input type=\"submit\" value=\"" . $Language[19] . "\" dir=\"ltr\" tabindex=\"1\" /> <input type=\"submit\" value=\"" . $Language[20] . "\" dir=\"ltr\" tabindex=\"1\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t";
}
class Class_5
{
    public $smtpHost = NULL;
    public $smtpPort = NULL;
    public $smtpUser = NULL;
    public $smtpPass = NULL;
    public $smtpSocket = NULL;
    public $smtpReturn = 0;
    public $secure = "";
    public $toemail = "";
    public $subject = "";
    public $message = "";
    public $headers = "";
    public $fromemail = "";
    public $delimiter = "\r\n";
    public $debug = true;
    public function __construct($SMTP)
    {
        if ($SMTP["secure_connection"] == "yes") {
            $this->secure = "tls";
        } else {
            $this->secure = "none";
        }
        $this->smtpHost = $SMTP["smtpaddress"];
        $this->smtpPort = !empty($SMTP["smtpport"]) ? intval($SMTP["smtpport"]) : 25;
        $this->smtpUser =& $SMTP["accountname"];
        $this->smtpPass =& $SMTP["accountpassword"];
        $this->delimiter = "\r\n";
    }
    public function function_91($toemail, $subject, $message, $from = "", $uheaders = "", $charset = "", $webmasteremail = "", $http_host = "")
    {
        $toemail = $this->function_92($toemail);
        if (empty($toemail)) {
            return false;
        }
        $delimiter =& $this->delimiter;
        $toemail = $this->function_93($toemail);
        $subject = $this->function_92($subject);
        $message = preg_replace("#(\r\n|\r|\n)#s", $delimiter, trim($message));
        if ((strtolower($charset) == "iso-8859-1" || $charset == "") && preg_match("/&[a-z0-9#]+;/i", $message)) {
            $message = utf8_encode($message);
            $subject = utf8_encode($subject);
            var_285 = "UTF-8";
            var_286 = true;
        } else {
            var_285 = $charset;
            var_286 = false;
        }
        $message = $this->function_93($message, var_286);
        $subject = $this->function_94($this->function_93($subject, var_286), var_285, false, false);
        $from = $this->function_92($from);
        if (empty($from)) {
            var_287 = "PHP/" . phpversion() . " via the PHP TS SE SMTP Class";
            if (var_286) {
                var_287 = utf8_encode(var_287);
            }
            var_287 = $this->function_94($this->function_93(var_287, var_286), var_285);
            if (!isset($headers)) {
                $headers = "";
            }
            $headers .= "From: " . var_287 . " <" . $webmasteremail . ">" . $delimiter;
            $headers .= "Auto-Submitted: auto-generated" . $delimiter;
        } else {
            var_287 = $from;
            if (var_286) {
                var_287 = utf8_encode(var_287);
            }
            var_287 = $this->function_94($this->function_93(var_287, var_286), var_285);
            if (!isset($headers)) {
                $headers = "";
            }
            $headers .= "From: " . var_287 . " <" . $from . ">" . $delimiter;
            $headers .= "Sender: " . $webmasteremail . $delimiter;
        }
        $fromemail = $webmasteremail;
        $headers .= "Return-Path: " . $fromemail . $delimiter;
        if (!$http_host) {
            $http_host = substr(md5($message), 12, 18) . ".ts_unknown.unknown";
        }
        var_288 = "<" . gmdate("YmdHis") . "." . substr(md5($message . microtime()), 0, 12) . "@" . $http_host . ">";
        $headers .= "Message-ID: " . var_288 . $delimiter;
        $headers .= preg_replace("#(\r\n|\r|\n)#s", $delimiter, $uheaders);
        unset($uheaders);
        $headers .= "MIME-Version: 1.0" . $delimiter;
        $headers .= "Content-Type: text/html" . (var_285 ? "; charset=\"" . var_285 . "\"" : "") . $delimiter;
        $headers .= "Content-Transfer-Encoding: 8bit" . $delimiter;
        $headers .= "X-Priority: 3" . $delimiter;
        $headers .= "X-Mailer: TS SE Mail via PHP" . $delimiter;
        $headers .= "Date: " . date("r") . $delimiter;
        $this->toemail = $toemail;
        $this->subject = $subject;
        $this->message = $message;
        $this->headers = $headers;
        $this->fromemail = $fromemail;
        return true;
    }
    public function function_95($msg, $expectedResult = false)
    {
        if ($msg !== false && !empty($msg)) {
            fputs($this->smtpSocket, $msg . "\r\n");
        }
        if ($expectedResult !== false) {
            $result = "";
            while (var_289 = @fgets($this->smtpSocket, 1024)) {
                $result .= var_289;
                if (!preg_match("#^(\\d{3}) #", var_289, var_220)) {
                }
            }
            $this->smtpReturn = intval(var_220[1]);
            return $this->smtpReturn == $expectedResult;
        }
        return true;
    }
    public function function_96($msg)
    {
        if ($this->debug) {
            trigger_error($msg, 512);
        }
        return false;
    }
    public function function_97()
    {
        if (!$this->smtpSocket) {
            return false;
        }
        if (!$this->function_95("EHLO " . $this->smtpHost, 250) && !$this->function_95("HELO " . $this->smtpHost, 250)) {
            return false;
        }
        return true;
    }
    public function function_98()
    {
        if (!$this->toemail) {
            return false;
        }
        $this->smtpSocket = fsockopen(($this->secure == "ssl" ? "ssl://" : "tcp://") . $this->smtpHost, $this->smtpPort, fsockError, fsockErrorStr, 30);
        if ($this->smtpSocket) {
            if (!$this->function_95(false, 220)) {
                return $this->function_96($this->smtpReturn . " Unexpected response when connecting to SMTP server");
            }
            if (!$this->function_97()) {
                return $this->function_96($this->smtpReturn . " Unexpected response from SMTP server during handshake");
            }
            if ($this->secure == "tls" && function_exists("stream_socket_enable_crypto")) {
                if ($this->function_95("STARTTLS", 220) && !stream_socket_enable_crypto($this->smtpSocket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    return $this->function_96("Unable to negotitate TLS handshake.");
                }
                $this->function_97();
            }
            if ($this->smtpUser && $this->smtpPass && $this->function_95("AUTH LOGIN", 334) && (!$this->function_95(base64_encode($this->smtpUser), 334) || !$this->function_95(base64_encode($this->smtpPass), 235))) {
                return $this->function_96($this->smtpReturn . " Authorization to the SMTP server failed");
            }
            if (!$this->function_95("MAIL FROM:<" . $this->fromemail . ">", 250)) {
                return $this->function_96($this->smtpReturn . " Unexpected response from SMTP server during FROM address transmission");
            }
            var_290 = explode(",", $this->toemail);
            foreach (var_290 as $address) {
                if (!$this->function_95("RCPT TO:<" . trim($address) . ">", 250)) {
                    return $this->function_96($this->smtpReturn . " Unexpected response from SMTP server during TO address transmission");
                }
            }
            if ($this->function_95("DATA", 354)) {
                $this->function_95("Date: " . gmdate("r"), false);
                $this->function_95("To: " . $this->toemail, false);
                $this->function_95(trim($this->headers), false);
                $this->function_95("Subject: " . $this->subject, false);
                $this->function_95("\r\n", false);
                $this->message = preg_replace("#^\\." . $this->delimiter . "#m", ".." . $this->delimiter, $this->message);
                $this->function_95($this->message, false);
                if (!$this->function_95(".", 250)) {
                    return $this->function_96($this->smtpReturn . " Unexpected response from SMTP server when ending transmission");
                }
                $this->function_95("QUIT", 221);
                fclose($this->smtpSocket);
                return true;
            }
            return $this->function_96($this->smtpReturn . " Unexpected response from SMTP server during data transmission");
        } else {
            return $this->function_96("Unable to connect to SMTP server");
        }
    }
    public function function_92($text)
    {
        $text = preg_replace("/(\r\n|\r|\n)/s", "\r\n", trim($text));
        $pos = strpos($text, "\r\n");
        if ($pos !== false) {
            return substr($text, 0, $pos);
        }
        return $text;
    }
    public function function_93($text, $doUniCode = false)
    {
        if ($doUniCode) {
            $text = preg_replace_callback("/&#([0-9]+);/siU", function ($matches) {
                return var_291($matches[1]);
            }, $text);
        }
        return str_replace(["&lt;", "&gt;", "&quot;", "&amp;"], ["<", ">", "\"", "&"], $text);
    }
    public function function_94($text, $charset = "utf-8", $force_encode = false, $quoted_string = true)
    {
        $text = trim($text);
        if (!$charset) {
            return $text;
        }
        if ($force_encode) {
            var_292 = true;
        } else {
            var_292 = false;
            $i = 0;
            while ($i < strlen($text)) {
                if (127 < ord($text[$i])) {
                    var_292 = true;
                } else {
                    $i++;
                }
            }
        }
        if (var_292) {
            var_293 = preg_replace_callback("#([^a-zA-Z0-9!*+\\-/ ])#", function ($matches) {
                return "'=" . strtoupper(dechex(ord(str_replace("\\\\\"", "\\\"", $matches[1]))));
            }, $text);
            var_293 = str_replace(" ", "_", var_293);
            var_293 = "=?" . $charset . "?q?" . var_293 . "?=";
            return var_293;
        }
        if ($quoted_string) {
            $text = str_replace(["\"", "(", ")"], ["\\\"", "\\(", "\\)"], $text);
            return "\"" . $text . "\"";
        }
        return preg_replace("#(\\r\\n|\\n|\\r)+#", " ", $text);
    }
}
function function_75()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function function_77()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        var_236("../index.php");
    }
}
function function_78($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\twindow.location.href=\"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-equiv=\"refresh\" content=\"0;url=" . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function function_99($url, $seconds)
{
    global $Language;
    echo "<br />" . var_294(str_replace("{1}", $seconds, $Language[39])) . "\r\n\t<meta http-equiv=\"refresh\" content=\"" . $seconds . ";url=" . $url . "\" />";
    exit;
}
function function_100($to, $subject, $body)
{
    global $MAIN;
    global $SMTP;
    global $THEME;
    var_295 = $MAIN["SITENAME"];
    $fromemail = $MAIN["SITEEMAIL"];
    var_296 = false;
    if (strtoupper(substr(PHP_OS, 0, 3) == "WIN")) {
        var_297 = "\r\n";
        var_296 = true;
    } else {
        if (strtoupper(substr(PHP_OS, 0, 3) == "MAC")) {
            var_297 = "\r";
        } else {
            var_297 = "\n";
        }
    }
    var_298 = md5(uniqid(rand(), true) . time());
    var_41 = $_SERVER["SERVER_NAME"];
    $headers = "From: " . var_295 . " <" . $fromemail . ">" . var_297;
    $headers .= "Reply-To: " . var_295 . " <" . $fromemail . ">" . var_297;
    $headers .= "Return-Path: " . var_295 . " <" . $fromemail . ">" . var_297;
    $headers .= "Message-ID: <" . var_298 . " thesystem@" . var_41 . ">" . var_297;
    $headers .= "X-Mailer: PHP v" . phpversion() . var_297;
    $headers .= "MIME-Version: 1.0" . var_297;
    $headers .= "Content-Transfer-Encoding: 8bit" . var_297;
    $headers .= "Content-type: text/html; charset=" . $THEME["charset"] . var_297;
    $headers .= "X-Sender: PHP" . var_297;
    if ($SMTP["smtptype"] == "default") {
        return mail($to, $subject, $body, $headers);
    }
    if ($SMTP["smtptype"] == "advanced") {
        if (isset($SMTP["smtp"]) && $SMTP["smtp"] == "yes") {
            ini_set("SMTP", $SMTP["smtp_host"]);
            ini_set("smtp_port", $SMTP["smtp_port"]);
            if (var_296) {
                ini_set("sendmail_from", $SMTP["smtp_from"]);
            }
        }
        var_299 = mail($to, $subject, $body, $headers);
        if (isset($SMTP["smtp"]) && $SMTP["smtp"] == "yes") {
            ini_restore("SMTP");
            ini_restore("smtp_port");
            if (var_296) {
                ini_restore("sendmail_from");
            }
        }
        return var_299;
    }
    var_300 = new Class_5($SMTP);
    var_300->function_91($to, trim($subject), trim($body), $fromemail, "", $THEME["charset"], $fromemail, $MAIN["BASEURL"]);
    var_301 = var_300->function_98();
    return var_301;
}
function function_76($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function function_79($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_86($numresults, &$page, &$perpage, $maxperpage = 20, $defaultperpage = 20)
{
    $perpage = intval($perpage);
    if ($perpage < 1) {
        $perpage = $defaultperpage;
    } else {
        if ($maxperpage < $perpage) {
            $perpage = $maxperpage;
        }
    }
    var_240 = ceil($numresults / $perpage);
    if (var_240 == 0) {
        var_240 = 1;
    }
    if ($page < 1) {
        $page = 1;
    } else {
        if (var_240 < $page) {
            $page = var_240;
        }
    }
}
function function_87($pagenumber, $perpage, $total)
{
    var_241 = $perpage * ($pagenumber - 1);
    var_89 = var_241 + $perpage;
    if ($total < var_89) {
        var_89 = $total;
    }
    var_241++;
    return ["first" => number_format(var_241), "last" => number_format(var_89)];
}
function function_82($perpage, $results, $address)
{
    if ($results < $perpage) {
        return ["", ""];
    }
    if ($results) {
        var_242 = @ceil($results / $perpage);
    } else {
        var_242 = 0;
    }
    $pagenumber = isset($_GET["page"]) ? intval($_GET["page"]) : (isset($_POST["page"]) ? intval($_POST["page"]) : "");
    function_86($results, $pagenumber, $perpage, 200);
    var_243 = ($pagenumber - 1) * $perpage;
    var_244 = $pagenumber * $perpage;
    if ($results < var_244) {
        var_244 = $results;
        if ($results < var_243) {
            var_243 = $results - $perpage - 1;
        }
    }
    if (var_243 < 0) {
        var_243 = 0;
    }
    var_245 = var_246 = var_247 = var_248 = var_249 = "";
    var_250 = 0;
    if ($results <= $perpage) {
        var_251["pagenav"] = false;
        return ["", "LIMIT " . var_243 . ", " . $perpage];
    }
    var_251["pagenav"] = true;
    $total = number_format($results);
    var_251["last"] = false;
    var_251["first"] = var_251["last"];
    var_251["next"] = var_251["first"];
    var_251["prev"] = var_251["next"];
    if (1 < $pagenumber) {
        var_252 = $pagenumber - 1;
        var_253 = function_87(var_252, $perpage, $results);
        var_251["prev"] = true;
    }
    if ($pagenumber < var_242) {
        var_254 = $pagenumber + 1;
        var_255 = function_87(var_254, $perpage, $results);
        var_251["next"] = true;
    }
    var_256 = "3";
    if (!isset(var_257) || !is_array(var_257)) {
        var_258 = "10 50 100 500 1000";
        var_257[] = preg_split("#\\s+#s", var_258, -1, PREG_SPLIT_NO_EMPTY);
        while (var_250++ < var_242) {
        }
        var_259 = isset(var_252) && var_252 != 1 ? "page=" . var_252 : "";
        var_245 = "\r\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\r\n\t\t\t<td style=\"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div style=\"float: left;\" id=\"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . var_242 . "</li>\r\n\t\t\t\t\t\t" . (var_251["first"] ? "<li><a class=\"smalltext\" href=\"" . $address . "\" title=\"First Page - Show Results " . var_260["first"] . " to " . var_260["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . (var_251["prev"] ? "<li><a class=\"smalltext\" href=\"" . $address . var_259 . "\" title=\"Previous Page - Show Results " . var_253["first"] . " to " . var_253["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . var_245 . "\r\n\t\t\t\t\t\t" . (var_251["next"] ? "<li><a class=\"smalltext\" href=\"" . $address . "page=" . var_254 . "\" title=\"Next Page - Show Results " . var_255["first"] . " to " . var_255["last"] . " of " . $total . "\">&gt;</a></li>" : "") . (var_251["last"] ? "<li><a class=\"smalltext\" href=\"" . $address . "page=" . var_242 . "\" title=\"Last Page - Show Results " . var_261["first"] . " to " . var_261["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
        return [var_245, "LIMIT " . var_243 . ", " . $perpage];
    }
    if (var_256 <= abs(var_250 - $pagenumber) && var_256 != 0) {
        if (var_250 == 1) {
            var_260 = function_87(1, $perpage, $results);
            var_251["first"] = true;
        }
        if (var_250 == var_242) {
            var_261 = function_87(var_242, $perpage, $results);
            var_251["last"] = true;
        }
        if (in_array(abs(var_250 - $pagenumber), var_257) && var_250 != 1 && var_250 != var_242) {
            var_262 = function_87(var_250, $perpage, $results);
            var_263 = var_250 - $pagenumber;
            if (0 < var_263) {
                var_263 = "+" . var_263;
            }
            var_245 .= "<li><a class=\"smalltext\" href=\"" . $address . (var_250 != 1 ? "page=" . var_250 : "") . "\" title=\"Show results " . var_262["first"] . " to " . var_262["last"] . " of " . $total . "\"><!--" . var_263 . "-->" . var_250 . "</a></li>";
        }
    } else {
        if (var_250 == $pagenumber) {
            var_264 = function_87(var_250, $perpage, $results);
            var_245 .= "<li><a name=\"current\" class=\"current\" title=\"Showing results " . var_264["first"] . " to " . var_264["last"] . " of " . $total . "\">" . var_250 . "</a></li>";
        } else {
            var_262 = function_87(var_250, $perpage, $results);
            var_245 .= "<li><a href=\"" . $address . (var_250 != 1 ? "page=" . var_250 : "") . "\" title=\"Show results " . var_262["first"] . " to " . var_262["last"] . " of " . $total . "\">" . var_250 . "</a></li>";
        }
    }
}
function function_84($timestamp = "")
{
    var_265 = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date(var_265, $timestamp);
}
function function_88($bytes = 0)
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
function function_81($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>