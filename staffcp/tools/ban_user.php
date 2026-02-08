<?php
checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/ban_user.lang");
$Message = "";
$username = isset($_GET["username"]) ? trim($_GET["username"]) : "";
$usergroup = "";
$reason = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $username = trim($_POST["username"]);
    $usergroup = intval($_POST["usergroup"]);
    $reason = trim($_POST["reason"]);
    if ($username && $usergroup && $reason) {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT ip, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users LEFT JOIN usergroups g ON (users.$usergroup = g.gid) WHERE `username` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'");
        if (mysqli_num_rows($query) == 0) {
            $Message = showAlertError($Language[2]);
        } else {
            $User = mysqli_fetch_assoc($query);
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
            $LoggedAdminDetails = mysqli_fetch_assoc($query);
            if ($User["cansettingspanel"] == "yes" && $LoggedAdminDetails["cansettingspanel"] != "yes" || $User["canstaffpanel"] == "yes" && $LoggedAdminDetails["canstaffpanel"] != "yes" || $User["issupermod"] == "yes" && $LoggedAdminDetails["issupermod"] != "yes") {
                $Message = showAlertError($Language[14]);
            } else {
                $SysMsg = str_replace(["{1}", "{2}"], [$username, $_SESSION["ADMIN_USERNAME"]], $Language[15]);
                $modcomment = gmdate("Y-m-d") . " - " . trim($SysMsg) . " Reason: " . $reason . "\n";
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET `enabled` = 'no', $usergroup = '" . $usergroup . "', $notifs = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $reason) . "', $modcomment = CONCAT('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $modcomment) . "', modcomment) WHERE `username` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'");
                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                    logStaffAction($SysMsg);
                    if ($_POST["banip"] == "1") {
                        $bans = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT value FROM ipbans WHERE `id` = 1");
                        if (mysqli_num_rows($bans)) {
                            $banned = mysqli_fetch_assoc($bans);
                            if ($banned["value"] != "") {
                                $value = trim($banned["value"] . " " . $User["ip"]);
                            } else {
                                $value = trim($User["ip"]);
                            }
                            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE ipbans SET $value = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $value) . "', $date = NOW(), $modifier = '" . $_SESSION["ADMIN_ID"] . "' WHERE `id` = 1");
                        } else {
                            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ipbans VALUES (1, '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], trim($User["ip"])) . "', NOW(), '" . $_SESSION["ADMIN_ID"] . "')");
                        }
                        $IP = ip2long(trim($User["ip"]));
                        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM xbt_deny_from_hosts WHERE $begin = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $IP) . "' OR $end = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $IP) . "'");
                        if (!mysqli_num_rows($query)) {
                            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO xbt_deny_from_hosts (begin,end) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $IP) . "','" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $IP) . "')");
                        }
                    }
                    $Message = showAlertError($Language[3]);
                } else {
                    $Message = showAlertError($Language[4]);
                }
            }
        }
    } else {
        $Message = showAlertError($Language[1]);
    }
}
$showusergroups = "\r\n<select $name = \"usergroup\">";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title FROM usergroups WHERE `isbanned` = 'yes' ORDER by disporder ASC");
while ($UG = mysqli_fetch_assoc($query)) {
    $showusergroups .= "\r\n\t<option $value = \"" . $UG["gid"] . "\"" . ($usergroup == $UG["gid"] ? " $selected = \"selected\"" : "") . ">" . $UG["title"] . "</option>";
}
$showusergroups .= "\r\n</select>";
echo "<form $action = \"";
echo $_SERVER["SCRIPT_NAME"];
echo "?do=ban_user\" $method = \"post\">\r\n";
echo $Message;
echo "<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>";
echo $Language[8];
echo "</b></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[5];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"username\" $value = \"";
echo htmlspecialchars($username);
echo "\" $size = \"35\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[6];
echo "</td>\r\n\t\t<td class=\"alt2\">";
echo $showusergroups;
echo "</td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[7];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"reason\" $value = \"";
echo htmlspecialchars($reason);
echo "\" $size = \"35\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[11];
echo "</td>\r\n\t\t<td class=\"alt2\">\r\n\t\t\t<div class=\"smallfont\" $style = \"white-space:nowrap\">\r\n\t\t\t\t<input $type = \"radio\" $name = \"banip\" $id = \"rb_1_exact_2\" $value = \"1\" $tabindex = \"1\" />";
echo $Language[12];
echo "\t\t\t\t<input $type = \"radio\" $name = \"banip\" $id = \"rb_0_exact_2\" $value = \"0\" $tabindex = \"1\" $checked = \"checked\" />";
echo $Language[13];
echo "\t\t\t</div>\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" $align = \"right\"></td>\r\n\t\t<td class=\"tcat2\">\r\n\t\t\t<input $type = \"submit\" class=\"button\" $tabindex = \"1\" $value = \"";
echo $Language[8];
echo "\" $accesskey = \"s\" />\r\n\t\t\t<input $type = \"reset\" class=\"button\" $tabindex = \"1\" $value = \"";
echo $Language[9];
echo "\" $accesskey = \"r\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";
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

?>