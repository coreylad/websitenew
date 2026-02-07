<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . function_75() . "/search_ip.lang");
$Message = "";
$username = isset($_GET["username"]) ? trim($_GET["username"]) : "";
$ip = isset($_GET["ip"]) ? trim($_GET["ip"]) : "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $username = trim($_POST["username"]);
    $ip = trim($_POST["ip"]);
    if ($username) {
        $sql = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, ip, username, last_access, email, uploaded, downloaded, invites, seedbonus, g.title, g.namestyle FROM users LEFT JOIN usergroups g ON (users.usergroup=g.gid) WHERE username = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'");
        if (0 < mysqli_num_rows($sql)) {
            $User = mysqli_fetch_assoc($sql);
            $User["ip"] = htmlspecialchars($User["ip"]);
            $userips = [];
            $userips[] = "<a href=\"index.php?do=search_ip&amp;ip=" . $User["ip"] . "\">" . $User["ip"] . "</a>";
            $findips = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT ip FROM iplog WHERE userid = '" . $User["id"] . "' ORDER by ip DESC");
            while ($uip = mysqli_fetch_assoc($findips)) {
                $uip["ip"] = htmlspecialchars($uip["ip"]);
                if (!in_array("<a href=\"index.php?do=search_ip&amp;ip=" . $uip["ip"] . "\">" . $uip["ip"] . "</a>", $userips)) {
                    $userips[] = "<a href=\"index.php?do=search_ip&amp;ip=" . $uip["ip"] . "\">" . $uip["ip"] . "</a>";
                }
            }
            $Found = "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . $User["username"] . "\">" . function_83($User["username"], $User["namestyle"]) . "</a>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t" . htmlspecialchars($User["email"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t<div align=\"justify\"><small>" . implode(" | ", $userips) . "</small></div>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t" . $User["title"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t" . function_84($User["last_access"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t" . var_238($User["uploaded"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t" . var_238($User["downloaded"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t" . number_format($User["invites"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">\r\n\t\t\t\t\t\t" . $User["seedbonus"] . "\r\n\t\t\t\t\t</td>\t\t\t\t\t\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
            $Message = "\r\n\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"9\"><b>" . $Language[3] . "</b></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[9] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[20] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[19] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[12] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[13] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[14] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[15] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[16] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[17] . "</b></td>\t\t\t\t\t\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . $Found . "\r\n\t\t\t</table>";
        } else {
            $Message = function_76($Language[5]);
        }
    } else {
        if ($ip) {
            $sql = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT i.ip, u.id, u.username, u.last_access, u.email, u.uploaded, u.downloaded, u.invites, u.seedbonus, u.ip as ip2, g.title, g.namestyle FROM iplog i LEFT JOIN users u ON (i.userid=u.id) LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE i.ip LIKE '%" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ip) . "%'");
            if (0 < mysqli_num_rows($sql)) {
                $Found = "";
                while ($User = mysqli_fetch_assoc($sql)) {
                    if ($User["username"]) {
                        $Found .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t<a href=\"index.php?do=edit_user&amp;username=" . $User["username"] . "\">" . function_83($User["username"], $User["namestyle"]) . "</a>\r\n\t\t\t\t\t</td>\t\t\t\t\t\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . htmlspecialchars($User["email"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . htmlspecialchars($User["ip"] ? $User["ip"] : ($User["ip2"] ? $User["ip2"] : "-")) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $User["title"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $User["last_access"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . var_238($User["uploaded"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . var_238($User["downloaded"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . number_format($User["invites"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $User["seedbonus"] . "\r\n\t\t\t\t\t</td>\t\t\t\t\t\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                    }
                }
                $Message = "\r\n\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" align=\"center\" colspan=\"9\"><b>" . $Language[3] . "</b></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[9] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[20] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[19] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[12] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[13] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[14] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[15] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[16] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[17] . "</b></td>\t\t\t\t\t\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . $Found . "\r\n\t\t\t</table>";
            } else {
                $Message = function_76($Language[4]);
            }
        } else {
            $Message = function_76($Language[6]);
        }
    }
}
echo "<form action=\"";
echo $_SERVER["SCRIPT_NAME"];
echo "?do=search_ip\" method=\"post\">\r\n";
echo $Message;
echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" align=\"center\" colspan=\"2\"><b>";
echo $Language[2];
echo "</b></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo $Language[7];
echo "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"ip\" value=\"";
echo htmlspecialchars($ip);
echo "\" size=\"35\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo $Language[8];
echo "</td>\r\n\t\t<td class=\"alt2\"><input type=\"text\" class=\"bginput\" name=\"username\" value=\"";
echo htmlspecialchars($username);
echo "\" size=\"35\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\">\t\r\n\t\t\t<input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"";
echo $Language[10];
echo "\" accesskey=\"s\" />\r\n\t\t\t<input type=\"reset\" class=\"button\" tabindex=\"1\" value=\"";
echo $Language[11];
echo "\" accesskey=\"r\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";
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
function function_76($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
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
function function_83($username, $namestyle)
{
    return str_replace("{username}", $username, $namestyle);
}

?>