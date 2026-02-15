<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/advertising.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
$Ads = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_cache` WHERE $cachename = 'ads'");
if (mysqli_num_rows($query)) {
    $Result = mysqli_fetch_assoc($query);
    $Ads = $Result["content"];
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $Ads = trim($_POST["ads"]);
    mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO `ts_cache` VALUES ('ads', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Ads) . "', " . time() . ")");
    $Message = str_replace(["{1}", "{2}"], ["Ads", $_SESSION["ADMIN_USERNAME"]], $Language[3]);
    logStaffAction($Message);
    $Message = showAlertError($Message);
}
echo loadTinyMCEEditor(1, "exact", "ads") . "\r\n\r\n" . $Message . "\r\n<form $method = \"post\" $action = \"index.php?do=advertising\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . $Language[6] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<textarea $name = \"ads\" $id = \"ads\" $style = \"width: 99%; height: 300px;\">" . $Ads . "</textarea>\r\n\t\t\t<p><a $href = \"javascript:toggleEditor('ads');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p>\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" $align = \"center\"><input $type = \"submit\" $value = \"" . $Language[4] . "\" /> <input $type = \"reset\" $value = \"" . $Language[5] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>";
function loadTinyMCEEditor($type = 1, $mode = "textareas", $elements = "")
{
    define("EDITOR_TYPE", $type);
    define("TINYMCE_MODE", $mode);
    define("TINYMCE_ELEMENTS", $elements);
    define("WORKPATH", "./../scripts/");
    define("TINYMCE_EMOTIONS_URL", "./../tinymce_emotions.php");
    ob_start();
    include "./../tinymce.php";
    $editorContent = ob_get_contents();
    ob_end_clean();
    return $editorContent;
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