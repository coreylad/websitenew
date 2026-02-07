<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . getStaffLanguage() . "/add_user.lang");
$Message = "";
$email = "";
$username = "";
$password = "";
$usergroup = "1";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $email = trim($_POST["email"]);
    $usergroup = intval($_POST["usergroup"]);
    if ($username && $password && $email && $usergroup) {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM users WHERE $username = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "' OR $email = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $email) . "'");
        if (0 < mysqli_num_rows($query)) {
            $Message = showAlertError($Language[1]);
        } else {
            if (preg_match("|[^a-z\\|A-Z\\|0-9]|", $username)) {
                $Message = showAlertError($Language[2]);
            } else {
                if (!preg_match("#^[a-z0-9.!\\#\$%&'*+-/=?^_`{|}~]+@([0-9.]+|([^\\s'\"<>@,;]+\\.+[a-z]{2,6}))\$#si", $email)) {
                    $Message = showAlertError($Language[3]);
                } else {
                    $secret = generateSecret();
                    $passhash = md5($secret . $password . $secret);
                    $SysMsg = str_replace(["{1}", "{2}"], [$username, $_SESSION["ADMIN_USERNAME"]], $Language[14]);
                    $modcomment = gmdate("Y-m-d") . " - " . trim($SysMsg) . "\n";
                    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO users (status, username, passhash, secret, email, added, usergroup, modcomment) VALUES ('confirmed', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "', '" . $passhash . "', '" . $secret . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $email) . "', NOW(), '" . $usergroup . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $modcomment) . "')");
                    $userid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                    if ($userid) {
                        logStaffAction($SysMsg);
                        redirectTo("index.php?do=edit_user&$username = " . htmlspecialchars($username));
                    } else {
                        $Message = showAlertError($Language[13]);
                    }
                }
            }
        }
    } else {
        $Message = showAlertError($Language[4]);
    }
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE u.$id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
$LoggedAdminDetails = mysqli_fetch_assoc($query);
$showusergroups = "\r\n<select $name = \"usergroup\" $tabindex = \"1\" class=\"bginput\">";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, cansettingspanel, canstaffpanel, issupermod FROM usergroups ORDER by disporder ASC");
while ($UG = mysqli_fetch_assoc($query)) {
    if (!($UG["cansettingspanel"] == "yes" && $LoggedAdminDetails["cansettingspanel"] != "yes" || $UG["canstaffpanel"] == "yes" && $LoggedAdminDetails["canstaffpanel"] != "yes" || $UG["issupermod"] == "yes" && $LoggedAdminDetails["issupermod"] != "yes")) {
        $showusergroups .= "\r\n\t\t<option $value = \"" . $UG["gid"] . "\"" . ($usergroup == $UG["gid"] ? " $selected = \"selected\"" : "") . ">" . $UG["title"] . "</option>";
    }
}
$showusergroups .= "\r\n</select>";
echo "<form $action = \"";
echo $_SERVER["SCRIPT_NAME"];
echo "?do=add_user\" $method = \"post\">\r\n";
echo $Message;
echo "<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\"><b>";
echo $Language[6];
echo "</b></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[7];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"username\" $value = \"";
echo htmlspecialchars($username);
echo "\" $size = \"35\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[8];
echo "</td>\r\n\t\t<td class=\"alt2\"><input $type = \"password\" class=\"bginput\" $name = \"password\" $value = \"\" $size = \"35\" $dir = \"ltr\" $tabindex = \"1\" $autocomplete = \"off\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt1\" $align = \"right\">";
echo $Language[9];
echo "</td>\r\n\t\t<td class=\"alt1\"><input $type = \"text\" class=\"bginput\" $name = \"email\" $value = \"";
echo htmlspecialchars($email);
echo "\" $size = \"35\" $dir = \"ltr\" $tabindex = \"1\" /></td>\r\n\t</tr>\r\n\t<tr $valign = \"top\">\r\n\t\t<td class=\"alt2\" $align = \"right\">";
echo $Language[10];
echo "</td>\r\n\t\t<td class=\"alt2\">";
echo $showusergroups;
echo "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\" $align = \"right\"></td>\r\n\t\t<td class=\"tcat2\">\r\n\t\t\t<input $type = \"submit\" class=\"button\" $tabindex = \"1\" $value = \"";
echo $Language[11];
echo "\" $accesskey = \"s\" />\r\n\t\t\t<input $type = \"reset\" class=\"button\" $tabindex = \"1\" $value = \"";
echo $Language[12];
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
function generateSecret($length = 20)
{
    $var_308 = ["a", "A", "b", "B", "c", "C", "d", "D", "e", "E", "f", "F", "g", "G", "h", "H", "i", "I", "j", "J", "k", "K", "l", "L", "m", "M", "n", "N", "o", "O", "p", "P", "q", "Q", "r", "R", "s", "S", "t", "T", "u", "U", "v", "V", "w", "W", "x", "X", "y", "Y", "z", "Z", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
    $var_309 = "";
    for ($i = 1; $i <= $length; $i++) {
        $ch = rand(0, count($var_308) - 1);
        $var_309 .= $var_308[$ch];
    }
    return $var_309;
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}

?>