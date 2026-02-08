<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/new_category.lang");
$Message = "";
$name = "";
$sort = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $name = trim($_POST["name"]);
    $sort = intval($_POST["sort"]);
    if ($name) {
        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp (name, sort) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $name) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $sort) . "')");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $SysMsg = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[3]);
            logStaffAction($SysMsg);
            redirectTo("index.php?do=manage_tools");
            exit;
        }
        $Message = showAlertError($Language[9]);
    } else {
        $Message = showAlertError($Language[8]);
    }
}
echo "\t\t\t\t\r\n<form $method = \"post\" $action = \"index.php?do=new_category\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"tborder\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Language[4] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"name\" $value = \"" . htmlspecialchars($name) . "\" $size = \"40\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Language[5] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"sort\" $value = \"" . intval($sort) . "\" $size = \"40\" /></td>\r\n\t</tr>\t\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[6] . "\" /> <input $type = \"reset\" $value = \"" . $Language[7] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>";
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