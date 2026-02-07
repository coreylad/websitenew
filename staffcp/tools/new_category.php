<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . function_75() . "/new_category.lang");
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
            function_79($SysMsg);
            function_78("index.php?do=manage_tools");
            exit;
        }
        $Message = function_76($Language[9]);
    } else {
        $Message = function_76($Language[8]);
    }
}
echo "\t\t\t\t\r\n<form method=\"post\" action=\"index.php?do=new_category\">\r\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"tborder\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Language[4] . "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" name=\"name\" value=\"" . htmlspecialchars($name) . "\" size=\"40\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Language[5] . "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" name=\"sort\" value=\"" . intval($sort) . "\" size=\"40\" /></td>\r\n\t</tr>\t\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . $Language[6] . "\" /> <input type=\"reset\" value=\"" . $Language[7] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>";
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