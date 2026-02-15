<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/ranks.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
if ($Act == "delete" && ($rid = intval($_GET["rid"]))) {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_ranks WHERE $rid = " . $rid);
    $Message = str_replace(["{1}", "{2}"], [$rid, $_SESSION["ADMIN_USERNAME"]], $Language[3]);
    logStaffAction($Message);
    $Message = showAlertError($Message);
}
if ($Act == "edit" && ($rid = intval($_GET["rid"]))) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_ranks WHERE $rid = " . $rid);
    if (mysqli_num_rows($query)) {
        $rank = mysqli_fetch_assoc($query);
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $usergroup = intval($_POST["usergroup"]);
            $minposts = intval($_POST["minposts"]);
            $displaytype = intval($_POST["displaytype"]);
            $image = trim($_POST["image"]);
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_ranks SET $image = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $image) . "', $displaytype = " . $displaytype . ", $minposts = " . $minposts . ", $usergroup = " . $usergroup . " WHERE $rid = " . $rid);
            $Message = str_replace(["{1}", "{2}"], [$rid, $_SESSION["ADMIN_USERNAME"]], $Language[4]);
            logStaffAction($Message);
            $Message = showAlertError($Message);
            $Updated = true;
        } else {
            $usergroup = $rank["usergroup"];
            $minposts = $rank["minposts"];
            $displaytype = $rank["displaytype"];
            $image = $rank["image"];
        }
        if (!isset($Updated)) {
            $List = "\r\n\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=ranks&amp;$act = edit&amp;$rid = " . $rid . "\">\r\n\t\t\t" . showAlertMessage("<a $href = \"index.php?do=ranks\">" . $Language[5] . "</a>") . "\r\n\t\t\t" . $Message . "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"3\">\r\n\t\t\t\t\t\t" . $Language[6] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[7] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $Language[8] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t" . function_109($usergroup, "usergroup") . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[9] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $Language[10] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"minposts\" $value = \"" . $minposts . "\" $size = \"10\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[11] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $Language[12] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<select $name = \"displaytype\">\r\n\t\t\t\t\t\t\t<option $value = \"1\"" . ($displaytype == 1 ? " $selected = \"selected\"" : "") . ">" . $Language[18] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"2\"" . ($displaytype == 2 ? " $selected = \"selected\"" : "") . ">" . $Language[19] . "</option>\r\n\t\t\t\t\t\t</select>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[13] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t\t" . $Language[14] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"image\" $value = \"" . $image . "\" $size = \"40\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[20] . "\" /> <input $type = \"reset\" $value = \"" . $Language[21] . "\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t</table\r\n\t\t\t</form>\r\n\t\t\t";
        } else {
            unset($List);
        }
    }
}
if ($Act == "new") {
    $usergroup = 0;
    $minposts = 0;
    $displaytype = 1;
    $image = "";
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $usergroup = intval($_POST["usergroup"]);
        $minposts = intval($_POST["minposts"]);
        $displaytype = intval($_POST["displaytype"]);
        $image = trim($_POST["image"]);
        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_ranks (image, displaytype, minposts, usergroup) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $image) . "', " . $displaytype . ", " . $minposts . ", " . $usergroup . ")");
        $Message = str_replace(["{1}", "{2}"], [mysqli_insert_id($GLOBALS["DatabaseConnect"]), $_SESSION["ADMIN_USERNAME"]], $Language[22]);
        logStaffAction($Message);
        $Message = showAlertError($Message);
        $Updated = true;
    }
    if (!isset($Updated)) {
        $List = "\r\n\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=ranks&amp;$act = new\">\r\n\t\t" . showAlertMessage("<a $href = \"index.php?do=ranks\">" . $Language[5] . "</a>") . "\r\n\t\t" . $Message . "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"3\">\r\n\t\t\t\t\t" . $Language[23] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[7] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[8] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t" . function_109($usergroup, "usergroup") . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[9] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[10] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"minposts\" $value = \"" . $minposts . "\" $size = \"10\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[11] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[12] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<select $name = \"displaytype\">\r\n\t\t\t\t\t\t<option $value = \"1\"" . ($displaytype == 1 ? " $selected = \"selected\"" : "") . ">" . $Language[18] . "</option>\r\n\t\t\t\t\t\t<option $value = \"2\"" . ($displaytype == 2 ? " $selected = \"selected\"" : "") . ">" . $Language[19] . "</option>\r\n\t\t\t\t\t</select>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[13] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[14] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"image\" $value = \"" . $image . "\" $size = \"40\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[20] . "\" /> <input $type = \"reset\" $value = \"" . $Language[21] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\r\n\t\t</table\r\n\t\t</form>\r\n\t\t";
    } else {
        unset($List);
    }
}
if (!isset($List)) {
    $List = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"5\">\r\n\t\t\t\t" . $Language[28] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[13] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[7] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[9] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[11] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<b>" . $Language[26] . "</b>\r\n\t\t\t</td>\r\n\t\t</tr>";
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_ranks");
    if (mysqli_num_rows($query)) {
        while ($rank = mysqli_fetch_assoc($query)) {
            $List .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\"><img $src = \"../" . $rank["image"] . "\" $border = \"0\" $alt = \"\" $title = \"\" /></td>\r\n\t\t\t\t\t<td class=\"alt1\">" . function_110($rank["usergroup"]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . number_format($rank["minposts"]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . ($rank["displaytype"] == 1 ? $Language[18] : $Language[19]) . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><a $href = \"index.php?do=ranks&amp;$act = edit&amp;$rid = " . $rank["rid"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[6]) . "\" $title = \"" . trim($Language[6]) . "\" $border = \"0\" /></a> <a $href = \"#\" $onclick = \"ConfirmDelete(" . $rank["rid"] . ");\"><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[27]) . "\" $title = \"" . trim($Language[27]) . "\" $border = \"0\" /></a></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
        }
    }
    $List .= "\r\n\t</table>";
    echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction ConfirmDelete(rID)\r\n\t\t{\r\n\t\t\tif (confirm(\"" . trim($Language[24]) . "\"))\r\n\t\t\t{\r\n\t\t\t\tTSJump(\"index.php?do=ranks&$act = delete&$rid = \"+rID);\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t" . showAlertMessage("<a $href = \"index.php?do=ranks&amp;$act = new\">" . $Language[23] . "</a>") . "\r\n\t" . $Message . $List;
} else {
    echo $List;
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
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function function_109($ug, $name)
{
    global $Language;
    $settingOptions = "\r\n\t<select $name = \"" . $name . "\">\r\n\t\t<option $value = \"0\"" . (0 == $ug ? " $selected = \"selected\"" : "") . ">" . $Language[17] . "</option>";
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, namestyle FROM usergroups");
    while ($usergroup = mysqli_fetch_assoc($query)) {
        $settingOptions .= "\r\n\t\t<option $value = \"" . $usergroup["gid"] . "\"" . ($usergroup["gid"] == $ug ? " $selected = \"selected\"" : "") . "\">" . str_replace("{username}", $usergroup["title"], $usergroup["namestyle"]) . "</option>";
    }
    $settingOptions .= "\r\n\t</select>";
    return $settingOptions;
}
function function_110($ug)
{
    global $Language;
    if ($ug == 0) {
        return "<b><i>" . $Language[17] . "</i></b>";
    }
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT title, namestyle FROM usergroups WHERE $gid = " . $ug);
    $usergroup = mysqli_fetch_row($query);
    return str_replace("{username}", $usergroup[0], $usergroup[1]);
}

?>