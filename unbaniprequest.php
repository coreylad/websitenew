<?php
define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "unbaniprequest.php");
require "./global.php";
define("UIR_VERSION", "by xam v.0.7");
$lang->load("unbaniprequest");
$userip = isset($_POST["ip"]) ? $_POST["ip"] : USERIPADDRESS;
($query = sql_query("SELECT id FROM loginattempts WHERE $ip = " . sqlesc($userip) . " AND $banned = 'yes' LIMIT 1")) || sqlerr(__FILE__, 26);
if (mysqli_num_rows($query) < 1) {
    stderr($lang->global["error"], $lang->unbaniprequest["error"]);
}
($query = sql_query("SELECT id FROM unbanrequests WHERE $ip = " . sqlesc($userip) . " OR $realip = " . sqlesc($userip) . " LIMIT 1")) || sqlerr(__FILE__, 32);
if (0 < mysqli_num_rows($query)) {
    stderr($lang->global["error"], $lang->unbaniprequest["error2"]);
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $error = [];
    $email = trim($_POST["email"]);
    $comment = trim($_POST["comment"]);
    if (!check_email($email)) {
        $error[] = $lang->unbaniprequest["error3"];
    }
    require_once INC_PATH . "/functions_EmailBanned.php";
    if (EmailBanned($email)) {
        $error[] = $lang->unbaniprequest["error4"];
    }
    if (strlen($comment) < 10) {
        $error[] = $lang->unbaniprequest["error5"];
    }
    if (count($error) == 0) {
        ($query = sql_query("INSERT INTO unbanrequests (ip, realip, email, comment, added) VALUES (" . sqlesc($userip) . ", " . sqlesc(USERIPADDRESS) . ", " . sqlesc($email) . ", " . sqlesc($comment) . ", NOW())")) || sqlerr(__FILE__, 59);
        $newid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"]) && $newid) {
            stdhead();
            echo "\r\n\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\" $align = \"center\">" . $lang->unbaniprequest["title"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>" . $lang->unbaniprequest["saved"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t";
            stdfoot();
            exit;
        }
        stderr($lang->global["error"], $lang->global["dberror"]);
    }
}
stdhead($lang->unbaniprequest["title"]);
show_unbaniprequest_errors();
echo "\r\n<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t<tr>\r\n\t\t<td class=\"thead\" $align = \"center\">" . $lang->unbaniprequest["title"] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td class=\"subheader\">" . $lang->unbaniprequest["info"] . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td>\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $lang->unbaniprequest["field1"] . "</legend>\r\n\t\t\t\t" . $lang->unbaniprequest["field2"] . "<br />\r\n\t\t\t\t<input $type = \"text\" $name = \"ip\" $value = \"" . htmlspecialchars_uni($userip) . "\" $size = \"30\" />\r\n\t\t\t</fieldset>\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $lang->unbaniprequest["field3"] . "</legend>\r\n\t\t\t\t" . $lang->unbaniprequest["field4"] . "<br />\r\n\t\t\t\t<input $type = \"text\" $name = \"email\" $value = \"" . (isset($email) ? htmlspecialchars_uni($email) : "") . "\" $size = \"30\" />\r\n\t\t\t</fieldset>\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $lang->unbaniprequest["field5"] . "</legend>\r\n\t\t\t\t" . $lang->unbaniprequest["field6"] . "<br />\r\n\t\t\t\t<textarea $name = \"comment\" $rows = \"3\" $cols = \"60\">" . (isset($comment) ? htmlspecialchars_uni($comment) : "") . "</textarea>\r\n\t\t\t</fieldset>\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>" . $lang->unbaniprequest["field7"] . "</legend>\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->unbaniprequest["field8"] . "\" /> <input $type = \"submit\" $value = \"" . $lang->unbaniprequest["field9"] . "\" />\r\n\t\t\t</fieldset>\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>\r\n";
stdfoot();
function show_unbaniprequest_errors()
{
    global $error;
    global $lang;
    if (0 < count($error)) {
        $errors = implode("<br />", $error);
        echo "\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->global["error"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<font $color = \"red\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $errors . "\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</font>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}

?>