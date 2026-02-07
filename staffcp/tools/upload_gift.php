<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . function_75() . "/upload_gift.lang");
$Message = "";
$amount = "0";
$type = "GB";
$usergroups = [];
$username = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $amount = 0 + $_POST["amount"];
    $usergroups = isset($_POST["usergroups"]) ? $_POST["usergroups"] : "";
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
    $type = $_POST["type"];
    if ($type == "MB") {
        $uamount = $amount * 1024 * 1024;
    } else {
        $uamount = $amount * 1024 * 1024 * 1024;
    }
    if ($amount && $uamount && (is_array($usergroups) && count($usergroups) || $username) && $type) {
        if ($username) {
            if (preg_match("@,@", $username)) {
                $usernames = explode(",", $username);
                foreach ($usernames as $user) {
                    $user = trim($user);
                    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT usergroup FROM users WHERE $username = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $user) . "'");
                    if (0 < mysqli_num_rows($query)) {
                        $newModComment = str_replace("'", "\"", date("Y-m-d") . " - " . str_replace(["{1}", "{2}", "{3}"], [$user, var_238($uamount), $_SESSION["ADMIN_USERNAME"]], $Language[13]) . "\\n");
                        $newModCommentSQL = ", `modcomment` = IF(ISNULL(modcomment), '" . $newModComment . "', CONCAT('" . $newModComment . "', modcomment))";
                        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET `uploaded` = uploaded + " . $uamount . $newModCommentSQL . " WHERE $username = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $user) . "'") or exit(mysqli_error($GLOBALS["DatabaseConnect"]));
                    }
                }
                $SysMsg = str_replace(["{1}", "{2}", "{3}"], [$username, var_238($uamount), $_SESSION["ADMIN_USERNAME"]], $Language[13]);
            } else {
                $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT usergroup FROM users WHERE $username = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'");
                if (0 < mysqli_num_rows($query)) {
                    $newModComment = str_replace("'", "\"", date("Y-m-d") . " - " . str_replace(["{1}", "{2}", "{3}"], [$username, var_238($uamount), $_SESSION["ADMIN_USERNAME"]], $Language[13]) . "\\n");
                    $newModCommentSQL = ", `modcomment` = IF(ISNULL(modcomment), '" . $newModComment . "', CONCAT('" . $newModComment . "', modcomment))";
                    mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $uploaded = uploaded + " . $uamount . $newModCommentSQL . " WHERE $username = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'");
                    exit(mysqli_error($GLOBALS["DatabaseConnect"]));
                }
                $Message = function_76($Language[12]);
            }
            if (!$Message && mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                function_79($SysMsg);
                $Message = function_76($SysMsg);
            }
        } else {
            $work = implode(",", $usergroups);
            $SysMsg = str_replace(["{1}", "{2}", "{3}"], [$work, var_238($uamount), $_SESSION["ADMIN_USERNAME"]], $Language[3]);
            $newModComment = str_replace("'", "\"", date("Y-m-d") . " - " . $SysMsg . "\\n");
            $newModCommentSQL = ", `modcomment` = IF(ISNULL(modcomment), '" . $newModComment . "', CONCAT('" . $newModComment . "', modcomment))";
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET $uploaded = uploaded + " . $uamount . $newModCommentSQL . " WHERE usergroup IN (0, " . $work . ")");
            exit(mysqli_error($GLOBALS["DatabaseConnect"]));
        }
    } else {
        $Message = function_76($Language[10]);
    }
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
$LoggedAdminDetails = mysqli_fetch_assoc($query);
$count = 0;
$showusergroups = "\r\n<table>\r\n\t<tr>\t";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups ORDER by disporder ASC");
while ($UG = mysqli_fetch_assoc($query)) {
    if (!($UG["cansettingspanel"] == "yes" && $LoggedAdminDetails["cansettingspanel"] != "yes" || $UG["canstaffpanel"] == "yes" && $LoggedAdminDetails["canstaffpanel"] != "yes" || $UG["issupermod"] == "yes" && $LoggedAdminDetails["issupermod"] != "yes")) {
        if ($count && $count % 8 == 0) {
            $showusergroups .= "</tr><tr>";
        }
        $showusergroups .= "<td><input $type = \"checkbox\" $name = \"usergroups[]\" $value = \"" . $UG["gid"] . "\"" . (is_array($usergroups) && count($usergroups) && in_array($UG["gid"], $usergroups) ? " $checked = \"checked\"" : "") . " /></td><td>" . str_replace("{username}", $UG["title"], $UG["namestyle"]) . "</td>";
        $count++;
    }
}
$showusergroups .= "</tr></table>";
echo "\r\n\r\n" . $Message . "\r\n<form $method = \"post\" $action = \"index.php?do=upload_gift\">\r\n<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $colspan = \"2\" $align = \"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $align = \"right\">" . $Language[4] . "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" $name = \"amount\" $value = \"" . htmlspecialchars($amount) . "\" $size = \"10\" />\r\n\t\t\t<select $name = \"type\">\r\n\t\t\t\t<option $value = \"GB\"" . ($type == "GB" ? " $selected = \"selected\"" : "") . ">GB</option>\r\n\t\t\t\t<option $value = \"MB\"" . ($type == "MB" ? " $selected = \"selected\"" : "") . ">MB</option>\r\n\t\t\t</select></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt2\" $align = \"right\">" . $Language[11] . "</td>\r\n\t\t<td class=\"alt2\"><input $type = \"text\" $name = \"username\" $value = \"" . htmlspecialchars($username) . "\" $size = \"45\" /> <small>" . $Language[14] . "</small></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"alt1\" $valign = \"top\" $align = \"right\">" . $Language[6] . "</td>\r\n\t\t<td class=\"alt1\">" . $showusergroups . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" $align = \"right\"></td>\r\n\t\t<td class=\"tcat2\"><input $type = \"submit\" $value = \"" . $Language[7] . "\" /> <input $type = \"reset\" $value = \"" . $Language[8] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>";
function function_75()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function function_77()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        var_236("../index.php");
    }
}
function function_78($url)
{
    if (!headers_sent()) {
        header("Location: " . $url);
    } else {
        echo "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function function_76($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function function_88($bytes = 0)
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
function function_79($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}

?>