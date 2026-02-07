<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . function_75() . "/advertising.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
$Ads = "";
$Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_cache` WHERE $cachename = 'ads'");
if (mysqli_num_rows($Q)) {
    $Result = mysqli_fetch_assoc($Q);
    $Ads = $Result["content"];
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $Ads = trim($_POST["ads"]);
    mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO `ts_cache` VALUES ('ads', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Ads) . "', " . time() . ")");
    $Message = str_replace(["{1}", "{2}"], ["Ads", $_SESSION["ADMIN_USERNAME"]], $Language[3]);
    function_79($Message);
    $Message = function_76($Message);
}
echo function_90(1, "exact", "ads") . "\r\n\r\n" . $Message . "\r\n<form $method = \"post\" $action = \"index.php?do=advertising\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . $Language[6] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<textarea $name = \"ads\" $id = \"ads\" $style = \"width: 99%; height: 300px;\">" . $Ads . "</textarea>\r\n\t\t\t<p><a $href = \"javascript:toggleEditor('ads');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p>\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" $align = \"center\"><input $type = \"submit\" $value = \"" . $Language[4] . "\" /> <input $type = \"reset\" $value = \"" . $Language[5] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>";
function function_90($type = 1, $mode = "textareas", $elements = "")
{
    define("EDITOR_TYPE", $type);
    define("TINYMCE_MODE", $mode);
    define("TINYMCE_ELEMENTS", $elements);
    define("WORKPATH", "./../scripts/");
    define("TINYMCE_EMOTIONS_URL", "./../tinymce_emotions.php");
    ob_start();
    include "./../tinymce.php";
    $var_81 = ob_get_contents();
    ob_end_clean();
    return $var_81;
}
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
function function_78($url, $timeout = false)
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
function function_76($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function function_79($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}

?>