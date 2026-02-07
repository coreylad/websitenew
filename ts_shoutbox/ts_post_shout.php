<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "ts_post_shout.php");
define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
$rootpath = "./../";
define("NO_LOGIN_REQUIRED", true);
require $rootpath . "global.php";
$uid = intval($_POST["uid"]);
$shout = fixAjaxText($_POST["shout"]);
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $uid && $shout && $CURUSER["id"] && $usergroups["canshout"] == "yes" && $uid == $CURUSER["id"]) {
    $text = strval($shout);
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
    $msg = "";
    if (!$is_mod) {
        if ($usergroups["floodlimit"] != "0") {
            $query = sql_query("SELECT date FROM ts_shoutbox WHERE uid = " . sqlesc($CURUSER["id"]) . " ORDER by date DESC LIMIT 1");
            if (0 < mysqli_num_rows($query)) {
                $Result = mysqli_fetch_assoc($query);
                $last_shout = $Result["date"];
                $lang->load("shoutbox");
                $msg = flood_check($lang->shoutbox["floodcomment"], $last_shout, true);
            }
        }
        $query = sql_query("SELECT canshout FROM ts_u_perm WHERE userid = " . sqlesc($CURUSER["id"]));
        if (0 < mysqli_num_rows($query)) {
            $shoutperm = mysqli_fetch_assoc($query);
            if ($shoutperm["canshout"] == "0") {
                $msg = $lang->global["shouterror"];
            }
        }
    }
    if (empty($msg)) {
        $notice = "0";
        if ($is_mod && substr($text, 0, 7) == "/notice") {
            $text = str_replace("/notice", "", $text);
            $notice = "1";
        }
        $TSSEConfig->TSLoadConfig("SHOUTBOX");
        if (!empty($S_DISABLETAGS)) {
            $S_DISABLETAGS = explode(",", $S_DISABLETAGS);
            if (count($S_DISABLETAGS)) {
                $orjtext = $text;
                foreach ($S_DISABLETAGS as $RemoveTag) {
                    if (preg_match("#\\[" . $RemoveTag . "\\]|\\[\\/" . $RemoveTag . "\\]#isU", $text)) {
                        $text = "";
                    }
                }
                if ($orjtext != $text) {
                    $lang->load("shoutbox");
                    show_message(sprintf($lang->shoutbox["tagerror"], implode(", ", $S_DISABLETAGS)));
                }
            }
        }
        $channel = isset($_POST["channel"]) ? intval($_POST["channel"]) : 0;
        $s_pc2perm = isset($s_pc2perm) ? @explode(",", $s_pc2perm) : [];
        $s_pc3perm = isset($s_pc3perm) ? @explode(",", $s_pc3perm) : [];
        $s_pc4perm = isset($s_pc4perm) ? @explode(",", $s_pc4perm) : [];
        $s_pc5perm = isset($s_pc5perm) ? @explode(",", $s_pc5perm) : [];
        if ($channel == 1 && !in_array($CURUSER["usergroup"], $s_pc2perm)) {
            $channel = 0;
        }
        if ($channel == 2 && !in_array($CURUSER["usergroup"], $s_pc3perm)) {
            $channel = 0;
        }
        if ($channel == 3 && !in_array($CURUSER["usergroup"], $s_pc4perm)) {
            $channel = 0;
        }
        if ($channel == 4 && !in_array($CURUSER["usergroup"], $s_pc5perm)) {
            $channel = 0;
        }
        if (substr($text, 0, 8) == "/private") {
            $text = str_replace("/private", "", $text);
            $s_cansendps = $s_cansendps ? @explode(",", $s_cansendps) : [];
            if (in_array($CURUSER["usergroup"], $s_cansendps)) {
                $SearchUserName = explode(" ", $text);
                $text = trim(str_replace($SearchUserName[1], "", $text));
                if (!$text) {
                    show_message($lang->global["dontleavefieldsblank"]);
                }
                $query = sql_query("SELECT u.username, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE LOWER(u.username) = " . sqlesc(strtolower($SearchUserName[1])));
                if (mysqli_num_rows($query)) {
                    $UserResult = mysqli_fetch_assoc($query);
                    $SearchUserName[1] = get_user_color($UserResult["username"], $UserResult["namestyle"]);
                    sql_query("INSERT INTO ts_shoutbox (uid, date, shout, private, receiver, channel) VALUES ('" . $uid . "', '" . TIMENOW . "', " . sqlesc($text) . ", '1', " . sqlesc($SearchUserName[1]) . ", '" . $channel . "')");
                } else {
                    show_message($lang->global["nousername"]);
                }
            } else {
                sql_query("INSERT INTO ts_shoutbox (uid, date, shout, notice, channel) VALUES ('" . $uid . "', '" . TIMENOW . "', " . sqlesc($text) . ", '" . $notice . "', '" . $channel . "')");
            }
        } else {
            sql_query("INSERT INTO ts_shoutbox (uid, date, shout, notice, channel) VALUES ('" . $uid . "', '" . TIMENOW . "', " . sqlesc($text) . ", '" . $notice . "', '" . $channel . "')");
        }
    } else {
        show_message($msg, true);
    }
}
function show_message($msg, $strip = false)
{
    global $shoutboxcharset;
    header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Content-type: text/html; charset=" . $shoutboxcharset);
    exit($strip ? strip_tags($msg) : $msg);
}

?>