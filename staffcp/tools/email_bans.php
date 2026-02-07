<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . function_75() . "/email_bans.lang");
$Message = "";
$value = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $value = trim($_POST["value"]);
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE bannedemails SET value = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $value) . "' WHERE id = 1");
    $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[5]);
    function_79($Message);
    $Message = function_76($Message);
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT value FROM bannedemails WHERE id = 1");
$BANNEDEMAILS = mysqli_fetch_assoc($query);
echo "\r\n" . $Message . "\r\n<form method=\"post\" action=\"index.php?do=email_bans\">\r\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" align=\"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<textarea style=\"width: 99%; height: 100px;\" name=\"value\">" . $BANNEDEMAILS["value"] . "</textarea>\r\n\t\t\t<div>" . $Language[8] . "</div>\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . $Language[6] . "\" /> <input type=\"reset\" value=\"" . $Language[7] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>";
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
function function_76($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function function_79($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}

?>