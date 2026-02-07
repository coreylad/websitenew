<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/duplicate_ips.lang");
$Message = "";
$ids = [];
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $userids = isset($_POST["ids"]) ? $_POST["ids"] : "";
    if ($userids && is_array($userids) && 0 < count($userids) && $userids[0] != "") {
        $userQuery = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid FROM usergroups WHERE `isbanned` = 'yes'");
        $Result = mysqli_fetch_assoc($userQuery);
        $usergroupid = $Result["gid"];
        $userids = implode(",", $userids);
        $SysMsg = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[15]);
        $modcomment = gmdate("Y-m-d") . " - " . trim($SysMsg) . "\n";
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET `enabled` = 'no', $usergroup = '" . $usergroupid . "', $notifs = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $SysMsg) . "', $modcomment = CONCAT('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $modcomment) . "', modcomment) WHERE id IN (" . $userids . ")");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            $SysMsg = str_replace(["{1}", "{2}"], [$userids, $_SESSION["ADMIN_USERNAME"]], $Language[14]);
            logStaffAction($SysMsg);
        }
    }
}
$counter = mysqli_query($GLOBALS["DatabaseConnect"], "\r\n\t\tSELECT ip, count(*) as tot \r\n\t\tFROM users\r\n\t\tGROUP BY ip \r\n\t\tHAVING tot > 1 \r\n\t\tORDER BY ip");
if (!mysqli_num_rows($counter)) {
    echo "\r\n\t\r\n\t" . showAlertError($Language[1]);
} else {
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
    $LoggedAdminDetails = mysqli_fetch_assoc($query);
    $perpage = isset($_GET["perpage"]) ? intval($_GET["perpage"]) : (isset($_POST["perpage"]) ? intval($_POST["perpage"]) : 0);
    $page = isset($_GET["page"]) ? intval($_GET["page"]) : (isset($_POST["page"]) ? intval($_POST["page"]) : 0);
    if (!$perpage) {
        $perpage = 22;
    }
    if (!$page) {
        $page = 1;
    }
    $start = ($page - 1) * $perpage;
    $x = "";
    while ($ip = mysqli_fetch_assoc($counter)) {
        $ip["ip"] = preg_replace("/[^0-9-\\.]/u", "-", trim($ip["ip"]));
        if ($ip["ip"] != "") {
            $x .= "'" . $ip["ip"] . "',";
        }
    }
    $x .= "'XXX'";
    $max = mysqli_query($GLOBALS["DatabaseConnect"], "\r\n\t\tSELECT ip\r\n\t\tFROM users \r\n\t\tWHERE ip IN (" . $x . ") \r\n\t\tORDER BY ip");
    $total = ceil(mysqli_num_rows($max) / $perpage);
    $info = mysqli_query($GLOBALS["DatabaseConnect"], "\r\n\t\tSELECT u.id, u.ip, u.username, u.email, u.added, u.last_access, u.uploaded, u.downloaded, u.invites, u.seedbonus, g.title, g.namestyle, g.cansettingspanel, g.canstaffpanel, g.issupermod\r\n\t\tFROM users u \r\n\t\tLEFT JOIN usergroups g ON (u.`usergroup` = g.gid)\r\n\t\tWHERE u.ip IN (" . $x . ") \r\n\t\tORDER BY u.ip\r\n\t\tLIMIT " . $start . ", " . $perpage);
    if ($page != 1) {
        $prv = $page - 1;
        $firstpage = "<input $type = \"button\" class=\"button\" $tabindex = \"1\" $value = \"&laquo; \" $onclick = \"window.$location = 'index.php?do=duplicate_ips&$perpage = " . $perpage . "&$page = 1'\">";
        $prevpage = "<input $type = \"button\" class=\"button\" $tabindex = \"1\" $value = \"&lt; \" $onclick = \"window.$location = 'index.php?do=duplicate_ips&$perpage = " . $perpage . "&$page = " . $prv . "'\">";
    }
    if (isset($totalpages) && $page != $totalpages) {
        $nxt = $page + 1;
        $nextpage = "<input $type = \"button\" class=\"button\" $tabindex = \"1\" $value = \" &gt;\" $onclick = \"window.$location = 'index.php?do=duplicate_ips&$perpage = " . $perpage . "&$page = " . $nxt . "'\">";
        $lastpage = "<input $type = \"button\" class=\"button\" $tabindex = \"1\" $value = \" &raquo;\" $onclick = \"window.$location = 'index.php?do=duplicate_ips&$perpage = " . $perpage . "&$page = " . $total . "'\">";
    }
    $ip1 = "";
    $Found = "";
    while ($arr = mysqli_fetch_assoc($info)) {
        if ($arr["ip"] != $ip1) {
            $face = "700; color:#000000;";
            $bg = "background-color:#EBC7C7;\"";
        } else {
            $face = "100";
            $bg = "";
        }
        $Username = "<a $href = \"index.php?do=edit_user&amp;$username = " . $arr["username"] . "\">" . applyUsernameStyle($arr["username"], $arr["namestyle"]) . "</a>";
        if ($arr["cansettingspanel"] == "yes" && $LoggedAdminDetails["cansettingspanel"] != "yes" || $arr["canstaffpanel"] == "yes" && $LoggedAdminDetails["canstaffpanel"] != "yes" || $arr["issupermod"] == "yes" && $LoggedAdminDetails["issupermod"] != "yes") {
            $Username = "<i>" . $Language[18] . "</i>";
        }
        $Found .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $style = \"font-weight:" . $face . "; " . $bg . "\">\r\n\t\t\t\t" . $Username . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $style = \"font-weight:" . $face . "; " . $bg . "\">\r\n\t\t\t\t" . htmlspecialchars($arr["ip"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $style = \"font-weight:" . $face . "; " . $bg . "\">\r\n\t\t\t\t" . htmlspecialchars($arr["email"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $style = \"font-weight:" . $face . "; " . $bg . "\">\r\n\t\t\t\t" . formatTimestamp($arr["added"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $style = \"font-weight:" . $face . "; " . $bg . "\">\r\n\t\t\t\t" . formatTimestamp($arr["last_access"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $style = \"font-weight:" . $face . "; " . $bg . "\">\r\n\t\t\t\t" . var_238($arr["uploaded"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $style = \"font-weight:" . $face . "; " . $bg . "\">\r\n\t\t\t\t" . var_238($arr["downloaded"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $style = \"font-weight:" . $face . "; " . $bg . "\">\r\n\t\t\t\t" . number_format($arr["invites"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $style = \"font-weight:" . $face . "; " . $bg . "\">\r\n\t\t\t\t" . $arr["seedbonus"] . "\r\n\t\t\t</td>\t\t\t\t\t\r\n\t\t\t<td class=\"alt1\" $style = \"font-weight:" . $face . "; " . $bg . "\" $align = \"center\">\r\n\t\t\t\t<input $type = \"checkbox\" $name = \"ids[]\" $value = \"" . $arr["id"] . "\" $checkme = \"group\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
        $ip1 = $arr["ip"];
    }
    $options = [];
    for ($i = 1; $i <= $total; $i++) {
        $options[$i] = $Language[16] . " " . $i;
    }
    $pages = "<select $name = \"page\" $onchange = \"window.$location = 'index.php?do=duplicate_ips&$perpage = " . $perpage . "&$page = ' + this.value\" class=\"bginput\">\n" . function_317($options, $page) . "\t</select>";
    echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t{\r\n\t\t\tvar $frm = document.forms[formname];\r\n\t\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t\t{\r\n\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].$checked = = false)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<form $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=duplicate_ips" . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "\" $method = \"post\" $name = \"dublicate_ips\">\r\n\t\r\n\t" . $Message . "\t\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"10\"><b>" . $Language[3] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[4] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[5] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[6] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[13] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[7] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[8] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[9] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[10] . "</b></td>\r\n\t\t\t<td class=\"alt2\"><b>" . $Language[11] . "</b></td>\t\t\t\r\n\t\t\t<td class=\"alt2\" $align = \"center\"><input $type = \"checkbox\" $checkall = \"group\" $onclick = \"javascript: return select_deselectAll ('dublicate_ips', this, 'group');\"></td>\r\n\t\t</tr>\r\n\t\t" . (isset($Found) ? $Found : "") . "\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\" $align = \"right\" $colspan = \"11\">\t\t\t\t\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[12] . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"11\" $align = \"center\">\r\n\t\t\t\t" . $total . " - " . $Language[17] . "  " . (isset($firstpage) ? $firstpage : "") . " " . (isset($prevpage) ? $prevpage : "") . " &nbsp; " . $pages . " &nbsp; " . (isset($nextpage) ? $nextpage : "") . " " . (isset($lastpage) ? $lastpage : "") . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>";
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
function redirectTo($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function formatBytes($bytes = 0)
{
    if ($bytes < 1024000) {
        return number_format($bytes / 1024, 2) . " KB";
    }
    if ($bytes < 1048576000) {
        return number_format($bytes / 1048576, 2) . " MB";
    }
    if ($bytes < 0) {
        return number_format($bytes / 1073741824, 2) . " GB";
    }
    return number_format($bytes / 0, 2) . " TB";
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function formatTimestamp($timestamp = "")
{
    $var_265 = "m-d-Y h:i A";
    if (empty($timestamp)) {
        $timestamp = time();
    } else {
        if (strstr($timestamp, "-")) {
            $timestamp = strtotime($timestamp);
        }
    }
    return date($var_265, $timestamp);
}
function applyUsernameStyle($username, $namestyle)
{
    return str_replace("{username}", $username, $namestyle);
}
function function_317($array, $selectedid = "", $htmlise = false)
{
    if (is_array($array)) {
        $options = "";
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $options .= "\t\t<optgroup $label = \"" . function_318($htmlise, htmlspecialchars($key), $key) . "\">\n";
                $options .= function_317($val, $selectedid, $var_620, $htmlise);
                $options .= "\t\t</optgroup>\n";
            } else {
                if (is_array($selectedid)) {
                    $var_621 = function_318(in_array($key, $selectedid), " $selected = \"selected\"", "");
                } else {
                    $var_621 = function_318($key == $selectedid, " $selected = \"selected\"", "");
                }
                $options .= "\t\t<option $value = \"" . function_318($key !== "no_value", $key) . "\"" . $var_621 . ">" . function_318($htmlise, htmlspecialchars($val), $val) . "</option>\n";
            }
        }
    }
    return $options;
}
function function_318($expression, $returntrue, $returnfalse = "")
{
    return $expression ? $returntrue : $returnfalse;
}

?>