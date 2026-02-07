<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/new_tool.lang");
$Message = "";
$category = isset($_GET["cid"]) ? intval($_GET["cid"]) : 0;
$toolname = isset($_GET["toolname"]) ? trim($_GET["toolname"]) : "";
$filename = isset($_GET["filename"]) ? trim($_GET["filename"]) : "";
$usergroups = [];
$sort = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $category = intval($_POST["cid"]);
    $toolname = trim($_POST["toolname"]);
    $filename = trim($_POST["filename"]);
    $usergroups = isset($_POST["usergroups"]) ? $_POST["usergroups"] : [];
    $sort = intval($_POST["sort"]);
    if ($category && $toolname && $filename && count($usergroups) && is_array($usergroups)) {
        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_tools (cid, toolname, filename, usergroups, sort) VALUES ('" . $category . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $toolname) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $filename) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], implode(",", $usergroups)) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $sort) . "')");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $SysMsg = str_replace(["{1}", "{2}"], [$toolname, $_SESSION["ADMIN_USERNAME"]], $Language[3]);
            logStaffAction($SysMsg);
            redirectTo("index.php?do=manage_tools");
            exit;
        }
        $Message = showAlertError($Language[12]);
    } else {
        $Message = showAlertError($Language[11]);
    }
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
$LoggedAdminDetails = mysqli_fetch_assoc($query);
$showusergroups = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups ORDER by disporder ASC");
while ($UG = mysqli_fetch_assoc($query)) {
    if (!($UG["cansettingspanel"] == "yes" && $LoggedAdminDetails["cansettingspanel"] != "yes" || $UG["canstaffpanel"] == "yes" && $LoggedAdminDetails["canstaffpanel"] != "yes" || $UG["issupermod"] == "yes" && $LoggedAdminDetails["issupermod"] != "yes")) {
        $showusergroups .= "\r\n\t\t<div $style = \"margin-bottom: 3px;\">\r\n\t\t\t<label><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"" . $UG["gid"] . "\"" . (in_array($UG["gid"], $usergroups) || $UG["cansettingspanel"] == "yes" ? " $checked = \"checked\"" : "") . " $style = \"vertical-align: middle;\" /> " . strip_tags(str_replace("{username}", $UG["title"], $UG["namestyle"]), "<b><span><strong><em><i><u>") . "</label>\r\n\t\t</div>";
    }
}
$showcategories = "<select $name = \"cid\">";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT cid, name FROM ts_staffcp ORDER by sort ASC");
while ($cats = mysqli_fetch_assoc($query)) {
    $showcategories .= "<option $value = \"" . $cats["cid"] . "\"" . ($category == $cats["cid"] ? " $selected = \"selected\"" : (isset($_GET["cid"]) && $_GET["cid"] == $cats["cid"] ? " $selected = \"selected\"" : "")) . ">" . htmlspecialchars($cats["name"]) . "</option>";
}
$showcategories .= "</select>";
echo "\r\n" . $Message . "\r\n<form $method = \"post\" $action = \"index.php?do=new_tool\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"tborder\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Language[5] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"toolname\" $value = \"" . htmlspecialchars($toolname) . "\" $size = \"40\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Language[6] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"filename\" $value = \"" . htmlspecialchars($filename) . "\" $size = \"40\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Language[7] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"sort\" $value = \"" . intval($sort) . "\" $size = \"40\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Language[13] . "</td>\r\n\t\t<td class=\"alt1\">" . $showcategories . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[8] . "</td>\r\n\t\t<td class=\"alt1\">" . $showusergroups . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[9] . "\" /> <input $type = \"reset\" $value = \"" . $Language[10] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>";
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
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}

?>