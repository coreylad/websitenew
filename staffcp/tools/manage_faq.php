<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/manage_faq.lang");
$Message = "";
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$id = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : 0);
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
$Result = mysqli_fetch_assoc($query);
$MAIN = unserialize($Result["content"]);
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $Act == "save_order") {
    foreach ($_POST["order"] as $_id => $_sort) {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_faq SET $disporder = '" . intval($_sort) . "' WHERE `id` = '" . $_id . "'");
    }
}
if ($Act == "delete_category" && $id) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name FROM ts_faq WHERE `id` = '" . $id . "'");
    if (mysqli_num_rows($query)) {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_faq WHERE `id` = '" . $id . "' OR $pid = '" . $id . "'");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $Result = mysqli_fetch_assoc($query);
            $name = $Result["name"];
            $Message = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[20]);
            logStaffAction($Message);
            $Message = showAlertError($Message);
        }
    }
}
if ($Act == "delete_child" && $id) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name FROM ts_faq WHERE `id` = '" . $id . "'");
    if (mysqli_num_rows($query)) {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_faq WHERE `id` = '" . $id . "'");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $Result = mysqli_fetch_assoc($query);
            $name = $Result["name"];
            $Message = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[23]);
            logStaffAction($Message);
            $Message = showAlertError($Message);
        }
    }
}
if ($Act == "edit_category" && $id) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name, disporder FROM ts_faq WHERE `id` = '" . $id . "' AND $type = '1'");
    if (mysqli_num_rows($query)) {
        $FAQ = mysqli_fetch_assoc($query);
        $name = $FAQ["name"];
        $disporder = $FAQ["disporder"];
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $name = trim($_POST["name"]);
            $disporder = intval($_POST["disporder"]);
            if ($name) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_faq SET $name = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $name) . "', $disporder = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $disporder) . "' WHERE `id` = '" . $id . "'");
                $Message = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[21]);
                logStaffAction($Message);
                $Message = showAlertError($Message);
                $Done = true;
            } else {
                $Message = showAlertError($Language[3]);
            }
        }
        if (!isset($Done)) {
            echo "\r\n\t\t\t" . $Message . "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_faq&$act = edit_category&$id = " . $id . "\">\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[2] . " - " . $Language[4] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[12] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"name\" $value = \"" . htmlspecialchars($name) . "\" $size = \"40\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[10] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"disporder\" $value = \"" . intval($disporder) . "\" $size = \"10\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[8] . "\" /> <input $type = \"reset\" $value = \"" . $Language[9] . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
        }
    }
}
if ($Act == "new_category") {
    $name = "";
    $disporder = "";
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $name = trim($_POST["name"]);
        $disporder = trim($_POST["disporder"]);
        if ($name) {
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_faq (type, name, disporder) VALUES ('1', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $name) . "', '" . $disporder . "')");
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                $Message = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[22]);
                logStaffAction($Message);
                $Message = showAlertError($Message);
                $Done = true;
            }
        } else {
            $Message = showAlertError($Language[3]);
        }
    }
    if (!isset($Done)) {
        echo "\r\n\t\t" . $Message . "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=manage_faq&$act = new_category\">\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t" . $Language[2] . " - " . $Language[7] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[12] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"name\" $value = \"" . htmlspecialchars($name) . "\" $size = \"99\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[10] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"disporder\" $value = \"" . intval($disporder) . "\" $size = \"10\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[8] . "\" /> <input $type = \"reset\" $value = \"" . $Language[9] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
if ($Act == "new_child" && $id) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_faq WHERE `id` = '" . $id . "' AND $type = '1'");
    if (mysqli_num_rows($query)) {
        $name = "";
        $pid = $id;
        $disporder = "";
        $description = "";
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $name = trim($_POST["name"]);
            $pid = intval($_POST["pid"]);
            $disporder = trim($_POST["disporder"]);
            $description = trim($_POST["description"]);
            if ($name && $pid && $description) {
                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_faq (type, name, pid, disporder, description) VALUES ('2', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $name) . "', '" . $pid . "', '" . $disporder . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "')");
                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                    $Message = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[25]);
                    logStaffAction($Message);
                    $Message = showAlertError($Message);
                    $Done = true;
                }
            } else {
                $Message = showAlertError($Language[3]);
            }
        }
        if (!isset($Done)) {
            $showcategories = "<select $name = \"pid\">";
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, name FROM ts_faq WHERE $type = '1' ORDER by disporder ASC");
            while ($cats = mysqli_fetch_assoc($query)) {
                $showcategories .= "<option $value = \"" . $cats["id"] . "\"" . ($pid == $cats["id"] ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars($cats["name"]) . "</option>";
            }
            $showcategories .= "</select>";
            echo loadTinyMCEEditor() . "\r\n\t\t\t" . $Message . "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_faq&$act = new_child&$id = " . $id . "\">\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[2] . " - " . $Language[6] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[12] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"name\" $value = \"" . htmlspecialchars($name) . "\" $style = \"width: 99%;\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[13] . "<br /><small>" . $Language[27] . "</small></td>\r\n\t\t\t\t\t<td class=\"alt1\"><textarea $name = \"description\" $id = \"description\" $style = \"width: 100%; height: 100px;\">" . htmlspecialchars($description) . "</textarea>\r\n\t\t\t\t\t<p><a $href = \"javascript:toggleEditor('description');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[26] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . $showcategories . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[10] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"disporder\" $value = \"" . intval($disporder) . "\" $size = \"10\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[8] . "\" /> <input $type = \"reset\" $value = \"" . $Language[9] . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
        }
    }
}
if ($Act == "edit_child" && $id) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name, pid, disporder, description FROM ts_faq WHERE `id` = '" . $id . "' AND $type = '2'");
    if (mysqli_num_rows($query)) {
        $FAQ = mysqli_fetch_assoc($query);
        $name = $FAQ["name"];
        $pid = $FAQ["pid"];
        $disporder = $FAQ["disporder"];
        $description = $FAQ["description"];
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $name = trim($_POST["name"]);
            $pid = intval($_POST["pid"]);
            $disporder = trim($_POST["disporder"]);
            $description = trim($_POST["description"]);
            if ($name && $pid && $description) {
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_faq SET $name = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $name) . "', $pid = '" . $pid . "', $disporder = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $disporder) . "', $description = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "' WHERE `id` = '" . $id . "'");
                $Message = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[24]);
                logStaffAction($Message);
                $Message = showAlertError($Message);
                $Done = true;
            } else {
                $Message = showAlertError($Language[3]);
            }
        }
        if (!isset($Done)) {
            $showcategories = "<select $name = \"pid\">";
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, name FROM ts_faq WHERE $type = '1' ORDER by disporder ASC");
            while ($cats = mysqli_fetch_assoc($query)) {
                $showcategories .= "<option $value = \"" . $cats["id"] . "\"" . ($pid == $cats["id"] ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars($cats["name"]) . "</option>";
            }
            $showcategories .= "</select>";
            echo loadTinyMCEEditor() . "\r\n\t\t\t" . $Message . "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_faq&$act = edit_child&$id = " . $id . "\">\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[2] . " - " . $Language[18] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[12] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"name\" $value = \"" . htmlspecialchars($name) . "\" $style = \"width: 99%;\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[13] . "<br /><small>" . $Language[27] . "</small></td>\r\n\t\t\t\t\t<td class=\"alt1\"><textarea $name = \"description\" $id = \"description\" $style = \"width: 100%; height: 100px;\">" . htmlspecialchars($description) . "</textarea>\r\n\t\t\t\t\t<p><a $href = \"javascript:toggleEditor('description');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[26] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . $showcategories . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[10] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"disporder\" $value = \"" . intval($disporder) . "\" $size = \"10\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[8] . "\" /> <input $type = \"reset\" $value = \"" . $Language[9] . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
        }
    }
}
$FAQSubCats = [];
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, name, pid, disporder FROM ts_faq WHERE $type = '2' ORDER BY disporder ASC");
while ($FSC = mysqli_fetch_assoc($query)) {
    $FAQSubCats[$FSC["pid"]][] = "\r\n\t<table>\r\n\t\t<tr>\r\n\t\t\t<td $width = \"1%\" $valign = \"top\">\r\n\t\t\t\t<a $href = \"index.php?do=manage_faq&amp;$act = edit_child&amp;$id = " . $FSC["id"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[18]) . "\" $title = \"" . trim($Language[18]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t</td>\r\n\t\t\t<td $width = \"1%\" $valign = \"top\">\r\n\t\t\t\t<a $href = \"index.php?do=manage_faq&amp;$act = delete_child&amp;$id = " . $FSC["id"] . "\" $onclick = \"return confirm('" . trim($Language[19]) . "\\n\\n" . trim($Language[17]) . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[19]) . "\" $title = \"" . trim($Language[19]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t</td>\r\n\t\t\t<td $width = \"88%\">\r\n\t\t\t\t" . trim($FSC["name"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td $width = \"10%\" $align = \"right\">\r\n\t\t\t\t<input $type = \"text\" $size = \"5\" $value = \"" . $FSC["disporder"] . "\" $name = \"order[" . $FSC["id"] . "]\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t";
}
$Output = [];
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, name, disporder FROM ts_faq WHERE $type = '1' ORDER BY disporder ASC");
if (0 < mysqli_num_rows($query)) {
    while ($FC = mysqli_fetch_assoc($query)) {
        $Output[] = "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=manage_faq&$act = save_order\" $name = \"sort_order\">\r\n\t\t<table $cellpadding = \"5\" $cellspacing = \"0\" $border = \"0\" $align = \"center\" $width = \"500\" $style = \"border-collapse:separate\" class=\"tborder\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\">\r\n\t\t\t\t\t<span $style = \"float: right;\">\r\n\t\t\t\t\t\t<a $href = \"index.php?do=manage_faq&amp;$act = new_child&amp;$id = " . $FC["id"] . "\"><img $src = \"images/tool_new.png\" $alt = \"" . trim($Language[6]) . "\" $title = \"" . trim($Language[6]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=manage_faq&amp;$act = edit_category&amp;$id = " . $FC["id"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[4]) . "\" $title = \"" . trim($Language[4]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=manage_faq&amp;$act = delete_category&amp;$id = " . $FC["id"] . "\" $onclick = \"return confirm('" . trim($Language[5]) . "\\n\\n" . trim($Language[14]) . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[5]) . "\" $title = \"" . trim($Language[5]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t\t\t</span>\r\n\t\t\t\t\t" . $FC["name"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . (isset($FAQSubCats[$FC["id"]]) ? implode(" ", $FAQSubCats[$FC["id"]]) : "&nbsp;" . $Language[15]) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t" . (isset($FAQSubCats[$FC["id"]]) ? "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\" $align = \"right\"><input $type = \"submit\" $value = \"" . $Language[8] . "\" /> <input $type = \"reset\" $value = \"" . $Language[9] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t" : "") . "\r\n\t\t</table>\r\n\t\t</form>";
    }
} else {
    $Output[] = showAlertError(str_replace("{1}", "index.php?do=manage_faq&amp;$act = new_category", $Language[16]));
}
echo "\r\n" . showAlertMessage("<a $href = \"index.php?do=manage_faq&$act = new_category\">" . $Language[7] . "</a>") . "\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\">" . $Language[2] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<table $cellpadding = \"4\" $cellspacing = \"0\" $border = \"0\" $align = \"center\" $width = \"100%\" $style = \"border-collapse:separate\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $valign = \"top\">";
for ($i = 0; $i <= count($Output); $i++) {
    if ($i && $i % 3 == 0) {
        echo "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td $valign = \"top\">";
    }
    if (isset($Output[$i]) && $Output[$i] != "") {
        echo $Output[$i];
    }
}
echo "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t</td>\r\n\t</tr>\r\n</table>";
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
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>