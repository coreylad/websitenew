<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/search_passkey.lang");
$Message = "";
$passkey = isset($_GET["passkey"]) ? trim($_GET["passkey"]) : "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $passkey = isset($_POST["passkey"]) ? trim($_POST["passkey"]) : "";
    if ($passkey) {
        $sql = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, ip, username, last_access, email, uploaded, downloaded, invites, seedbonus, g.title, g.namestyle FROM users LEFT JOIN usergroups g ON (users.$usergroup = g.gid) WHERE $torrent_pass = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $passkey) . "'");
        if (0 < mysqli_num_rows($sql)) {
            $User = mysqli_fetch_assoc($sql);
            $User["ip"] = htmlspecialchars($User["ip"]);
            $userips = [];
            $userips[] = "<a $href = \"index.php?do=search_ip&amp;$ip = " . $User["ip"] . "\">" . $User["ip"] . "</a>";
            $findips = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT ip FROM iplog WHERE `userid` = '" . $User["id"] . "' ORDER by ip DESC");
            while ($uip = mysqli_fetch_assoc($findips)) {
                $uip["ip"] = htmlspecialchars($uip["ip"]);
                if (!in_array("<a $href = \"index.php?do=search_ip&amp;$ip = " . $uip["ip"] . "\">" . $uip["ip"] . "</a>", $userips)) {
                    $userips[] = "<a $href = \"index.php?do=search_ip&amp;$ip = " . $uip["ip"] . "\">" . $uip["ip"] . "</a>";
                }
            }
            $Found = "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $User["username"] . "\">" . applyUsernameStyle($User["username"], $User["namestyle"]) . "</a>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t\t" . htmlspecialchars($User["email"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t\t<div $align = \"justify\"><small>" . implode(" | ", $userips) . "</small></div>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t\t" . $User["title"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t\t" . formatTimestamp($User["last_access"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t\t" . var_238($User["uploaded"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t\t" . var_238($User["downloaded"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t\t" . number_format($User["invites"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t\t" . $User["seedbonus"] . "\r\n\t\t\t\t\t</td>\t\t\t\t\t\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
            $Message = "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"9\"><b>" . $Language[3] . " for Passkey: " . htmlspecialchars($passkey) . "</b></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[9] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[20] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[19] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[12] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[13] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[14] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[15] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[16] . "</b></td>\r\n\t\t\t\t\t<td class=\"alt2\"><b>" . $Language[17] . "</b></td>\t\t\t\t\t\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . $Found . "\r\n\t\t\t</table>";
        } else {
            if (strlen($passkey) != 32) {
                $Message = showAlertError($Language[5]);
            } else {
                $Message = showAlertError($Language[4]);
            }
        }
    } else {
        $Message = showAlertError($Language[6]);
    }
}
echo "<form $action = \"";
echo $_SERVER["SCRIPT_NAME"];
echo "?do=search_passkey\" $method = \"post\">\r\n";
echo $Message;
echo "<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>";
echo $Language[2];
echo "</b></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[7];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"passkey\" $value = \"";
echo htmlspecialchars($passkey);
echo "\" $size = \"50\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\t\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\">\t\r\n\t\t\t<input $type = \"submit\" class=\"button\" $tabindex = \"1\" $value = \"";
echo $Language[10];
echo "\" $accesskey = \"s\" />\r\n\t\t\t<input $type = \"reset\" class=\"button\" $tabindex = \"1\" $value = \"";
echo $Language[11];
echo "\" $accesskey = \"r\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";
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

?>