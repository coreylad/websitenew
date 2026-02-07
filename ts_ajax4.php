<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
define("NO_LOGIN_REQUIRED", true);
define("THIS_SCRIPT", "ts_ajax4.php");
require "./global.php";
define("TS_AJAX_VERSION", "1.2.0 by xam");
if (strtoupper($_SERVER["REQUEST_METHOD"]) != "POST" || !isset($CURUSER) || $CURUSER["id"] == 0) {
    exit;
}
$do = isset($_POST["do"]) ? trim($_POST["do"]) : "";
$groupid = isset($_POST["groupid"]) ? intval($_POST["groupid"]) : 0;
if (!is_valid_id($groupid)) {
    show_msg($lang->ts_social_groups["error1"], true);
}
$query = sql_query("SELECT owner FROM ts_social_groups WHERE groupid = " . sqlesc($groupid));
if (mysqli_num_rows($query) == 0) {
    show_msg($lang->ts_social_groups["error1"], true);
} else {
    $SG = mysqli_fetch_assoc($query);
}
$lang->load("ts_social_groups");
$query = sql_query("SELECT userid FROM ts_social_group_members WHERE userid = " . sqlesc($CURUSER["id"]) . " AND type = 'public'");
if (mysqli_num_rows($query) == 0) {
    show_msg($lang->ts_social_groups["error7"], true);
}
if ($do == "save_sgm" && SGPermission("canpost") && SGPermission("canview")) {
    $text = fixAjaxText($_POST["message"]);
    $text = strval($text);
    if (strtolower($shoutboxcharset) != "utf-8") {
        if (function_exists("iconv")) {
            $text = iconv("UTF-8", $shoutboxcharset, $text);
        } else {
            if (function_exists("mb_convert_encoding")) {
                $text = mb_convert_encoding($text, $shoutboxcharset, "UTF-8");
            } else {
                if (strtolower($shoutboxcharset) == "iso-8859-1") {
                    $text = utf8_decode($text);
                }
            }
        }
    }
    if (!$text || strlen($text) < 3) {
        show_msg($lang->ts_social_groups["error1"], true);
    }
    $userid = intval($CURUSER["id"]);
    $posted = TIMENOW;
    sql_query("INSERT INTO ts_social_group_messages (groupid, userid, posted, message) VALUES (" . sqlesc($groupid) . ", " . sqlesc($CURUSER["id"]) . ", " . sqlesc($posted) . ", " . sqlesc($text) . ")") or exit;
    $mid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
    if ($mid) {
        if ($SG["owner"] != $CURUSER["id"]) {
            require_once INC_PATH . "/functions_pm.php";
            send_pm($SG["owner"], sprintf($lang->ts_social_groups["s4"], $BASEURL . "/ts_social_groups.php?do=showgroup&groupid=" . $groupid . "#message_" . $mid), $lang->ts_social_groups["s5"]);
        }
        sql_query("UPDATE ts_social_groups SET messages = messages + 1, lastpostdate = '" . $posted . "', lastposter = '" . $CURUSER["id"] . "' WHERE groupid = " . sqlesc($groupid));
        $query = sql_query("SELECT uid FROM ts_social_groups_subscribe WHERE groupid = " . sqlesc($groupid) . " AND uid != " . sqlesc($CURUSER["id"]));
        if (0 < mysqli_num_rows($query)) {
            require_once INC_PATH . "/functions_pm.php";
            while ($User = mysqli_fetch_assoc($query)) {
                send_pm($User["uid"], sprintf($lang->ts_social_groups["s6"], $BASEURL . "/ts_social_groups.php?do=showgroup&groupid=" . $groupid . "#message_" . $mid), $lang->ts_social_groups["s5"]);
            }
        }
    }
    $ULink = "<a href=\"" . ts_seo($CURUSER["id"], $CURUSER["username"]) . "\">" . get_user_color($CURUSER["username"], $usergroups["namestyle"]) . "</a>";
    $UAvatar = get_user_avatar($CURUSER["avatar"], true, "80", "80");
    $UMsg = format_comment($text);
    $Posted = my_datee($dateformat, $posted) . " " . my_datee($timeformat, $posted);
    show_msg("\n\t<tr>\n\t\t<td class=\"none\">\n\t\t\t<table width=\"100%\" cellpadding=\"1\" cellspacing=\"0\" border=\"0\">\n\t\t\t\t<tr>\n\t\t\t\t\t<th rowspan=\"2\" class=\"none\" width=\"80\" height=\"80\" valign=\"top\">\n\t\t\t\t\t\t" . $UAvatar . "\n\t\t\t\t\t</th>\n\t\t\t\t\t<td class=\"none\" valign=\"top\">\n\t\t\t\t\t\t<div class=\"subheader\">" . sprintf($lang->ts_social_groups["by2"], $Posted, $ULink) . "</div>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"none\" valign=\"top\">\n\t\t\t\t\t\t<div id=\"message_" . $mid . "\" name=\"message_" . $mid . "\">\n\t\t\t\t\t\t\t" . $UMsg . "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t</table>\n\t\t</td>\n\t</tr>\n\t");
}
function show_msg($message = "", $error = false)
{
    global $shoutboxcharset;
    header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Content-type: text/html; charset=" . $shoutboxcharset);
    if ($error) {
        exit("<error>" . $message . "</error>");
    }
    exit($message);
}
function SGPermission($Option)
{
    global $usergroups;
    $Options = ["canview" => "0", "cancreate" => "1", "canpost" => "2", "candelete" => "3", "canjoin" => "4", "canedit" => "5", "canmanagemsg" => "6", "canmanagegroup" => "7"];
    $What = isset($Options[$Option]) ? $Options[$Option] : 0;
    return $usergroups["sgperms"][$What] == "1" ? true : false;
}

?>