<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "recover.php");
require "./global.php";
include_once INC_PATH . "/functions_security.php";
failedloginscheck("Recover");
$lang->load("recover");
define("R_VERSION", "1.3.4 by xam");
if (isset($CURUSER) && 0 < $CURUSER["id"]) {
    stderr($lang->global["error"], $lang->recover["error"]);
}
$act = isset($_GET["act"]) ? $_GET["act"] : (isset($_POST["act"]) ? $_POST["act"] : "");
if ($act == "manual") {
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $_GET["id"] = $_POST["id"];
        $_GET["secret"] = $_POST["secret"];
    } else {
        $lang->load("confirm");
        $form = "\n\t\t<form method=\"post\" action=\"" . $_SERVER["SCRIPT_NAME"] . "?act=manual\">\n\t\t<input type=\"hidden\" name=\"act\" value=\"manual\" />\n\t\t<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">\n\t\t\t<tr>\n\t\t\t\t<td colspan=\"2\" class=\"thead\">" . $lang->confirm["manual1"] . "</td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t\t<td colspan=\"2\" class=\"subheader\">" . $lang->confirm["manual4"] . "</td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t\t<td align=\"right\">" . $lang->confirm["manual2"] . "</td>\n\t\t\t\t<td align=\"left\"><input type=\"text\" name=\"id\" value=\"\" size=\"32\" /></td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t\t<td align=\"right\">" . $lang->confirm["manual3"] . "</td>\n\t\t\t\t<td align=\"left\"><input type=\"text\" name=\"secret\" value=\"\" size=\"32\" /></td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"" . $lang->confirm["manual5"] . "\" /></td>\n\t\t\t</tr>\n\t\t</table>\n\t\t</form>\n\t\t";
        stdhead($lang->confirm["manual1"]);
        echo $form;
        stdfoot();
        exit;
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($act)) {
    function safe_email($email)
    {
        return str_replace(["<", ">", "\\'", "\\\"", "\\\\"], "", $email);
    }
    function unesc($x)
    {
        if (get_magic_quotes_gpc()) {
            return stripslashes($x);
        }
        return $x;
    }
    if (isCaptchaEnabled()) {
        check_code(isset($_POST["imagestring"]) ? $_POST["imagestring"] : "", "recover.php", true);
    }
    $email = unesc(htmlspecialchars(trim($_POST["email"])));
    $email = safe_email($email);
    if (!$email) {
        failedlogins($lang->global["dontleavefieldsblank"], true);
    }
    if (!check_email($email)) {
        failedlogins($lang->recover["error2"], true);
    }
    ($res = sql_query("SELECT id, passhash, email FROM users WHERE email=" . sqlesc($email) . " LIMIT 1")) || sqlerr(__FILE__, 100);
    ($arr = mysqli_fetch_assoc($res)) || failedlogins($lang->recover["error3"], true);
    $sec = mksecret();
    sql_query("DELETE FROM ts_user_validation WHERE userid = " . sqlesc($arr["id"]));
    sql_query("INSERT INTO ts_user_validation (editsecret, userid) VALUES (" . sqlesc($sec) . ", " . sqlesc($arr["id"]) . ")") || sqlerr(__FILE__, 104);
    if (!mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
        stderr($lang->global["error"], $lang->global["dberror"]);
    }
    $hash = md5($sec . $email . $arr["passhash"] . $sec);
    $body = sprintf($lang->recover["body"], $email, USERIPADDRESS, $BASEURL, $arr["id"], $hash, $SITENAME, "recover");
    sent_mail($arr["email"], sprintf($lang->recover["subject"], $SITENAME), $body, "recover");
    stdhead($lang->recover["head"]);
    stdmsg($lang->recover["head"], $lang->recover["msent"], true, "success");
    stdfoot();
    exit;
}
if (isset($_GET["id"]) && isset($_GET["secret"])) {
    $id = (int) $_GET["id"];
    $md5 = trim($_GET["secret"]);
    if (empty($id) || !is_valid_id($id) || strlen($md5) != 32) {
        stderr($lang->global["error"], $lang->recover["invalidcodeorid"]);
    }
    $res = sql_query("SELECT u.username, u.email, u.passhash, e.editsecret FROM users u LEFT JOIN ts_user_validation e ON (u.id=e.userid) WHERE u.id = " . sqlesc($id));
    ($arr = mysqli_fetch_assoc($res)) || stderr($lang->global["error"], $lang->global["nouserid"]);
    $email = $arr["email"];
    $sec = hash_pad($arr["editsecret"]);
    if (preg_match("/^ *\$/s", $sec)) {
        stderr($lang->global["error"], $lang->recover["invalidcodeorid"]);
    }
    if ($md5 != md5($sec . $email . $arr["passhash"] . $sec)) {
        stderr($lang->global["error"], $lang->recover["invalidcode3"]);
    }
    $newpassword = mksecret(10);
    $sec = mksecret();
    $newpasshash = md5($sec . $newpassword . $sec);
    sql_query("UPDATE users SET secret=" . sqlesc($sec) . ", passhash=" . sqlesc($newpasshash) . " WHERE id=" . sqlesc($id)) || stderr($lang->global["error"], $lang->global["dberror"]);
    if (!mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
        stderr($lang->global["error"], $lang->global["dberror"]);
    }
    sql_query("DELETE FROM ts_user_validation WHERE userid = " . sqlesc($id));
    $body = sprintf($lang->recover["body2"], $arr["username"], $newpassword, $BASEURL, $SITENAME);
    sent_mail($email, sprintf($lang->recover["subject2"], $SITENAME), $body, "details");
} else {
    stdhead($lang->recover["head"]);
    $error = "";
    if (!empty($_GET["error"])) {
        if ($_GET["error"] == 1) {
            $error = "<tr><td colspan=\"2\"><div class=\"error\">" . sprintf($lang->recover["errortype1"], remaining()) . "</div></td></tr>";
        } else {
            if ($_GET["error"] == 2) {
                $error = "<tr><td colspan=\"2\"><div class=\"error\">" . sprintf($lang->global["invalidimagecode"], remaining()) . "</div></td></tr>";
            }
        }
    }
    echo "\t\n\t<form method=\"post\" action=\"" . $_SERVER["SCRIPT_NAME"] . "\" name=\"recover\" onsubmit=\"document.forms['recover'].elements['send'].disabled=true; document.forms['recover'].elements['send'].value='" . $lang->global["pleasewait"] . "';\">\t\n\t<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n\t\t<tr>\n\t\t\t<td align=\"center\" class=\"thead\">" . $lang->recover["head"] . "</td>\n\t\t</tr>\n\t\t<tr>\n\t\t\t<td>" . sprintf($lang->recover["info"], $maxloginattempts) . "</td>\n\t\t</tr>\n\t</table>\n\t<br />\n\t<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\t\t\n\t<tr>\n\t\t<td colspan=\"2\" align=\"center\" class=\"thead\">" . $lang->recover["head"] . "</td>\n\t</tr>";
    if (isset($error)) {
        echo $error;
    }
    echo "\n\t<tr>\n\t\t<td class=\"rowhead\" style=\"vertical-align: middle;\">" . $lang->recover["fieldemail"] . "</td>\n\t\t<td><input type=\"text\" size=\"26\" name=\"email\" id=\"email\" class=\"inputUsername\" />\n\t\t" . ($iv == "no" ? " \n\t\t<input type=\"submit\" value=\"" . $lang->global["buttonrecover"] . "\" class=\"button\" name=\"send\" />" : "") . "</td>\n\t</tr>";
    show_image_code(true, $lang->global["buttonrecover"], "name=\"send\" ");
    echo "\n\t</table>\n\t</form>\n\t";
    stdfoot();
}
function hash_pad($hash)
{
    return str_pad($hash, 20);
}

?>