<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/invite_tree.lang");
$Message = "";
$username = isset($_GET["username"]) ? trim($_GET["username"]) : (isset($_POST["username"]) ? trim($_POST["username"]) : "");
$Found = "";
$IFound = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    if ($username) {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, u.username, u.added, u.last_access, u.invites, u.uploaded, u.downloaded, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$username = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "' LIMIT 1");
        if (0 < mysqli_num_rows($query)) {
            $User = mysqli_fetch_assoc($query);
            $Found = "\t\t\t\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"6\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[2] . " - " . $User["username"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[4] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[9] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[10] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[11] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[12] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt2\">\r\n\t\t\t\t\t\t" . $Language[13] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"20%\">\r\n\t\t\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $User["username"] . "\">" . applyUsernameStyle($User["username"], $User["namestyle"]) . "</a>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"20%\">\r\n\t\t\t\t\t\t" . formatTimestamp($User["added"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"20%\">\r\n\t\t\t\t\t\t" . formatTimestamp($User["last_access"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"10%\">\r\n\t\t\t\t\t\t" . number_format($User["invites"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"15%\">\r\n\t\t\t\t\t\t" . formatBytes($User["uploaded"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"15%\">\r\n\t\t\t\t\t\t" . formatBytes($User["downloaded"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t";
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.username, u.added, u.last_access, u.invites, u.uploaded, u.downloaded, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$invited_by = '" . $User["id"] . "' ORDER by u.username ASC");
            if (0 < mysqli_num_rows($query)) {
                $IFound = "\r\n\t\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t";
                while ($IUser = mysqli_fetch_assoc($query)) {
                    $IFound .= "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"20%\">\r\n\t\t\t\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $IUser["username"] . "\">" . applyUsernameStyle($IUser["username"], $IUser["namestyle"]) . "</a>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"20%\">\r\n\t\t\t\t\t\t\t" . formatTimestamp($IUser["added"]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"20%\">\r\n\t\t\t\t\t\t\t" . formatTimestamp($IUser["last_access"]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"10%\">\r\n\t\t\t\t\t\t\t" . number_format($IUser["invites"]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"15%\">\r\n\t\t\t\t\t\t\t" . formatBytes($IUser["uploaded"]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td class=\"alt1\" $width = \"15%\">\r\n\t\t\t\t\t\t\t" . formatBytes($IUser["downloaded"]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t";
                }
                $IFound .= "\r\n\t\t\t\t</table>";
            } else {
                $Message = showAlertError($Language[8]);
            }
        } else {
            $Message = showAlertError($Language[3]);
        }
    } else {
        $Message = showAlertError($Language[7]);
    }
}
echo "<form $action = \"";
echo $_SERVER["SCRIPT_NAME"];
echo "?do=invite_tree\" $method = \"post\">\r\n";
echo $Message . $Found . $IFound;
echo "<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>";
echo $Language[2];
echo "</b></td>\r\n\t</tr>\t\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[4];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"username\" $value = \"";
echo htmlspecialchars($username);
echo "\" $size = \"35\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\">\t\r\n\t\t\t<input $type = \"submit\" class=\"button\" $tabindex = \"1\" $value = \"";
echo $Language[5];
echo "\" $accesskey = \"s\" />\r\n\t\t\t<input $type = \"reset\" class=\"button\" $tabindex = \"1\" $value = \"";
echo $Language[6];
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
function applyUsernameStyle($username, $namestyle)
{
    return str_replace("{username}", $username, $namestyle);
}

?>