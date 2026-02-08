<?php
define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "login.php");
require "./global.php";
include_once INC_PATH . "/functions_security.php";
include_once INC_PATH . "/functions_login.php";
failedloginscheck();
cur_user_check();
$lang->load("login");
define("L_VERSION", "1.6.1 by xam");
$do = isset($_GET["do"]) ? $_GET["do"] : (isset($_POST["do"]) ? $_POST["do"] : "");
if ($do == "activation_code") {
    function show_activation_errors()
    {
        global $activation_error;
        global $lang;
        if (0 < count($activation_error)) {
            $errors = implode("<br />", $activation_error);
            echo "\n\t\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t\t" . $lang->global["error"] . "\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<font $color = \"red\">\n\t\t\t\t\t\t\t<strong>\n\t\t\t\t\t\t\t\t" . $errors . "\n\t\t\t\t\t\t\t</strong>\n\t\t\t\t\t\t</font>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t\t</table>\n\t\t\t\t<br />\n\t\t\t";
        }
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $activation_error = [];
        $lang->load("signup");
        $email = isset($_POST["email"]) ? htmlspecialchars_uni($_POST["email"]) : "";
        require_once INC_PATH . "/functions_EmailBanned.php";
        if (empty($email) || !check_email($email)) {
            $activation_error[] = $lang->signup["validemail"];
        } else {
            if (EmailBanned($email)) {
                $activation_error[] = $lang->signup["validemail"];
            }
        }
        if (count($activation_error) == 0) {
            function safe_email($email)
            {
                return str_replace(["<", ">", "\\'", "\\\"", "\\\\"], "", $email);
            }
            $email = safe_email($email);
            $res = sql_query("SELECT id, username, secret FROM users WHERE `enabled` = 'yes' AND $status = 'pending' AND $email = " . sqlesc($email));
            if (!mysqli_num_rows($res)) {
                $activation_error[] = $lang->login["resend4"];
            } else {
                ($row = @mysqli_fetch_assoc($res)) || stderr($lang->global["error"], $lang->global["dberror"]);
                sql_query("REPLACE INTO ts_user_validation (editsecret, userid) VALUES (" . sqlesc($row["secret"]) . ", " . sqlesc($row["id"]) . ")") || sqlerr(__FILE__, 93);
                $body = sprintf($lang->signup["verifiyemailbody"], $row["username"], $BASEURL, $row["id"], md5($row["secret"]), $SITENAME);
                sent_mail($email, sprintf($lang->signup["verifiyemailsubject"], $SITENAME), $body, "signup", false);
                redirect("ok.php?$type = signup&$email = " . urlencode($email));
                exit;
            }
        }
    }
    stdhead($lang->login["resend"]);
    show_activation_errors();
    echo "\n\t<form $method = \"post\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\n\t<input $type = \"hidden\" $name = \"do\" $value = \"activation_code\" />\n\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\n\t\t<tr>\n\t\t\t<td $align = \"left\" class=\"thead\" $colspan = \"2\">\n\t\t\t\t" . $lang->login["resend"] . "\n\t\t\t</td>\n\t\t</tr>\n\t\t<tr>\n\t\t\t<td $align = \"right\" $width = \"60%\">\n\t\t\t\t<b>" . sprintf($lang->login["resend2"], $SITENAME) . "</b>\n\t\t\t</td>\n\t\t\t<td $align = \"left\" $width = \"40%\">\n\t\t\t\t<input $type = \"text\" $name = \"email\" $value = \"\" /> <input $type = \"submit\" $value = \"" . $lang->login["resend3"] . "\" />\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n\t</form>\n\t";
    stdfoot();
    exit;
}
logoutcookie();
stdhead($lang->login["head"]);
$username = isset($_GET["username"]) ? htmlspecialchars_uni($_GET["username"]) : "";
$error = "";
$returnto = "";
if (!empty($_GET["returnto"])) {
    $returnto = urldecode($_GET["returnto"]);
    if (!isset($_GET["nowarn"])) {
        $error = $lang->login["loginfirst"];
    }
} else {
    if (!empty($_GET["error"])) {
        if ($_GET["error"] == 1) {
            $error = sprintf($lang->login["error1"], remaining());
        } else {
            if ($_GET["error"] == 2) {
                $error = sprintf($lang->global["invalidimagecode"], remaining());
            } else {
                if ($_GET["error"] == 3) {
                    $error = $lang->global["dontleavefieldsblank"];
                } else {
                    if ($_GET["error"] == 4) {
                        $error = sprintf($lang->global["incorrectlogin"], "<a $href = \"" . $BASEURL . "/recover.php\">");
                    }
                }
            }
        }
    }
}
if (!empty($error)) {
    echo "\n\t<table $border = \"0\" $cellpadding = \"5\" $width = \"100%\">\n\t\t<tr>\n\t\t\t<td class=\"thead\">" . $lang->global["error"] . "</td>\n\t\t</tr>\n\t\t<tr>\n\t\t\t<td>\n\t\t\t\t" . $error . "\n\t\t\t</td>\n\t\t</tr>\n\t</table>\n\t<br />";
}
echo "\n<form $method = \"post\" $action = \"takelogin.php\">\n<table $border = \"0\" $cellpadding = \"5\" $width = \"100%\">\n<tr><td $colspan = \"2\" class=\"thead\" $align = \"center\">\n" . $SITENAME . " " . $lang->login["head"] . "\n</td></tr>\n<tr>\n<td class=\"rowhead\">" . $lang->login["username"] . "</td>\n<td $align = \"left\"><input $type = \"text\" $name = \"username\" $id = \"usernameloginphp\" class=\"inputUsername\" $value = \"" . $username . "\" /></td>\n</tr>\n<tr>\n<td class=\"rowhead\">" . $lang->login["password"] . "</td>\n<td $align = \"left\"><input $type = \"password\" $name = \"password\" class=\"inputPassword\" $value = \"\" /></td>\n</tr>";
show_image_code();
echo "\n<tr><td class=\"rowhead\"><input $type = \"checkbox\" class=\"none\" $name = \"logout\" $style = \"vertical-align: middle;\" $value = \"yes\" />" . $lang->login["logout15"] . "\n<td $align = \"left\"><input $type = \"submit\" $value = \"" . $lang->login["login"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->login["reset"] . "\" /></td></tr>\n";
if (!empty($returnto)) {
    echo "<input $type = \"hidden\" $name = \"returnto\" $value = \"" . htmlspecialchars_uni($returnto) . "\" />\n";
}
echo "\n</table></form>\n" . $lang->login["footer"];
stdfoot();

?>