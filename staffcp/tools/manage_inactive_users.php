<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Language = file("languages/" . getStaffLanguage() . "/manage_inactive_users.lang");
$Message = "";
$show_per_page = 40;
$maxdays = 60;
$deleteafter = 15;
$maxmails = 500;
$waitlimit = 5;
$usergroups = [];
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
$Result = mysqli_fetch_assoc($query);
$MAIN = unserialize($Result["content"]);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'THEME'");
$Result = mysqli_fetch_assoc($query);
$THEME = unserialize($Result["content"]);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'SMTP'");
$Result = mysqli_fetch_assoc($query);
$SMTP = unserialize($Result["content"]);
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $Act == "save_config") {
    $show_per_page = intval($_POST["show_per_page"]);
    $maxdays = intval($_POST["maxdays"]);
    $deleteafter = intval($_POST["deleteafter"]);
    $maxmails = intval($_POST["maxmails"]);
    $waitlimit = intval($_POST["waitlimit"]);
    $usergroups = isset($_POST["usergroups"]) ? $_POST["usergroups"] : [];
    if ($show_per_page && $maxdays && $deleteafter && $maxmails && $waitlimit && isset($usergroups[0]) && $usergroups[0] != "") {
        $shouldShowList = true;
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
            $shouldShowList = true;
        }
    }
}
if (isset($shouldShowList) && $shouldShowList) {
    $usergroups = implode(",", $usergroups);
    $count_query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT COUNT(id) as count FROM users WHERE `enabled` = 'yes' AND $status = 'confirmed' AND UNIX_TIMESTAMP(last_access) < UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL - " . $maxdays . " DAY)) AND usergroup IN (" . $usergroups . ")");
    $Result = mysqli_fetch_assoc($count_query);
    $total_count = $Result["count"];
    list($pagertop, $limit) = buildPaginationLinks($show_per_page, $total_count, "index.php?do=manage_inactive_users&amp;$show_per_page = " . $show_per_page . "&amp;$maxdays = " . $maxdays . "&amp;$deleteafter = " . $deleteafter . "&amp;$maxmails = " . $maxmails . "&amp;$waitlimit = " . $waitlimit . "&amp;$usergroups = " . $usergroups . "&amp;");
    $query = ["inactive" => mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id,u.username,u.email,u.uploaded,u.downloaded,u.last_access,u.added,i.inactivitytag,g.namestyle FROM users u LEFT JOIN ts_inactivity i ON (u.`id` = i.userid) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$enabled = 'yes' AND u.$status = 'confirmed' AND UNIX_TIMESTAMP(u.last_access) < UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL - " . $maxdays . " DAY)) AND u.usergroup IN (" . $usergroups . ") ORDER BY i.inactivitytag DESC, u.last_access DESC " . $limit), "warn" => mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id,u.username,u.email,u.uploaded,u.downloaded,u.last_access,u.added,i.inactivitytag,g.namestyle FROM users u LEFT JOIN ts_inactivity i ON (u.`id` = i.userid) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$enabled = 'yes' AND u.$status = 'confirmed' AND IF (i.inactivitytag>0,i.$inactivitytag = 0,u.id>0) AND UNIX_TIMESTAMP(u.last_access) < UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL - " . $maxdays . " DAY)) AND u.usergroup IN (" . $usergroups . ") ORDER BY u.last_access")];
    if ($Act == "warn") {
        if (mysqli_num_rows($query["warn"])) {
            $subject = str_replace("{1}", $MAIN["SITENAME"], $Language[27]);
            echo "\r\n\t\t\t<div $id = \"sending\" $name = \"sending\">\r\n\t\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\">" . $Language[2] . " - " . $Language[8] . " (" . number_format($total_count) . ")</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\">";
            $Totalwarn = 0;
            while ($user = mysqli_fetch_array($query["warn"])) {
                if ($maxmails > $Totalwarn) {
                    echo "\r\n\t\t\t\t\t\t\t\t<table $cellpadding = \"4\" $cellspacing = \"0\" $border = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td $width = \"50%\">\r\n\t\t\t\t\t\t\t\t\t\t\t" . $Language[11] . "\r\n\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t\t<td $width = \"35%\">\r\n\t\t\t\t\t\t\t\t\t\t\t<b>" . htmlspecialchars($user["email"]) . "</b>\r\n\t\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t\t\t<td $width = \"15%\">";
                    $message = str_replace(["{1}", "{2}", "{3}", "{4}", "{5}"], [$user["username"], $MAIN["SITENAME"], $maxdays, $deleteafter, $MAIN["BASEURL"]], $Language[30]);
                    $sendmail = var_283($user["email"], $subject, nl2br($message));
                    if ($sendmail) {
                        echo "<font $color = \"green\">" . $Language[25] . "</font>";
                    } else {
                        echo "<font $color = \"red\">" . $Language[29] . "</font>";
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
            logStaffAction($SysMsg);
            echo showAlertError($SysMsg);
            var_284("index.php?do=manage_inactive_users&amp;$show_per_page = " . $show_per_page . "&amp;$maxdays = " . $maxdays . "&amp;$deleteafter = " . $deleteafter . "&amp;$maxmails = " . $maxmails . "&amp;$waitlimit = " . $waitlimit . "&amp;$usergroups = " . $usergroups . "&amp;$act = warn", $waitlimit);
        } else {
            echo "\r\n\t\t\t" . showAlertError($Language[31]);
        }
    } else {
        if ($Act == "check_delete") {
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT i.userid, u.id, u.username, u.email FROM ts_inactivity i LEFT JOIN users u ON (i.$userid = u.id) WHERE i.inactivitytag != 0 AND UNIX_TIMESTAMP(u.last_access) < i.inactivitytag AND i.inactivitytag < UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL - " . $deleteafter . " DAY))");
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
                logStaffAction($SysMsg);
                echo showAlertError($SysMsg);
            } else {
                echo showAlertError($Language[31]);
            }
        } else {
            echo "\r\n\t\t" . showAlertMessage("<a $href = \"index.php?do=manage_inactive_users&amp;$show_per_page = " . $show_per_page . "&amp;$maxdays = " . $maxdays . "&amp;$deleteafter = " . $deleteafter . "&amp;$maxmails = " . $maxmails . "&amp;$waitlimit = " . $waitlimit . "&amp;$usergroups = " . $usergroups . "&amp;$act = warn\"><small>" . $Language[8] . "</small></a> | <a $href = \"index.php?do=manage_inactive_users&amp;$show_per_page = " . $show_per_page . "&amp;$maxdays = " . $maxdays . "&amp;$deleteafter = " . $deleteafter . "&amp;$maxmails = " . $maxmails . "&amp;$waitlimit = " . $waitlimit . "&amp;$usergroups = " . $usergroups . "&amp;$act = check_delete\"><small>" . $Language[9] . "</small></a>") . "";
            if (mysqli_num_rows($query["inactive"])) {
                echo "\r\n\t\t\t" . $pagertop . "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"7\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[2] . " (" . number_format($total_count) . ")\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[3] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[4] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[5] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[6] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[23] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[24] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[7] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                while ($user = mysqli_fetch_assoc($query["inactive"])) {
                    echo "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $user["username"] . "\">" . str_replace("{username}", $user["username"], $user["namestyle"]) . "</a>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t" . $user["email"] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t" . formatTimestamp($user["added"]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t" . formatTimestamp($user["last_access"]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t" . formatBytes($user["uploaded"]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t" . formatBytes($user["downloaded"]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t\t" . ($user["inactivitytag"] != 0 ? str_replace(["{1}", "{2}"], [formatTimestamp($user["inactivitytag"]), formatTimestamp($user["inactivitytag"] + $deleteafter * 24 * 60 * 60)], $Language[21]) : $Language[22]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t";
                }
                echo "\r\n\t\t\t\t</table>\r\n\t\t\t\t" . $pagertop;
            } else {
                echo showAlertError($Language[31]);
            }
        }
    }
} else {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
    $LoggedAdminDetails = mysqli_fetch_assoc($query);
    $count = 0;
    $showusergroups = "\r\n\t<table>\r\n\t\t<tr>\t";
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups ORDER by disporder ASC");
    while ($UG = mysqli_fetch_assoc($query)) {
        if (!($UG["cansettingspanel"] == "yes" && $LoggedAdminDetails["cansettingspanel"] != "yes" || $UG["canstaffpanel"] == "yes" && $LoggedAdminDetails["canstaffpanel"] != "yes" || $UG["issupermod"] == "yes" && $LoggedAdminDetails["issupermod"] != "yes")) {
            if ($count && $count % 8 == 0) {
                $showusergroups .= "</tr><tr>";
            }
            $showusergroups .= "<td><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"" . $UG["gid"] . "\"" . (is_array($usergroups) && count($usergroups) && in_array($UG["gid"], $usergroups) ? " $checked = \"checked\"" : "") . " /></td><td>" . str_replace("{username}", $UG["title"], $UG["namestyle"]) . "</td>";
            $count++;
        }
    }
    $showusergroups .= "</tr></table>";
    echo "\r\n\t<form $method = \"post\" $action = \"index.php?do=manage_inactive_users&$act = save_config\">\r\n\t\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . " - " . $Language[12] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $Language[13] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<input $type = \"text\" $name = \"show_per_page\" $value = \"" . intval($show_per_page) . "\" $dir = \"ltr\" $tabindex = \"1\" $size = \"10\" /> <small>" . $Language[14] . "</small>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . $Language[15] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<input $type = \"text\" $name = \"maxdays\" $value = \"" . intval($maxdays) . "\" $dir = \"ltr\" $tabindex = \"1\" $size = \"10\" /> <small>" . $Language[16] . "</small>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $Language[17] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<input $type = \"text\" $name = \"deleteafter\" $value = \"" . intval($deleteafter) . "\" $dir = \"ltr\" $tabindex = \"1\" $size = \"10\" /> <small>" . $Language[18] . "</small>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . $Language[35] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<input $type = \"text\" $name = \"maxmails\" $value = \"" . intval($maxmails) . "\" $dir = \"ltr\" $tabindex = \"1\" $size = \"10\" /> <small>" . $Language[36] . "</small>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $Language[37] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<input $type = \"text\" $name = \"waitlimit\" $value = \"" . intval($waitlimit) . "\" $dir = \"ltr\" $tabindex = \"1\" $size = \"10\" /> <small>" . $Language[38] . "</small>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $valign = \"top\">\r\n\t\t\t\t" . $Language[34] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . $showusergroups . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[19] . "\" $dir = \"ltr\" $tabindex = \"1\" /> <input $type = \"submit\" $value = \"" . $Language[20] . "\" $dir = \"ltr\" $tabindex = \"1\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t";
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
            $this->$secure = "tls";
        } else {
            $this->$secure = "none";
        }
        $this->$smtpHost = $SMTP["smtpaddress"];
        $this->$smtpPort = !empty($SMTP["smtpport"]) ? intval($SMTP["smtpport"]) : 25;
        $this->$smtpUser = & $SMTP["accountname"];
        $this->$smtpPass = & $SMTP["accountpassword"];
        $this->$delimiter = "\r\n";
    }
    public function function_91($toemail, $subject, $message, $from = "", $uheaders = "", $charset = "", $webmasteremail = "", $http_host = "")
    {
        $toemail = $this->sanitizeEmailText($toemail);
        if (empty($toemail)) {
            return false;
        }
        $delimiter =& $this->delimiter;
        $toemail = $this->decodeHtmlEntities($toemail);
        $subject = $this->sanitizeEmailText($subject);
        $message = preg_replace("#(\r\n|\r|\n)#s", $delimiter, trim($message));
        if ((strtolower($charset) == "iso-8859-1" || $charset == "") && preg_match("/&[a-z0-9#]+;/i", $message)) {
            $message = utf8_encode($message);
            $subject = utf8_encode($subject);
            $var_285 = "UTF-8";
            $var_286 = true;
        } else {
            $var_285 = $charset;
            $var_286 = false;
        }
        $message = $this->decodeHtmlEntities($message, $var_286);
        $subject = $this->encodeEmailHeaderRFC2047($this->decodeHtmlEntities($subject, $var_286), $var_285, false, false);
        $from = $this->sanitizeEmailText($from);
        if (empty($from)) {
            $emailFromHeader = "PHP/" . phpversion() . " via the PHP TS SE SMTP Class";
            if ($var_286) {
                $emailFromHeader = utf8_encode($emailFromHeader);
            }
            $emailFromHeader = $this->encodeEmailHeaderRFC2047($this->decodeHtmlEntities($emailFromHeader, $var_286), $var_285);
            if (!isset($headers)) {
                $headers = "";
            }
            $headers .= "From: " . $emailFromHeader . " <" . $webmasteremail . ">" . $delimiter;
            $headers .= "Auto-Submitted: auto-generated" . $delimiter;
        } else {
            $emailFromHeader = $from;
            if ($var_286) {
                $emailFromHeader = utf8_encode($emailFromHeader);
            }
            $emailFromHeader = $this->encodeEmailHeaderRFC2047($this->decodeHtmlEntities($emailFromHeader, $var_286), $var_285);
            if (!isset($headers)) {
                $headers = "";
            }
            $headers .= "From: " . $emailFromHeader . " <" . $from . ">" . $delimiter;
            $headers .= "Sender: " . $webmasteremail . $delimiter;
        }
        $fromemail = $webmasteremail;
        $headers .= "Return-Path: " . $fromemail . $delimiter;
        if (!$http_host) {
            $http_host = substr(md5($message), 12, 18) . ".ts_unknown.unknown";
        }
        $var_288 = "<" . gmdate("YmdHis") . "." . substr(md5($message . microtime()), 0, 12) . "@" . $http_host . ">";
        $headers .= "Message-ID: " . $var_288 . $delimiter;
        $headers .= preg_replace("#(\r\n|\r|\n)#s", $delimiter, $uheaders);
        unset($uheaders);
        $headers .= "MIME-Version: 1.0" . $delimiter;
        $headers .= "Content-Type: text/html" . ($var_285 ? "; $charset = \"" . $var_285 . "\"" : "") . $delimiter;
        $headers .= "Content-Transfer-Encoding: 8bit" . $delimiter;
        $headers .= "X-Priority: 3" . $delimiter;
        $headers .= "X-Mailer: TS SE Mail via PHP" . $delimiter;
        $headers .= "Date: " . date("r") . $delimiter;
        $this->$toemail = $toemail;
        $this->$subject = $subject;
        $this->$message = $message;
        $this->$headers = $headers;
        $this->$fromemail = $fromemail;
        return true;
    }
    public function smtpSendCommand($msg, $expectedResult = false)
    {
        if ($msg !== false && !empty($msg)) {
            fputs($this->smtpSocket, $msg . "\r\n");
        }
        if ($expectedResult !== false) {
            $result = "";
            while ($smtpLine = @fgets($this->smtpSocket, 1024)) {
                $result .= $smtpLine;
                if (!preg_match("#^(\\d{3}) #", $smtpLine, $smtpMatches)) {
                }
            }
            $this->$smtpReturn = intval($smtpMatches[1]);
            return $this->$smtpReturn == $expectedResult;
        }
        return true;
    }
    public function smtpDebugError($msg)
    {
        if ($this->debug) {
            trigger_error($msg, 512);
        }
        return false;
    }
    public function smtpHandshake()
    {
        if (!$this->smtpSocket) {
            return false;
        }
        if (!$this->smtpSendCommand("EHLO " . $this->smtpHost, 250) && !$this->smtpSendCommand("HELO " . $this->smtpHost, 250)) {
            return false;
        }
        return true;
    }
    public function smtpSendEmail()
    {
        if (!$this->toemail) {
            return false;
        }
        $this->$smtpSocket = fsockopen(($this->$secure == "ssl" ? "ssl://" : "tcp://") . $this->smtpHost, $this->smtpPort, fsockError, fsockErrorStr, 30);
        if ($this->smtpSocket) {
            if (!$this->smtpSendCommand(false, 220)) {
                return $this->smtpDebugError($this->smtpReturn . " Unexpected response when connecting to SMTP server");
            }
            if (!$this->smtpHandshake()) {
                return $this->smtpDebugError($this->smtpReturn . " Unexpected response from SMTP server during handshake");
            }
            if ($this->$secure == "tls" && function_exists("stream_socket_enable_crypto")) {
                if ($this->smtpSendCommand("STARTTLS", 220) && !stream_socket_enable_crypto($this->smtpSocket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    return $this->smtpDebugError("Unable to negotitate TLS handshake.");
                }
                $this->smtpHandshake();
            }
            if ($this->smtpUser && $this->smtpPass && $this->smtpSendCommand("AUTH LOGIN", 334) && (!$this->smtpSendCommand(base64_encode($this->smtpUser), 334) || !$this->smtpSendCommand(base64_encode($this->smtpPass), 235))) {
                return $this->smtpDebugError($this->smtpReturn . " Authorization to the SMTP server failed");
            }
            if (!$this->smtpSendCommand("MAIL FROM:<" . $this->fromemail . ">", 250)) {
                return $this->smtpDebugError($this->smtpReturn . " Unexpected response from SMTP server during FROM address transmission");
            }
            $var_290 = explode(",", $this->toemail);
            foreach ($var_290 as $address) {
                if (!$this->smtpSendCommand("RCPT TO:<" . trim($address) . ">", 250)) {
                    return $this->smtpDebugError($this->smtpReturn . " Unexpected response from SMTP server during TO address transmission");
                }
            }
            if ($this->smtpSendCommand("DATA", 354)) {
                $this->smtpSendCommand("Date: " . gmdate("r"), false);
                $this->smtpSendCommand("To: " . $this->toemail, false);
                $this->smtpSendCommand(trim($this->headers), false);
                $this->smtpSendCommand("Subject: " . $this->subject, false);
                $this->smtpSendCommand("\r\n", false);
                $this->$message = preg_replace("#^\\." . $this->delimiter . "#m", ".." . $this->delimiter, $this->message);
                $this->smtpSendCommand($this->message, false);
                if (!$this->smtpSendCommand(".", 250)) {
                    return $this->smtpDebugError($this->smtpReturn . " Unexpected response from SMTP server when ending transmission");
                }
                $this->smtpSendCommand("QUIT", 221);
                fclose($this->smtpSocket);
                return true;
            }
            return $this->smtpDebugError($this->smtpReturn . " Unexpected response from SMTP server during data transmission");
        } else {
            return $this->smtpDebugError("Unable to connect to SMTP server");
        }
    }
    public function sanitizeEmailText($text)
    {
        $text = preg_replace("/(\r\n|\r|\n)/s", "\r\n", trim($text));
        $pos = strpos($text, "\r\n");
        if ($pos !== false) {
            return substr($text, 0, $pos);
        }
        return $text;
    }
    public function decodeHtmlEntities($text, $doUniCode = false)
    {
        if ($doUniCode) {
            $text = preg_replace_callback("/&#([0-9]+);/siU", function ($matches) {
                return var_291($matches[1]);
            }, $text);
        }
        return str_replace(["&lt;", "&gt;", "&quot;", "&amp;"], ["<", ">", "\"", "&"], $text);
    }
    public function encodeEmailHeaderRFC2047($text, $charset = "utf-8", $force_encode = false, $quoted_string = true)
    {
        $text = trim($text);
        if (!$charset) {
            return $text;
        }
        if ($force_encode) {
            $var_292 = true;
        } else {
            $var_292 = false;
            $i = 0;
            while ($i < strlen($text)) {
                if (127 < ord($text[$i])) {
                    $var_292 = true;
                } else {
                    $i++;
                }
            }
        }
        if ($var_292) {
            $var_293 = preg_replace_callback("#([^a-zA-Z0-9!*+\\-/ ])#", function ($matches) {
                return "'=" . strtoupper(dechex(ord(str_replace("\\\\\"", "\\\"", $matches[1]))));
            }, $text);
            $var_293 = str_replace(" ", "_", $var_293);
            $var_293 = "=?" . $charset . "?q?" . $var_293 . "?=";
            return $var_293;
        }
        if ($quoted_string) {
            $text = str_replace(["\"", "(", ")"], ["\\\"", "\\(", "\\)"], $text);
            return "\"" . $text . "\"";
        }
        return preg_replace("#(\\r\\n|\\n|\\r)+#", " ", $text);
    }
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
function function_99($url, $seconds)
{
    global $Language;
    echo "<br />" . var_294(str_replace("{1}", $seconds, $Language[39])) . "\r\n\t<meta http-$equiv = \"refresh\" $content = \"" . $seconds . ";$url = " . $url . "\" />";
    exit;
}
function function_100($to, $subject, $body)
{
    global $MAIN;
    global $SMTP;
    global $THEME;
    $var_295 = $MAIN["SITENAME"];
    $fromemail = $MAIN["SITEEMAIL"];
    $var_296 = false;
    if (strtoupper(substr(PHP_OS, 0, 3) == "WIN")) {
        $emailLineDelimiter = "\r\n";
        $var_296 = true;
    } else {
        if (strtoupper(substr(PHP_OS, 0, 3) == "MAC")) {
            $emailLineDelimiter = "\r";
        } else {
            $emailLineDelimiter = "\n";
        }
    }
    $var_298 = md5(uniqid(rand(), true) . time());
    $var_41 = $_SERVER["SERVER_NAME"];
    $headers = "From: " . $var_295 . " <" . $fromemail . ">" . $emailLineDelimiter;
    $headers .= "Reply-To: " . $var_295 . " <" . $fromemail . ">" . $emailLineDelimiter;
    $headers .= "Return-Path: " . $var_295 . " <" . $fromemail . ">" . $emailLineDelimiter;
    $headers .= "Message-ID: <" . $var_298 . " thesystem@" . $var_41 . ">" . $emailLineDelimiter;
    $headers .= "X-Mailer: PHP v" . phpversion() . $emailLineDelimiter;
    $headers .= "MIME-Version: 1.0" . $emailLineDelimiter;
    $headers .= "Content-Transfer-Encoding: 8bit" . $emailLineDelimiter;
    $headers .= "Content-type: text/html; $charset = " . $THEME["charset"] . $emailLineDelimiter;
    $headers .= "X-Sender: PHP" . $emailLineDelimiter;
    if ($SMTP["smtptype"] == "default") {
        return mail($to, $subject, $body, $headers);
    }
    if ($SMTP["smtptype"] == "advanced") {
        if (isset($SMTP["smtp"]) && $SMTP["smtp"] == "yes") {
            ini_set("SMTP", $SMTP["smtp_host"]);
            ini_set("smtp_port", $SMTP["smtp_port"]);
            if ($var_296) {
                ini_set("sendmail_from", $SMTP["smtp_from"]);
            }
        }
        $var_299 = mail($to, $subject, $body, $headers);
        if (isset($SMTP["smtp"]) && $SMTP["smtp"] == "yes") {
            ini_restore("SMTP");
            ini_restore("smtp_port");
            if ($var_296) {
                ini_restore("sendmail_from");
            }
        }
        return $var_299;
    }
    $var_300 = new Class_5($SMTP);
    $var_300->function_91($to, trim($subject), trim($body), $fromemail, "", $THEME["charset"], $fromemail, $MAIN["BASEURL"]);
    $var_301 = $var_300->smtpSendEmail();
    return $var_301;
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function validatePerPage($numresults, &$page, &$perpage, $maxperpage = 20, $defaultperpage = 20)
{
    $perpage = intval($perpage);
    if ($perpage < 1) {
        $perpage = $defaultperpage;
    } else {
        if ($maxperpage < $perpage) {
            $perpage = $maxperpage;
        }
    }
    $totalPages = ceil($numresults / $perpage);
    if ($totalPages == 0) {
        $totalPages = 1;
    }
    if ($page < 1) {
        $page = 1;
    } else {
        if ($totalPages < $page) {
            $page = $totalPages;
        }
    }
}
function calculatePagination($pagenumber, $perpage, $total)
{
    $paginationFirstItem = $perpage * ($pagenumber - 1);
    $paginationLastItem = $paginationFirstItem + $perpage;
    if ($total < $paginationLastItem) {
        $paginationLastItem = $total;
    }
    $paginationFirstItem++;
    return ["first" => number_format($paginationFirstItem), "last" => number_format($paginationLastItem)];
}
function buildPaginationLinks($perpage, $results, $address)
{
    if ($results < $perpage) {
        return ["", ""];
    }
    if ($results) {
        $queryResult = @ceil($results / $perpage);
    } else {
        $queryResult = 0;
    }
    $pagenumber = isset($_GET["page"]) ? intval($_GET["page"]) : (isset($_POST["page"]) ? intval($_POST["page"]) : "");
    validatePerPage($results, $pagenumber, $perpage, 200);
    $limitOffset = ($pagenumber - 1) * $perpage;
    $var_244 = $pagenumber * $perpage;
    if ($results < $var_244) {
        $var_244 = $results;
        if ($results < $limitOffset) {
            $limitOffset = $results - $perpage - 1;
        }
    }
    if ($limitOffset < 0) {
        $limitOffset = 0;
    }
    $paginationLinks = $var_246 = $var_247 = $var_248 = $var_249 = "";
    $currentPage = 0;
    if ($results <= $perpage) {
        $paginationHtml["pagenav"] = false;
        return ["", "LIMIT " . $limitOffset . ", " . $perpage];
    }
    $paginationHtml["pagenav"] = true;
    $total = number_format($results);
    $paginationHtml["last"] = false;
    $paginationHtml["first"] = $paginationHtml["last"];
    $paginationHtml["next"] = $paginationHtml["first"];
    $paginationHtml["prev"] = $paginationHtml["next"];
    if (1 < $pagenumber) {
        $previousPage = $pagenumber - 1;
        $previousPageInfo = calculatePagination($previousPage, $perpage, $results);
        $paginationHtml["prev"] = true;
    }
    if ($pagenumber < $queryResult) {
        $nextPageNumber = $pagenumber + 1;
        $nextPageInfo = calculatePagination($nextPageNumber, $perpage, $results);
        $paginationHtml["next"] = true;
    }
    $pageRangeThreshold = "3";
    if (!isset($paginationSkipLinksArray) || !is_array($paginationSkipLinksArray)) {
        $var_258 = "10 50 100 500 1000";
        $paginationSkipLinksArray[] = preg_split("#\\s+#s", $var_258, -1, PREG_SPLIT_NO_EMPTY);
        while ($currentPage++ < $queryResult) {
        }
        $var_259 = isset($previousPage) && $previousPage != 1 ? "page=" . $previousPage : "";
        $paginationLinks = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t<tr>\r\n\t\t\t<td $style = \"padding: 0px 0px 1px 0px;\">\r\n\t\t\t\t<div $style = \"float: left;\" $id = \"navcontainer_f\">\r\n\t\t\t\t\t<ul>\r\n\t\t\t\t\t\t<li>" . $pagenumber . " - " . $queryResult . "</li>\r\n\t\t\t\t\t\t" . ($paginationHtml["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "\" $title = \"First Page - Show Results " . $firstPageInfo["first"] . " to " . $firstPageInfo["last"] . " of " . $total . "\">&laquo; First</a></li>" : "") . ($paginationHtml["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $var_259 . "\" $title = \"Previous Page - Show Results " . $previousPageInfo["first"] . " to " . $previousPageInfo["last"] . " of " . $total . "\">&lt;</a></li>" : "") . "\r\n\t\t\t\t\t\t" . $paginationLinks . "\r\n\t\t\t\t\t\t" . ($paginationHtml["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $nextPageNumber . "\" $title = \"Next Page - Show Results " . $nextPageInfo["first"] . " to " . $nextPageInfo["last"] . " of " . $total . "\">&gt;</a></li>" : "") . ($paginationHtml["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "page=" . $queryResult . "\" $title = \"Last Page - Show Results " . $lastPageInfo["first"] . " to " . $lastPageInfo["last"] . " of " . $total . "\">Last <strong>&raquo;</strong></a></li>" : "") . "\r\n\t\t\t\t\t</ul>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
        return [$paginationLinks, "LIMIT " . $limitOffset . ", " . $perpage];
    }
    if ($pageRangeThreshold <= abs($currentPage - $pagenumber) && $pageRangeThreshold != 0) {
        if ($currentPage == 1) {
            $firstPageInfo = calculatePagination(1, $perpage, $results);
            $paginationHtml["first"] = true;
        }
        if ($currentPage == $queryResult) {
            $lastPageInfo = calculatePagination($queryResult, $perpage, $results);
            $paginationHtml["last"] = true;
        }
        if (in_array(abs($currentPage - $pagenumber), $paginationSkipLinksArray) && $currentPage != 1 && $currentPage != $queryResult) {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $pageOffsetDisplay = $currentPage - $pagenumber;
            if (0 < $pageOffsetDisplay) {
                $pageOffsetDisplay = "+" . $pageOffsetDisplay;
            }
            $paginationLinks .= "<li><a class=\"smalltext\" $href = \"" . $address . ($currentPage != 1 ? "page=" . $currentPage : "") . "\" $title = \"Show results " . $pageRangeInfo["first"] . " to " . $pageRangeInfo["last"] . " of " . $total . "\"><!--" . $pageOffsetDisplay . "-->" . $currentPage . "</a></li>";
        }
    } else {
        if ($currentPage == $pagenumber) {
            $currentPageInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a $name = \"current\" class=\"current\" $title = \"Showing results " . $currentPageInfo["first"] . " to " . $currentPageInfo["last"] . " of " . $total . "\">" . $currentPage . "</a></li>";
        } else {
            $pageRangeInfo = calculatePagination($currentPage, $perpage, $results);
            $paginationLinks .= "<li><a $href = \"" . $address . ($currentPage != 1 ? "page=" . $currentPage : "") . "\" $title = \"Show results " . $pageRangeInfo["first"] . " to " . $pageRangeInfo["last"] . " of " . $total . "\">" . $currentPage . "</a></li>";
        }
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
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>