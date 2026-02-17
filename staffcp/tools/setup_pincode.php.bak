<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/setup_pincode.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $current_pincode = isset($_POST["current_pincode"]) ? trim($_POST["current_pincode"]) : "";
    $new_pincode1 = isset($_POST["new_pincode1"]) ? trim($_POST["new_pincode1"]) : "";
    $new_pincode2 = isset($_POST["new_pincode2"]) ? trim($_POST["new_pincode2"]) : "";
    if ($current_pincode && $new_pincode1 && $new_pincode2) {
        if ($new_pincode1 === $new_pincode2) {
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT pincode, sechash FROM pincode WHERE `area` = 2");
            if (mysqli_num_rows($query)) {
                $Pincode = mysqli_fetch_assoc($query);
                if ($Pincode["pincode"] === md5(md5($Pincode["sechash"]) . md5($current_pincode))) {
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM pincode WHERE `area` = 2");
                    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
                    $Result = mysqli_fetch_assoc($query);
                    $MAIN = unserialize($Result["content"]);
                    $sechash = md5($MAIN["SITENAME"]);
                    $pincode = md5(md5($sechash) . md5($new_pincode1));
                    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO pincode (pincode, sechash, area) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $pincode) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $sechash) . "', 2)");
                    if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                        logStaffAction(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[12]));
                        $Message = showAlertError($Language[10]);
                    } else {
                        $Message = showAlertError($Language[11]);
                    }
                } else {
                    $Message = showAlertError($Language[8]);
                }
            }
        } else {
            $Message = showAlertError($Language[9]);
        }
    } else {
        $Message = showAlertError($Language[3]);
    }
}
echo "\r\n\r\n" . $Message . "\r\n<form $method = \"post\" $action = \"index.php?do=setup_pincode\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Language[4] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"password\" $name = \"current_pincode\" $value = \"\" $size = \"35\" $autocomplete = \"off\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Language[5] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"password\" $name = \"new_pincode1\" $value = \"\" $size = \"35\" $autocomplete = \"off\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Language[6] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"password\" $name = \"new_pincode2\" $value = \"\" $size = \"35\" $autocomplete = \"off\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[7] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>";
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

?>