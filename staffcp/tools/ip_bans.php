<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/ip_bans.lang");
$Message = "";
$value = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $value = trim($_POST["value"]);
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ipbans SET $value = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $value) . "', $date = NOW(), $modifier = '" . $_SESSION["ADMIN_ID"] . "' WHERE `id` = 1");
    if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
        $SysMsg = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[5]);
        logStaffAction($SysMsg);
    }
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT value FROM ipbans WHERE `id` = 1");
$IPBANS = mysqli_fetch_assoc($query);
echo "\t\t\t\t\r\n\r\n" . $Message . "\r\n<form $method = \"post\" $action = \"index.php?do=ip_bans\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<textarea $style = \"width: 99%; height: 400px;\" $name = \"value\">" . $IPBANS["value"] . "</textarea>\r\n\t\t\t<small>" . $Language[8] . "</small>\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[6] . "\" /> <input $type = \"reset\" $value = \"" . $Language[7] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>";
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
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}

?>