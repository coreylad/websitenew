<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/manage_events.lang");
$Message = "";
$Found = "";
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$id = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : 0);
$months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
if ($Act == "delete" && $id) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT title FROM ts_events WHERE $id = '" . $id . "'");
    if (mysqli_num_rows($query)) {
        $Events = mysqli_fetch_assoc($query);
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_events WHERE $id = '" . $id . "'");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $Message = str_replace(["{1}", "{2}"], [$Events["title"], $_SESSION["ADMIN_USERNAME"]], $Language[16]);
            logStaffAction($Message);
            $Message = showAlertMessage($Message);
        }
    }
}
if ($Act == "edit" && $id) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_events WHERE $id = '" . $id . "'");
    if (mysqli_num_rows($query)) {
        $Events = mysqli_fetch_assoc($query);
        $title = $Events["title"];
        $event = $Events["event"];
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
            $event = isset($_POST["event"]) ? trim($_POST["event"]) : "";
            $date = htmlspecialchars($_POST["month"]) . "-" . intval($_POST["day"]) . "-" . intval($_POST["year"]);
            if ($title && $event && $date) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_events SET $title = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', $event = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $event) . "', $date = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $date) . "' WHERE $id = '" . $id . "'");
                $Message = str_replace(["{1}", "{2}"], [$Events["title"], $_SESSION["ADMIN_USERNAME"]], $Language[17]);
                logStaffAction($Message);
                $Message = showAlertMessage($Message);
                $Done = true;
            } else {
                $Message = showAlertError($Language[19]);
            }
        }
        if (!isset($Done)) {
            $_date = explode("-", $Events["date"]);
            $showmonths = "\r\n\t\t\t\t<select $name = \"month\">";
            foreach ($months as $_m) {
                $showmonths .= "\r\n\t\t\t\t\t<option $value = \"" . $_m . "\"" . (isset($_POST["month"]) && $_POST["month"] == $_m || $_m == $_date[0] ? " $selected = \"selected\"" : "") . ">" . $_m . "</option>";
            }
            $showmonths .= "\r\n\t\t\t\t</select>";
            echo "\t\t\r\n\t\t\t" . $Message . "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_events&$act = edit&$id = " . $id . "\">\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[2] . " - " . $Language[8] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[4] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"title\" $value = \"" . htmlspecialchars($title) . "\" $style = \"width: 99%;\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[5] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><textarea $name = \"event\" $style = \"width: 99%; height: 100px;\">" . htmlspecialchars($event) . "</textarea></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[3] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[10] . " " . $showmonths . " " . $Language[11] . " <input $type = \"text\" $name = \"day\" $size = \"2\" $value = \"" . htmlspecialchars(isset($_POST["day"]) ? $_POST["day"] : $_date[1]) . "\" /> " . $Language[12] . " <input $type = \"text\" $name = \"year\" $size = \"4\" $value = \"" . htmlspecialchars(isset($_POST["year"]) ? $_POST["year"] : $_date[2]) . "\"></td>\r\n\t\t\t\t</tr>\t\t\t\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[13] . "\" /> <input $type = \"reset\" $value = \"" . $Language[14] . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
        }
    }
}
if ($Act == "new") {
    $title = "";
    $event = "";
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
        $event = isset($_POST["event"]) ? trim($_POST["event"]) : "";
        $date = htmlspecialchars($_POST["month"]) . "-" . intval($_POST["day"]) . "-" . intval($_POST["year"]);
        if ($title && $event && $date) {
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_events (title, event, date) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $event) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $date) . "')");
            $Message = str_replace(["{1}", "{2}"], [$title, $_SESSION["ADMIN_USERNAME"]], $Language[18]);
            logStaffAction($Message);
            $Message = showAlertMessage($Message);
            $Done = true;
        } else {
            $Message = showAlertError($Language[19]);
        }
    }
    if (!isset($Done)) {
        $showmonths = "\r\n\t\t\t<select $name = \"month\">";
        foreach ($months as $_m) {
            $showmonths .= "\r\n\t\t\t\t<option $value = \"" . $_m . "\"" . (isset($_POST["month"]) && $_POST["month"] == $_m ? " $selected = \"selected\"" : "") . ">" . $_m . "</option>";
        }
        $showmonths .= "\r\n\t\t\t</select>";
        echo "\t\t\r\n\t\t\r\n\t\t" . $Message . "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=manage_events&$act = new&$id = " . $id . "\">\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t" . $Language[2] . " - " . $Language[7] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[4] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"title\" $value = \"" . htmlspecialchars($title) . "\" $style = \"width: 99%;\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[5] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><textarea $name = \"event\" $style = \"width: 99%; height: 100px;\">" . htmlspecialchars($event) . "</textarea></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[3] . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[10] . " " . $showmonths . " " . $Language[11] . " <input $type = \"text\" $name = \"day\" $size = \"2\" $value = \"" . htmlspecialchars(isset($_POST["day"]) ? $_POST["day"] : "") . "\" /> " . $Language[12] . " <input $type = \"text\" $name = \"year\" $size = \"4\" $value = \"" . htmlspecialchars(isset($_POST["year"]) ? $_POST["year"] : "") . "\"></td>\r\n\t\t\t</tr>\t\t\t\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[13] . "\" /> <input $type = \"reset\" $value = \"" . $Language[14] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_events ORDER BY date DESC");
if (mysqli_num_rows($query) == 0) {
    $Found .= "<tr><td $colspan = \"4\" class=\"alt1\">" . str_replace("{1}", "index.php?do=manage_events&amp;$act = new", $Language[15]) . "</td></tr>";
} else {
    while ($Events = mysqli_fetch_assoc($query)) {
        $Found .= "\r\n\t\t<tr>\t\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($Events["date"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($Events["title"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . htmlspecialchars($Events["event"]) . "\r\n\t\t\t</td>\t\t\t\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<a $href = \"index.php?do=manage_events&amp;$act = edit&amp;$id = " . $Events["id"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . $Language[8] . "\" $title = \"" . $Language[8] . "\" $border = \"0\" /></a> <a $href = \"index.php?do=manage_events&amp;$act = delete&amp;$id = " . $Events["id"] . "\"><img $src = \"images/tool_delete.png\" $alt = \"" . $Language[9] . "\" $title = \"" . $Language[9] . "\" $border = \"0\" /></a> \r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
}
echo showAlertMessage("<a $href = \"index.php?do=manage_events&amp;$act = new\">" . $Language[7] . "</a>") . "\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"4\">" . $Language[2] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[3] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[4] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[5] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . $Language[6] . "\r\n\t\t</td>\t\t\r\n\t</tr>\r\n\t" . $Found . "\r\n</table>";
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
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>