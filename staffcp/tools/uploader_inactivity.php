<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/uploader_inactivity.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $UploaderGroups = implode(",", $_POST["usergroups"]);
    $DemoteTo = intval($_POST["DemoteTo"]);
    $timelimit = intval($_POST["timelimit"]);
    $Configarray = serialize(["UploaderGroups" => $UploaderGroups, "DemoteTo" => $DemoteTo, "timelimit" => $timelimit]);
    mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO `ts_config` VALUES ('UI', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Configarray) . "')");
    $Message = str_replace(["{1}"], [$_SESSION["ADMIN_USERNAME"]], $Language[3]);
    logStaffAction($Message);
    $Message = showAlertError($Message);
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'UI'");
$Result = mysqli_fetch_assoc($query);
$UI = unserialize($Result["content"]);
$UploaderGroups = $UI["UploaderGroups"] ? explode(",", $UI["UploaderGroups"]) : [];
$squery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, namestyle FROM usergroups");
$scount = 1;
for ($sgids = "\r\n<fieldset>\r\n\t<legend>" . $Language[6] . "</legend>\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTableNoBorder\">\r\n\t\t\t<tr>"; $gid = mysqli_fetch_assoc($squery); $scount++) {
    if ($scount % 4 == 1) {
        $sgids .= "</tr><tr>";
    }
    $sgids .= "\r\n\t<td class=\"alt1\"><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"" . $gid["gid"] . "\"" . (in_array($gid["gid"], $UploaderGroups, true) ? " $checked = \"checked\"" : "") . " /></td>\r\n\t<td class=\"alt1\">" . str_replace("{username}", $gid["title"], $gid["namestyle"]) . "</td>";
}
$sgids .= "\r\n\t\t\t</tr>\r\n\t\t</table>\r\n</fieldset>";
$udquery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title FROM usergroups WHERE $cansettingspanel = 'no' AND $canstaffpanel = 'no' AND $issupermod = 'no'");
$udgids = "<select $name = \"DemoteTo\">";
while ($udgid = mysqli_fetch_assoc($udquery)) {
    $udgids .= "<option $value = \"" . $udgid["gid"] . "\"" . ($UI["DemoteTo"] == $udgid["gid"] ? " $selected = \"selected\"" : "") . ">" . $udgid["title"] . "</option>";
}
$udgids .= "</select>";
echo "\r\n" . showAlertMessage("<a $href = \"index.php?do=manage_cronjobs\">" . $Language[13] . "</a>") . "\r\n" . $Message . "\r\n<form $method = \"post\" $action = \"index.php?do=uploader_inactivity\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"3\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[4] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t" . $Language[5] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t" . $sgids . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[7] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t" . $Language[8] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t" . $udgids . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[9] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t" . $Language[10] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t<input $type = \"text\" $name = \"timelimit\" $value = \"" . $UI["timelimit"] . "\" $size = \"10\" />\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\">\r\n\t\t\t<input $type = \"submit\" $value = \"" . $Language[11] . "\" /> <input $type = \"reset\" $value = \"" . $Language[12] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>\r\n";
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
function redirectTo($url, $timeout = false)
{
    if (!headers_sent()) {
        if (!$timeout) {
            header("Location: " . $url);
        } else {
            header("Refresh: 5; $url = " . $url);
        }
    } else {
        if (!$timeout) {
            echo "\r\n\t\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t\t\t</script>\r\n\t\t\t\t<noscript>\r\n\t\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t\t\t</noscript>";
        } else {
            echo "\r\n\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\tsetTimeout( \"window.location.$href = '" . $url . "'\", 5000);\r\n\t\t\t</script>\r\n\t\t\t<noscript>\r\n\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"5;$url = " . $url . "\" />\r\n\t\t\t</noscript>\r\n\t\t\t";
        }
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
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>