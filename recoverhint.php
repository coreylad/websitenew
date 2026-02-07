<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "recoverhint.php");
require "./global.php";
include_once INC_PATH . "/functions_security.php";
failedloginscheck("Recover");
$lang->load("recover");
define("RH_VERSION", "1.2.3 by xam");
$act = isset($_GET["act"]) ? (int) $_GET["act"] : "0";
if ($act == "0") {
    stdhead($lang->recover["head"]);
    if (!empty($_GET["error"])) {
        if (isset($_GET["error"]) && $_GET["error"] == "1") {
            $error = "<tr><td colspan=\"2\"><div class=\"error\">" . sprintf($lang->recover["errortype3"], remaining()) . "</div></td></tr>";
        } else {
            if (isset($_GET["error"]) && $_GET["error"] == "2") {
                $error = "<tr><td colspan=\"2\"><div class=\"error\">" . sprintf($lang->global["invalidimagecode"], remaining()) . "</div></td></tr>";
            }
        }
    }
    echo "\r\n\t\t<form method=\"post\" action=\"recoverhint.php?act=1\" name=\"recover\" onsubmit=\"document.forms['recover'].elements['send'].disabled=true; document.forms['recover'].elements['send'].value='" . $lang->global["pleasewait"] . "';\">\r\n\t\t<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td align=\"center\" class=\"thead\">" . $lang->recover["head"] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>" . sprintf($lang->recover["info2"], $maxloginattempts) . "</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t<br />\r\n\t\t<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td colspan=\"2\" align=\"center\" class=\"thead\">" . $lang->recover["head"] . "</td>\r\n\t\t\t</tr>";
    if (isset($error)) {
        echo $error;
    }
    echo "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"rowhead\" style=\"vertical-align: middle;\">" . $lang->recover["fieldusername"] . "</td>\r\n\t\t\t\t<td><input class=\"inputUsername\" type=\"text\" size=\"30\" name=\"username\" /> " . ($iv == "no" ? " <input type=\"submit\" value=\"" . $lang->global["buttonrecover"] . "\" name=\"send\" class=\"button\" />" : "") . "</td>\r\n\t\t\t</tr>";
    show_image_code(true, $lang->global["buttonrecover"], "name=\"send\" ");
    echo "\r\n\t\t</table>\r\n\t\t</form>";
    stdfoot();
    exit;
}
if ($act == "1") {
    if (isCaptchaEnabled()) {
        check_code(isset($_POST["imagestring"]) ? $_POST["imagestring"] : "", "recoverhint.php", true);
    }
    $username = isset($_POST["username"]) && $_POST["username"] ? htmlspecialchars_uni($_POST["username"]) : "";
    if (empty($username) || !validusername($username)) {
        failedlogins("silent", false, false);
        stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
        exit;
    }
    staffnamecheck($username);
    ($res = sql_query("SELECT id, username FROM users WHERE username=" . sqlesc($username) . " AND status = 'confirmed' AND enabled = 'yes' LIMIT 1")) || sqlerr(__FILE__, 118);
    if (1 <= mysqli_num_rows($res)) {
        $arr = mysqli_fetch_assoc($res);
        $securehash = securehash($arr["id"] . $arr["username"]);
        setcookie("securehash_recoverhint", $securehash, TIMENOW + 3600);
        redirect("recoverhint.php?act=3&id=" . $arr["id"] . "&username=" . $username, $lang->global["redirect"]);
    } else {
        stdhead($lang->recover["head"]);
        stdmsg($lang->global["error"], $lang->global["nousername"]);
        failedlogins("silent", false, false);
        stdfoot();
    }
    exit;
}
if ($act == "3") {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = (int) $_GET["id"];
        int_check($id);
        $answer = isset($_POST["answer"]) && $_POST["answer"] ? htmlspecialchars_uni($_POST["answer"]) : "";
        if (!$answer) {
            failedlogins("silent", false, false);
            stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
        }
        $res = sql_query("SELECT id, username, status, enabled FROM users WHERE id = " . sqlesc($id));
        ($user = mysqli_fetch_assoc($res)) || stderr($lang->global["error"], $lang->global["nouserid"]);
        if (empty($user["username"]) || !validusername($user["username"])) {
            failedlogins("silent", false, false);
            stderr($lang->global["error"], $lang->global["dontleavefieldsblank"]);
            exit;
        }
        staffnamecheck($user["username"]);
        $securehash = securehash($user["id"] . $user["username"]);
        if ($_COOKIE["securehash_recoverhint"] != $securehash || empty($_COOKIE["securehash_recoverhint"]) || empty($securehash)) {
            failedlogins("silent", false, false);
            print_no_permission();
            exit;
        }
        $query = sql_query("SELECT passhint, hintanswer FROM ts_secret_questions WHERE userid = " . sqlesc($user["id"]));
        $Array = mysqli_fetch_assoc($query);
        if ($Array && is_array($Array)) {
            $user = array_merge($user, $Array);
        } else {
            $user = false;
        }
        if (md5($answer) != $user["hintanswer"] || empty($user["hintanswer"])) {
            failedlogins("silent", false, false);
            stderr($lang->global["error"], $lang->recover["invalidanswer"]);
        } else {
            if (!$user || $user["status"] == "pending" || $user["enabled"] == "no" || empty($user["passhint"]) || empty($user["hintanswer"])) {
                failedlogins("silent", false, false);
                stderr($lang->global["error"], $lang->global["nouserid"]);
                exit;
            }
            $newpassword = mksecret(10);
            $sec = mksecret();
            $newpasshash = md5($sec . $newpassword . $sec);
            sql_query("UPDATE users SET secret=" . sqlesc($sec) . ", passhash=" . sqlesc($newpasshash) . " WHERE id=" . sqlesc($id));
            if (!mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                stderr($lang->global["error"], $lang->global["dberror"]);
            }
            sql_query("DELETE FROM ts_user_validation WHERE userid = " . sqlesc($id));
            stderr($lang->recover["generated1"], sprintf($lang->recover["generated2"], $newpassword, $BASEURL), false);
        }
    } else {
        $id = (int) $_GET["id"];
        $username = isset($_GET["username"]) ? htmlspecialchars_uni($_GET["username"]) : "";
        staffnamecheck($username);
        if (empty($id) || !is_valid_id($id) || empty($username) || !validusername($username)) {
            failedlogins("silent", false, false);
            print_no_permission();
            exit;
        }
        $res = sql_query("SELECT id, username, status, enabled FROM users WHERE id = " . sqlesc($id) . " AND username = " . sqlesc($username));
        ($user = mysqli_fetch_assoc($res)) || stderr($lang->global["error"], $lang->global["nouserid"]);
        $securehash = securehash($user["id"] . $user["username"]);
        if (!isset($_COOKIE["securehash_recoverhint"]) || $_COOKIE["securehash_recoverhint"] != $securehash || empty($_COOKIE["securehash_recoverhint"]) || empty($securehash)) {
            failedlogins("silent", false, false);
            print_no_permission();
            exit;
        }
        $query = sql_query("SELECT passhint, hintanswer FROM ts_secret_questions WHERE userid = " . sqlesc($user["id"]));
        $Array = mysqli_fetch_assoc($query);
        if ($Array && is_array($Array)) {
            $user = array_merge($user, $Array);
        } else {
            $user = false;
        }
        if (!$user || $user["status"] == "pending" || $user["enabled"] == "no" || empty($user["passhint"]) || empty($user["hintanswer"])) {
            failedlogins("silent", false, false);
            stderr($lang->global["error"], $lang->global["nouserid"]);
            exit;
        }
        stdhead($lang->recover["head"]);
        echo "\r\n\t\t<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"6\" wpar=\"nowrap\">\r\n\t\t<form method=\"POST\" action=\"recoverhint.php?act=3&id=" . $id . "\">\r\n\t\t<tr><td align=\"center\" class=\"thead\" colspan=\"2\">" . $lang->recover["head"] . "</td></tr>\r\n\t\t<tr><td colspan=\"2\">" . $lang->recover["info3"] . "</td></tr>\r\n\t\t<tr><td class=\"rowhead\">" . $lang->recover["sq"] . "</td>";
        $HF[0] = "/1/";
        $HF[1] = "/2/";
        $HF[2] = "/3/";
        $HR[0] = "<font color=blue>" . $lang->recover["hr0"] . "</font>";
        $HR[1] = "<font color=blue>" . $lang->recover["hr1"] . "</font>";
        $HR[2] = "<font color=blue>" . $lang->recover["hr2"] . "</font>";
        $passhint = preg_replace($HF, $HR, $user["passhint"]);
        echo "<td>" . $passhint . "</td>";
        echo "<tr><td class=\"rowhead\">" . $lang->recover["ha"] . "</td>";
        echo "<td><input type=\"text\" size=\"40\" name=\"answer\" id=\"specialboxn\" /> <input type=\"submit\" value=\"" . $lang->global["buttonrecover"] . "\" class=\"button\" /></td></tr>";
        echo "</form></table>";
        stdfoot();
    }
}
function staffnamecheck($username)
{
    global $rootpath;
    global $lang;
    $username = strtolower($username);
    $query = sql_query("SELECT id FROM users WHERE username = " . sqlesc($username));
    if (0 < mysqli_num_rows($query)) {
        $res = mysqli_fetch_assoc($query);
        $userid = intval($res["id"]);
    } else {
        stderr($lang->global["error"], $lang->global["nousername"]);
    }
    $Query = sql_query("SELECT `content` FROM `ts_config` WHERE configname = \"STAFFTEAM\"");
    $Result = mysqli_fetch_assoc($Query);
    $results = explode(",", $Result["content"]);
    if (in_array($username . ":" . $userid, $results)) {
        stderr($lang->global["error"], $lang->recover["denyaccessforstaff"], false);
        exit;
    }
}
function validusername($username)
{
    if (!preg_match("|[^a-z\\|A-Z\\|0-9]|", $username)) {
        return true;
    }
    return false;
}

?>