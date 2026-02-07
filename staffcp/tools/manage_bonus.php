<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . function_75() . "/manage_bonus.lang");
$Message = "";
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$id = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : 0);
if ($Act == "delete" && $id) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT bonusname FROM bonus WHERE id = '" . $id . "'");
    if (mysqli_num_rows($query)) {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM bonus WHERE id = '" . $id . "'");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $Result = mysqli_fetch_assoc($query);
            $bonusname = $Result["bonusname"];
            $Message = str_replace(["{1}", "{2}"], [$bonusname, $_SESSION["ADMIN_USERNAME"]], $Language[11]);
            function_79($Message);
            $Message = function_81($Message);
        }
    }
}
if ($Act == "new") {
    $bonusname = "";
    $points = "";
    $description = "";
    $art = "";
    $menge = "";
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $bonusname = trim($_POST["bonusname"]);
        $points = trim($_POST["points"]);
        $description = trim($_POST["description"]);
        $art = trim($_POST["art"]);
        $menge = trim($_POST["menge"]);
        if ($bonusname && $points && $description && $art && $menge) {
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO bonus (bonusname, points, description, art, menge) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $bonusname) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $points) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $art) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $menge) . "')");
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                $Message = str_replace(["{1}", "{2}"], [$bonusname, $_SESSION["ADMIN_USERNAME"]], $Language[13]);
                function_79($Message);
                $Message = function_81($Message);
                $Done = true;
            }
        } else {
            $Message = function_76($Language[3]);
        }
    }
    if (!isset($Done)) {
        echo "\r\n\t\t" . $Message . "\r\n\t\t<form method=\"post\" action=\"index.php?do=manage_bonus&act=new\">\r\n\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\r\n\t\t\t\t\t" . $Language[2] . " - " . $Language[6] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[14] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"bonusname\" value=\"" . htmlspecialchars($bonusname) . "\" size=\"99\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" valign=\"top\">" . $Language[18] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><textarea name=\"description\" style=\"width: 100%; height: 100px;\">" . htmlspecialchars($description) . "</textarea></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[15] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"points\" value=\"" . htmlspecialchars($points) . "\" size=\"30\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[16] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"menge\" value=\"" . htmlspecialchars($menge) . "\" size=\"30\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[17] . "</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<select name=\"art\">\r\n\t\t\t\t\t\t<option value=\"traffic\"" . ($art == "traffic" ? " selected=\"selected\"" : "") . ">" . $Language[19] . "</option>\r\n\t\t\t\t\t\t<option value=\"invite\"" . ($art == "invite" ? " selected=\"selected\"" : "") . ">" . $Language[20] . "</option>\r\n\t\t\t\t\t\t<option value=\"title\"" . ($art == "title" ? " selected=\"selected\"" : "") . ">" . $Language[21] . "</option>\r\n\t\t\t\t\t\t<option value=\"class\"" . ($art == "class" ? " selected=\"selected\"" : "") . ">" . $Language[22] . "</option>\r\n\t\t\t\t\t\t<option value=\"gift_1\"" . ($art == "gift_1" ? " selected=\"selected\"" : "") . ">" . $Language[23] . "</option>\r\n\t\t\t\t\t\t<option value=\"warning\"" . ($art == "warning" ? " selected=\"selected\"" : "") . ">" . $Language[24] . "</option>\r\n\t\t\t\t\t\t<option value=\"ratiofix\"" . ($art == "ratiofix" ? " selected=\"selected\"" : "") . ">" . $Language[25] . "</option>\r\n\t\t\t\t\t</select>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\t\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . $Language[7] . "\" /> <input type=\"reset\" value=\"" . $Language[8] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
if ($Act == "edit" && $id) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM bonus WHERE id = '" . $id . "'");
    if (mysqli_num_rows($query)) {
        $Bonus = mysqli_fetch_assoc($query);
        $bonusname = $Bonus["bonusname"];
        $points = $Bonus["points"];
        $description = $Bonus["description"];
        $art = $Bonus["art"];
        $menge = $Bonus["menge"];
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $bonusname = trim($_POST["bonusname"]);
            $points = trim($_POST["points"]);
            $description = trim($_POST["description"]);
            $art = trim($_POST["art"]);
            $menge = trim($_POST["menge"]);
            if ($bonusname && $points && $description && $art && $menge) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE bonus SET bonusname = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $bonusname) . "', points = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $points) . "', description = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "', art = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $art) . "', menge = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $menge) . "' WHERE id = '" . $id . "'");
                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                    $Message = str_replace(["{1}", "{2}"], [$bonusname, $_SESSION["ADMIN_USERNAME"]], $Language[12]);
                    function_79($Message);
                    $Message = function_81($Message);
                    $Done = true;
                }
            } else {
                $Message = function_76($Language[3]);
            }
        }
        if (!isset($Done)) {
            echo "\r\n\t\t\t<form method=\"post\" action=\"index.php?do=manage_bonus&act=edit&id=" . $id . "\">\r\n\t\t\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\r\n\t\t\t\t\t\t" . $Language[2] . " - " . $Language[4] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[14] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"bonusname\" value=\"" . htmlspecialchars($bonusname) . "\" size=\"99\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" valign=\"top\">" . $Language[18] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><textarea name=\"description\" style=\"width: 100%; height: 100px;\">" . htmlspecialchars($description) . "</textarea></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[15] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"points\" value=\"" . htmlspecialchars($points) . "\" size=\"30\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[16] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input type=\"text\" name=\"menge\" value=\"" . htmlspecialchars($menge) . "\" size=\"30\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[17] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t<select name=\"art\">\r\n\t\t\t\t\t\t\t<option value=\"traffic\"" . ($art == "traffic" ? " selected=\"selected\"" : "") . ">" . $Language[19] . "</option>\r\n\t\t\t\t\t\t\t<option value=\"invite\"" . ($art == "invite" ? " selected=\"selected\"" : "") . ">" . $Language[20] . "</option>\r\n\t\t\t\t\t\t\t<option value=\"title\"" . ($art == "title" ? " selected=\"selected\"" : "") . ">" . $Language[21] . "</option>\r\n\t\t\t\t\t\t\t<option value=\"class\"" . ($art == "class" ? " selected=\"selected\"" : "") . ">" . $Language[22] . "</option>\r\n\t\t\t\t\t\t\t<option value=\"gift_1\"" . ($art == "gift_1" ? " selected=\"selected\"" : "") . ">" . $Language[23] . "</option>\r\n\t\t\t\t\t\t\t<option value=\"warning\"" . ($art == "warning" ? " selected=\"selected\"" : "") . ">" . $Language[24] . "</option>\r\n\t\t\t\t\t\t\t<option value=\"ratiofix\"" . ($art == "ratiofix" ? " selected=\"selected\"" : "") . ">" . $Language[25] . "</option>\r\n\t\t\t\t\t\t</select>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\t\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . $Language[7] . "\" /> <input type=\"reset\" value=\"" . $Language[8] . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
        }
    }
}
$Found = "";
$Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM bonus ORDER by bonusname ASC");
if (0 < mysqli_num_rows($Query)) {
    while ($Bonus = mysqli_fetch_assoc($Query)) {
        $Found .= "\r\n\t\t<tr>\t\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($Bonus["bonusname"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($Bonus["points"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($Bonus["art"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . ($Bonus["art"] == "traffic" || $Bonus["art"] == "gift_1" ? var_238($Bonus["menge"]) : number_format($Bonus["menge"])) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . substr($Bonus["description"], 0, 100) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" align=\"center\">\r\n\t\t\t\t<a href=\"index.php?do=manage_bonus&amp;act=edit&amp;id=" . $Bonus["id"] . "\"><img src=\"images/tool_edit.png\" alt=\"" . $Language[4] . "\" title=\"" . $Language[4] . "\" border=\"0\" /></a> <a href=\"index.php?do=manage_bonus&amp;act=delete&amp;id=" . $Bonus["id"] . "\"><img src=\"images/tool_delete.png\" alt=\"" . $Language[5] . "\" title=\"" . $Language[5] . "\" border=\"0\" /></a>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
} else {
    $Found .= "<tr><td colspan=\"6\" class=\"alt1\">" . str_replace("{1}", "index.php?do=manage_bonus&amp;act=new", $Language[10]) . "</td></tr>";
}
echo function_81("<a href=\"index.php?do=manage_bonus&amp;act=new\">" . $Language[6] . "</a>") . "\r\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" align=\"center\" colspan=\"6\">" . $Language[2] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[14] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[15] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[17] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[16] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[18] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" align=\"center\">\r\n\t\t\t" . $Language[26] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $Found . "\r\n</table>";
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
function function_81($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function function_79($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_88($bytes = 0)
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

?>