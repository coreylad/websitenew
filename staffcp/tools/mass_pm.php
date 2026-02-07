<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

var_235();
$Language = file("languages/" . function_75() . "/mass_pm.lang");
$Message = "";
$subject = "";
$msg = "";
$sender = "0";
$usergroups = [];
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $subject = isset($_POST["subject"]) ? trim($_POST["subject"]) : "";
    $msg = isset($_POST["msg"]) ? trim($_POST["msg"]) : "";
    $sender = isset($_POST["sender"]) ? intval($_POST["sender"]) : "0";
    $usergroups = isset($_POST["usergroups"]) ? $_POST["usergroups"] : [];
    if ($subject && $msg && $usergroups && $usergroups[0] != "") {
        $work = implode(",", $usergroups);
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM users WHERE usergroup IN (" . $work . ")");
        $total = mysqli_num_rows($query);
        if (0 < $total) {
            while ($User = mysqli_fetch_assoc($query)) {
                var_237($User["id"], $msg, $subject, $sender);
            }
            $Message = function_76(str_replace("{1}", number_format($total), $Language[12]));
            function_79(str_replace(["{1}", "{2}", "{3}"], [$_SESSION["ADMIN_USERNAME"], $work, $subject], $Language[13]));
        }
    } else {
        $Message = function_76($Language[3]);
    }
}
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT u.id, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE u.id = '" . $_SESSION["ADMIN_ID"] . "' LIMIT 1");
$LoggedAdminDetails = mysqli_fetch_assoc($query);
$showusergroups = "";
$query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, cansettingspanel, canstaffpanel, issupermod, namestyle FROM usergroups ORDER by disporder ASC");
while ($UG = mysqli_fetch_assoc($query)) {
    $showusergroups .= "\r\n\t<div style=\"margin-bottom: 3px;\">\r\n\t\t<label><input type=\"checkbox\" name=\"usergroups[]\" value=\"" . $UG["gid"] . "\"" . (in_array($UG["gid"], $usergroups) ? " checked=\"checked\"" : "") . " style=\"vertical-align: middle;\" /> " . strip_tags(str_replace("{username}", $UG["title"], $UG["namestyle"]), "<b><span><strong><em><i><u>") . "</label>\r\n\t</div>";
}
echo function_90(2, "exact", "textarea1") . "\r\n\r\n" . $Message . "\r\n<form method=\"post\" action=\"index.php?do=mass_pm\">\r\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"mainTable\">\r\n\t<tr>\r\n\t\t<td class=\"tcat\" colspan=\"2\" align=\"center\">\r\n\t\t\t" . $Language[2] . "\r\n\t\t</td>\r\n\t</tr>\r\n\r\n\t<tr>\r\n\t\t<td class=\"alt1\" valign=\"top\" style=\"width: 155px;\">" . $Language[6] . "</td>\r\n\t\t<td class=\"alt1\">\r\n\t\t\t<select name=\"sender\">\r\n\t\t\t\t<option value=\"0\"" . ($sender == "0" ? " selected=\"selected\"" : "") . ">" . $Language[7] . "</option>\r\n\t\t\t\t<option value=\"" . $_SESSION["ADMIN_ID"] . "\"" . ($sender == $_SESSION["ADMIN_ID"] ? " selected=\"selected\"" : "") . ">" . $Language[8] . " (" . $_SESSION["ADMIN_USERNAME"] . ")</option>\r\n\t\t\t</select>\r\n\t\t</td>\r\n\t</tr>\r\n\r\n\t<tr>\r\n\t\t<td class=\"alt1\">" . $Language[4] . "</td>\r\n\t\t<td class=\"alt1\"><input type=\"text\" name=\"subject\" value=\"" . htmlspecialchars($subject) . "\" style=\"width: 99%;\" /></td>\r\n\t</tr>\r\n\t\r\n\t<tr>\r\n\t\t<td class=\"alt1\" valign=\"top\">" . $Language[5] . "</td>\r\n\t\t<td class=\"alt1\"><textarea name=\"msg\" id=\"textarea1\" style=\"width: 100%; height: 200px;\">" . htmlspecialchars($msg) . "</textarea>\r\n\t\t<p><a href=\"javascript:toggleEditor('textarea1');\"><img src=\"images/tool_refresh.png\" border=\"0\" /></a></p></td>\r\n\t</tr>\r\n\t\r\n\t<tr>\r\n\t\t<td class=\"alt1\" valign=\"top\">" . $Language[9] . "</td>\r\n\t\t<td class=\"alt1\">" . $showusergroups . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td  class=\"tcat2\"></td>\r\n\t\t<td class=\"tcat2\"><input type=\"submit\" value=\"" . $Language[10] . "\" /> <input type=\"reset\" value=\"" . $Language[11] . "\" /></td>\r\n\t</tr>\r\n</table>\r\n</form>";
function function_90($type = 1, $mode = "textareas", $elements = "")
{
    define("EDITOR_TYPE", $type);
    define("TINYMCE_MODE", $mode);
    define("TINYMCE_ELEMENTS", $elements);
    define("WORKPATH", "./../scripts/");
    define("TINYMCE_EMOTIONS_URL", "./../tinymce_emotions.php");
    var_281 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE configname = 'MAIN'");
    var_20 = mysqli_fetch_assoc(var_281);
    var_27 = unserialize(var_20["content"]);
    var_282 = var_27["pic_base_url"];
    unset(var_27);
    define("PIC_BASEURL", var_282);
    ob_start();
    include "./../tinymce.php";
    var_81 = ob_get_contents();
    ob_end_clean();
    return var_81;
}
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
        mysqli_query($GLOBALS["DatabaseConnect"], "\r\n\t\t\t\t\tINSERT INTO messages\r\n\t\t\t\t\t\t(sender, receiver, added, subject, msg, unread, saved, location)\r\n\t\t\t\t\t\tVALUES\r\n\t\t\t\t\t\t('" . $sender . "', '" . $receiver . "', NOW(), '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $subject) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $msg) . "', '" . $unread . "', '" . $saved . "', '" . $location . "')\r\n\t\t\t\t\t");
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE users SET pmunread = pmunread + 1 WHERE id = '" . $receiver . "'");
    }
}

?>