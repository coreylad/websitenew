<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/search_user.lang");
$Message = "";
$username = isset($_GET["username"]) ? trim($_GET["username"]) : "";
$usergroup = "";
$email = isset($_GET["email"]) ? trim($_GET["email"]) : "";
$ip = isset($_GET["ip"]) ? trim($_GET["ip"]) : "";
$joindateafter = "";
$joindatebefore = "";
$lastaccessafter = "";
$lastaccessbefore = "";
$bdayafter = "";
$bdaybefore = "";
$postgreater = "";
$postless = "";
$warnsgreater = "";
$warnless = "";
$ulgreater = "";
$ulless = "";
$dlgreater = "";
$dlless = "";
$ratiogreater = "";
$ratioless = "";
$idgreater = "";
$idless = "";
$modcomment = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $username = trim($_POST["username"]);
    $usergroup = intval($_POST["usergroup"]);
    $email = trim($_POST["email"]);
    $ip = trim($_POST["ip"]);
    $joindateafter = trim($_POST["joindateafter"]);
    $joindatebefore = trim($_POST["joindatebefore"]);
    $lastaccessafter = trim($_POST["lastaccessafter"]);
    $lastaccessbefore = trim($_POST["lastaccessbefore"]);
    $bdayafter = trim($_POST["bdayafter"]);
    $bdaybefore = trim($_POST["bdaybefore"]);
    $postgreater = intval($_POST["postgreater"]);
    $postless = intval($_POST["postless"]);
    $warnsgreater = intval($_POST["warnsgreater"]);
    $warnless = intval($_POST["warnless"]);
    $ulgreater = intval($_POST["ulgreater"]);
    $ulless = intval($_POST["ulless"]);
    $dlgreater = intval($_POST["dlgreater"]);
    $dlless = intval($_POST["dlless"]);
    $ratiogreater = trim($_POST["ratiogreater"]);
    $ratioless = trim($_POST["ratioless"]);
    $idgreater = intval($_POST["idgreater"]);
    $idless = intval($_POST["idless"]);
    $modcomment = trim($_POST["modcomment"]);
    $Queries = [];
    if ($joindateafter) {
        $Queries[] = "UNIX_TIMESTAMP(added) > '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], strtotime($joindateafter)) . "'";
    }
    if ($joindatebefore) {
        $Queries[] = "UNIX_TIMESTAMP(added) < '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], strtotime($joindatebefore)) . "'";
    }
    if ($lastaccessafter) {
        $Queries[] = "UNIX_TIMESTAMP(last_access) > '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], strtotime($lastaccessafter)) . "'";
    }
    if ($lastaccessbefore) {
        $Queries[] = "UNIX_TIMESTAMP(last_access) < '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], strtotime($lastaccessbefore)) . "'";
    }
    if ($bdayafter) {
        $Queries[] = "UNIX_TIMESTAMP(birthday) > '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $bdayafter) . "'";
    }
    if ($bdaybefore) {
        $Queries[] = "UNIX_TIMESTAMP(birthday) < '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $bdaybefore) . "'";
    }
    if ($postgreater) {
        $Queries[] = "totalposts >= '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $postgreater) . "'";
    }
    if ($postless) {
        $Queries[] = "totalposts < '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $postless) . "'";
    }
    if ($warnsgreater) {
        $Queries[] = "timeswarned >= '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $warnsgreater) . "'";
    }
    if ($warnless) {
        $Queries[] = "timeswarned < '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $warnless) . "'";
    }
    if ($ulgreater) {
        $Queries[] = "uploaded >= '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ulgreater * 1024 * 1024 * 1024) . "'";
    }
    if ($ulless) {
        $Queries[] = "uploaded < '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ulless * 1024 * 1024 * 1024) . "'";
    }
    if ($dlgreater) {
        $Queries[] = "downloaded >= '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $dlgreater * 1024 * 1024 * 1024) . "'";
    }
    if ($dlless) {
        $Queries[] = "downloaded < '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $dlless * 1024 * 1024 * 1024) . "'";
    }
    if ($ratiogreater) {
        $Queries[] = "uploaded / downloaded >= '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ratiogreater) . "'";
    }
    if ($ratioless) {
        $Queries[] = "uploaded / downloaded < '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ratioless) . "'";
    }
    if ($idgreater) {
        $Queries[] = "id >= '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $idgreater) . "'";
    }
    if ($idless) {
        $Queries[] = "id < '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $idless) . "'";
    }
    if ($modcomment) {
        $Queries[] = "`modcomment` LIKE '%" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $modcomment) . "%'";
    }
    if ($username) {
        if ($_POST["exact"] == "1") {
            $Queries[] = "`username` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'";
        } else {
            $Queries[] = "`username` LIKE '%" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "%'";
        }
    }
    if ($usergroup) {
        $Queries[] = "`usergroup` = '" . $usergroup . "'";
    }
    if ($email) {
        $Queries[] = "`email` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $email) . "'";
    }
    if ($ip) {
        $Queries[] = "`ip` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $ip) . "'";
    }
    $sql = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, username, last_access, email, ip, uploaded, downloaded, invites, seedbonus, g.title, g.namestyle FROM users LEFT JOIN usergroups g ON (users.$usergroup = g.gid)" . (count($Queries) ? " WHERE " . implode(" AND ", $Queries) : ""));
    if (mysqli_num_rows($sql) == 0) {
        $Message = showAlertError($Language[4]);
    } else {
        $Found = "";
        while ($User = mysqli_fetch_assoc($sql)) {
            $Found .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . $User["username"] . "\">" . applyUsernameStyle($User["username"], $User["namestyle"]) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . $User["title"] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . htmlspecialchars($User["email"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . htmlspecialchars($User["ip"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatTimestamp($User["last_access"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatBytes($User["uploaded"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . formatBytes($User["downloaded"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . number_format($User["invites"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . $User["seedbonus"] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a $href = \"index.php?do=edit_user&amp;$username = " . htmlspecialchars($User["username"]) . "\"><img $src = \"images/user_edit.png\" $alt = \"" . $Language[18] . "\" $title = \"" . $Language[18] . "\" $border = \"0\" /></a>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
        $Message = "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"10\"><b>" . $Language[3] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[12] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[19] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[20] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[13] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[14] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[15] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[16] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[17] . "</b></td>\r\n\t\t\t\t<td class=\"alt2\"><b>" . $Language[18] . "</b></td>\r\n\t\t\t</tr>\r\n\t\t\t" . $Found . "\r\n\t\t</table>";
    }
}
$showusergroups = "\r\n<select $name = \"usergroup\">\r\n\t<option $value = \"0\"></option>";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title FROM usergroups ORDER by disporder ASC");
while ($UG = mysqli_fetch_assoc($query)) {
    $showusergroups .= "\r\n\t<option $value = \"" . $UG["gid"] . "\"" . ($usergroup == $UG["gid"] ? " $selected = \"selected\"" : "") . ">" . $UG["title"] . "</option>";
}
$showusergroups .= "\r\n</select>";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
$Result = mysqli_fetch_assoc($query);
$MAIN = unserialize($Result["content"]);
echo "<script $type = \"text/javascript\">\r\n\t\$(function()\r\n\t{\r\n\t\t\$('#joindateafter,#joindatebefore,#lastaccessafter,#lastaccessbefore').datepicker({dateFormat: \"yy-mm-dd\", changeMonth: true, changeYear: true, closeText: \"X\", showButtonPanel: true});\r\n\t\t\$('#bdayafter,#bdaybefore').datepicker({dateFormat: 'dd-mm-yy', changeMonth: true, changeYear: true, closeText: \"X\", showButtonPanel: true});\r\n\t});\r\n</script>\r\n<form $action = \"";
echo $_SERVER["SCRIPT_NAME"];
echo "?do=search_user\" $method = \"post\" $name = \"search_user\">\r\n";
echo $Message;
echo "<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>";
echo $Language[2];
echo "</b></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[6];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"username\" $value = \"";
echo htmlspecialchars($username);
echo "\" $size = \"35\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[7];
echo "</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t<div class=\"smallfont\" $style = \"white-space:nowrap\">\r\n\t\t\t\t<input $type = \"radio\" $name = \"exact\" $id = \"rb_1_exact_2\" $value = \"1\" $tabindex = \"1\" />";
echo $Language[8];
echo "\t\t\t\t<input $type = \"radio\" $name = \"exact\" $id = \"rb_0_exact_2\" $value = \"0\" $tabindex = \"1\" $checked = \"checked\" />";
echo $Language[9];
echo "\t\t\t</div>\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[12];
echo "</td>\r\n\t\t<td class=\"alt1\">";
echo $showusergroups;
echo "</td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[19];
echo "</td>\r\n\t\t<td class=\"alt2\"><input $type = \"text\" class=\"bginput\" $name = \"email\" $value = \"";
echo htmlspecialchars($email);
echo "\" $size = \"35\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[20];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"ip\" $value = \"";
echo htmlspecialchars($ip);
echo "\" $size = \"35\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[39];
echo "</td>\r\n\t\t<td class=\"alt2\"><input $type = \"text\" class=\"bginput\" $name = \"modcomment\" $value = \"";
echo htmlspecialchars($modcomment);
echo "\" $size = \"35\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[21];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"joindateafter\" $id = \"joindateafter\" $value = \"";
echo htmlspecialchars($joindateafter);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[22];
echo "</td>\r\n\t\t<td class=\"alt2\"><input $type = \"text\" class=\"bginput\" $name = \"joindatebefore\" $id = \"joindatebefore\" $value = \"";
echo htmlspecialchars($joindatebefore);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[23];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"lastaccessafter\" $id = \"lastaccessafter\" $value = \"";
echo htmlspecialchars($lastaccessafter);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[24];
echo "</td>\r\n\t\t<td class=\"alt2\"><input $type = \"text\" class=\"bginput\" $name = \"lastaccessbefore\" $id = \"lastaccessbefore\" $value = \"";
echo htmlspecialchars($lastaccessbefore);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[25];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"bdayafter\" $id = \"bdayafter\" $value = \"";
echo htmlspecialchars($bdayafter);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[26];
echo "</td>\r\n\t\t<td class=\"alt2\"><input $type = \"text\" class=\"bginput\" $name = \"bdaybefore\" $id = \"bdaybefore\" $value = \"";
echo htmlspecialchars($bdaybefore);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[27];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"postgreater\" $value = \"";
echo htmlspecialchars($postgreater);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[28];
echo "</td>\r\n\t\t<td class=\"alt2\"><input $type = \"text\" class=\"bginput\" $name = \"postless\" $value = \"";
echo htmlspecialchars($postless);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[29];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"warnsgreater\" $value = \"";
echo htmlspecialchars($warnsgreater);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[30];
echo "</td>\r\n\t\t<td class=\"alt2\"><input $type = \"text\" class=\"bginput\" $name = \"warnless\" $value = \"";
echo htmlspecialchars($warnless);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[31];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"ulgreater\" $value = \"";
echo htmlspecialchars($ulgreater);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /> (GB)</td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[32];
echo "</td>\r\n\t\t<td class=\"alt2\"><input $type = \"text\" class=\"bginput\" $name = \"ulless\" $value = \"";
echo htmlspecialchars($ulless);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /> (GB)</td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[33];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"dlgreater\" $value = \"";
echo htmlspecialchars($dlgreater);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /> (GB)</td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[34];
echo "</td>\r\n\t\t<td class=\"alt2\"><input $type = \"text\" class=\"bginput\" $name = \"dlless\" $value = \"";
echo htmlspecialchars($dlless);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /> (GB)</td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[35];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"ratiogreater\" $value = \"";
echo htmlspecialchars($ratiogreater);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[36];
echo "</td>\r\n\t\t<td class=\"alt2\"><input $type = \"text\" class=\"bginput\" $name = \"ratioless\" $value = \"";
echo htmlspecialchars($ratioless);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[37];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"idgreater\" $value = \"";
echo htmlspecialchars($idgreater);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[38];
echo "</td>\r\n\t\t<td class=\"alt2\"><input $type = \"text\" class=\"bginput\" $name = \"idless\" $value = \"";
echo htmlspecialchars($idless);
echo "\" $size = \"10\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" $align = \"right\"></td>\r\n\t\t<td class=\"tcat2\">\r\n\t\t\t<input $type = \"submit\" class=\"button\" $tabindex = \"1\" $value = \"";
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

?>