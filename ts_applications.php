<?php
define("THIS_SCRIPT", "ts_applications.php");
require "./global.php";
define("TSA_VERSION", "0.3 by xam");
$lang->load("ts_applications");
// Refactored for PSR-12 compliance and descriptive naming
$applicationAction = isset($_GET["do"]) ? $_GET["do"] : (isset($_POST["do"]) ? $_POST["do"] : "");
$userId = 0 + $CURUSER["id"];
if (!isset($CURUSER) || $CURUSER["id"] == 0) {
    print_no_permission();
}
($query = sql_query("SELECT rid FROM ts_application_requests WHERE `uid` = '" . $userId . "' AND $status = '0'")) || sqlerr(__FILE__, 59);
if (0 < mysqli_num_rows($query)) {
    stderr($lang->global["error"], $lang->ts_applications["error5"]);
}
if ($applicationAction == "save_apply" && ($applicationId = intval($_GET["aid"])) && is_valid_id($applicationId)) {
    $url = trim($_POST["url"]);
    $info = trim($_POST["info"]);
    if (strlen($url) < 5 || !preg_match("/^[a-zA-Z]+[:\\/\\/]+[A-Za-z0-9\\-_]+\\.+[A-Za-z0-9\\.\\/%&=\\?\\-_]+\$/i", $url) || $url == "http://") {
        $errors[] = $lang->ts_applications["error4"];
    }
    if (strlen($info) < 5) {
        $errors[] = str_replace(":", "", $lang->ts_applications["info"]);
    }
    if (is_array($errors) && 0 < count($errors)) {
        $applicationAction = "apply";
        $_POST["apply"][$applicationId] = "yes";
    } else {
        sql_query("INSERT INTO ts_application_requests (aid, uid, url, info, created) VALUES ('" . $applicationId . "', '" . $userId . "', " . sqlesc($url) . ", " . sqlesc($info) . ", '" . TIMENOW . "')") || sqlerr(__FILE__, 87);
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            ($query = sql_query("SELECT u.id, g.gid FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE g.$cansettingspanel = 'yes' AND u.$enabled = 'yes'")) || sqlerr(__FILE__, 90);
            if (0 < mysqli_num_rows($query)) {
                $subject = $lang->ts_applications["subject"];
                $msg = sprintf($lang->ts_applications["msg"], "[URL=" . $BASEURL . "/userdetails.php?$id = " . $userId . "]" . $CURUSER["username"] . "[/URL]", "[URL=" . $BASEURL . "/" . $staffcp_path . "/index.php?do=manage_applications&$act = view&$id = " . $applicationId . "]", "[/URL]");
                require_once INC_PATH . "/functions_pm.php";
                while ($staffMember = mysqli_fetch_assoc($query)) {
                    send_pm($staffMember["id"], $msg, $subject);
                }
            }
            stdhead();
            $errors[] = $lang->ts_applications["done"];
            show_app_errors(sprintf($lang->ts_applications["header"], $SITENAME));
            stdfoot();
            exit;
        }
        stderr($lang->global["error"], $lang->global["dberror"]);
    }
}
if ($applicationAction == "apply" && ($applicationId = intval($_GET["aid"])) && is_valid_id($applicationId)) {
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        if (!$_POST["apply"][$aid] || $_POST["apply"][$aid] != "yes") {
            $errors[] = $lang->ts_applications["error"];
        } else {
            ($query = sql_query("SELECT title FROM ts_applications WHERE `enabled` = '1' AND $aid = '" . $aid . "'")) || sqlerr(__FILE__, 124);
            if (0 < mysqli_num_rows($query)) {
                $app = mysqli_fetch_assoc($query);
                $title = sprintf($lang->ts_applications["header"], $SITENAME);
                stdhead($title);
                show_app_errors();
                echo "\r\n\t\t\t\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=save_apply&$aid = " . $aid . "\">\r\n\t\t\t\t<input $type = \"hidden\" $name = \"do\" $value = \"save_apply\" />\r\n\t\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td $align = \"left\" class=\"thead\">\r\n\t\t\t\t\t\t\t" . $title . "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t\t<legend>" . $app["title"] . "</legend>\r\n\t\t\t\t\t\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"2\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"none\" $align = \"right\">" . $lang->ts_applications["username"] . "</td>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"none\"><input $type = \"text\" $size = \"30\" $name = \"username\" $value = \"" . $CURUSER["username"] . "\" $disabled = \"disabled\" /></td>\r\n\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"none\" $align = \"right\">" . $lang->ts_applications["email"] . "</td>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"none\"><input $type = \"text\" $size = \"40\" $name = \"email\" $value = \"" . $CURUSER["email"] . "\" $disabled = \"disabled\" /></td>\r\n\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"none\" $align = \"right\">" . $lang->ts_applications["url"] . "</td>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"none\"><input $type = \"text\" $size = \"50\" $name = \"url\" $value = \"" . (isset($url) && !empty($url) ? htmlspecialchars_uni($url) : "http://") . "\" /></td>\r\n\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"none\" $align = \"right\" $valign = \"top\">" . $lang->ts_applications["info"] . "</td>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"none\"><textarea $name = \"info\" $style = \"width: 390px; height: 90px;\">" . (isset($info) && !empty($info) ? htmlspecialchars_uni($info) : "") . "</textarea></td>\r\n\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t\t<td class=\"none\" $align = \"center\" $colspan = \"2\"><input $type = \"submit\" $value = \"" . $lang->ts_applications["button3"] . "\" /> <input $type = \"reset\" $value = \"" . $lang->ts_applications["button4"] . "\" /></td>\r\n\t\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t</table>\r\n\t\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t</table>\r\n\t\t\t\t</form>\r\n\t\t\t\t";
                stdfoot();
                exit;
            }
            stderr($lang->global["error"], $lang->ts_applications["error3"]);
        }
    }
    ($query = sql_query("SELECT title, description, requirements, enabled FROM ts_applications WHERE $aid = '" . $aid . "'")) || sqlerr(__FILE__, 180);
    if (0 < mysqli_num_rows($query)) {
        $app = mysqli_fetch_assoc($query);
        if ($app["enabled"] != "1") {
            $errors[] = $lang->ts_applications["error3"];
        } else {
            $title = sprintf($lang->ts_applications["header"], $SITENAME) . " - " . $app["title"];
            stdhead($title);
            show_app_errors();
            echo "\r\n\t\t\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?do=apply&$aid = " . $aid . "\">\r\n\t\t\t<input $type = \"hidden\" $name = \"do\" $value = \"apply\" />\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $align = \"left\" class=\"thead\">\r\n\t\t\t\t\t\t" . $title . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td>\r\n\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t<legend>" . $lang->ts_applications["desc"] . "</legend>\r\n\t\t\t\t\t\t\t" . nl2br($app["description"]) . "\r\n\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t<legend>" . $lang->ts_applications["req"] . "</legend>\r\n\t\t\t\t\t\t\t" . nl2br($app["requirements"]) . "\r\n\t\t\t\t\t\t</fieldset>\t\t\t\t\t\r\n\t\t\t\t\t\t<fieldset>\r\n\t\t\t\t\t\t\t<legend>" . $lang->ts_applications["button"] . "</legend>\r\n\t\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"apply[" . $aid . "]\" $value = \"yes\" class=\"inlineimg\" /> " . $lang->ts_applications["apply"] . " \r\n\t\t\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->ts_applications["button"] . "\" />\r\n\t\t\t\t\t\t</fieldset>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t</form>\r\n\t\t\t";
            stdfoot();
            exit;
        }
    } else {
        $errors[] = $lang->ts_applications["error3"];
    }
}
$title = sprintf($lang->ts_applications["header"], $SITENAME);
stdhead($title);
$str = "";
($query = sql_query("SELECT aid, title, description, created, enabled FROM ts_applications ORDER BY created, enabled DESC")) || sqlerr(__FILE__, 235);
if (0 < mysqli_num_rows($query)) {
    while ($app = mysqli_fetch_assoc($query)) {
        if ($app["enabled"] == "1") {
            $button = "<input $type = \"button\" $value = \"" . $lang->ts_applications["button"] . "\" $onclick = \"jumpto('" . $_SERVER["SCRIPT_NAME"] . "?do=apply&$aid = " . $app["aid"] . "'); return false;\" />";
        } else {
            $button = "<input $type = \"button\" $value = \"" . $lang->ts_applications["button2"] . "\" $onclick = \"alert('" . $lang->ts_applications["error3"] . "'); return false;\" />";
        }
        $str .= "\r\n\t\t<tr>\r\n\t\t\t<td $align = \"left\" $valign = \"top\">" . $app["title"] . "</td>\r\n\t\t\t<td $align = \"left\" $valign = \"top\">" . nl2br($app["description"]) . "</td>\r\n\t\t\t<td $align = \"center\" $valign = \"center\">" . my_datee($dateformat, $app["created"]) . " " . my_datee($timeformat, $app["created"]) . "</td>\r\n\t\t\t<td $align = \"center\" $valign = \"center\">" . $button . "</td>\r\n\t\t</tr>";
    }
} else {
    $str = "<tr><td $colspan = \"4\">" . $lang->ts_applications["norecord"] . "</td></tr>";
}
show_app_errors();
echo "\r\n<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n<input $type = \"hidden\" $name = \"do\" $value = \"apply\" />\r\n<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t<tr>\r\n\t\t<td $align = \"left\" class=\"thead\" $colspan = \"4\">\r\n\t\t\t" . $title . "\r\n\t\t</td>\t\t\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td $align = \"left\" class=\"subheader\" $width = \"30%\">\r\n\t\t\t" . $lang->ts_applications["title"] . "\r\n\t\t</td>\r\n\t\t<td $align = \"left\" class=\"subheader\" $width = \"40%\">\r\n\t\t\t" . $lang->ts_applications["desc"] . "\r\n\t\t</td>\r\n\t\t<td $align = \"center\" class=\"subheader\" $width = \"20%\">\r\n\t\t\t" . $lang->ts_applications["created"] . "\r\n\t\t</td>\r\n\t\t<td $align = \"center\" class=\"subheader\" $width = \"10%\">\r\n\t\t\t" . $lang->ts_applications["button"] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t" . $str . "\r\n</table>\r\n</form>\r\n";
stdfoot();
function show_app_errors($text = "")
{
    global $errors;
    global $lang;
    if (is_array($errors) && 0 < count($errors)) {
        $error = implode("<br />", $errors);
        echo "\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . ($text ? $text : $lang->global["error"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<font $color = \"red\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $error . "\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</font>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}

?>