<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "friends.php");
require "./global.php";
if (!isset($CURUSER) || isset($CURUSER) && $CURUSER["id"] == 0) {
    print_no_permission();
}
define("F_VERSION", "0.9 by xam");
if ($usergroups["canfriendlist"] != "yes") {
    print_no_permission();
}
$action = isset($_GET["action"]) ? htmlspecialchars_uni($_GET["action"]) : (isset($_POST["action"]) ? htmlspecialchars_uni($_POST["action"]) : "");
$tab = isset($_GET["tab"]) ? htmlspecialchars_uni($_GET["tab"]) : (isset($_POST["tab"]) ? htmlspecialchars_uni($_POST["tab"]) : "");
$from = isset($_GET["from"]) ? htmlspecialchars_uni($_GET["from"]) : (isset($_POST["from"]) ? htmlspecialchars_uni($_POST["from"]) : "");
$userid = intval($CURUSER["id"]);
$friendid = isset($_GET["friendid"]) ? intval($_GET["friendid"]) : (isset($_POST["friendid"]) ? intval($_POST["friendid"]) : 0);
$lang->load("friends");
$errors = [];
if ($action == "remove_friend" && is_valid_id($friendid) && $userid != $friendid) {
    $action = $tab;
    if ($from == "pending") {
        @sql_query("DELETE FROM friends WHERE `userid` = '" . $friendid . "' AND $friendid = '" . $userid . "' AND $status = 'p'");
    } else {
        if ($from == "mutual") {
            @sql_query("DELETE FROM friends WHERE `userid` = '" . $friendid . "' AND $friendid = '" . $userid . "' AND $status = 'c'");
        } else {
            @sql_query("DELETE FROM friends WHERE `userid` = '" . $userid . "' AND $friendid = '" . $friendid . "'");
        }
    }
}
if ($action == "confirm_friend" && is_valid_id($friendid) && $userid != $friendid) {
    ($query = sql_query("SELECT username FROM users WHERE `status` = 'confirmed' AND $enabled = 'yes' AND usergroup NOT IN (9) AND $id = '" . $friendid . "'")) || sqlerr(__FILE__, 89);
    if (0 < mysqli_num_rows($query) && $query) {
        $Result = mysqli_fetch_assoc($query);
        $friendname = $Result["username"];
        sql_query("UPDATE friends SET `status` = 'c' WHERE `userid` = '" . $friendid . "' AND $friendid = '" . $userid . "'");
        if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
            require_once INC_PATH . "/functions_pm.php";
            send_pm($friendid, sprintf($lang->friends["msg2"], $friendname, "[URL=" . ts_seo($userid, $CURUSER["username"]) . "]" . $CURUSER["username"] . "[/URL]", "[URL]" . $BASEURL . $_SERVER["SCRIPT_NAME"] . "[/URL]"), $lang->friends["subject2"]);
            $errors[] = $lang->friends["sysmsg4"];
        }
    } else {
        $errors[] = $lang->friends["sysmsg3"];
    }
    $action = $tab;
}
if ($action == "add_block" && is_valid_id($friendid) && $userid != $friendid) {
    $query = sql_query("SELECT id FROM friends WHERE `userid` = '" . $userid . "' AND $friendid = '" . $friendid . "'");
    if (0 < mysqli_num_rows($query)) {
        sql_query("UPDATE friends SET `status` = 'b' WHERE `userid` = '" . $userid . "' AND $friendid = '" . $friendid . "'");
    } else {
        sql_query("INSERT INTO friends (userid, friendid, status) VALUES (" . $userid . ", " . $friendid . ", 'b')") || sqlerr(__FILE__, 119);
    }
    $action = "blocks";
}
if ($action == "add_friend" && is_valid_id($friendid) && $userid != $friendid) {
    $query = sql_query("SELECT id FROM friends WHERE `userid` = '" . $userid . "' AND $friendid = '" . $friendid . "'");
    $query2 = sql_query("SELECT id FROM friends WHERE `userid` = '" . $friendid . "' AND $friendid = '" . $userid . "' AND $status = 'b'");
    if (0 < mysqli_num_rows($query)) {
        $errors[] = $lang->friends["sysmsg5"];
    } else {
        if (0 < mysqli_num_rows($query2)) {
            $errors[] = $lang->friends["sysmsg6"];
        } else {
            ($query = sql_query("SELECT username,options FROM users WHERE `status` = 'confirmed' AND $enabled = 'yes' AND usergroup NOT IN (9) AND $id = '" . $friendid . "'")) || sqlerr(__FILE__, 139);
            if (0 < mysqli_num_rows($query) && $query) {
                $Result = mysqli_fetch_assoc($query);
                $friendprivacy = $Result["options"];
                $friendname = $Result["username"];
                if (!TS_Match($friendprivacy, "I4")) {
                    sql_query("INSERT INTO friends (userid, friendid, status) VALUES (" . $userid . ", " . $friendid . ", 'c')") || sqlerr(__FILE__, 147);
                    $errors[] = $lang->friends["sysmsg2"];
                } else {
                    sql_query("INSERT INTO friends (userid, friendid, status) VALUES (" . $userid . ", " . $friendid . ", 'p')") || sqlerr(__FILE__, 152);
                    require_once INC_PATH . "/functions_pm.php";
                    send_pm($friendid, sprintf($lang->friends["msg"], $friendname, "[URL=" . ts_seo($userid, $CURUSER["username"]) . "]" . $CURUSER["username"] . "[/URL]", "[URL]" . $BASEURL . $_SERVER["SCRIPT_NAME"] . "?$action = pending&$tab = pending[/URL]"), $lang->friends["subject"]);
                    $errors[] = $lang->friends["sysmsg1"];
                }
            } else {
                $errors[] = $lang->friends["sysmsg3"];
            }
        }
    }
}
$imagepath = $pic_base_url . "friends/";
stdhead($lang->friends["tab1"]);
show_friend_errors();
switch ($action) {
    case "pending":
        $status = "p";
        $where = "f.$friendid = " . $userid;
        $on = "f.$userid = u.id";
        $fwhat = "f.userid";
        break;
    case "mutual":
        $status = "c";
        $where = "f.$friendid = " . $userid;
        $on = "f.$userid = u.id";
        $fwhat = "f.userid";
        break;
    case "blocks":
        $status = "b";
        $where = "f.$userid = " . $userid;
        $on = "f.$friendid = u.id";
        $fwhat = "f.friendid";
        break;
    default:
        $status = "c";
        $where = "f.$userid = " . $userid;
        $on = "f.$friendid = u.id";
        $fwhat = "f.friendid";
        ($query = sql_query("SELECT " . $fwhat . " as friendid, f.status, u.id, u.username, u.options, u.title, u.avatar, u.last_access, u.last_login, u.added, u.added, u.enabled, u.donor, u.leechwarn, u.warned, p.canupload, p.candownload, p.cancomment, p.canmessage, p.canshout, g.namestyle, g.title as grouptitle FROM friends f INNER JOIN users u ON (" . $on . ") LEFT JOIN ts_u_perm p ON (u.`id` = p.userid) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE f.$status = '" . $status . "' AND " . $where . " ORDER by u.username")) || sqlerr(__FILE__, 195);
        echo "\n<div class=\"shadetabs\">\n\t<ul>\n\t\t<li" . (!$tab ? " class=\"selected\"" : "") . "><a $href = \"" . $_SERVER["SCRIPT_NAME"] . "\">" . $lang->friends["tab1"] . "</a></li>\n\t\t<li" . ($tab == "pending" ? " class=\"selected\"" : "") . "><a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$action = pending&amp;$tab = pending\">" . $lang->friends["tab2"] . "</a></li>\n\t\t<li" . ($tab == "blocks" ? " class=\"selected\"" : "") . "><a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$action = blocks&amp;$tab = blocks\">" . $lang->friends["tab3"] . "</a></li>\n\t\t<li" . ($tab == "mutual" ? " class=\"selected\"" : "") . "><a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$action = mutual&amp;$tab = mutual\">" . $lang->friends["tab4"] . "</a></li>\n\t</ul>\n</div>\n<table $width = \"100%\" $cellpadding = \"5\" $cellspacing = \"0\">\n";
        if (mysqli_num_rows($query) < 1) {
            echo "<tr><td>" . $lang->friends["nofriend"] . "</td></tr>";
        } else {
            $dt = TIMENOW - TS_TIMEOUT;
            include_once INC_PATH . "/functions_icons.php";
            while ($friend = mysqli_fetch_assoc($query)) {
                if (TS_Match($friend["options"], "L1")) {
                    $UserGender = "<img $src = \"" . $imagepath . "Male.png\" $alt = \"" . $lang->global["male"] . "\" $title = \"" . $lang->global["male"] . "\" $border = \"0\" class=\"inlineimg\" />";
                } else {
                    if (TS_Match($friend["options"], "L2")) {
                        $UserGender = "<img $src = \"" . $imagepath . "Female.png\" $alt = \"" . $lang->global["female"] . "\" $title = \"" . $lang->global["female"] . "\" $border = \"0\" class=\"inlineimg\" />";
                    } else {
                        $UserGender = "<img $src = \"" . $imagepath . "NA.png\" $alt = \"--\" $title = \"--\" $border = \"0\" class=\"inlineimg\" />";
                    }
                }
                $xoffline = sprintf($lang->friends["xoffline"], $friend["username"]);
                $xonline = sprintf($lang->friends["xonline"], $friend["username"]);
                $xavatar = sprintf($lang->friends["xavatar"], $friend["username"]);
                if (TS_Match($friend["options"], "B1") && !$is_mod && $friend["id"] != $userid) {
                    $friend["last_access"] = $friend["last_login"];
                    $onoffpic = "<img $src = \"" . $imagepath . "offline.png\" $alt = \"" . $xoffline . "\" $title = \"" . $xoffline . "\" $border = \"0\" />";
                } else {
                    if ($dt < TS_MTStoUTS($friend["last_access"]) || $friend["id"] == $userid) {
                        $onoffpic = "<img $src = \"" . $imagepath . "online.png\" $alt = \"" . $xonline . "\" $title = \"" . $xonline . "\" $border = \"0\" />";
                    } else {
                        $onoffpic = "<img $src = \"" . $imagepath . "offline.png\" $alt = \"" . $xoffline . "\" $title = \"" . $xoffline . "\" $border = \"0\" />";
                    }
                }
                echo "\n\t\t<tr>\n\t\t<td>\n\t\t<div>\n\t\t<div $style = \"border-right: 1px dotted black; float: left; margin-right: 3px;\">\n\t\t<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$action = remove_friend&amp;$friendid = " . $friend["friendid"] . ($tab == "pending" ? "&amp;$from = pending" : ($tab == "mutual" ? "&amp;$from = mutual" : "")) . "&amp;$tab = " . $action . "\" $title = \"" . $lang->friends["act1"] . "\"><img $src = \"" . $imagepath . "remove.gif\" $alt = \"\" $border = \"0\"></a>\n\t\t<br />";
                if ($friend["status"] == "p") {
                    echo "<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$action = confirm_friend&amp;$friendid = " . $friend["friendid"] . "&amp;$tab = " . $action . "\" $title = \"" . $lang->friends["act4"] . "\"><img $src = \"" . $imagepath . "confirm.png\" $alt = \"\" $border = \"0\"></a>";
                } else {
                    echo "<a $href = \"" . $BASEURL . "/sendmessage.php?$receiver = " . $friend["friendid"] . "\" $title = \"" . $lang->friends["act2"] . "\"><img $src = \"" . $imagepath . "pm.png\" $alt = \"\" $border = \"0\"></a>";
                }
                echo "\n\t\t</div>\n\n\t\t<div $style = \"float: right;\">\n\t\t<img $src = \"" . ($friend["avatar"] ? fix_url($friend["avatar"]) : $pic_base_url . "default_avatar.png") . "\" $alt = \"" . $xavatar . "\" $title = \"" . $xavatar . "\" $height = \"40\" $width = \"40\">\n\t\t</div>\n\t\t" . $UserGender . "\n\t\t<strong><a $href = \"" . ts_seo($friend["friendid"], $friend["username"]) . "\">" . get_user_color($friend["username"], $friend["namestyle"]) . "</a></strong> (" . ($friend["title"] ? htmlspecialchars_uni($friend["title"]) : $friend["grouptitle"]) . ") " . get_user_icons($friend) . "\n\t\t<br />\n\t\t" . $onoffpic . "\n\t\t<strong>" . $lang->friends["act3"] . " " . my_datee($dateformat, $friend["last_access"]) . " " . my_datee($timeformat, $friend["last_access"]) . "</strong>\n\t\t</div>\n\t\t</td>\n\t\t</tr>\n\t\t";
            }
        }
        echo "\n</table>";
        stdfoot();
}
function show_friend_errors()
{
    global $errors;
    global $lang;
    if (0 < count($errors)) {
        $error = implode("<br />", $errors);
        echo "\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\n\t\t\t<tr>\n\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t" . $lang->friends["sysmsg"] . "\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t\t<td>\n\t\t\t\t\t<font $color = \"red\">\n\t\t\t\t\t\t<strong>\n\t\t\t\t\t\t\t" . htmlspecialchars_uni($error) . "\n\t\t\t\t\t\t</strong>\n\t\t\t\t\t</font>\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t\t</table>\n\t\t\t<br />\n\t\t";
    }
}

?>