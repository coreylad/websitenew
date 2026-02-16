<?php
checkStaffAuthentication();
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Cid = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : 0);
$Language = file("languages/" . getStaffLanguage() . "/manage_rules.lang");
$Message = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
$Result = mysqli_fetch_assoc($query);
$MAIN = unserialize($Result["content"]);
if ($Act == "delete" && $Cid) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT title FROM rules WHERE `id` = '" . $Cid . "'");
    if (mysqli_num_rows($query)) {
        $Rules = mysqli_fetch_assoc($query);
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM rules WHERE `id` = '" . $Cid . "'");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $Message = str_replace(["{1}", "{2}"], [$Rules["title"], $_SESSION["ADMIN_USERNAME"]], $Language[4]);
            logStaffAction($Message);
            $Message = showAlertMessage($Message);
        }
    }
}
if ($Act == "edit" && $Cid) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM rules WHERE `id` = '" . $Cid . "'");
    if (mysqli_num_rows($query)) {
        $Rules = mysqli_fetch_assoc($query);
        $title = $Rules["title"];
        $text = $Rules["text"];
        $usergroups = explode(",", $Rules["usergroups"]);
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
            $text = isset($_POST["text"]) ? trim($_POST["text"]) : "";
            $usergroups = isset($_POST["usergroups"]) ? implode(",", $_POST["usergroups"]) : "";
            if ($title && $text) {
                $Changes[] = "`title` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "'";
                $Changes[] = "`text` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $text) . "'";
                $Changes[] = "`usergroups` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $usergroups) . "'";
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE rules SET " . implode(", ", $Changes) . " WHERE `id` = '" . $Cid . "'");
                $Message = str_replace(["{1}", "{2}"], [$Rules["title"], $_SESSION["ADMIN_USERNAME"]], $Language[5]);
                logStaffAction($Message);
                $Message = showAlertMessage($Message);
                $Done = true;
            } else {
                $Message = showAlertError($Language[9]);
            }
        }
        if (!isset($Done)) {
            echo loadTinyMCEEditor() . "\r\n\t\t\t" . $Message . "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_rules&$act = edit&$id = " . $Cid . "\">\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[2] . " - " . $Language[11] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[7] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"title\" $value = \"" . htmlspecialchars($title) . "\" $style = \"width: 99%;\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[8] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><textarea $name = \"text\" $id = \"text\" $style = \"width: 100%; height: 100px;\">" . htmlspecialchars($text) . "</textarea>\r\n\t\t\t\t\t<p><a $href = \"javascript:toggleEditor('text');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[20] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . function_148($usergroups) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[13] . "\" /> <input $type = \"reset\" $value = \"" . $Language[14] . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
        }
    }
}
if ($Act == "new") {
    $title = "";
    $text = "";
    $usergroups = [];
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
        $text = isset($_POST["text"]) ? trim($_POST["text"]) : "";
        $usergroups = isset($_POST["usergroups"]) ? implode(",", $_POST["usergroups"]) : "";
        if (!$title || !$text) {
            $Message = showAlertError($Language[9]);
        } else {
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO rules (title, text, usergroups) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $text) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $usergroups) . "')");
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                $Message = str_replace(["{1}", "{2}"], [$title, $_SESSION["ADMIN_USERNAME"]], $Language[6]);
                logStaffAction($Message);
                $Message = showAlertMessage($Message);
                $Done = true;
            }
        }
    }
    if (!isset($Done)) {
        echo loadTinyMCEEditor() . "\r\n\t\t" . $Message . "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=manage_rules&$act = new\">\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t" . $Language[2] . " - " . $Language[18] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[7] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"title\" $value = \"" . htmlspecialchars($title) . "\" $style = \"width: 99%;\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[8] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><textarea $name = \"text\" $id = \"text\" $style = \"width: 100%; height: 100px;\">" . htmlspecialchars($text) . "</textarea>\r\n\t\t\t\t<p><a $href = \"javascript:toggleEditor('text');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[20] . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . function_148($usergroups) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[13] . "\" /> <input $type = \"reset\" $value = \"" . $Language[14] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
$Found = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM rules");
if (0 < mysqli_num_rows($query)) {
    while ($Rules = mysqli_fetch_assoc($query)) {
        $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $Rules["title"] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . substr(strip_tags($Rules["text"]), 0, 150) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $Rules["usergroups"] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<a $href = \"index.php?do=manage_rules&amp;$act = edit&amp;$id = " . $Rules["id"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . $Language[11] . "\" $title = \"" . $Language[11] . "\" $border = \"0\" /></a> <a $href = \"index.php?do=manage_rules&amp;$act = delete&amp;$id = " . $Rules["id"] . "\"><img $src = \"images/tool_delete.png\" $alt = \"" . $Language[12] . "\" $title = \"" . $Language[12] . "\" $border = \"0\" /></a>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
} else {
    $Found .= "<tr><td $colspan = \"4\" class=\"alt1\">" . str_replace("{1}", "index.php?do=manage_rules&amp;$act = new", $Language[19]) . "</td></tr>";
}
echo showAlertMessage("<a $href = \"index.php?do=manage_rules&amp;$act = new\">" . $Language[18] . "</a>") . "\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"4\">" . $Language[2] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[7] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[8] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t" . $Language[20] . "\r\n\t\t</td>\r\n\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t" . $Language[17] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $Found . "\r\n</table>";
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
function function_148($selected = "")
{
    if (!is_array($selected)) {
        $selected = explode(",", $selected);
    }
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
    $currentUserPerms = mysqli_fetch_assoc($query);
    $count = 0;
    $userGroupsHtml = "\r\n\t<table>\r\n\t\t<tr>\t";
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups WHERE `isbanned` = 'no' ORDER by disporder ASC");
    while ($row = mysqli_fetch_assoc($query)) {
        if (!($row["cansettingspanel"] == "yes" && $currentUserPerms["cansettingspanel"] != "yes" || $row["canstaffpanel"] == "yes" && $currentUserPerms["canstaffpanel"] != "yes" || $row["issupermod"] == "yes" && $currentUserPerms["issupermod"] != "yes")) {
            if ($count && $count % 8 == 0) {
                $userGroupsHtml .= "</tr><tr>";
            }
            $userGroupsHtml .= "<td><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"[" . $row["gid"] . "]\"" . (is_array($selected) && count($selected) && (in_array("[" . $row["gid"] . "]", $selected) || preg_match("#\\[" . intval($row["gid"]) . "\\]#isU", implode("", $selected))) ? " $checked = \"checked\"" : "") . " /></td><td>" . str_replace("{username}", $row["title"], $row["namestyle"]) . "</td>";
            $count++;
        }
    }
    $userGroupsHtml .= "</tr></table>";
    return $userGroupsHtml;
}

?>