<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/manage_countries.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Cid = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : 0);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
$Result = mysqli_fetch_assoc($query);
$MAIN = unserialize($Result["content"]);
$Message = "";
if ($Act == "delete" && $Cid) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name FROM countries WHERE `id` = '" . $Cid . "'");
    if (mysqli_num_rows($query)) {
        $Result = mysqli_fetch_assoc($query);
        $name = $Result["name"];
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM countries WHERE `id` = '" . $Cid . "'");
        $Message = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[13]);
        logStaffAction($Message);
        $Message = showAlertError($Message);
    }
}
if ($Act == "new") {
    $name = "";
    $flagpic = "";
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $name = trim($_POST["name"]);
        $flagpic = trim($_POST["flagpic"]);
        if ($name && $flagpic) {
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO countries (name, flagpic) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $name) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $flagpic) . "')");
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                $Message = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[15]);
                logStaffAction($Message);
                $Message = showAlertError($Message);
                $Done = true;
            }
        } else {
            $Message = showAlertError($Language[4]);
        }
    }
    if (!isset($Done)) {
        echo "\r\n\t\t<form $action = \"index.php?do=manage_countries&$act = new\" $method = \"post\" $name = \"manage_countries\">\r\n\t\t\r\n\t\t" . $Message . "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t" . $Language[2] . " - " . $Language[10] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[5] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"name\" $value = \"" . htmlspecialchars($name) . "\" $size = \"35\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[6] . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . function_157($flagpic) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\t\r\n\t\t\t\t\t<input $type = \"submit\" class=\"button\" $tabindex = \"1\" $value = \"" . $Language[11] . "\" $accesskey = \"s\" />\r\n\t\t\t\t\t<input $type = \"reset\" class=\"button\" $tabindex = \"1\" $value = \"" . $Language[12] . "\" $accesskey = \"r\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
if ($Act == "edit" && $Cid) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name, flagpic FROM countries WHERE `id` = '" . $Cid . "'");
    if (mysqli_num_rows($query)) {
        $Country = mysqli_fetch_assoc($query);
        $name = $Country["name"];
        $flagpic = $Country["flagpic"];
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $name = trim($_POST["name"]);
            $flagpic = trim($_POST["flagpic"]);
            if ($name && $flagpic) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE countries SET $name = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $name) . "', $flagpic = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $flagpic) . "' WHERE `id` = '" . $Cid . "'");
                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                    $Message = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[14]);
                    logStaffAction($Message);
                    $Message = showAlertError($Message);
                    $Done = true;
                }
            } else {
                $Message = showAlertError($Language[4]);
            }
        }
        if (!isset($Done)) {
            echo "\r\n\t\t\t<form $action = \"index.php?do=manage_countries&$act = edit&$id = " . $Cid . "\" $method = \"post\" $name = \"manage_countries\">\r\n\t\t\t\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[2] . " - " . $Language[8] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[5] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"name\" $value = \"" . htmlspecialchars($name) . "\" $size = \"35\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[6] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . function_157($flagpic) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\">\t\r\n\t\t\t\t\t\t<input $type = \"submit\" class=\"button\" $tabindex = \"1\" $value = \"" . $Language[11] . "\" $accesskey = \"s\" />\r\n\t\t\t\t\t\t<input $type = \"reset\" class=\"button\" $tabindex = \"1\" $value = \"" . $Language[12] . "\" $accesskey = \"r\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
        }
    }
}
$Found = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM countries ORDER by name ASC");
if (0 < mysqli_num_rows($query)) {
    while ($Country = mysqli_fetch_assoc($query)) {
        $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">" . htmlspecialchars($Country["name"]) . "</td>\r\n\t\t\t<td class=\"alt1\"><img $src = \"" . $MAIN["pic_base_url"] . "flag/" . $Country["flagpic"] . "\" $alt = \"\" $border = \"\" /></td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\"><a $href = \"index.php?do=manage_countries&amp;$act = edit&amp;$id = " . $Country["id"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . $Language[8] . "\" $title = \"" . $Language[8] . "\" $border = \"0\" /></a> <a $href = \"index.php?do=manage_countries&amp;$act = delete&amp;$id = " . $Country["id"] . "\"><img $src = \"images/tool_delete.png\" $alt = \"" . $Language[9] . "\" $title = \"" . $Language[9] . "\" $border = \"0\" /></a></td>\r\n\t\t</tr>";
    }
} else {
    $Found .= "<tr><td $colspan = \"5\" class=\"alt1\">" . str_replace("{1}", "index.php?do=manage_countries&amp;$act = new", $Language[16]) . "</td></tr>";
}
echo showAlertMessage("<a $href = \"index.php?do=manage_countries&amp;$act = new\">" . $Language[10] . "</a>") . "\t\t\r\n\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"3\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">" . $Language[5] . "</td>\r\n\t\t<td class=\"alt2\">" . $Language[6] . "</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">" . $Language[7] . "</td>\r\n\t</tr>\r\n\t" . $Found . "\r\n</table>\r\n";
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
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_157($selected = "")
{
    global $MAIN;
    if (is_dir("../images")) {
        $countryId = "../images/flag/";
    } else {
        if (is_dir("../pic")) {
            $countryId = "../pic/flag/";
        } else {
            return "<select $name = \"flagpic\"></select>";
        }
    }
    $countryName = $MAIN["pic_base_url"] . "/flag/";
    $countryList = scandir($countryId);
    $countryFlag = "<select $name = \"flagpic\">";
    foreach ($countryList as $countryData) {
        if (in_array(checkPermission($countryData), ["png", "gif", "jpg"])) {
            $countryFlag .= "<option $value = \"" . htmlspecialchars($countryData) . "\"" . ($selected == $countryData ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars($countryData) . "</option>";
        }
    }
    $countryFlag .= "</select>";
    return $countryFlag;
}
function function_154($file)
{
    return strtolower(substr(strrchr($file, "."), 1));
}

?>