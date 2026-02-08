<?php
define("THIS_SCRIPT", "ts_update_shout.php");
define("SKIP_LOCATION_SAVE", true);
define("DEBUGMODE", false);
define("IN_AJAX", true);
define("SKIP_MOD_QUERIES", true);
define("NO_LOGIN_REQUIRED", true);
define("IS_SHOUTBOX", true);
$rootpath = "./../";
require $rootpath . "global.php";
$FBShoutbox = isset($_POST["FBShoutbox"]) && $_POST["FBShoutbox"] == "1" && user_options($CURUSER["options"], "fb-shoutbox") ? true : false;
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
    $TSSEConfig->TSLoadConfig("SHOUTBOX");
    $S_DELETESTIME = 0 + $S_DELETESTIME;
    if (!$S_DELETESTIME) {
        $S_DELETESTIME = 604800;
    }
    $S_SPERPAGE = 0 + $S_SPERPAGE;
    if (!$S_SPERPAGE) {
        $S_SPERPAGE = 250;
    }
    if (0 < $S_DELETESTIME) {
        sql_query("DELETE FROM ts_shoutbox WHERE date < '" . (TIMENOW - $S_DELETESTIME * 60 * 60) . "'");
    }
    $channel = isset($_POST["channel"]) ? intval($_POST["channel"]) : 0;
    $s_pc2perm = isset($s_pc2perm) ? @explode(",", $s_pc2perm) : [];
    $s_pc3perm = isset($s_pc3perm) ? @explode(",", $s_pc3perm) : [];
    $s_pc4perm = isset($s_pc4perm) ? @explode(",", $s_pc4perm) : [];
    $s_pc5perm = isset($s_pc5perm) ? @explode(",", $s_pc5perm) : [];
    if (!isset($CURUSER)) {
        $channel = 0;
    } else {
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
    }
    $imagepath = $pic_base_url . "friends/";
    $output = "";
    $query = sql_query("SELECT s.*, u.username, u.enabled, u.donor, u.leechwarn, u.warned, u.options, u.last_access, u.avatar, g.namestyle FROM ts_shoutbox s LEFT JOIN users u ON (s.$uid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE $channel = " . sqlesc($channel) . " ORDER BY s.date " . ($FBShoutbox ? "ASC" : "DESC" . ($S_SPERPAGE ? " LIMIT " . $S_SPERPAGE : "")));
    if (mysqli_num_rows($query)) {
        $dt = TIMENOW - 300;
        $lang->load("shoutbox");
        $s_cansendps = $s_cansendps ? @explode(",", $s_cansendps) : [];
        while ($shouts = mysqli_fetch_assoc($query)) {
            $IsPrivate = false;
            if ($shouts["private"] && !empty($shouts["receiver"])) {
                if (isset($CURUSER) && ($shouts["receiver"] == get_user_color($CURUSER["username"], $usergroups["namestyle"]) || strtolower($shouts["receiver"]) == strtolower($CURUSER["username"] || strip_tags($CURUSER["username"]) == strip_tags($shouts["receiver"])) || $usergroups["cansettingspanel"] == "yes" || $shouts["uid"] == $CURUSER["id"])) {
                    $IsPrivate = true;
                }
            }
            $SKIP = false;
            if ($shouts["notice"] == "1") {
                $shouts["shout"] = "[i][b]" . $tsshoutbotname . "[/b] " . $shouts["shout"] . "[/i]";
                $SKIP = true;
            }
            if (TS_Match($shouts["options"], "B1") && !$is_mod && $CURUSER["id"] != $shouts["uid"]) {
                $offline = true;
            } else {
                if ($dt < TS_MTStoUTS($shouts["last_access"]) || $CURUSER["id"] == $shouts["uid"]) {
                    $offline = false;
                } else {
                    $offline = true;
                }
            }
            $UserPics = "";
            $manage = [];
            if ($shouts["enabled"] == "yes") {
                if ($shouts["donor"] == "yes") {
                    $UserPics .= "<img $src = \"" . $BASEURL . "/ts_shoutbox/images/donor.gif\" $alt = \"" . $lang->global["imgdonated"] . "\" $title = \"" . $lang->global["imgdonated"] . "\" $border = \"0\" class=\"inlineimg\" $height = \"11\" $width = \"11\" />";
                }
                if ($shouts["leechwarn"] == "yes" || $shouts["warned"] == "yes") {
                    $UserPics .= "<img $src = \"" . $BASEURL . "/ts_shoutbox/images/warned.gif\" $alt = \"" . $lang->global["imgwarned"] . "\" $title = \"" . $lang->global["imgwarned"] . "\" $border = \"0\" class=\"inlineimg\" $height = \"11\" $width = \"11\" />";
                }
            } else {
                $UserPics .= "<img $src = \"" . $BASEURL . "/ts_shoutbox/images/banned.gif\" $alt = \"" . $lang->global["disabled"] . "\" $title = \"" . $lang->global["disabled"] . "\" $border = \"0\" class=\"inlineimg\" $height = \"11\" $width = \"11\" />";
            }
            if (isset($CURUSER) && ($is_mod || $S_CANEDIT == "yes" && $shouts["uid"] == $CURUSER["id"])) {
                $manage[] = "<a $href = \"javascript:void(0);\" $onclick = \"EditShout(" . $shouts["sid"] . "); return false;\"><img $src = \"" . $BASEURL . "/ts_shoutbox/images/edit.gif\" $alt = \"\" $border = \"0\" class=\"inlineimg\" $height = \"11\" $width = \"11\" /></a>";
            }
            if (isset($CURUSER) && ($is_mod || $S_CANDELETE == "yes" && $shouts["uid"] == $CURUSER["id"])) {
                $manage[] = "<a $href = \"javascript:void(0);\" $onclick = \"DeleteShout(" . $shouts["sid"] . "); return false;\"><img $src = \"" . $BASEURL . "/ts_shoutbox/images/delete.gif\" $alt = \"\" $border = \"0\" class=\"inlineimg\" $height = \"11\" $width = \"11\" /></a>";
            }
            if (!$SKIP && isset($CURUSER)) {
                $manage[] = "<a $href = \"" . $BASEURL . "/sendmessage.php?$receiver = " . $shouts["uid"] . "\"><img $src = \"" . $BASEURL . "/ts_shoutbox/images/pm.png\" $alt = \"" . sprintf($lang->shoutbox["pm"], $shouts["username"]) . "\" $title = \"" . sprintf($lang->shoutbox["pm"], $shouts["username"]) . "\" $border = \"0\" $width = \"11\" $height = \"11\" class=\"inlineimg\" /></a>";
                if (in_array($CURUSER["usergroup"], $s_cansendps)) {
                    $manage[] = "<img $src = \"" . $BASEURL . "/ts_shoutbox/images/private.png\" $alt = \"" . $lang->shoutbox["private"] . "\" $title = \"" . $lang->shoutbox["private"] . "\" $border = \"0\" $width = \"11\" $height = \"11\" class=\"inlineimg\" $style = \"cursor: pointer;\" $onclick = \"PrivateShout('" . $shouts["username"] . "');\" />";
                }
            }
            $vAvatar = get_user_avatar($shouts["avatar"], false, "", "", "max-width: 24px; border-bottom: 4px solid " . ($offline ? "red" : "green") . " !important; vertical-align: top;");
            $vName = "<a $href = \"" . ts_seo($shouts["uid"], $shouts["username"]) . "\">" . get_user_color($shouts["username"], $shouts["namestyle"]) . "</a>";
            if ($FBShoutbox) {
                $output .= "\r\n\t\t\t\t<div class=\"fb-shoutbox-shout-msg" . ($IsPrivate ? " fb-shoutbox-private-shout" : ($SKIP ? " fb-shoutbox-notice-shout" : "")) . "\" $id = \"shout_" . $shouts["sid"] . "\">\r\n\t\t\t\t\t<div class=\"fb-shoutbox-manage\">" . implode("&nbsp;&nbsp;", $manage) . "</div>\r\n\t\t\t\t\t<div class=\"fb-shoutbox-info\"><time>" . my_datee($timeformat, $shouts["date"]) . "</time><br />" . $vAvatar . "</div>\r\n\t\t\t\t\t<span class=\"fb-shoutbox-username\">" . (!$SKIP ? $vName . " " . $UserPics : "") . ($IsPrivate ? " => " . $shouts["receiver"] . "&nbsp;&nbsp;" : "") . "</span> \r\n\t\t\t\t\t<span class=\"fb-shoutbox-message\" $id = \"shout_msg_" . $shouts["sid"] . "\">" . format_comment($shouts["shout"]) . "</span>\r\n\t\t\t\t\t<div class=\"clear\"></div>\r\n\t\t\t\t</div>";
            } else {
                $output .= "\r\n\t\t\t\t<div class=\"shoutbox " . ($SKIP ? "shoutboxnotice" : ($IsPrivate ? "shoutboxprivatemsg" : "smallfont")) . "\" $id = \"whole_shout_" . $shouts["sid"] . "\" $name = \"whole_shout_" . $shouts["sid"] . "\" $style = \"position: relative; \" $rel = \"shoutRows\" $data = \"" . $shouts["sid"] . "\">\r\n\t\t\t\t\t<div $id = \"manage_shout_" . $shouts["sid"] . "\" $style = \"position: absolute; right: 2px; top: 0px; background: #ddd; border: 1px solid #fff; padding: 1px 5px 5px 5px; opacity: 0.9; display: none;\">" . implode("&nbsp;&nbsp;", $manage) . "</div>\r\n\t\t\t\t\t\r\n\t\t\t\t\t<div $style = \"float: left; text-align: left; margin-right: 5px; font-size: 11px; !important;\">\r\n\t\t\t\t\t\t" . my_datee($timeformat, $shouts["date"]) . "\t\t\t\t\r\n\t\t\t\t\t\t" . (!$SKIP ? "\r\n\t\t\t\t\t\t<span $style = \"right: 0 5px;\">" . $vAvatar . "</span>\r\n\t\t\t\t\t\t<span>" . $vName . " " . $UserPics . "</span>\r\n\t\t\t\t\t\t" : "") . "\r\n\t\t\t\t\t</div>\r\n\r\n\t\t\t\t\t<div $style = \"text-align: left; font-size: 11px !important;\" $id = \"shout_" . $shouts["sid"] . "\" $name = \"shout_" . $shouts["sid"] . "\">" . ($IsPrivate ? " => " . $shouts["receiver"] . "&nbsp;&nbsp;" : "") . format_comment($shouts["shout"]) . "</div>\r\n\t\t\t\t\t\r\n\t\t\t\t\t<div $style = \"clear: both;\"></div>\r\n\t\t\t\t</div>";
            }
        }
    } else {
        $lang->load("shoutbox");
        $output = "<div $id = \"noShoutYet\">" . $lang->shoutbox["noshout"] . "</div>";
    }
    show_message($output);
}
function show_message($msg, $strip = false)
{
    global $shoutboxcharset;
    header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Content-type: text/html; $charset = " . $shoutboxcharset);
    exit($strip ? strip_tags($msg) : $msg);
}

?>