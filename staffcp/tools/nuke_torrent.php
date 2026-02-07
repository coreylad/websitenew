<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . function_75() . "/nuke_torrent.lang");
$Message = "";
$tid = isset($_GET["tid"]) ? intval($_GET["tid"]) : (isset($_POST["tid"]) ? intval($_POST["tid"]) : "");
$reason = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $tid) {
    $reason = trim($_POST["reason"]);
    if ($reason) {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name FROM torrents WHERE id = '" . $tid . "'");
        if (0 < mysqli_num_rows($query)) {
            $Result = mysqli_fetch_assoc($query);
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE torrents SET isnuked = 'yes', WhyNuked = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], htmlspecialchars($reason)) . "' WHERE id = '" . $tid . "'");
            $SysMsg = str_replace(["{1}", "{2}", "{3}"], [$Result["name"], $_SESSION["ADMIN_USERNAME"], $reason], $Language[7]);
            function_79($SysMsg);
            $Message = function_76($SysMsg);
        } else {
            $Message = function_76($Language[6]);
        }
    } else {
        $Message = function_76($Language[9]);
    }
}
echo "\t\t\t\t\r\n\r\n" . $Message . "\r\n<form method=\"post\" action=\"index.php?do=nuke_torrent\">\r\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Language[3] . "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" name=\"tid\" value=\"" . intval($tid) . "\" size=\"10\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\" align=\"right\">" . $Language[8] . "</td>\r\n\t\t<td class=\"alt2\"><input type=\"text\" name=\"reason\" value=\"" . htmlspecialchars($reason) . "\" size=\"50\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . $Language[4] . "\" /> <input type=\"reset\" value=\"" . $Language[5] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>";
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