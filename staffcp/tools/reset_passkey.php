<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . function_75() . "/reset_passkey.lang");
$Message = "";
$username = isset($_GET["username"]) ? trim($_GET["username"]) : "";
$reason = "";
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $username = trim($_POST["username"]);
    $reason = trim($_POST["reason"]);
    if ($username && $reason) {
        $Query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users LEFT JOIN usergroups g ON (users.usergroup=g.gid) WHERE username = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'");
        if (mysqli_num_rows($Query) == 0) {
            $Message = function_76($Language[2]);
        } else {
            $User = mysqli_fetch_assoc($Query);
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE u.id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
            $LoggedAdminDetails = mysqli_fetch_assoc($query);
            if ($User["cansettingspanel"] == "yes" && $LoggedAdminDetails["cansettingspanel"] != "yes" || $User["canstaffpanel"] == "yes" && $LoggedAdminDetails["canstaffpanel"] != "yes" || $User["issupermod"] == "yes" && $LoggedAdminDetails["issupermod"] != "yes") {
                $Message = function_76($Language[12]);
            } else {
                $SysMsg = str_replace(["{1}", "{2}"], [$username, $_SESSION["ADMIN_USERNAME"]], $Language[9]);
                $modcomment = gmdate("Y-m-d") . " - " . trim($SysMsg) . "\n";
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET torrent_pass = '', modcomment = CONCAT('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $modcomment) . "', modcomment) WHERE username = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $username) . "'");
                if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                    function_79($SysMsg);
                    var_237($User["id"], str_replace(["{1}", "{2}"], [$_SESSION["ADMIN_USERNAME"], $reason], $Language[10]), $Language[6]);
                    $Message = function_76($Language[3]);
                } else {
                    $Message = function_76($Language[11]);
                }
            }
        }
    } else {
        $Message = function_76($Language[1]);
    }
}
echo "<form action=\"";
echo $_SERVER["SCRIPT_NAME"];
echo "?do=reset_passkey\" method=\"post\" name=\"reset_passkey\">\r\n";
echo $Message;
echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" align=\"center\" colspan=\"2\"><b>";
echo $Language[6];
echo "</b></td>\r\n\t</tr>\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt1\" align=\"right\">";
echo $Language[4];
echo "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" class=\"bginput\" name=\"username\" value=\"";
echo htmlspecialchars($username);
echo "\" size=\"35\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\t\r\n\t<tr valign=\"top\">\r\n\t\t<td class=\"alt2\" align=\"right\">";
echo $Language[5];
echo "</td>\r\n\t\t<td class=\"alt2\"><input type=\"text\" class=\"bginput\" name=\"reason\" value=\"";
echo htmlspecialchars($reason);
echo "\" size=\"35\" dir=\"ltr\" tabindex=\"1\" /></td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\">\t\r\n\t\t\t<input type=\"submit\" class=\"button\" tabindex=\"1\" value=\"";
echo $Language[6];
echo "\" accesskey=\"s\" />\r\n\t\t\t<input type=\"reset\" class=\"button\" tabindex=\"1\" value=\"";
echo $Language[7];
echo "\" accesskey=\"r\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>";
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
        echo "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\twindow.location.href=\"" . $url . "\";\r\n\t\t</script>\r\n\t\t<noscript>\r\n\t\t\t<meta http-equiv=\"refresh\" content=\"0;url=" . $url . "\" />\r\n\t\t</noscript>";
    }
    exit;
}
function function_76($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function function_79($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function function_80($receiver = 0, $msg = "", $subject = "", $sender = 0, $saved = "no", $location = "1", $unread = "yes")
{
    if (!($sender != 0 && !$sender || !$receiver || empty($msg))) {
        mysqli_query($GLOBALS["DatabaseConnect"], "\r\n\t\t\t\t\tINSERT INTO messages \r\n\t\t\t\t\t\t(sender, receiver, added, subject, msg, unread, saved, location)\r\n\t\t\t\t\t\tVALUES \r\n\t\t\t\t\t\t('" . $sender . "', '" . $receiver . "', NOW(), '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $subject) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $msg) . "', '" . $unread . "', '" . $saved . "', '" . $location . "')\r\n\t\t\t\t\t");
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET pmunread = pmunread + 1 WHERE id = '" . $receiver . "'");
    }
}

?>