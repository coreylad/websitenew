<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/mass_invite.lang");
$Message = "";
$amount = isset($_POST["amount"]) ? intval($_POST["amount"]) : 5;
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $amount) {
    $HashArray = [];
    for ($i = 1; $i <= $amount; $i++) {
        $hash = substr(md5(md5(rand())), 0, 32);
        $HashArray[] = "<li>" . $hash . "</li>";
        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO invites (inviter, invitee, hash, time_invited) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $_SESSION["ADMIN_ID"]) . "', 'manual', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $hash) . "', NOW())");
    }
    $Message = showAlertError(str_replace("{1}", number_format($amount), $Language[6]) . "<hr /><ol>" . implode(" ", $HashArray) . "</ol>");
}
echo "\r\n\r\n" . $Message . "\r\n<form $method = \"post\" $action = \"index.php?do=mass_invite\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $align = \"right\">" . $Language[5] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"amount\" $value = \"" . $amount . "\" $style = \"width: 50px;\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td $align = \"right\" class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[3] . "\" /> <input $type = \"reset\" $value = \"" . $Language[4] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>";
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
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}

?>