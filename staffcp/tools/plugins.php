<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/plugins.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
if ($Act == "delete" && ($pid = intval($_GET["pid"]))) {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_plugins WHERE $pid = " . $pid);
    $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[3]);
    logStaffAction($Message);
    $Message = showAlertError($Message);
}
if ($Act == "change_status" && ($pid = intval($_GET["pid"]))) {
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_plugins SET $active = IF($active = 1,0,1) WHERE $pid = " . $pid);
    $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[3]);
    logStaffAction($Message);
    $Message = showAlertError($Message);
}
if ($Act == "edit" && ($pid = intval($_GET["pid"])) || $Act == "new") {
    if ($Act == "edit") {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_plugins WHERE $pid = " . $pid);
        $Plugin = mysqli_fetch_assoc($query);
    } else {
        $pid = 0;
        $Plugin = [];
        $Plugin["name"] = "";
        $Plugin["description"] = "";
        $Plugin["content"] = "";
        $Plugin["position"] = "2";
        $Plugin["sort"] = "0";
        $Plugin["permission"] = "0";
        $Plugin["active"] = "1";
    }
    $name = $Plugin["name"];
    $description = $Plugin["description"];
    $content = $Plugin["content"];
    $position = $Plugin["position"];
    $sort = $Plugin["sort"];
    $permission = $Plugin["permission"];
    $active = $Plugin["active"];
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $name = trim($_POST["name"]);
        $description = trim($_POST["description"]);
        $content = trim($_POST["content"]);
        $position = intval($_POST["position"]);
        $sort = intval($_POST["sort"]);
        $permission = isset($_POST["usergroups"]) && is_array($_POST["usergroups"]) ? implode("", $_POST["usergroups"]) : "";
        $active = intval($_POST["active"]);
        if ($Act == "edit") {
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_plugins SET $name = \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $name) . "\", $description = \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "\", $content = \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $content) . "\", $position = " . $position . ", $sort = " . $sort . ", $permission = \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $permission) . "\", $active = " . $active . " WHERE $pid = " . $pid);
        } else {
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_plugins (name, description, content, position, sort, permission, active) VALUES (\"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $name) . "\", \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "\", \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $content) . "\", " . $position . ", " . $sort . ", \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $permission) . "\", " . $active . ")");
        }
        $UPDATED = true;
        $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[3]);
        logStaffAction($Message);
        $Message = showAlertError($Message);
    }
    if (!isset($UPDATED)) {
        $squery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, namestyle FROM usergroups");
        $sgids = "";
        while ($gid = mysqli_fetch_assoc($squery)) {
            $sgids .= "\r\n\t\t\t<div $style = \"margin-bottom: 3px;\">\r\n\t\t\t\t<label><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"[" . $gid["gid"] . "]\"" . ($permission && strstr($permission, "[" . $gid["gid"] . "]") ? " $checked = \"checked\"" : "") . " $style = \"vertical-align: middle;\" /> " . strip_tags(str_replace("{username}", $gid["title"], $gid["namestyle"]), "<b><span><strong><em><i><u>") . "</label>\r\n\t\t\t</div>";
        }
        $sgids .= "\r\n\t\t\t<div $style = \"margin-bottom: 3px;\">\r\n\t\t\t\t<label><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"[guest]\"" . ($permission && strstr($permission, "[guest]") ? " $checked = \"checked\"" : "") . " $style = \"vertical-align: middle;\" /> -" . $Language[33] . "-</label>\r\n\t\t\t</div>";
        $sgids .= "\r\n\t\t\t<div $style = \"margin-bottom: 3px;\">\r\n\t\t\t\t<label><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"[all]\"" . ($permission && strstr($permission, "[all]") ? " $checked = \"checked\"" : "") . " $style = \"vertical-align: middle;\" /> -" . $Language[34] . "-</label>\r\n\t\t\t</div>";
        $List = loadTinyMCEEditor() . "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=plugins&$act = " . $Act . "&$pid = " . $pid . "\">\r\n\t\t" . showAlertMessage("<a $href = \"index.php?do=plugins\">" . $Language[23] . "</a>") . "\r\n\t\t" . $Message . "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t\t\t" . $Language[2] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">" . $Language[16] . "<div class=\"alt2Div\">" . $Language[17] . "</div></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"name\" $value = \"" . $name . "\" $style = \"width: 97%;\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">" . $Language[18] . "<div class=\"alt2Div\">" . $Language[19] . "</div></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"description\" $value = \"" . $description . "\" $style = \"width: 97%;\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">" . $Language[20] . "<div class=\"alt2Div\">" . $Language[21] . "</div></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t<textarea $name = \"content\" $style = \"width: 99%; height: 80px;\">" . $content . "</textarea>\r\n\t\t\t\t\t<p><a $href = \"javascript:toggleEditor('content');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">" . $Language[10] . "<div class=\"alt2Div\">" . $Language[24] . "</div></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t<select $name = \"position\">\r\n\t\t\t\t\t\t<option $value = \"1\"" . ($position == 1 ? " $selected = \"selected\"" : "") . ">" . $Language[11] . "</option>\r\n\t\t\t\t\t\t<option $value = \"2\"" . ($position == 2 ? " $selected = \"selected\"" : "") . ">" . $Language[12] . "</option>\r\n\t\t\t\t\t\t<option $value = \"3\"" . ($position == 3 ? " $selected = \"selected\"" : "") . ">" . $Language[13] . "</option>\r\n\t\t\t\t\t</select>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">" . $Language[25] . "<div class=\"alt2Div\">" . $Language[28] . "</div></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t<select $name = \"active\">\r\n\t\t\t\t\t\t<option $value = \"1\"" . ($active == 1 ? " $selected = \"selected\"" : "") . ">" . $Language[26] . "</option>\r\n\t\t\t\t\t\t<option $value = \"0\"" . ($active == 0 ? " $selected = \"selected\"" : "") . ">" . $Language[27] . "</option>\r\n\t\t\t\t\t</select>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">" . $Language[7] . "<div class=\"alt2Div\">" . $Language[29] . "</div></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"sort\" $value = \"" . $sort . "\" $size = \"10\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\">" . $Language[30] . "<div class=\"alt2Div\">" . $Language[31] . "</div></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">\r\n\t\t\t\t\t" . $sgids . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[8] . "\" /> <input $type = \"reset\" $value = \"" . $Language[22] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
if ($Act == "save_order") {
    foreach ($_POST as $row) {
        $segments = explode(":", $row);
        $position = str_replace("column-", "", $segments[0]);
        $plugins = explode(",", $segments[1]);
        foreach ($plugins as $sort => $pid) {
            $pid = str_replace("plugin-", "", $pid);
            $Query = "UPDATE ts_plugins SET $position = '" . $position . "', $sort = '" . $sort . "' WHERE $pid = " . $pid;
            mysqli_query($GLOBALS["DatabaseConnect"], $Query);
            if (mysqli_errno($GLOBALS["DatabaseConnect"])) {
                echo $Query . "<br />" . mysqli_error($GLOBALS["DatabaseConnect"]);
            } else {
                echo "Moved Plugin: " . $pid . " to position: " . $position . " and updated rank to: " . $sort . "<br />";
            }
        }
    }
    $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[3]);
    logStaffAction($Message);
    exit;
} else {
    if (!isset($List)) {
        $LeftPlugins = [];
        $MiddlePlugins = [];
        $RightPlugins = [];
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_plugins ORDER BY active DESC, sort ASC");
        while ($plugin = mysqli_fetch_assoc($query)) {
            if ($plugin["position"] == "1") {
                $LeftPlugins[] = $plugin;
            } else {
                if ($plugin["position"] == "2") {
                    $MiddlePlugins[] = $plugin;
                } else {
                    $RightPlugins[] = $plugin;
                }
            }
        }
        $List1 = implode(" ", function_315($LeftPlugins));
        $List2 = implode(" ", function_315($MiddlePlugins));
        $List3 = implode(" ", function_315($RightPlugins));
        echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tvar settings \$t = \r\n\t\t{\r\n\t\t\t" . var_615() . "\r\n\t\t};\r\n\t\tvar $options = \r\n\t\t{\r\n\t\t\tportal \t: \"columns\",\r\n\t\t\teditorEnabled : true,\r\n\t\t\tsaveurl : \"index.php?do=plugins&$act = save_order\",\r\n\t\t\tTSDebug : true\r\n\t\t};\r\n\t\tvar $data = {};\r\n\t\tvar portal;\r\n\t\tEvent.observe(window, \"load\", function()\r\n\t\t{\r\n\t\t\$tportal = new Portal(settings, options, data);\r\n\t\t});\r\n\t</script>\r\n\t" . showAlertMessage("<a $href = \"index.php?do=plugins&$act = new\">" . $Language[9] . "</a>") . "\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<div $id = \"wrapper\">\r\n\t\t\t\t\t<div $id = \"columns\">\r\n\t\t\t\t\t\t<div $id = \"column-1\" class=\"column menu\"></div>\r\n\t\t\t\t\t\t<div $id = \"column-2\" class=\"column blocks\"></div>\r\n\t\t\t\t\t\t<div $id = \"column-3\" class=\"column sidebar\"></div>\r\n\t\t\t\t\t\t<div class=\"portal-column\" $id = \"portal-column-block-list\" $style = \"display: none;\">\r\n\t\t\t\t\t\t\t" . $List1 . "\r\n\t\t\t\t\t\t\t" . $List2 . "\r\n\t\t\t\t\t\t\t" . $List3 . "\r\n\t\t\t\t\t\t</div>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t\t<div $style = \"clear:both;\"></div>\r\n\t\t\t\t\t<div $id = \"debug\" $style = \"display: none;\">\r\n\t\t\t\t\t\t<p $style = \"margin:0px;\" $id = \"data\"></p>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
    } else {
        echo $List;
    }
}
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
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES (\"" . $_SESSION["ADMIN_ID"] . "\", \"" . time() . "\", \"" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "\")");
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
// DEAD CODE: function_316() is never called. Appears to format plugin positions for JSON output.
function function_316()
{
    $plugins = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_plugins ORDER BY sort ASC");
    $var_616 = [];
    while ($plugin = mysqli_fetch_assoc($plugins)) {
        $var_616["column-" . $plugin["position"]][] = $plugin;
    }
    $var_617 = [];
    foreach ($var_616 as $var_618 => $plugins) {
        $var_619 = [];
        foreach ($plugins as $plugin) {
            $var_619[] = "'plugin-" . $plugin["pid"] . "'";
        }
        $var_617[] = "'" . $var_618 . "'" . ":[" . implode(",", $var_619) . "]";
    }
    return implode(",", $var_617);
}
function function_315($PluginArray)
{
    global $Language;
    $var_451 = [];
    foreach ($PluginArray as $Plugin) {
        $var_451[] = "\r\n\t\t<div class=\"block\" $id = \"plugin-" . $Plugin["pid"] . "\">\r\n\t\t\t<h1 class=\"draghandle\">\r\n\t\t\t\t" . $Plugin["description"] . "\r\n\t\t\t</h1>\r\n\t\t\t<p>\r\n\t\t\t\t" . ($Plugin["active"] == 1 ? "<a $href = \"index.php?do=plugins&amp;$act = change_status&amp;$pid = " . $Plugin["pid"] . "\"><img $src = \"images/accept.png\" $alt = \"" . trim($Language[15]) . "\" $title = \"" . trim($Language[15]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>" : "<a $href = \"index.php?do=plugins&amp;$act = change_status&amp;$pid = " . $Plugin["pid"] . "\"><img $src = \"images/cancel.png\" $alt = \"" . trim($Language[14]) . "\" $title = \"" . trim($Language[14]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>") . "\r\n\t\t\t\t<a $href = \"index.php?do=plugins&amp;$act = edit&amp;$pid = " . $Plugin["pid"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[4]) . "\" $title = \"" . trim($Language[4]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=plugins&amp;$act = delete&amp;$pid = " . $Plugin["pid"] . "\" $onclick = \"return confirm('" . trim($Language[6]) . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[5]) . "\" $title = \"" . trim($Language[5]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t</p>\r\n\t\t</div>";
    }
    return $var_451;
}

?>