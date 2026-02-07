<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/payment_api_manager.lang");
$Message = "";
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
if ($Act == "edit" && isset($_GET["aid"]) && ($Aid = intval($_GET["aid"]))) {
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_subscriptions_api WHERE $aid = '" . $Aid . "'");
    if (mysqli_num_rows($Query)) {
        $API = mysqli_fetch_assoc($Query);
        $active = $API["active"];
        $email = $API["email"];
        $secretkey = $API["secretkey"];
        $title = htmlspecialchars($API["title"]);
        $currency = htmlspecialchars($API["currency"]);
        $widget = $API["widget"];
        $secretkey2 = $API["secretkey2"];
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $active = isset($_POST["active"]) ? intval($_POST["active"]) : 0;
            $email = isset($_POST["email"]) && !empty($_POST["email"]) ? trim($_POST["email"]) : $email;
            $secretkey = isset($_POST["secretkey"]) && !empty($_POST["secretkey"]) ? trim($_POST["secretkey"]) : $secretkey;
            $currency = isset($_POST["currency"]) && !empty($_POST["currency"]) ? trim($_POST["currency"]) : $currency;
            $widget = isset($_POST["widget"]) && !empty($_POST["widget"]) ? trim($_POST["widget"]) : $widget;
            $secretkey2 = isset($_POST["secretkey2"]) && !empty($_POST["secretkey2"]) ? trim($_POST["secretkey2"]) : $secretkey2;
            if ((!$_POST["email"] || !$_POST["currency"]) && $API["method"] != "paymentwall") {
                $Message = showAlertError($Language[17]);
            }
            if (empty($Message)) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_subscriptions_api SET $active = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $active) . "', $email = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $email) . "', $secretkey = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $secretkey) . "', $currency = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $currency) . "', $widget = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $widget) . "', $secretkey2 = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $secretkey2) . "'  WHERE $aid = '" . $Aid . "'");
                redirectTo("index.php?do=payment_api_manager");
                exit;
            }
        }
        echo "\r\n\t\t<form $action = \"index.php?do=payment_api_manager&$act = edit&$aid = " . $Aid . "\" $method = \"post\">\r\n\t\t" . $Message . "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\">\r\n\t\t\t\t\t" . $Language[14] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $Language[3] . "</legend>\r\n\t\t\t\t\t\t" . $title . "\r\n\t\t\t\t\t</fieldset>\t\t\t\t\t\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $Language[5] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"radio\" $name = \"active\" $value = \"1\"" . ($active == "1" ? " $checked = \"checked\"" : "") . " /> " . $Language[7] . "\r\n\t\t\t\t\t\t<input $type = \"radio\" $name = \"active\" $value = \"0\"" . ($active == "0" ? " $checked = \"checked\"" : "") . " /> " . $Language[8] . "\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend><font $color = \"red\">*</font> " . $Language[4] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"currency\" $value = \"" . htmlspecialchars($currency) . "\" $size = \"60\" $dir = \"ltr\" $tabindex = \"7\" /><br /><font $color = \"red\">*</font><small>" . $Language[18] . "</small>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\t\t\t\t\r\n\t\t\t\t\t" . ($API["method"] == "paymentwall" ? "\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>Application Key</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"secretkey\" $value = \"" . htmlspecialchars($secretkey) . "\" $size = \"60\" $dir = \"ltr\" $tabindex = \"7\" />\r\n\t\t\t\t\t\t<div></div>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>Secret Key</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"secretkey2\" $value = \"" . htmlspecialchars($secretkey2) . "\" $size = \"60\" $dir = \"ltr\" $tabindex = \"7\" />\r\n\t\t\t\t\t\t<div></div>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>Widget</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"widget\" $value = \"" . htmlspecialchars($widget) . "\" $size = \"60\" $dir = \"ltr\" $tabindex = \"7\" />\r\n\t\t\t\t\t\t<div></div>\r\n\t\t\t\t\t</fieldset>" : "\r\n\t\t\t\t\t\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . str_replace("{1}", $title, trim($Language[10])) . "</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"email\" $value = \"" . htmlspecialchars($email) . "\" $size = \"60\" $dir = \"ltr\" $tabindex = \"7\" />\r\n\t\t\t\t\t\t<div><small>" . str_replace("{1}", $title, trim($Language[11])) . "</small></div>\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . str_replace("{1}", $title, trim($Language[12])) . "</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" class=\"bginput\" $name = \"secretkey\" $value = \"" . htmlspecialchars($secretkey) . "\" $size = \"60\" $dir = \"ltr\" $tabindex = \"7\" />\r\n\t\t\t\t\t\t<div><small>" . str_replace("{1}", $title, trim($Language[13])) . "</small></div>\r\n\t\t\t\t\t</fieldset>") . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\t\t\t\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[15] . "\" /> <input $type = \"reset\" $value = \"" . $Language[16] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>\r\n\t\t";
    } else {
        $Act = "";
    }
}
if (empty($Act)) {
    $List = "";
    $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_subscriptions_api");
    if (mysqli_num_rows($Query)) {
        while ($API = mysqli_fetch_assoc($Query)) {
            $List .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . htmlspecialchars($API["title"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . htmlspecialchars($API["currency"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . ($API["active"] == "1" ? $Language[7] : $Language[8]) . "\r\n\t\t\t\t</td>\t\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<a $href = \"index.php?do=payment_api_manager&amp;$act = edit&amp;$aid = " . $API["aid"] . "\">" . trim($Language[9]) . "</a>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    }
    echo "\r\n\t\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $colspan = \"6\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">" . $Language[3] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[4] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[5] . "</td>\r\n\t\t\t<td class=\"alt2\">" . $Language[6] . "</td>\r\n\t\t</tr>\r\n\t\t" . $List . "\r\n\t</table>\r\n\t";
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
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>