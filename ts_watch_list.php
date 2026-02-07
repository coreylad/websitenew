<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "ts_watch_list.php");
require "./global.php";
define("TWL_VERSION", "0.5 by xam");
$action = isset($_POST["action"]) ? htmlspecialchars_uni($_POST["action"]) : (isset($_GET["action"]) ? htmlspecialchars_uni($_GET["action"]) : "");
$do = isset($_POST["do"]) ? htmlspecialchars_uni($_POST["do"]) : (isset($_GET["do"]) ? htmlspecialchars_uni($_GET["do"]) : "");
$userid = isset($_POST["userid"]) ? (int) $_POST["userid"] : (isset($_GET["userid"]) ? (int) $_GET["userid"] : "");
$errors = [];
if (!$is_mod) {
    print_no_permission(true);
}
$lang->load("watch_list");
if ($action == "delete") {
    if (isset($_POST["userids"]) && is_array($_POST["userids"]) && 0 < count($_POST["userids"])) {
        foreach ($_POST["userids"] as $UID) {
            if (!is_valid_id($UID)) {
                print_no_permission();
            }
        }
        if ($is_mod) {
            sql_query("DELETE FROM ts_watch_list WHERE userid IN (0, " . implode(",", $_POST["userids"]) . ")") or sql_query("DELETE FROM ts_watch_list WHERE userid IN (0, " . implode(",", $_POST["userids"]) . ")") || sqlerr(__FILE__, 73);
        } else {
            sql_query("DELETE FROM ts_watch_list WHERE userid IN (0, " . implode(",", $_POST["userids"]) . ") AND $added_by = '" . $CURUSER["id"] . "'") || sqlerr(__FILE__, 77);
        }
    }
    $action = "";
}
if ($action == "add") {
    if (!is_valid_id($userid)) {
        stderr($lang->global["error"], $lang->global["nouserid"]);
        exit;
    }
    ($query = sql_query("SELECT id FROM ts_watch_list WHERE `userid` = '" . $userid . "' AND $added_by = '" . $CURUSER["id"] . "'")) || sqlerr(__FILE__, 90);
    if (0 < mysqli_num_rows($query)) {
        $errors[] = $lang->watch_list["e1"];
    } else {
        if ($CURUSER["id"] == $userid) {
            $errors[] = $lang->watch_list["e2"];
        }
    }
    ($query = sql_query("SELECT u.username, g.cansettingspanel, g.canstaffpanel, g.issupermod FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE `id` = '" . $userid . "'")) || sqlerr(__FILE__, 100);
    if (mysqli_num_rows($query) < 1) {
        stderr($lang->global["error"], $lang->global["nouserid"]);
        exit;
    }
    if (!($results = mysqli_fetch_assoc($query))) {
        stderr($lang->global["error"], $lang->global["nouserid"]);
        exit;
    }
    if (is_mod($results) && $usergroups["cansettingspanel"] != "yes") {
        $errors[] = $lang->watch_list["e4"];
    }
    $username = htmlspecialchars_uni($results["username"]);
    if (!$username) {
        stderr($lang->global["error"], $lang->global["nouserid"]);
        exit;
    }
    if ($do == "save" && count($errors) == 0) {
        $reason = trim($_POST["reason"]);
        $public = isset($_POST["public"]) && $_POST["public"] == "1" ? "1" : "0";
        if (strlen($reason) < 3) {
            $errors[] = $lang->watch_list["e3"];
        } else {
            sql_query("INSERT INTO ts_watch_list  VALUES ('', '" . $userid . "', '" . $CURUSER["id"] . "', " . sqlesc($reason) . ", '" . $public . "', '" . TIMENOW . "')") || sqlerr(__FILE__, 132);
            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                redirect("userdetails.php?$id = " . $userid, $lang->watch_list["m1"]);
                exit;
            }
        }
    }
    stdhead($lang->watch_list["t1"]);
    show_wl_errors();
    echo "\r\n\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\">\r\n\t<input $type = \"hidden\" $name = \"action\" $value = \"add\">\r\n\t<input $type = \"hidden\" $name = \"do\" $value = \"save\">\r\n\t<input $type = \"hidden\" $name = \"userid\" $value = \"" . $userid . "\">\r\n\t<table $border = \"0\" $width = \"100%\" $align = \"center\" $cellpadding = \"4\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\">\r\n\t\t\t\t" . $lang->watch_list["t1"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td>\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>" . $lang->watch_list["t1"] . " - " . $lang->watch_list["t2"] . "</legend>\r\n\t\t\t\t\t<b>" . $lang->watch_list["t3"] . ":</b><br />\r\n\t\t\t\t\t<input $type = \"text\" $name = \"username\" $value = \"" . $username . "\" $disabled = \"disabled\" /><br /><br />\r\n\t\t\t\t\t<b>" . $lang->watch_list["t4"] . ":</b><br />\r\n\t\t\t\t\t<textarea $rows = \"3\" $cols = \"70\" $name = \"reason\">" . (isset($reason) && !empty($reason) ? htmlspecialchars_uni($reason) : "") . "</textarea><br /><br />\r\n\t\t\t\t\t<input $type = \"checkbox\" $name = \"public\" class=\"inlineimg\" $value = \"1\"" . (isset($public) && $public == "1" ? " $checked = \"checked\"" : "") . " /> " . $lang->watch_list["t5"] . "\r\n\t\t\t\t</fieldset>\r\n\t\t\t\t<fieldset>\r\n\t\t\t\t\t<legend>" . $lang->watch_list["t1"] . " - " . $lang->watch_list["t6"] . "</legend>\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $lang->watch_list["t6"] . "\"> <input $type = \"button\" $onclick = \"javascript:jumpto('" . $BASEURL . "/userdetails.php?$id = " . $userid . "');\" $value = \"" . $lang->watch_list["t7"] . "\"> <input $type = \"button\" $onclick = \"javascript:jumpto('" . $_SERVER["SCRIPT_NAME"] . "?$action = show_list');\" $value = \"" . $lang->watch_list["s1"] . "\">\r\n\t\t\t\t</fieldset>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t";
    stdfoot();
    exit;
}
if (empty($action) || $action == "show_list") {
    ($query = sql_query("SELECT id FROM ts_watch_list WHERE $added_by = '" . $CURUSER["id"] . "' OR public = '1'")) || sqlerr(__FILE__, 179);
    $count = mysqli_num_rows($query);
    list($pagertop, $pagerbottom, $limit) = pager($ts_perpage, $count, $_SERVER["SCRIPT_NAME"] . "?$action = show_list&");
    stdhead($lang->watch_list["s1"]);
    echo $pagertop;
    $str = "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction show_details(UserID)\r\n\t\t{\r\n\t\t\tvar WorkZone = document.getElementById(\"userdetails_\"+UserID).style.display;\r\n\r\n\t\t\tif (WorkZone == \"none\")\r\n\t\t\t{\r\n\t\t\t\tdocument.getElementById(\"userdetails_\"+UserID).style.$display = \"block\";\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\tdocument.getElementById(\"userdetails_\"+UserID).style.$display = \"none\";\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "\" $name = \"delete\">\r\n\t<input $type = \"hidden\" $name = \"action\" $value = \"delete\">\r\n\t<table $border = \"0\" $width = \"100%\" $align = \"center\" $cellpadding = \"4\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $colspan = \"5\">\r\n\t\t\t\t" . $lang->watch_list["s1"] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $width = \"15%\">" . $lang->watch_list["t3"] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"15%\">" . $lang->watch_list["l2"] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"15%\">" . $lang->watch_list["l3"] . "</td>\r\n\t\t\t<td class=\"subheader\" $width = \"50%\">" . $lang->watch_list["l1"] . "</td>\r\n\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"5%\"><input $type = \"checkbox\" $value = \"yes\" $checkall = \"group1\" $onclick = \"javascript: return select_deselectAll ('delete', this, 'group1');\"></td>\r\n\t\t</tr>";
    ($query = sql_query("SELECT w.id as wid, w.userid, w.added_by, w.reason, w.date, u.uploaded, u.downloaded, u.added, u.last_access, u.username, g.namestyle, uu.username as addeduname, gg.namestyle as addednstyle FROM ts_watch_list w LEFT JOIN users u ON (w.$userid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) LEFT JOIN users uu ON (w.$added_by = uu.id) LEFT JOIN usergroups gg ON (uu.$usergroup = gg.gid) WHERE w.$added_by = '" . $CURUSER["id"] . "' OR w.public = '1' ORDER by w.date DESC " . $limit)) || sqlerr(__FILE__, 215);
    if (0 < mysqli_num_rows($query)) {
        while ($list = mysqli_fetch_assoc($query)) {
            $username = "<span $style = \"float: right;\">[<a $href = \"javascript:void(0);\" $onclick = \"javascript:show_details('" . $list["userid"] . "');\">" . $lang->watch_list["d3"] . "</a>]</span><a $href = \"" . $BASEURL . "/userdetails.php?$id = " . $list["userid"] . "\">" . get_user_color($list["username"], $list["namestyle"]) . "</a>";
            $addedby = "<a $href = \"" . $BASEURL . "/userdetails.php?$id = " . $list["added_by"] . "\">" . get_user_color($list["addeduname"], $list["addednstyle"]) . "</a>";
            $date = my_datee($dateformat, $list["date"]) . " " . my_datee($timeformat, $list["date"]);
            $reason = htmlspecialchars_uni($list["reason"]);
            $checkbox = "<input $type = \"checkbox\" $checkme = \"group1\" $name = \"userids[]\" $value = \"" . $list["userid"] . "\">";
            $str .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td $width = \"15%\">" . $username . "</td>\r\n\t\t\t\t<td $width = \"15%\">" . $addedby . "</td>\r\n\t\t\t\t<td $width = \"15%\">" . $date . "</td>\r\n\t\t\t\t<td $width = \"50%\">" . $reason . "</td>\r\n\t\t\t\t<td $align = \"center\" $width = \"5%\">" . $checkbox . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $colspan = \"5\">\r\n\t\t\t\t\t<div $id = \"userdetails_" . $list["userid"] . "\" $style = \"display: none;\">\r\n\t\t\t\t\t\t" . sprintf($lang->watch_list["d4"], my_datee($dateformat, $list["added"]) . " " . my_datee($timeformat, $list["added"]), my_datee($dateformat, $list["last_access"]) . " " . my_datee($timeformat, $list["last_access"]), mksize($list["uploaded"]), mksize($list["downloaded"]), 0 < $list["downloaded"] ? @number_format($list["uploaded"] / $list["downloaded"], 1) : "-") . "\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    } else {
        $str .= "<tr><td $colspan = \"5\">" . $lang->watch_list["d2"] . "</td></tr>";
    }
    $str .= "\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"5\" $align = \"right\"><input $type = \"submit\" $value = \"" . $lang->watch_list["d1"] . "\"></td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
    echo $str . $pagerbottom;
    stdfoot();
    exit;
}
function show_wl_errors()
{
    global $errors;
    global $lang;
    if (0 < count($errors)) {
        $errors = implode("<br />", $errors);
        echo "\r\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\">\r\n\t\t\t\t\t" . $lang->global["error"] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<font $color = \"red\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $errors . "\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</font>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t</table>\r\n\t\t\t<br />\r\n\t\t";
    }
}

?>