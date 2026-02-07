<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "ts_save_shout.php");
define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
$rootpath = "./../";
define("NO_LOGIN_REQUIRED", true);
require $rootpath . "global.php";
$uid = intval($_POST["uid"]);
$sid = intval($_POST["sid"]);
$NewShout = fixAjaxText($_POST["newshout"]);
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $CURUSER["id"] && $sid && $NewShout && $CURUSER["id"] == $uid) {
    $TSSEConfig->TSLoadConfig("SHOUTBOX");
    $query = sql_query("SELECT uid, notice FROM ts_shoutbox WHERE $sid = '" . $sid . "'");
    if (0 < mysqli_num_rows($query)) {
        $shouts = mysqli_fetch_assoc($query);
        if ($is_mod || $S_CANEDIT == "yes" && $shouts["uid"] == $CURUSER["id"]) {
            $text = strval($NewShout);
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
            sql_query("UPDATE ts_shoutbox SET $shout = " . sqlesc($text) . " WHERE $sid = '" . $sid . "'");
            header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
            header("Content-type: text/html; $charset = " . $shoutboxcharset);
            if ($shouts["notice"] == "1") {
                $lang->load("shoutbox");
                $text = sprintf($lang->shoutbox["sysnotice"], $text);
            }
            exit(format_comment($text));
        }
    }
}

?>