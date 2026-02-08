<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
var_435();
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Cid = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : 0);
$Language = file("languages/" . getStaffLanguage() . "/manage_torrent_categories.lang");
$Message = "";
if ($Act == "delete" && $Cid) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name, type FROM categories WHERE `id` = '" . $Cid . "'");
    if (mysqli_num_rows($query)) {
        $Category = mysqli_fetch_assoc($query);
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM categories WHERE `id` = '" . $Cid . "'");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            if ($Category["type"] == "c") {
                mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM categories WHERE $pid = '" . $Cid . "'");
            }
            $SysMsg = str_replace(["{1}", "{2}"], [$Category["name"], $_SESSION["ADMIN_USERNAME"]], $Language[10]);
            logStaffAction($SysMsg);
            function_153();
        }
    }
}
if ($Act == "edit" && $Cid) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM categories WHERE `id` = '" . $Cid . "'");
    if (mysqli_num_rows($query)) {
        $Category = mysqli_fetch_assoc($query);
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $Changes = [];
            if (isset($_POST["usergroups"])) {
                if (in_array("[ALL]", $_POST["usergroups"]) || empty($_POST["usergroups"][0])) {
                    $_POST["canview"] = "[ALL]";
                } else {
                    $_POST["canview"] = implode(",", $_POST["usergroups"]);
                }
                unset($_POST["usergroups"]);
            }
            if (isset($_POST["usergroups2"])) {
                if (in_array("[ALL]", $_POST["usergroups2"]) || empty($_POST["usergroups2"][0])) {
                    $_POST["candownload"] = "[ALL]";
                } else {
                    $_POST["candownload"] = implode(",", $_POST["usergroups2"]);
                }
                unset($_POST["usergroups2"]);
            }
            foreach ($_POST as $name => $value) {
                $Changes[] = "`" . $name . "` = '" . (!empty($value) ? mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $value) : $value) . "'";
            }
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE categories SET " . implode(", ", $Changes) . " WHERE `id` = '" . $Cid . "'");
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                function_153();
                $Message = str_replace(["{1}", "{2}"], [$Category["name"], $_SESSION["ADMIN_USERNAME"]], $Language[11]);
                logStaffAction($Message);
                $Message = showAlertMessage($Message);
                $Done = true;
            }
        }
        if (!isset($Done)) {
            $Extra = "";
            if ($Category["type"] == "s") {
                $Selectbox = "<select $name = \"pid\">";
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, name FROM categories WHERE $type = 'c' ORDER BY name ASC");
                while ($Cats = mysqli_fetch_assoc($query)) {
                    $Selectbox .= "<option $value = \"" . $Cats["id"] . "\"" . ($Cats["id"] == $Category["pid"] ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars($Cats["name"]) . "</option>";
                }
                $Selectbox .= "</select >";
                $Extra = "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[17] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Selectbox . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
            }
            $canview = @explode(",", $Category["canview"]);
            $squery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, namestyle FROM usergroups");
            $scount = 1;
            for ($sgids = "\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>Select Usergroup(s)</legend>\r\n\t\t\t\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"2\" $width = \"100%\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"none\"><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"[ALL]\"" . (in_array("[ALL]", $canview, true) ? " $checked = \"checked\"" : "") . " /></td><td class=\"none\">" . $Language[28] . "</td>"; $gid = mysqli_fetch_assoc($squery); $scount++) {
                if ($scount % 4 == 1) {
                    $sgids .= "</tr><tr>";
                }
                $sgids .= "\r\n\t\t\t\t<td class=\"none\"><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"" . $gid["gid"] . "\"" . (in_array($gid["gid"], $canview, true) ? " $checked = \"checked\"" : "") . " /></td>\r\n\t\t\t\t<td class=\"none\">" . str_replace("{username}", $gid["title"], $gid["namestyle"]) . "</td>";
            }
            $sgids .= "\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t</table>\r\n\t\t\t</fieldset>";
            $candownload = @explode(",", $Category["candownload"]);
            $squery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, namestyle FROM usergroups");
            $scount = 1;
            for ($sgids2 = "\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>Select Usergroup(s)</legend>\r\n\t\t\t\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"2\" $width = \"100%\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"none\"><input $type = \"checkbox\" $name = \"usergroups2[]\" $value = \"[ALL]\"" . (in_array("[ALL]", $candownload, true) ? " $checked = \"checked\"" : "") . " /></td><td class=\"none\">" . $Language[28] . "</td>"; $gid = mysqli_fetch_assoc($squery); $scount++) {
                if ($scount % 4 == 1) {
                    $sgids2 .= "</tr><tr>";
                }
                $sgids2 .= "\r\n\t\t\t\t<td class=\"none\"><input $type = \"checkbox\" $name = \"usergroups2[]\" $value = \"" . $gid["gid"] . "\"" . (in_array($gid["gid"], $candownload, true) ? " $checked = \"checked\"" : "") . " /></td>\r\n\t\t\t\t<td class=\"none\">" . str_replace("{username}", $gid["title"], $gid["namestyle"]) . "</td>";
            }
            $sgids2 .= "\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t</table>\r\n\t\t\t</fieldset>";
            echo "\r\n\t\t\t\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_torrent_categories&$act = edit&$id = " . $Cid . "\">\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t\t" . $Language[6] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[13] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"name\" $value = \"" . htmlspecialchars($Category["name"]) . "\" $size = \"40\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[15] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"cat_desc\" $value = \"" . htmlspecialchars($Category["cat_desc"]) . "\" $size = \"40\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">" . $Language[14] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">" . var_435($Category["image"]) . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . $Extra . "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[27] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $sgids . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[29] . "</td>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t" . $sgids2 . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[20] . "\" /> <input $type = \"reset\" $value = \"" . $Language[21] . "\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>";
        }
    }
}
if ($Act == "new") {
    $name = "";
    $image = "";
    $cat_desc = "";
    $type = "";
    $pid = "";
    $Message = "";
    $canview = "[ALL]";
    $candownload = "[ALL]";
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $name = trim($_POST["name"]);
        $image = trim($_POST["image"]);
        $cat_desc = trim($_POST["cat_desc"]);
        $canview = implode(",", $_POST["usergroups"]);
        $candownload = implode(",", $_POST["usergroups2"]);
        if (isset($_POST["pid"]) && $_POST["pid"] != 0) {
            $pid = intval($_POST["pid"]);
            $type = "s";
        } else {
            $pid = "0";
            $type = "c";
        }
        if (!$name || !$image) {
            $Message = showAlertError($Language[24]);
        } else {
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO categories (name, image, cat_desc, type, pid, canview, candownload) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $name) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $image) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $cat_desc) . "', '" . $type . "', '" . $pid . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $canview) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $candownload) . "')");
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                function_153();
                $Message = str_replace(["{1}", "{2}"], [$name, $_SESSION["ADMIN_USERNAME"]], $Language[12]);
                logStaffAction($Message);
                $Message = showAlertMessage($Message);
                $Done = true;
            }
        }
    }
    if (!isset($Done)) {
        $id = isset($_GET["id"]) ? intval($_GET["id"]) : "";
        $Extra = "";
        if ($id) {
            $Head = $Language[26];
            $Selectbox = "<select $name = \"pid\"><option $value = \"0\"></option>";
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, name FROM categories WHERE $type = 'c' ORDER BY name ASC");
            while ($Cats = mysqli_fetch_assoc($query)) {
                $Selectbox .= "<option $value = \"" . $Cats["id"] . "\"" . ($Cats["id"] == $id ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars($Cats["name"]) . "</option>";
            }
            $Selectbox .= "</select >";
            $Extra = "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[17] . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . $Selectbox . "</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        } else {
            $Head = $Language[25];
        }
        $canview = ["[ALL]"];
        $squery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, namestyle FROM usergroups");
        $scount = 1;
        for ($sgids = "\r\n\t\t<fieldset>\r\n\t\t\t<legend>Select Usergroup(s)</legend>\r\n\t\t\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"2\" $width = \"100%\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"none\"><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"[ALL]\"" . (in_array("[ALL]", $canview, true) ? " $checked = \"checked\"" : "") . " /></td><td class=\"none\">" . $Language[28] . "</td>"; $gid = mysqli_fetch_assoc($squery); $scount++) {
            if ($scount % 4 == 1) {
                $sgids .= "</tr><tr>";
            }
            $sgids .= "\r\n\t\t\t<td class=\"none\"><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"" . $gid["gid"] . "\"" . (in_array($gid["gid"], $canview, true) ? " $checked = \"checked\"" : "") . " /></td>\r\n\t\t\t<td class=\"none\">" . str_replace("{username}", $gid["title"], $gid["namestyle"]) . "</td>";
        }
        $sgids .= "\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t</fieldset>";
        $candownload = ["[ALL]"];
        $squery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, namestyle FROM usergroups");
        $scount = 1;
        for ($sgids2 = "\r\n\t\t<fieldset>\r\n\t\t\t<legend>Select Usergroup(s)</legend>\r\n\t\t\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"2\" $width = \"100%\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td class=\"none\"><input $type = \"checkbox\" $name = \"usergroups2[]\" $value = \"[ALL]\"" . (in_array("[ALL]", $candownload, true) ? " $checked = \"checked\"" : "") . " /></td><td class=\"none\">" . $Language[28] . "</td>"; $gid = mysqli_fetch_assoc($squery); $scount++) {
            if ($scount % 4 == 1) {
                $sgids2 .= "</tr><tr>";
            }
            $sgids2 .= "\r\n\t\t\t<td class=\"none\"><input $type = \"checkbox\" $name = \"usergroups2[]\" $value = \"" . $gid["gid"] . "\"" . (in_array($gid["gid"], $candownload, true) ? " $checked = \"checked\"" : "") . " /></td>\r\n\t\t\t<td class=\"none\">" . str_replace("{username}", $gid["title"], $gid["namestyle"]) . "</td>";
        }
        $sgids2 .= "\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t</fieldset>";
        echo "\r\n\t\t\r\n\t\t" . $Message . "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=manage_torrent_categories&$act = new&$id = " . $id . "\">\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t\t\t" . $Head . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[13] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"name\" $value = \"" . htmlspecialchars($name) . "\" $size = \"40\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[15] . "</td>\r\n\t\t\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"cat_desc\" $value = \"" . htmlspecialchars($cat_desc) . "\" $size = \"40\" /></td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">" . $Language[14] . "</td>\r\n\t\t\t\t<td class=\"alt1\">" . var_435($image) . "</td>\r\n\t\t\t</tr>\r\n\t\t\t" . $Extra . "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[27] . "</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . $sgids . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\t\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $valign = \"top\">" . $Language[29] . "</td>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t" . $sgids2 . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\t\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[20] . "\" /> <input $type = \"reset\" $value = \"" . $Language[21] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
$SubCategories = [];
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM categories WHERE $type = 's' ORDER by name ASC");
while ($SC = mysqli_fetch_assoc($query)) {
    $SubCategories[$SC["pid"]][] = "\r\n\t<!-- Sub Category -->\r\n\t<table>\r\n\t\t<tr>\r\n\t\t\t<td $width = \"1%\">\r\n\t\t\t\t<a $href = \"index.php?do=manage_torrent_categories&amp;$act = edit&amp;$id = " . $SC["id"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[3]) . "\" $title = \"" . trim($Language[3]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t</td>\r\n\t\t\t<td $width = \"1%\">\r\n\t\t\t\t<a $href = \"index.php?do=manage_torrent_categories&amp;$act = delete&amp;$id = " . $SC["id"] . "\" $onclick = \"return confirm('" . trim($Language[4]) . ": " . trim($SC["name"]) . "\\n\\n" . trim($Language[5]) . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[4]) . "\" $title = \"" . trim($Language[4]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t</td>\r\n\t\t\t<td $width = \"88%\">\r\n\t\t\t\t" . trim($SC["name"]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t<!-- Sub Category -->\r\n\t";
}
$Output = [];
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM categories WHERE $type = 'c' ORDER by name ASC");
while ($ST = mysqli_fetch_assoc($query)) {
    $Output[] = "\r\n\t<!-- Category -->\r\n\t<table $cellpadding = \"4\" $cellspacing = \"0\" $border = \"0\" $align = \"center\" $width = \"400\" $style = \"border-collapse:separate\" class=\"tborder\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\">\r\n\t\t\t\t<span $style = \"float: right;\">\r\n\t\t\t\t\t<a $href = \"index.php?do=manage_torrent_categories&amp;$act = new&amp;$id = " . $ST["id"] . "\"><img $src = \"images/tool_new.png\" $alt = \"" . trim($Language[18]) . "\" $title = \"" . trim($Language[18]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=manage_torrent_categories&amp;$act = edit&amp;$id = " . $ST["id"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[6]) . "\" $title = \"" . trim($Language[6]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=manage_torrent_categories&amp;$act = delete&amp;$id = " . $ST["id"] . "\" $onclick = \"return confirm('" . trim($Language[7]) . ": " . trim($ST["name"]) . "\\n\\n" . trim($Language[8]) . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[7]) . "\" $title = \"" . trim($Language[7]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t\t</span>" . $ST["name"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . (isset($SubCategories[$ST["id"]]) ? implode(" ", $SubCategories[$ST["id"]]) : "&nbsp;" . $Language[9]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t<!-- Category -->\r\n\t";
}
$List = "";
$Count = 0;
foreach ($Output as $Category) {
    if ($Count % 2 == 0) {
        $List .= "</td><td $valign = \"top\">";
    }
    if ($Count % 6 == 0) {
        $List .= "</td></tr><tr><td $valign = \"top\">";
    }
    $List .= $Category;
    $Count++;
}
echo "\r\n" . showAlertMessage("<a $href = \"index.php?do=manage_torrent_categories&amp;$act = new\">" . trim($Language[25]) . "</a>") . "\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\">" . $Language[2] . "</td>\r\n\t</tr>\r\n</table>" . $List;
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
function function_154($file)
{
    return strtolower(substr(strrchr($file, "."), 1));
}
function function_153()
{
    $configQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
    $configRow = mysqli_fetch_assoc($configQuery);
    $configData = unserialize($configRow["content"]);
    $var_436 = [];
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM categories WHERE $type = 'c' ORDER by name,id");
    while ($var_418 = mysqli_fetch_assoc($query)) {
        $var_436[] = $var_418;
    }
    $var_437 = [];
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM categories WHERE $type = 's' ORDER by name,id");
    while ($var_418 = mysqli_fetch_assoc($query)) {
        $var_437[] = $var_418;
    }
    $var_438 = var_export($var_436, true);
    $var_439 = var_export($var_437, true);
    $var_440 = "../" . $configData["cache"] . "/categories.php";
    $var_441 = @fopen((string) $var_440, "w");
    $var_442 = "<?php\n/** TS Generated Cache#7 - Do Not Alter\n * Cache Name: Categories\n * Generated: " . gmdate("r") . "\n*/\n\n";
    $var_442 .= "\$_categoriesC = " . $var_438 . ";\n\n";
    $var_442 .= "\$_categoriesS = " . $var_439 . ";\n?>";
    @fwrite($var_441, $var_442);
    @fclose($var_441);
}
function function_155($selected = "")
{
    $configQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
    $configRow = mysqli_fetch_assoc($configQuery);
    $configData = unserialize($configRow["content"]);
    if (is_dir("../images")) {
        $var_443 = "../images/" . $configData["table_cat"] . "/";
    } else {
        if (is_dir("../pic")) {
            $var_443 = "../pic/" . $configData["table_cat"] . "/";
        } else {
            return "<select $name = \"image\"></select>";
        }
    }
    $var_444 = $configData["pic_base_url"] . $configData["table_cat"] . "/";
    $var_445 = scandir($var_443);
    $var_446 = "<select $name = \"image\">";
    foreach ($var_445 as $var_447) {
        if (in_array(function_154($var_447), ["png", "gif", "jpg"])) {
            $var_446 .= "<option $value = \"" . htmlspecialchars($var_447) . "\"" . ($selected == $var_447 ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars($var_447) . "</option>";
        }
    }
    $var_446 .= "</select>";
    return $var_446;
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>