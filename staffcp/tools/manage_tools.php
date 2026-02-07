<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Tid = isset($_GET["tid"]) ? intval($_GET["tid"]) : (isset($_POST["tid"]) ? intval($_POST["tid"]) : 0);
$Language = file("languages/" . getStaffLanguage() . "/manage_tools.lang");
$Message = "";
$HTMLOutput = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $Act == "save_order") {
    foreach ($_POST["order"] as $_tid => $_sort) {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_staffcp_tools SET $sort = '" . intval($_sort) . "' WHERE $tid = '" . $_tid . "'");
    }
}
if ($Act && $Tid) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_staffcp_tools WHERE $tid = \"" . $Tid . "\"");
    if (mysqli_num_rows($query)) {
        $Tool = mysqli_fetch_assoc($query);
        $AllowedUsergroups = explode(",", $Tool["usergroups"]);
        if ($_SESSION["ADMIN_GID"] && $AllowedUsergroups && in_array($_SESSION["ADMIN_GID"], $AllowedUsergroups)) {
            if ($Act == "delete") {
                mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_staffcp_tools WHERE $tid = '" . $Tid . "'");
                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                    $SysMsg = str_replace(["{1}", "{2}"], [$Tool["toolname"], $_SESSION["ADMIN_USERNAME"]], $Language[6]);
                    logStaffAction($SysMsg);
                }
            }
            if ($Act == "edit") {
                if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
                    $category = intval($_POST["cid"]);
                    $toolname = trim($_POST["toolname"]);
                    $filename = trim($_POST["filename"]);
                    $usergroups = isset($_POST["usergroups"]) ? $_POST["usergroups"] : [];
                    $sort = intval($_POST["sort"]);
                    if ($category && $toolname && $filename && count($usergroups) && is_array($usergroups)) {
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_staffcp_tools SET $cid = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $category) . "', $toolname = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $toolname) . "', $filename = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $filename) . "', $usergroups = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], implode(",", $usergroups)) . "', $sort = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $sort) . "' WHERE $tid = '" . $Tid . "'");
                        $SysMsg = str_replace(["{1}", "{2}"], [$Tool["toolname"], $_SESSION["ADMIN_USERNAME"]], $Language[7]);
                        logStaffAction($SysMsg);
                        redirectTo("index.php?do=manage_tools");
                        exit;
                    }
                    $Message = showAlertError($Language[18]);
                }
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
                $LoggedAdminDetails = mysqli_fetch_assoc($query);
                $showusergroups = "";
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups ORDER by disporder ASC");
                while ($UG = mysqli_fetch_assoc($query)) {
                    if (!($UG["cansettingspanel"] == "yes" && $LoggedAdminDetails["cansettingspanel"] != "yes" || $UG["canstaffpanel"] == "yes" && $LoggedAdminDetails["canstaffpanel"] != "yes" || $UG["issupermod"] == "yes" && $LoggedAdminDetails["issupermod"] != "yes")) {
                        $showusergroups .= "\r\n\t\t\t\t\t\t<div $style = \"margin-bottom: 3px;\">\r\n\t\t\t\t\t\t\t<label><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"" . $UG["gid"] . "\"" . (in_array($UG["gid"], $AllowedUsergroups) ? " $checked = \"checked\"" : "") . " $style = \"vertical-align: middle;\" /> " . strip_tags(str_replace("{username}", $UG["title"], $UG["namestyle"]), "<b><span><strong><em><i><u>") . "</label>\r\n\t\t\t\t\t\t</div>";
                    }
                }
                $showcategories = "<select $name = \"cid\">";
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT cid, name FROM ts_staffcp ORDER by sort ASC");
                while ($cats = mysqli_fetch_assoc($query)) {
                    $showcategories .= "<option $value = \"" . $cats["cid"] . "\"" . ($Tool["cid"] == $cats["cid"] ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars($cats["name"]) . "</option>";
                }
                $showcategories .= "</select>";
                $HTMLOutput .= "\r\n\t\t\t\t" . $Message . "\r\n\t\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_tools&$act = edit&$tid = " . $Tid . "\">\r\n\t\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t\t\t" . $Language[3] . " - " . $Language[4] . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\">" . $Language[12] . "</td>\r\n\t\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"toolname\" $value = \"" . htmlspecialchars($Tool["toolname"]) . "\" $size = \"40\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\">" . $Language[13] . "</td>\r\n\t\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"filename\" $value = \"" . htmlspecialchars($Tool["filename"]) . "\" $size = \"40\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\">" . $Language[15] . "</td>\r\n\t\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"sort\" $value = \"" . intval($Tool["sort"]) . "\" $size = \"40\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\">" . $Language[23] . "</td>\r\n\t\t\t\t\t\t<td class=\"alt1\">" . $showcategories . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[14] . "</td>\r\n\t\t\t\t\t\t<td class=\"alt1\">" . $showusergroups . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[16] . "\" /> <input $type = \"reset\" $value = \"" . $Language[17] . "\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t\t</form>";
            }
        }
    }
}
if (!$HTMLOutput) {
    $StaffTools = [];
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT tid, cid, toolname, filename, usergroups, sort FROM ts_staffcp_tools ORDER by sort, toolname ASC");
    while ($Tools = mysqli_fetch_assoc($query)) {
        $AllowedUsergroups = explode(",", $Tools["usergroups"]);
        if ($_SESSION["ADMIN_GID"] && in_array($_SESSION["ADMIN_GID"], $AllowedUsergroups)) {
            $StaffTools[$Tools["cid"]][] = "\r\n\t\t\t<table>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $width = \"1%\">\r\n\t\t\t\t\t\t<a $href = \"index.php?do=manage_tools&amp;$act = edit&amp;$tid = " . $Tools["tid"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[4]) . "\" $title = \"" . trim($Language[4]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td $width = \"1%\">\r\n\t\t\t\t\t\t<a $href = \"index.php?do=manage_tools&amp;$act = delete&amp;$tid = " . $Tools["tid"] . "\" $onclick = \"return confirm('" . trim($Language[5]) . ": " . trim(str_replace("'", "`", $Tools["toolname"])) . "\\n\\n" . trim($Language[9]) . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[5]) . "\" $title = \"" . trim($Language[5]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td $width = \"88%\">\r\n\t\t\t\t\t\t" . trim($Tools["toolname"]) . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td $width = \"10%\" $align = \"right\">\r\n\t\t\t\t\t\t<input $type = \"text\" $size = \"5\" $value = \"" . $Tools["sort"] . "\" $name = \"order[" . $Tools["tid"] . "]\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>";
        }
    }
    $Output = [];
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT cid, name FROM ts_staffcp ORDER by sort ASC");
    while ($ST = mysqli_fetch_assoc($query)) {
        $Output[] = "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=manage_tools&$act = save_order\" $name = \"sort_order\">\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\">\r\n\t\t\t\t\t<span $style = \"float: right;\">\r\n\t\t\t\t\t\t<a $href = \"index.php?do=new_tool&amp;$cid = " . $ST["cid"] . "\"><img $src = \"images/tool_new.png\" $alt = \"" . trim($Language[24]) . "\" $title = \"" . trim($Language[24]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=manage_category&amp;$cid = " . $ST["cid"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[20]) . "\" $title = \"" . trim($Language[20]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=manage_category&amp;$act = delete&amp;$cid = " . $ST["cid"] . "\" $onclick = \"return confirm('" . trim($Language[21]) . ": " . trim($ST["name"]) . "\\n\\n" . trim($Language[22]) . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[21]) . "\" $title = \"" . trim($Language[21]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t\t\t</span>\r\n\t\t\t\t\t" . $ST["name"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . (isset($StaffTools[$ST["cid"]]) ? implode(" ", $StaffTools[$ST["cid"]]) : "&nbsp;" . $Language[1]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t" . (isset($StaffTools[$ST["cid"]]) ? "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\" $align = \"right\"><input $type = \"submit\" $value = \"" . $Language[10] . "\" /> <input $type = \"reset\" $value = \"" . $Language[11] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t" : "") . "\r\n\t\t</table>\r\n\t\t</form>";
    }
    $HTMLOutput .= "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\">" . $Language[3] . "</td>\r\n\t\t</tr>\r\n\t</table>";
    for ($i = 0; $i <= count($Output); $i++) {
        if (isset($Output[$i]) && $Output[$i] != "") {
            $HTMLOutput .= $Output[$i];
        }
    }
}
echo $HTMLOutput;
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