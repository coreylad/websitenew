<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/smilies.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
$Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'MAIN'");
$Result = mysqli_fetch_assoc($Q);
$MAIN = unserialize($Result["content"]);
$SmiliePath = $MAIN["pic_base_url"] . "smilies/";
if (is_dir("../images")) {
    $ImagePath = "../images/smilies/";
} else {
    if (is_dir("../pic")) {
        $ImagePath = "../pic/smilies/";
    } else {
        $ImagePath = false;
    }
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $Act == "update_sorder" && ($sid = intval($_POST["sid"]))) {
    $sorder = intval($_POST["sorder"]);
    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_smilies SET $sorder = " . $sorder . " WHERE $sid = " . $sid);
    function_161();
    logStaffAction(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[19]));
}
if ($Act == "delete" && ($sid = intval($_GET["sid"]))) {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_smilies WHERE $sid = " . $sid);
    function_161();
    logStaffAction(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[19]));
}
if ($Act == "edit" && ($sid = intval($_GET["sid"])) || $Act == "new") {
    if ($Act == "edit") {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_smilies WHERE $sid = " . $sid);
        $smilie = mysqli_fetch_assoc($query);
    } else {
        $sid = 0;
        $smilie = [];
        $smilie["stitle"] = "";
        $smilie["stext"] = "";
        $smilie["spath"] = "";
        $smilie["sorder"] = "0";
    }
    $stitle = $smilie["stitle"];
    $stext = $smilie["stext"];
    $spath = $smilie["spath"];
    $sorder = $smilie["sorder"];
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $stitle = trim($_POST["stitle"]);
        $stext = trim($_POST["stext"]);
        $spath = trim($_POST["spath"]);
        $sorder = intval($_POST["sorder"]);
        if ($Act == "edit") {
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_smilies SET $stitle = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $stitle) . "', $stext = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $stext) . "', $spath = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $spath) . "', $sorder = " . $sorder . " WHERE $sid = " . $sid);
        } else {
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_smilies (stitle, stext, spath, sorder) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $stitle) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $stext) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $spath) . "', " . $sorder . ")");
        }
        $UPDATED = true;
        function_161();
        logStaffAction(str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[19]));
    }
    if (!isset($UPDATED)) {
        $selectbox = "<select $name = \"spath\" $onchange = \"ChangeImage(this.value);\">";
        $SmilieList = scandir($ImagePath);
        foreach ($SmilieList as $sm) {
            if ($sm != "." && $sm != ".." && in_array(function_149($sm), ["gif", "jpg", "jpeg", "bmp", "png"])) {
                $selectbox .= "<option $value = \"" . $sm . "\"" . ($sm == $spath ? " $selected = \"selected\"" : "") . ">" . $sm . "</option>";
            }
        }
        $selectbox .= "</select>";
        $List = "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\tfunction ChangeImage(selected)\r\n\t\t\t{\r\n\t\t\t\tTSGetID(\"imgpreview\").$src = \"" . $SmiliePath . "\"+selected;\r\n\t\t\t}\r\n\t\t</script>\r\n\t\t<form $method = \"post\" $action = \"index.php?do=smilies&$act = " . $Act . "&$sid = " . $sid . "\">\r\n\t\t\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\">\r\n\t\t\t\t\t" . $Language[2] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[9] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[10] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"stitle\" $value = \"" . $stitle . "\" $size = \"30\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[11] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[12] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"stext\" $value = \"" . $stext . "\" $size = \"30\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[13] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[14] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t" . $selectbox . " <img $id = \"imgpreview\" $src = \"" . $SmiliePath . $spath . "\" $border = \"0\" $title = \"\" $alt = \"\" $style = \"vertical-align: middle;\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[7] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[15] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"sorder\" $value = \"" . $sorder . "\" $size = \"30\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[16] . "\" /> <input $type = \"reset\" $value = \"" . $Language[17] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
if (!isset($List)) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_smilies ORDER BY sorder ASC, stitle DESC");
    $List = "";
    while ($smilie = mysqli_fetch_assoc($query)) {
        $List .= "\r\n\t\t<div $style = \"float: left; width: 256px; height: 68px; margin: 0 5px 5px 5px;\">\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" class=\"mainTableNoMinWidth\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\">\r\n\t\t\t\t\t<span $style = \"float: right;\">\r\n\t\t\t\t\t\t<a $href = \"index.php?do=smilies&amp;$act = edit&amp;$sid = " . $smilie["sid"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[3]) . "\" $title = \"" . trim($Language[3]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=smilies&amp;$act = delete&amp;$sid = " . $smilie["sid"] . "\" $onclick = \"return confirm('" . trim($Language[5]) . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[4]) . "\" $title = \"" . trim($Language[4]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t\t</span>\r\n\t\t\t\t<img $src = \"" . $SmiliePath . $smilie["spath"] . "\" $border = \"0\" $title = \"" . $smilie["stitle"] . "\" $alt = \"" . $smilie["stitle"] . "\" $style = \"max-width: 60px; max-height: 16px;\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=smilies&$act = update_sorder\" />\r\n\t\t\t\t\t\t<input $type = \"hidden\" $name = \"sid\" $value = \"" . $smilie["sid"] . "\" />\r\n\t\t\t\t\t\t" . $Language[7] . " <input $type = \"text\" $name = \"sorder\" $value = \"" . $smilie["sorder"] . "\" $size = \"2\" /> <input $type = \"submit\" $value = \"" . $Language[6] . "\" />\r\n\t\t\t\t\t</form>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</div>";
    }
    echo "\r\n\t" . showAlertMessage("<a $href = \"index.php?do=smilies&$act = new\">" . $Language[18] . "</a>") . "\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>" . $List;
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
        var_236("../index.php");
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
function function_161($array = [])
{
    $Q = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE $configname = 'MAIN'");
    $Result = mysqli_fetch_assoc($Q);
    $MAIN = unserialize($Result["content"]);
    $var_456 = "\$smilies = array (";
    if (count($array) == 0) {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT stext, spath FROM ts_smilies ORDER BY sorder, stitle");
        while ($var_457 = mysqli_fetch_assoc($query)) {
            $var_458[] = "'" . $var_457["stext"] . "' => '" . $var_457["spath"] . "'";
        }
    } else {
        foreach ($array as $var_459 => $file) {
            $var_458[] = "'" . $var_459 . "' => '" . $file . "'";
        }
    }
    $var_456 = $var_456 . implode(", ", $var_458) . ");";
    $var_440 = "../" . $MAIN["cache"] . "/smilies.php";
    $var_442 = "<?php\n/** TS Generated Cache#14 - Do Not Alter\n * Cache Name: Smilies\n * Generated: " . gmdate("r") . "\n*/\n";
    $var_442 .= $var_456 . "\n?>";
    if (!file_put_contents($var_440, $var_442)) {
        logStaffAction("I can't update Smilies cache.. Please check permission of " . $var_440 . " file..");
    }
}
function function_149($file)
{
    return strtolower(substr(strrchr($file, "."), 1));
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}

?>