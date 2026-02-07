<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/manage_awards.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'MAIN'");
$Result = mysqli_fetch_assoc($query);
$MAIN = unserialize($Result["content"]);
if (is_dir("../images")) {
    $AwardImageDir = "../images/awardmedals/";
} else {
    if (is_dir("../pic")) {
        $AwardImageDir = "../pic/awardmedals/";
    } else {
        $AwardImageDir = false;
    }
}
$Output = [];
$ShowAwardImages = "";
$FromUserdetailsUser = isset($_GET["username"]) ? trim($_GET["username"]) : "";
if ($Act == "delete" && ($award_id = intval($_GET["award_id"]))) {
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_awards WHERE $award_id = " . $award_id);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_awards_users WHERE $award_id = " . $award_id);
    $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[3]);
    logStaffAction($Message);
    $Message = showAlertError($Message);
}
if ($Act == "edit" && ($award_id = intval($_GET["award_id"]))) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT award_image FROM ts_awards WHERE $award_id = " . $award_id);
    if (0 < mysqli_num_rows($query)) {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $award_name = trim($_POST["award_name"]);
            $award_image = trim($_POST["award_image"]);
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ts_awards SET $award_name = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $award_name) . "', $award_image = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $award_image) . "' WHERE $award_id = " . $award_id);
            $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[14]);
            logStaffAction($Message);
            $Message = showAlertError($Message);
            $Act = "";
            $award_id = "";
        }
        if ($Message == "") {
            $EditAward = mysqli_fetch_assoc($query);
            $Images = scandir($AwardImageDir);
            $ShowAwardImages = "<select $name = \"award_image\" $onchange = \"update_award_image(this.value);\">";
            foreach ($Images as $Image) {
                if ($Image != "." && $Image != ".." && in_array(function_149($Image), ["gif", "jpg", "png"])) {
                    $ShowAwardImages .= "<option $value = \"" . htmlspecialchars($Image) . "\"" . ($EditAward["award_image"] == $Image ? " $selected = \"selected\"" : "") . ">" . htmlspecialchars($Image) . "</option>";
                }
            }
            $ShowAwardImages .= "</select>";
        }
    } else {
        $Act = "";
        $award_id = "";
    }
}
if ($Act == "new") {
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $award_name = trim($_POST["award_name"]);
        $award_image = trim($_POST["award_image"]);
        mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_awards (award_name, award_image) VALUES('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $award_name) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $award_image) . "')");
        $Message = str_replace(["{1}", "{2}"], [$award_name, $_SESSION["ADMIN_USERNAME"]], $Language[24]);
        logStaffAction($Message);
        $Message = showAlertError($Message);
        $Act = "";
    }
    if ($Message == "") {
        $Images = scandir($AwardImageDir);
        $ShowAwardImages = "<select $name = \"award_image\" $onchange = \"update_award_image(this.value);\">";
        foreach ($Images as $Image) {
            if ($Image != "." && $Image != ".." && in_array(function_149($Image), ["gif", "jpg", "png"])) {
                $ShowAwardImages .= "<option $value = \"" . htmlspecialchars($Image) . "\">" . htmlspecialchars($Image) . "</option>";
            }
        }
        $ShowAwardImages .= "</select>";
        $Output[] = "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\">\r\n\t\t\t\t\t" . $Language[9] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_awards&$act = new\">\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $Language[12] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"award_name\" $value = \"\" $size = \"30\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $Language[13] . "</legend>\r\n\t\t\t\t\t\t" . $ShowAwardImages . "<img $id = \"awardimagepreview\" $src = \"" . $MAIN["pic_base_url"] . "awardmedals/black.png\" $border = \"0\" $alt = \"\" $title = \"\" $width = \"26\" $height = \"16\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t<legend>" . $Language[9] . "</legend>\r\n\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[10] . "\" /> <input $type = \"reset\" $value = \"" . $Language[11] . "\" />\r\n\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t</form>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>";
    }
}
if ($Act == "remove_award" && ($award_id = intval($_GET["award_id"])) && ($userid = intval($_GET["userid"]))) {
    $Query1 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT award_name FROM ts_awards WHERE $award_id = " . $award_id);
    $Query2 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT username FROM users WHERE `id` = " . $userid);
    $Result1 = mysqli_fetch_assoc($Query1);
    $Result2 = mysqli_fetch_assoc($Query2);
    $Awarname = $Result1["award_name"];
    $Username = $Result2["username"];
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM ts_awards_users WHERE $award_id = " . $award_id . " AND $userid = " . $userid);
    $Message = str_replace(["{1}", "{2}", "{3}"], [$Awarname, $Username, $_SESSION["ADMIN_USERNAME"]], $Language[16]);
    logStaffAction($Message);
    $Message = showAlertError($Message);
}
if ($Act == "give_award" && ($award_id = intval($_GET["award_id"]))) {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT award_name FROM ts_awards WHERE $award_id = " . $award_id);
    if (0 < mysqli_num_rows($query)) {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $Result = mysqli_fetch_assoc($query);
            $Awarname = $Result["award_name"];
            $Username = trim($_POST["username"]);
            $reason = trim($_POST["reason"]);
            $givenby = $_SESSION["ADMIN_ID"];
            $date = time();
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM users WHERE `username` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Username) . "'");
            if (mysqli_num_rows($query)) {
                $Result = mysqli_fetch_assoc($query);
                $userid = $Result["id"];
                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_awards_users (award_id, userid, reason, givenby, date) VALUES (" . $award_id . ", " . $userid . ", '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $reason) . "', " . $givenby . ", " . $date . ")");
                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO messages (sender, receiver, added, subject, msg) VALUES (" . $_SESSION["ADMIN_ID"] . ", " . $userid . ", NOW(), '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Language[22]) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], str_replace("\\n", "\r\n\t\t\t\t", str_replace(["{0}", "{1}", "{2}", "{3}"], [$Username, $MAIN["SITENAME"], $Awarname, $reason], $Language[23]))) . "')");
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $pmunread = pmunread + 1 WHERE `id` = '" . $userid . "'");
                $Message = str_replace(["{1}", "{2}", "{3}"], [$Awarname, $Username, $_SESSION["ADMIN_USERNAME"]], $Language[19]);
                logStaffAction($Message);
                $Message = showAlertError($Message);
                $Act = "";
                $award_id = "";
            }
        }
    } else {
        $Act = "";
        $award_id = "";
    }
}
if ($Act == "give_award" && isset($_GET["username"]) && $_GET["username"]) {
    $Username = trim($_GET["username"]);
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM users WHERE `username` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Username) . "'");
    if (0 < mysqli_num_rows($query)) {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
            $award_id = trim($_POST["award_id"]);
            $reason = trim($_POST["reason"]);
            $givenby = $_SESSION["ADMIN_ID"];
            $date = time();
            $Result = mysqli_fetch_assoc($query);
            $userid = $Result["id"];
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT award_name FROM ts_awards WHERE $award_id = " . $award_id);
            if (mysqli_num_rows($query)) {
                $Result = mysqli_fetch_assoc($query);
                $Awarname = $Result["award_name"];
                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_awards_users (award_id, userid, reason, givenby, date) VALUES (" . $award_id . ", " . $userid . ", '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $reason) . "', " . $givenby . ", " . $date . ")");
                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO messages (sender, receiver, added, subject, msg) VALUES (" . $_SESSION["ADMIN_ID"] . ", " . $userid . ", NOW(), '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $Language[22]) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], str_replace("\\n", "\r\n\t\t\t\t", str_replace(["{0}", "{1}", "{2}", "{3}"], [$Username, $MAIN["SITENAME"], $Awarname, $reason], $Language[23]))) . "')");
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $pmunread = pmunread + 1 WHERE `id` = '" . $userid . "'");
                $Message = str_replace(["{1}", "{2}", "{3}"], [$Awarname, htmlspecialchars($Username), $_SESSION["ADMIN_USERNAME"]], $Language[19]);
                logStaffAction($Message);
                $Message = showAlertError($Message);
                $Act = "";
                $award_id = "";
            }
        } else {
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT award_id, award_name FROM ts_awards");
            $Selecbox = "<select $name = \"award_id\">";
            while ($Sawards = mysqli_fetch_assoc($query)) {
                $Selecbox .= "<option $value = \"" . $Sawards["award_id"] . "\">" . $Sawards["award_name"] . "</option>";
            }
            $Selecbox .= "</select>";
            $Output[] = "\r\n\t\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"tcat\">\r\n\t\t\t\t\t\t" . $Language[18] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_awards&$act = give_award&$username = " . htmlspecialchars($Username) . "\">\r\n\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t<legend>" . $Language[20] . "</legend>\r\n\t\t\t\t\t\t\t<input $type = \"text\" $name = \"username\" $value = \"" . htmlspecialchars($Username) . "\" $size = \"30\" />\r\n\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t<legend>" . $Language[21] . "</legend>\r\n\t\t\t\t\t\t\t<input $type = \"text\" $name = \"reason\" $value = \"\" $size = \"30\" />\r\n\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t<legend>" . $Language[12] . "</legend>\r\n\t\t\t\t\t\t\t" . $Selecbox . "\r\n\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t<legend>" . $Language[18] . "</legend>\r\n\t\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[10] . "\" /> <input $type = \"reset\" $value = \"" . $Language[11] . "\" />\r\n\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t</form>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>";
        }
    } else {
        $Act = "";
        $award_id = "";
    }
}
$AwardUsers = [];
$AwardUserList = [];
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT a.award_id, a.userid, u.username, g.namestyle FROM ts_awards_users a LEFT JOIN users u ON (a.$userid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid)");
while ($au = mysqli_fetch_assoc($query)) {
    if (isset($AwardUsers[$au["award_id"]])) {
        $AwardUsers[$au["award_id"]]++;
    } else {
        $AwardUsers[$au["award_id"]] = 1;
    }
    $AwardUserList[$au["award_id"]][] = ($FromUserdetailsUser == $au["username"] ? "<span class=\"highlight\">" : "") . str_replace("{username}", $au["username"], $au["namestyle"]) . ($FromUserdetailsUser == $au["username"] ? "</span>" : "") . " <a $href = \"index.php?do=manage_awards&amp;$act = remove_award&amp;$award_id = " . $au["award_id"] . "&amp;$userid = " . $au["userid"] . "\" $onclick = \"return confirm('" . trim($Language[17]) . "');\"><img $src = \"images/unconfirmed_users.png\" $alt = \"" . trim($Language[15]) . "\" $title = \"" . trim($Language[15]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>";
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM ts_awards ORDER BY award_sort");
while ($a = mysqli_fetch_assoc($query)) {
    $AwardImage = $MAIN["pic_base_url"] . "awardmedals/" . $a["award_image"];
    $AwardName = htmlspecialchars($a["award_name"]);
    $Output[] = "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\">\r\n\t\t\t\t<span $style = \"float: right;\">\r\n\t\t\t\t\t\t<a $href = \"index.php?do=manage_awards&amp;$act = give_award&amp;$award_id = " . $a["award_id"] . "\"><img $src = \"images/accept.png\" $alt = \"" . trim($Language[18]) . "\" $title = \"" . trim($Language[18]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=manage_awards&amp;$act = edit&amp;$award_id = " . $a["award_id"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[4]) . "\" $title = \"" . trim($Language[4]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=manage_awards&amp;$act = delete&amp;$award_id = " . $a["award_id"] . "\" $onclick = \"return confirm('" . trim($Language[6]) . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[5]) . "\" $title = \"" . trim($Language[5]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t\t</span>\r\n\t\t\t\t" . $AwardName . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t" . ($Act == "edit" && $award_id == $a["award_id"] ? "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_awards&$act = edit&$award_id = " . $award_id . "\">\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $Language[12] . "</legend>\r\n\t\t\t\t<input $type = \"text\" $name = \"award_name\" $value = \"" . $AwardName . "\" $size = \"30\" />\r\n\t\t\t</fieldset>\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $Language[13] . "</legend>\r\n\t\t\t\t" . $ShowAwardImages . " <img $id = \"awardimagepreview\" $src = \"" . $AwardImage . "\" $border = \"0\" $alt = \"\" $title = \"\" $width = \"26\" $height = \"16\" />\r\n\t\t\t</fieldset>\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $Language[4] . "</legend>\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[10] . "\" /> <input $type = \"reset\" $value = \"" . $Language[11] . "\" />\r\n\t\t\t</fieldset>\r\n\t\t\t</form>\r\n\t\t\t" : ($Act == "give_award" && $award_id == $a["award_id"] ? "\r\n\t\t\t<form $method = \"post\" $action = \"index.php?do=manage_awards&$act = give_award&$award_id = " . $award_id . "\">\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $Language[20] . "</legend>\r\n\t\t\t\t<input $type = \"text\" $name = \"username\" $value = \"\" $size = \"30\" />\r\n\t\t\t</fieldset>\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $Language[21] . "</legend>\r\n\t\t\t\t<input $type = \"text\" $name = \"reason\" $value = \"\" $size = \"30\" />\r\n\t\t\t</fieldset>\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $Language[18] . "</legend>\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[10] . "\" /> <input $type = \"reset\" $value = \"" . $Language[11] . "\" />\r\n\t\t\t</fieldset>\r\n\t\t\t</form>\r\n\t\t\t" : "\r\n\t\t\t\t<table>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td $rowspawn = \"2\" $valign = \"top\">\r\n\t\t\t\t\t\t\t<img $src = \"" . $AwardImage . "\" $alt = \"\" $title = \"\" $border = \"0\" />\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t<td $align = \"center\">\r\n\t\t\t\t\t\t\t" . (isset($AwardUsers[$a["award_id"]]) && count($AwardUsers[$a["award_id"]]) ? str_replace("{1}", number_format($AwardUsers[$a["award_id"]]), $Language[7]) . "<br />" . implode(" ", $AwardUserList[$a["award_id"]]) : $Language[8]) . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>")) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
}
$List = "";
$Count = 0;
foreach ($Output as $Award) {
    if ($Count % 2 == 0) {
        $List .= "</td><td $valign = \"top\">";
    }
    if ($Count % 6 == 0) {
        $List .= "</td></tr><tr><td $valign = \"top\">";
    }
    $List .= $Award;
    $Count++;
}
echo "\r\n<script $type = \"text/javascript\">\r\n\tfunction update_award_image(selected)\r\n\t{\r\n\t\tTSGetID(\"awardimagepreview\").$src = \"" . $MAIN["pic_base_url"] . "awardmedals/\"+selected;\r\n\t}\r\n</script>\r\n" . showAlertMessage("<a $href = \"index.php?do=manage_awards&amp;$act = new\">" . $Language[9] . "</a>") . "\r\n" . $Message . "\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\">" . $Language[2] . "</td>\r\n\t</tr>\r\n</table>\r\n\r\n\t" . $List . "";
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
function function_149($file)
{
    return strtolower(substr(strrchr($file, "."), 1));
}

?>