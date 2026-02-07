<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/edit_user.lang");
$Message = "";
$username = "";
$userid = "";
$FirstQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.* FROM users u INNER JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
exit(mysqli_error($GLOBALS["DatabaseConnect"]));
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
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')") or exit(mysqli_error($GLOBALS["DatabaseConnect"]));
}
function function_104($offset = "all")
{
    $var_313 = ["4294967284" => "timezone_gmt_minus_1200", "4294967285" => "timezone_gmt_minus_1100", "4294967286" => "timezone_gmt_minus_1000", "4294967287" => "timezone_gmt_minus_0900", "4294967288" => "timezone_gmt_minus_0800", "4294967289" => "timezone_gmt_minus_0700", "4294967290" => "timezone_gmt_minus_0600", "4294967291" => "timezone_gmt_minus_0500", "-4.5" => "timezone_gmt_minus_0430", "4294967292" => "timezone_gmt_minus_0400", "-3.5" => "timezone_gmt_minus_0330", "4294967293" => "timezone_gmt_minus_0300", "4294967294" => "timezone_gmt_minus_0200", "4294967295" => "timezone_gmt_minus_0100", "0" => "timezone_gmt_plus_0000", "1" => "timezone_gmt_plus_0100", "2" => "timezone_gmt_plus_0200", "3" => "timezone_gmt_plus_0300", "3.5" => "timezone_gmt_plus_0330", "4" => "timezone_gmt_plus_0400", "4.5" => "timezone_gmt_plus_0430", "5" => "timezone_gmt_plus_0500", "5.5" => "timezone_gmt_plus_0530", "5.75" => "timezone_gmt_plus_0545", "6" => "timezone_gmt_plus_0600", "6.5" => "timezone_gmt_plus_0630", "7" => "timezone_gmt_plus_0700", "8" => "timezone_gmt_plus_0800", "9" => "timezone_gmt_plus_0900", "9.5" => "timezone_gmt_plus_0930", "10" => "timezone_gmt_plus_1000", "11" => "timezone_gmt_plus_1100", "12" => "timezone_gmt_plus_1200"];
    return $offset == "all" ? $var_313 : $var_313[(string) $offset];
}
function function_105($tzoffset = 0, $autodst = 0, $dst = 0)
{
    $Language = file("languages/" . getStaffLanguage() . "/timezone.lang");
    $var_314 = "";
    $count = 0;
    foreach (function_104() as $var_315 => $var_316) {
        $var_314 .= "<option $value = \"" . $var_315 . "\"" . ($tzoffset == $var_315 ? " $selected = \"selected\"" : "") . ">" . $Language[$count] . "</option>";
        $count++;
    }
    $var_317 = [];
    if ($autodst) {
        $var_317[2] = " $selected = \"selected\"";
    } else {
        if ($dst) {
            $var_317[1] = " $selected = \"selected\"";
        } else {
            $var_317[0] = " $selected = \"selected\"";
        }
    }
    return "\r\n\t<div>" . $Language[34] . "</div>\r\n\t<select $name = \"tzoffset\" $id = \"sel_tzoffset\">\r\n\t\t" . $var_314 . "\r\n\t</select>\r\n\t";
}
function function_106($options, $field, $number = 0)
{
    if (!($options = strtoupper($options)) || !($field = strtolower($field))) {
        return false;
    }
    $array = ["parked" => "A1", "invisible" => "B1", "commentpm" => "C1", "avatars" => "D1", "showoffensivetorrents" => "E1", "popup" => "F1", "leftmenu" => "G1", "signatures" => "H1", "privacy" => "I" . $number, "acceptpms" => "K" . $number, "gender" => "L" . $number, "visitormsg" => "M" . $number, "autodst" => "N1", "dst" => "O1", "quickmenu" => "P1"];
    return preg_match("#" . $array[$field] . "#is", $options) ? true : false;
}
function generateSecret($length = 20)
{
    $var_308 = ["a", "A", "b", "B", "c", "C", "d", "D", "e", "E", "f", "F", "g", "G", "h", "H", "i", "I", "j", "J", "k", "K", "l", "L", "m", "M", "n", "N", "o", "O", "p", "P", "q", "Q", "r", "R", "s", "S", "t", "T", "u", "U", "v", "V", "w", "W", "x", "X", "y", "Y", "z", "Z", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
    $var_309 = "";
    for ($i = 1; $i <= $length; $i++) {
        $ch = rand(0, count($var_308) - 1);
        $var_309 .= $var_308[$ch];
    }
    return $var_309;
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
function function_107($Option)
{
    global $var_318;
    $var_304 = ["changeusername" => "0", "changepassword" => "1", "changeemail" => "2", "changeusergroup" => "3", "managedonationstatus" => "4", "managetimeoptions" => "5", "manageaccountdetails" => "6", "manageaccountpermissions" => "7", "manageaccountwarningdetails" => "8", "manageaccounthistory" => "9", "managesupportoptions" => "10", "managecontactdetails" => "11"];
    $var_319 = isset($var_304[$Option]) ? $var_304[$Option] : 0;
    return $var_318["edituserperms"][$var_319] == "1" ? true : false;
}
function function_108($str)
{
    $str = str_replace("&amp;", "&", $str);
    $str = str_replace("&apos", "'", $str);
    $str = str_replace("&#039;", "'", $str);
    $str = str_replace("&quot;", "\"", $str);
    $str = str_replace("&lt;", "<", $str);
    $str = str_replace("&gt;", ">", $str);
    return $str;
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>