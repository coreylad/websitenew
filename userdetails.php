<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("UD_VERSION", "4.1 by xam");
define("THIS_SCRIPT", "userdetails.php");
require "./global.php";
if (!isset($CURUSER) || isset($CURUSER) && $CURUSER["id"] == 0) {
    print_no_permission();
}
$lang->load("userdetails");
$userid = isset($_GET["id"]) ? intval($_GET["id"]) : (isset($_POST["id"]) ? intval($_POST["id"]) : intval($CURUSER["id"]));
$IsStaff = $is_mod;
$SameUser = $userid == $CURUSER["id"] ? true : false;
$defaulttemplate = ts_template();
$dimagedir = $BASEURL . "/include/templates/" . $defaulttemplate . "/images/";
if (!is_valid_id($userid)) {
    stderr($lang->global["error"], $lang->userdetails["invaliduser"], false);
}
if (PROFILE_MAX_VISITOR != -1 && PROFILE_MAX_VISITOR < 2) {
    define("PROFILE_MAX_VISITOR", 2);
}
$query = sql_query("SELECT userid FROM ts_profilevisitor WHERE `userid` = '" . $userid . "' AND $visible = '1' GROUP BY userid HAVING COUNT(*) > " . PROFILE_MAX_VISITOR);
if (0 < mysqli_num_rows($query)) {
    while ($user = mysqli_fetch_assoc($query)) {
        $QQuery = sql_query("SELECT userid, visitorid, dateline FROM ts_profilevisitor WHERE `userid` = '" . $user["userid"] . "' ORDER BY dateline DESC LIMIT " . PROFILE_MAX_VISITOR . ", 1");
        if (0 < mysqli_num_rows($QQuery)) {
            while ($delete = mysqli_fetch_assoc($QQuery)) {
                sql_query("DELETE FROM ts_profilevisitor WHERE $dateline = '" . $delete["dateline"] . "' AND $userid = '" . $delete["userid"] . "' AND $visitorid = '" . $delete["visitorid"] . "'");
            }
        }
    }
    unset($user);
    unset($delete);
}
if (!($SameUser || $usergroups["canviewotherprofile"] === "yes" || $is_mod)) {
    print_no_permission();
}
($Query = sql_query("SELECT u.*, p.canupload, p.candownload, p.cancomment, p.canmessage, p.canshout, c.name as countryname, c.flagpic, g.namestyle, g.title, g.cansettingspanel, v.vip_until FROM users u LEFT JOIN ts_u_perm p ON (u.`id` = p.userid) LEFT JOIN countries c ON (u.$country = c.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) LEFT JOIN ts_auto_vip v ON (u.`id` = v.userid) WHERE u.$id = " . $userid)) || sqlerr(__FILE__, 71);
if (0 < mysqli_num_rows($Query)) {
    $user = mysqli_fetch_assoc($Query);
} else {
    stderr($lang->global["error"], $lang->userdetails["invaliduser"], false);
}
if ((TS_Match($user["options"], "I3") || TS_Match($user["options"], "I4")) && !$IsStaff && !$SameUser) {
    $query = sql_query("SELECT id FROM friends WHERE `status` = 'c' AND $userid = " . $userid . " AND $friendid = " . (int) $CURUSER["id"]);
    if (!mysqli_num_rows($query) || TS_Match($user["options"], "I4")) {
        print_no_permission(false, true, $lang->userdetails["noperm"]);
    }
}
if ($user["status"] == "pending") {
    stderr($lang->global["error"], $lang->userdetails["pendinguser"]);
} else {
    if (!$user["username"] || !$user) {
        stderr($lang->global["error"], $lang->userdetails["invaliduser"], false);
    }
}
if ($user["cansettingspanel"] == "yes" && !$SameUser) {
    $user["ip"] = $lang->userdetails["hidden"];
} else {
    $user["ip"] = htmlspecialchars_uni($user["ip"]);
}
$user["email"] = htmlspecialchars_uni($user["email"]);
$user["page"] = "<a $href = \"" . str_replace("&amp;", "&", htmlspecialchars_uni($user["page"])) . "\" $alt = \"" . htmlspecialchars_uni($user["page"]) . "\" $title = \"" . htmlspecialchars_uni($user["page"]) . "\">" . cutename($user["page"], 30) . "</a>";
if (TS_Match($user["options"], "B1") && !$IsStaff && !$SameUser) {
    $user["last_access"] = $user["last_login"];
}
$USERMOOD = "";
if ($user["mood"]) {
    $Moods = [];
    if (is_dir("./images/moods/")) {
        $mooddirectory = "./images/moods/";
        $_FILES = scandir("./images/moods/");
    } else {
        if (is_dir("./pic/moods/")) {
            $mooddirectory = "./pic/moods/";
            $_FILES = scandir("./pic/moods/");
        }
    }
    if (isset($_FILES) && $_FILES && is_array($_FILES) && count($_FILES)) {
        foreach ($_FILES as $_FILENAME) {
            if ($_FILENAME != "." && $_FILENAME != ".." && in_array(get_extension($_FILENAME), ["gif", "png", "bmp", "jpg"])) {
                $Moods[] = $_FILENAME;
            }
        }
        foreach ($Moods as $MoodNumber => $MoodName) {
            $MoodNumber++;
            if ($MoodNumber == $user["mood"] && is_file($mooddirectory . $MoodName)) {
                $MoodAlt = $lang->userdetails["usermood"] . ": " . htmlspecialchars_uni(str_replace([".gif", ".png", ".bmp", ".jpg"], "", $MoodName));
                $USERMOOD = "<img $src = \"" . $pic_base_url . "moods/" . htmlspecialchars_uni($MoodName) . "\" $alt = \"" . $MoodAlt . "\" $title = \"" . $MoodAlt . "\" $border = \"0\" />";
            }
        }
    }
}
if ($user["invited_by"]) {
    ($query = sql_query("SELECT u.username, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$id = '" . $user["invited_by"] . "'")) || sqlerr(__FILE__, 153);
    if (0 < mysqli_num_rows($query)) {
        $IUser = mysqli_fetch_assoc($query);
        $user["invited_by"] = "<a $href = \"" . ts_seo($user["invited_by"], $IUser["username"]) . "\">" . get_user_color($IUser["username"], $IUser["namestyle"]) . "</a>";
    }
}
if ($userid != $CURUSER["id"]) {
    sql_query("\n\t\tREPLACE INTO ts_profilevisitor\n\t\t\t(userid, visitorid, dateline, visible)\n\t\tVALUES\n\t\t\t(\n\t\t\t\t'" . $userid . "',\n\t\t\t\t'" . $CURUSER["id"] . "',\n\t\t\t\t'" . TIMENOW . "',\n\t\t\t\t'1'\n\t\t\t)\n\t");
    sql_query("UPDATE users SET $visitorcount = visitorcount + 1 WHERE `id` = " . sqlesc($userid));
    $user["visitorcount"]++;
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && $_POST["do"] == "save_vmsg") {
    if (!$SameUser && TS_Match($user["options"], "M3")) {
        $error[] = $lang->userdetails["cerror4"];
    } else {
        if (!$SameUser && TS_Match($user["options"], "M2") && !$IsStaff) {
            ($query = sql_query("SELECT id FROM friends WHERE `status` = 'c' AND $userid = " . $userid . " AND $friendid = " . (int) $CURUSER["id"])) || sqlerr(__FILE__, 186);
            if (mysqli_num_rows($query) < 1) {
                $error[] = $lang->userdetails["cerror4"];
            }
        }
    }
    if (!$error) {
        $message = $_POST["message"];
        $msglong = strlen($message);
        if ($usergroups["cancomment"] == "no") {
            $error[] = $lang->global["nopermission"];
        } else {
            if (empty($message) || $msglong < 3) {
                $error[] = $lang->userdetails["cerror2"];
            } else {
                if (5000 < $msglong) {
                    $error[] = sprintf($lang->userdetails["cerror3"], $msglong);
                } else {
                    if ($_POST["isupdate"] && is_valid_id($_POST["isupdate"]) && $IsStaff) {
                        sql_query("UPDATE ts_visitor_messages SET $visitormsg = " . sqlesc($message) . " WHERE `id` = " . sqlesc(intval($_POST["isupdate"])));
                    } else {
                        sql_query("INSERT INTO ts_visitor_messages (userid,visitorid,visitormsg,added) VALUES (" . sqlesc($userid) . ", " . sqlesc($CURUSER["id"]) . "," . sqlesc($message) . ", '" . TIMENOW . "')") || sqlerr(__FILE__, 217);
                    }
                }
            }
        }
    }
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "GET" && isset($_GET["do"]) && $_GET["do"] == "delete_msg" && $IsStaff) {
    $Dmsg_id = intval($_GET["msg_id"]);
    if (is_valid_id($Dmsg_id)) {
        sql_query("DELETE FROM ts_visitor_messages WHERE `id` = " . sqlesc($Dmsg_id));
    }
}
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "GET" && isset($_GET["do"]) && $_GET["do"] == "edit_msg" && $IsStaff) {
    $Emsg_id = intval($_GET["msg_id"]);
    if (is_valid_id($Emsg_id)) {
        $eQuery = sql_query("SELECT visitormsg FROM ts_visitor_messages WHERE `id` = " . sqlesc($Emsg_id));
        if (0 < mysqli_num_rows($eQuery)) {
            $Result = mysqli_fetch_assoc($eQuery);
            $Vmsg = htmlspecialchars_uni($Result["visitormsg"]);
        }
    }
}
stdhead(sprintf($lang->userdetails["title"], $user["username"]), true, "<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/prototype.js?$v = " . O_SCRIPT_VERSION . "\"></script>");
if (TS_Match($user["options"], "A1") && $is_mod) {
    echo show_notice($lang->userdetails["parked"]);
}
require INC_PATH . "/functions_icons.php";
$Buttons = $CURUSER["id"] != $userid ? "\n<input $type = \"button\" class=\"button\" $value = \"" . $lang->userdetails["button1"] . "\" $onclick = \"jumpto('" . $BASEURL . "/sendmessage.php?$receiver = " . $userid . "'); return false;\" />\n<input $type = \"button\" class=\"button\" $value = \"" . $lang->userdetails["button2"] . "\" $onclick = \"TSOpenPopup('" . $BASEURL . "/report.php?$type = 1&$reporting = " . $userid . "', 'report', 500, 300); return false;\" />\n<input $type = \"button\" class=\"button\" $value = \"" . $lang->userdetails["button3"] . "\" $onclick = \"window.open('" . $BASEURL . "/transfer.php?$receiver = " . $userid . "','transfer','toolbar=no, $scrollbars = no, $resizable = no, $width = 700, $height = 350, $top = 250, $left = 250'); return false;\" />\n<input $type = \"button\" class=\"button\" $value = \"" . $lang->userdetails["button4"] . "\" $onclick = \"jumpto('" . $BASEURL . "/friends.php?$action = add_friend&$friendid = " . $userid . "'); return false;\" />\n<input $type = \"button\" class=\"button\" $value = \"" . $lang->userdetails["button5"] . "\" $onclick = \"jumpto('" . $BASEURL . "/friends.php?$action = add_block&$friendid = " . $userid . "&$tab = blocks'); return false;\" />" : "";
$imagepath = $pic_base_url . "friends/";
if (TS_Match($user["options"], "L1")) {
    $UserGender = "<img $src = \"" . $imagepath . "Male.png\" $alt = \"" . $lang->global["male"] . "\" $title = \"" . $lang->global["male"] . "\" $border = \"0\" class=\"inlineimg\" />";
} else {
    if (TS_Match($user["options"], "L2")) {
        $UserGender = "<img $src = \"" . $imagepath . "Female.png\" $alt = \"" . $lang->global["female"] . "\" $title = \"" . $lang->global["female"] . "\" $border = \"0\" class=\"inlineimg\" />";
    } else {
        $UserGender = "<img $src = \"" . $imagepath . "NA.png\" $alt = \"--\" $title = \"--\" $border = \"0\" class=\"inlineimg\" />";
    }
}
$UserInfo = ["username" => get_user_color($user["username"], $user["namestyle"]), "title" => get_user_color($user["title"], $user["namestyle"]), "joindate" => my_datee($regdateformat, $user["added"]), "lastaccess" => my_datee($dateformat, $user["last_access"]) . " " . my_datee($timeformat, $user["last_access"]), "page" => $IsStaff || $SameUser ? $user["page"] : $lang->userdetails["hidden"]];
require_once INC_PATH . "/function_user_rank.php";
$image = user_rank($user);
require_once INC_PATH . "/functions_ts_get_awards.php";
$awards = TSGetAwards($userid, $user["username"]);
require_once INC_PATH . "/functions_mkprettytime.php";
$country = "<img $src = \"" . $pic_base_url . "flag/" . ($user["flagpic"] ? $user["flagpic"] : "jollyroger.gif") . "\" $border = \"0\" $alt = \"" . $user["countryname"] . "\" $title = \"" . $user["countryname"] . "\" $height = \"25\" $width = \"36\" />";
$userbday = $user["birthday"] ? showFriendlyAge($user["birthday"]) : false;
$email = TS_Match($user["options"], "I1") || $SameUser || $IsStaff ? $user["email"] : $lang->userdetails["hidden"];
$uploaded = mksize($user["uploaded"]);
$downloaded = mksize($user["downloaded"]);
$signature = $user["signature"] ? "<hr />" . format_comment($user["signature"]) : "";
$donoruntil = ($SameUser || $IsStaff) && $user["donoruntil"] != "0000-00-00 00:00:00" ? sprintf($lang->userdetails["donoruntil"], mkprettytime(TS_MTStoUTS($user["donoruntil"]) - gmtime())) . "<br />" : "";
if (!$donoruntil && ($SameUser || $IsStaff) && isset($user["vip_until"]) && !empty($user["vip_until"]) && $user["vip_until"] != "0000-00-00 00:00:00") {
    $donoruntil = sprintf($lang->userdetails["donoruntil"], mkprettytime(TS_MTStoUTS($user["vip_until"]) - gmtime())) . "<br />";
}
$donated = $SameUser || $IsStaff ? sprintf($lang->userdetails["donated"], number_format($user["donated"], 1), number_format($user["total_donated"], 1)) . "<br />" : "";
$kps = $SameUser || $IsStaff ? "<br />" . sprintf($lang->userdetails["kps"], number_format($user["seedbonus"], 2)) : "";
$xoffline = sprintf($lang->userdetails["xoffline"], $user["username"]);
$xonline = sprintf($lang->userdetails["xonline"], $user["username"]);
// Time delta for user online status
$userTimeoutDelta = TIMENOW - TS_TIMEOUT;
if (TS_Match($user["options"], "B1") && !$SameUser && !$IsStaff) {
    $onoffpic = "<img $src = \"" . $imagepath . "offline.png\" $alt = \"" . $xoffline . "\" $title = \"" . $xoffline . "\" $border = \"0\" class=\"inlineimg\" />";
} else {
    if ($userTimeoutDelta < TS_MTStoUTS($user["last_access"]) || $SameUser) {
        $onoffpic = "<img $src = \"" . $imagepath . "online.png\" $alt = \"" . $xonline . "\" $title = \"" . $xonline . "\" $border = \"0\" class=\"inlineimg\" />";
    } else {
        $onoffpic = "<img $src = \"" . $imagepath . "offline.png\" $alt = \"" . $xoffline . "\" $title = \"" . $xoffline . "\" $border = \"0\" class=\"inlineimg\" />";
    }
}
// Share ratio and display
$shareRatio = "";
$shareRatioImage = "";
if (0 < $user["downloaded"]) {
    $shareRatio = $user["uploaded"] / $user["downloaded"];
    if (4 <= $shareRatio) {
        $shareRatioSmiley = "w00t";
    } else if (2 <= $shareRatio) {
        $shareRatioSmiley = "grin";
    } else if (1 <= $shareRatio) {
        $shareRatioSmiley = "smile1";
    } else if (0 <= $shareRatio) {
        $shareRatioSmiley = "noexpression";
    } else if (0 <= $shareRatio) {
        $shareRatioSmiley = "sad";
    } else {
        $shareRatioSmiley = "cry";
    }
    $shareRatio = floor($shareRatio * 1000) / 1000;
    $shareRatioImage = " <img $src = \"" . $pic_base_url . "smilies/" . $shareRatioSmiley . ".gif\" $border = \"0\" $alt = \"\" $title = \"\" class=\"inlineimg\" />";
}
$shareRatioDisplay = 0 < $user["downloaded"] ? number_format($user["uploaded"] / $user["downloaded"], 2) : (0 < $user["uploaded"] ? "INF." : "---");
$shareRatioDisplay = "<font $color = \"" . get_ratio_color($shareRatio) . "\">" . $shareRatioDisplay . $shareRatioImage . "</font>";
require TSDIR . "/" . $cache . "/downloadspeed.php";
require TSDIR . "/" . $cache . "/uploadspeed.php";
$downloadspeed = $uploadspeed = "--";
$UserSpeed = explode("~", $user["speed"]);
if (isset($_downloadspeed[$UserSpeed[0]])) {
    $downloadspeed = $_downloadspeed[$UserSpeed[0]]["name"];
}
if (isset($_uploadspeed[$UserSpeed[1]])) {
    $uploadspeed = $_uploadspeed[$UserSpeed[1]]["name"];
}
unset($_uploadspeed);
unset($_downloadspeed);
$lang->load("quick_editor");
require INC_PATH . "/functions_quick_editor.php";
require_once INC_PATH . "/class_tsquickbbcodeeditor.php";
$QuickEditor = new TSQuickBBCodeEditor();
$QuickEditor->ImagePath = $pic_base_url;
$QuickEditor->SmiliePath = $pic_base_url . "smilies/";
$QuickEditor->FormName = "quickreply";
$QuickEditor->TextAreaName = "message";
$VisitorMessagesForm = "\n" . $QuickEditor->GenerateJavascript() . "\n" . ($useajax == "yes" ? "\n<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/quick_vm.js\"></script>" : "") . "\n<form $method = \"POST\" $action = \"" . $_SERVER["SCRIPT_NAME"] . "?$id = " . $userid . "&do=save_vmsg\" $name = \"quickreply\" $id = \"quickreply\">\n<input $type = \"hidden\" $name = \"userid\" $value = \"" . $userid . "\" />\n<input $type = \"hidden\" $name = \"securitytoken\" $value = \"" . $CURUSER["securitytoken"] . "\" />\n<input $type = \"hidden\" $name = \"do\" $value = \"save_vmsg\" />\n" . (isset($Vmsg) ? "\n<input $type = \"hidden\" $name = \"isupdate\" $value = \"" . $Emsg_id . "\" />" : "") . "\n<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\n\t<tr>\n\t\t<td class=\"none\" $id = \"showvisitormessage\">\n\t\t\t" . $QuickEditor->GenerateBBCode() . "\n\t\t\t<br />\n\t\t\t<textarea $name = \"message\" $style = \"width:670px;height:85px;\" $id = \"message\">" . (isset($Vmsg) ? $Vmsg : (isset($message) ? $message : "")) . "</textarea><br />\n\t\t\t<span $id = \"loading-layer\" $style = \"display:none;\"><img $src = \"" . $dimagedir . "loading.gif\" $border = \"0\" $alt = \"\" $title = \"\" class=\"inlineimg\" /></span>\n\t\t\t" . ($useajax == "yes" ? "\n\t\t\t<input $type = \"button\" class=\"button\" $value = \"" . (isset($Vmsg) ? $lang->userdetails["visitormsg6"] : $lang->userdetails["visitormsg2"]) . "\" $name = \"submitvm\" $id = \"submitvm\" $onclick = \"javascript:TSajaxquickvm('" . $userid . "', '" . (isset($Vmsg) ? $Emsg_id : 0) . "');\" />" : "<input $type = \"submit\" $name = \"submit\" $value = \"" . (isset($Vmsg) ? $lang->userdetails["visitormsg6"] : $lang->userdetails["visitormsg2"]) . "\" class=\"button\" />") . "\n\t\t\t<input $type = \"reset\" $value = \"" . $lang->userdetails["visitormsg3"] . "\" class=\"button\" />\n\t\t</td>\n\t</tr>\n</table>\n</form>\n";
$VisitorMessages = "\n\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"2\" $cellspacing = \"0\">\n\t\t<tr>\n\t\t\t<td class=\"thead\">" . ts_collapse("content1a1") . $lang->userdetails["visitormsg1"] . "</td>\n\t\t</tr>\n\t\t" . ts_collapse("content1a1", 2) . "\n\t\t<tr>\n\t\t\t<td $id = \"PostedQuickVisitorMessages\" $name = \"PostedQuickVisitorMessages\" $style = \"display: none;\">\n\t\t\t</td>\n\t\t</tr>";
$Query = sql_query("SELECT id FROM ts_visitor_messages WHERE `userid` = " . sqlesc($userid));
$Count = mysqli_num_rows($Query);
list($pagertop, $pagerbottom, $limit) = pager($ts_perpage, $Count, ts_seo($userid, $user["username"]) . "&");
($Query2 = sql_query("SELECT v.id as visitormsgid, v.visitorid, v.visitormsg, v.added, u.username, u.avatar, g.namestyle FROM ts_visitor_messages v LEFT JOIN users u ON (v.$visitorid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE v.$userid = " . sqlesc($userid) . " ORDER by v.added DESC " . $limit)) || sqlerr(__FILE__, 403);
if (0 < mysqli_num_rows($Query2)) {
    while ($visitorMessage = mysqli_fetch_assoc($Query2)) {
        $visitorUsername = get_user_color($visitorMessage["username"], $visitorMessage["namestyle"]);
        $visitorAvatar = get_user_avatar($visitorMessage["avatar"], false, 60, 60);
        $visitorAdded = my_datee($dateformat, $visitorMessage["added"]) . " " . my_datee($timeformat, $visitorMessage["added"]);
        $visitorPoster = "<a $href = \"" . ts_seo($visitorMessage["visitorid"], $visitorMessage["username"]) . "\">" . $visitorUsername . "</a>";
        $visitorMessageText = format_comment($visitorMessage["visitormsg"]);
        $VisitorMessages .= "\n\t\t<tr>\n\t\t\t<td $id = \"ShowVisitorMessage" . $visitorMessage["visitormsgid"] . "\" $name = \"ShowVisitorMessage" . $visitorMessage["visitormsgid"] . "\">\n\t\t\t\t<div $style = \"float: left;\">" . $visitorAvatar . "</div>\n\t\t\t\t<div $style = \"overflow:auto; padding: 2px;\">\n\t\t\t\t\t<div class=\"subheader\">\n\t\t\t\t\t\t<span $style = \"float: right;\">[<a $onclick = \"TSOpenPopup('" . $BASEURL . "/report.php?$type = 5&$reporting = " . $visitorMessage["visitormsgid"] . "&$extra = " . $userid . "&$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "', 'report', 500, 300); return false;\" $href = \"javascript:void(0);\">" . $lang->userdetails["reportmsg"] . "</a>] " . ($IsStaff ? " [<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$id = " . $userid . "&do=delete_msg&$msg_id = " . $visitorMessage["visitormsgid"] . "\">" . $lang->userdetails["deletemsg"] . "</a>] [<a $href = \"" . $_SERVER["SCRIPT_NAME"] . "?$id = " . $userid . "&do=edit_msg&$msg_id = " . $visitorMessage["visitormsgid"] . "&$scrollto = showvisitormessage\">" . $lang->userdetails["editmsg"] . "</a>]" : "") . "</span> " . sprintf($lang->userdetails["visitormsg5"], $visitorAdded, $visitorPoster) . "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div $name = \"msg" . $visitorMessage["visitormsgid"] . "\" $id = \"msg" . $visitorMessage["visitormsgid"] . "\">" . $visitorMessageText . "</div>\n\t\t\t\t</div>\n\t\t\t</td>\n\t\t</tr>\n\t\t";
    }
} else {
    $VisitorMessages .= "\n\t<tr>\n\t\t<td>\n\t\t\t" . sprintf($lang->userdetails["visitormsg4"], $user["username"]) . "\n\t\t</td>\n\t</tr>";
}
$VisitorMessages .= "</table>";
if (0 < PROFILE_MAX_VISITOR) {
    $RecentVisitorsArray = [];
    $VQuery = sql_query("SELECT v.visitorid, u.username, g.namestyle FROM ts_profilevisitor v LEFT JOIN users u ON (v.$visitorid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE v.$userid = " . sqlesc($userid) . " ORDER By v.dateline DESC LIMIT " . PROFILE_MAX_VISITOR);
    if (0 < mysqli_num_rows($VQuery)) {
        while ($RV = mysqli_fetch_assoc($VQuery)) {
            $RecentVisitorsArray[] = "<a $href = \"" . ts_seo($RV["visitorid"], $RV["username"]) . "\">" . get_user_color($RV["username"], $RV["namestyle"]) . "</a>";
        }
    }
}
$SocialGroups = [];
$SGQuery = sql_query("SELECT m.groupid, sg.name FROM ts_social_group_members m LEFT JOIN ts_social_groups sg ON (m.$groupid = sg.groupid) WHERE m.$userid = " . sqlesc($userid) . " AND m.$type = 'public'");
if (0 < mysqli_num_rows($SGQuery)) {
    while ($SG = mysqli_fetch_assoc($SGQuery)) {
        $SocialGroups[] = "<a $href = \"" . $BASEURL . "/ts_social_groups.php?do=showgroup&amp;$groupid = " . $SG["groupid"] . "\">" . cutename($SG["name"], 50) . "</a>";
    }
}
$IM = "";
if ($user["contact"] != "" && $user["contact"] != "||||") {
    $lang->load("im");
    $CA_ARRAY = @explode("|", $user["contact"]);
    $CA = [];
    foreach ($CA_ARRAY as $_L => $_R) {
        $CA[$_L] = htmlspecialchars_uni($_R);
    }
    $IM = ($CA[0] ? "<div><img $src = \"" . $pic_base_url . "contact/icq.gif\" $alt = \"" . $lang->im["icq"] . "\" $border = \"0\" class=\"inlineimg\" /> <a $href = \"javascript:void(0);\" $onclick = \"TSOpenPopup('http://www.icq.com/people/webmsg.php?$to = " . urlencode($CA[0]) . "&$from = " . urlencode($CURUSER["username"]) . "&$fromemail = " . urlencode($CURUSER["email"]) . "', 'icq', 600, 600);\"><u>" . $CA[0] . "</u></a></div>" : "") . ($CA[1] ? "<div><img $src = \"" . $pic_base_url . "contact/aim.gif\" $alt = \"" . $lang->im["aim"] . "\" $border = \"0\" class=\"inlineimg\" /> <a $href = \"javascript:void(0);\" $onclick = \"TSOpenPopup('" . $BASEURL . "/im.php?$type = aim&amp;$userid = " . $user["id"] . "', 'AIM', 600, 150);\"><u>" . $CA[1] . "</u></a></div>" : "") . ($CA[2] ? "<div><img $src = \"" . $pic_base_url . "contact/skype.gif\" $alt = \"" . $lang->im["skype"] . "\" $border = \"0\" class=\"inlineimg\" /> <a $href = \"javascript:void(0);\" $onclick = \"TSOpenPopup('" . $BASEURL . "/im.php?$type = skype&amp;$userid = " . $user["id"] . "', 'SKYPE', 600, 300);\"><u>" . $CA[2] . "</u></a></div>" : "") . ($CA[3] ? "<div><img $src = \"" . $pic_base_url . "contact/msn.gif\" $alt = \"" . $lang->im["msn"] . "\" $border = \"0\" class=\"inlineimg\" /> <a $href = \"javascript:void(0);\" $onclick = \"TSOpenPopup('" . $BASEURL . "/im.php?$type = msn&amp;$userid = " . $user["id"] . "', 'MSN', 600, 150);\"><u>" . $CA[3] . "</u></a></div>" : "") . ($CA[4] ? "<div><img $src = \"" . $pic_base_url . "contact/yahoo.gif\" $alt = \"" . $lang->im["yahoo"] . "\" $border = \"0\" class=\"inlineimg\" /> <a $href = \"javascript:void(0);\" $onclick = \"TSOpenPopup('" . $BASEURL . "/im.php?$type = yahoo&amp;$userid = " . $user["id"] . "', 'YAHOO', 600, 150);\"><u>" . $CA[4] . "</u></a></div>" : "");
}
$StaffTools = $rating = "";
if ($ratingsystem == "yes") {
    if ($CURUSER["id"] == $userid) {
        $usergroups["canrate"] = "no";
    }
    require INC_PATH . "/class_ts_rating.php";
    $TSRating = new TS_Rating("user_" . $userid, $CURUSER["id"]);
    $SHowForm = $TSRating->ShowForm($lang->userdetails["rateuser"]);
    $rating = "\n\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/quick_rate.js?$v = " . O_SCRIPT_VERSION . "\"></script>\n\t<div $id = \"tsrating_results\" $name = \"tsrating_results\" $style = \"padding-bottom: 5px; padding-top: 10px;\">\n\t\t" . $TSRating->GetScore($lang->userdetails["ratedetails"]) . "\n\t\t" . ($SHowForm ? $SHowForm : "<br /><i><b>" . $lang->userdetails["alreadyvotes"] . "</b></i>") . "\n\t</div>\n\t";
}
if ($IsStaff) {
    $StaffTools = "\n\t<span $style = \"float: right\">\n\t\t<a $href = \"javascript:void(0);\" $id = \"manageaccount\">Manage Account</a>\n\t\t<script $type = \"text/javascript\">\n\t\t\tmenu_register(\"manageaccount\", true);\n\t\t</script>\n\t\t<div $id = \"manageaccount_menu\" class=\"menu_popup\" $style = \"display:none;\">\n\t\t\t<table $border = \"1\" $cellspacing = \"0\" $cellpadding = \"2\">\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"thead\">Manage Account</td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=edit_user&amp;$username = " . $user["username"] . "\">" . $lang->global["qinfo6"] . "</a></td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=warn_user&amp;$username = " . $user["username"] . "\">" . $lang->global["qinfo7"] . "</a></td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/badusers.php?$act = insert&do=save&$username = " . urlencode($user["username"]) . "&$email = " . urlencode($user["email"]) . "&$ipaddress = " . urlencode($user["ip"]) . "&$userid = " . $userid . "&$comment = Bad+User\">Insert Into Badusers</a></td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/ts_watch_list.php?$action = add&$userid = " . $userid . "\">Watch This User</a></td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"thead\">Award Manager</td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=manage_awards&$act = give_award&$username = " . htmlspecialchars($user["username"]) . "\">Give Award</a></td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=manage_awards&$username = " . htmlspecialchars($user["username"]) . "\">Manage User Awards</a></td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"thead\">Staff Tools</td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=invite_tree&$username = " . $user["username"] . "\">Show Invite Tree</a></td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=ip_info&amp;$ip = " . urlencode($user["ip"]) . "\">Show IP Info</a></td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/tsf_forums/tsf_search.php?$action = finduserthreads&$id = " . $userid . "\">Show User Threads</a></td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/tsf_forums/tsf_search.php?$action = finduserposts&$id = " . $userid . "\">Show User Posts</a></td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/userhistory.php?$action = viewcomments&$id = " . $userid . "\">Show User Comments</a></td>\n\t\t\t\t</tr>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=hit_and_run&$show_by_userid = " . $userid . "\">Hit & Run</a></td>\n\t\t\t\t</tr>\n\t\t\t</table>\n\t\t</div>\n\t\t<script $type = \"text/javascript\">\n\t\t\tmenu.activate(true);\n\t\t</script>&nbsp;&nbsp;\n\t</span>\n\t";
} else {
    $StaffTools = "\n\t<span $style = \"float: right\">\n\t\t<script $type = \"text/javascript\">\n\t\t\tmenu.activate(true);\n\t\t</script>&nbsp;&nbsp;\n\t</span>\n\t";
}
$Query = sql_query("SELECT * FROM ts_custom_profiles WHERE `userid` = " . sqlesc($userid));
if (mysqli_num_rows($Query)) {
    $UserProfileOptions = mysqli_fetch_assoc($Query);
    $USERSTYLE = "\n\t<style $type = \"text/css\">\n\t\n\t\tbody\n\t\t{\n\t\t\tbackground: #" . $UserProfileOptions["bg_color"] . ";\n\t\t}\n\n\t\t#main .left_side, #main .left_side_users\n\t\t{\n\t\t\tbackground: #" . $UserProfileOptions["left_side_bg_color"] . ";\t\t\n\t\t}\n\n\t\t table, .notification-border-e, table .main, textarea, .button\n\t\t{\n\t\t\tbackground: #" . $UserProfileOptions["table_bg_color"] . ";\n\t\t}\n\n\t\t .thead, .thead a, .notification-title-e, .notification-th-e\n\t\t{\n\t\t\tbackground: #" . $UserProfileOptions["thead_bg_color"] . ";\n\t\t}\t\n\n\t\t.subheader, .colhead, table .main .colhead\n\t\t{\n\t\t\tbackground: #" . $UserProfileOptions["sub_header_bg_color"] . ";\n\t\t}\n\n\t\ttable a\n\t\t{\n\t\t\tcolor: #" . $UserProfileOptions["link_color"] . ";\n\t\t}\n\n\t\t.subheader, .colhead, table .main .colhead, #main .left_side, #main .left_side_users, td, .notification-border-e, .thead, .thead a, .notification-title-e, .notification-th-e, textarea, .button\n\t\t{\n\t\t\tcolor: #" . $UserProfileOptions["text_color"] . ";\n\t\t}\n\n\t\ttd, .notification-border-e\n\t\t{\n\t\t\tborder: solid #" . $UserProfileOptions["border_color"] . " 1px;\n\t\t}\n\n\t\t#main .left_side, #main .left_side_users, td, .notification-border-e, .thead, .thead a, .notification-title-e, .notification-th-e, textarea, .button\n\t\t{\n\t\t\tfont-size: " . $UserProfileOptions["font_size"] . "; font-family: " . $UserProfileOptions["font_family"] . ";\n\t\t}\n\t</style>";
    echo $USERSTYLE;
}
echo "<script $type = \"text/javascript\">\n\tfunction getTabData(id, ip)\n\t{\n\t\tvar $url = baseurl+'/ts_ajax2.php';\n\t\tvar $rand = Math.random(9999);\n\t\tvar $pars = 'what=' + id + (ip ? '&$ip = '+ip : '')+'&$userid = ";
echo $userid;
echo "&$rand = ' + rand + '&$securitytoken = ' + securitytoken;\n\t\tvar $myAjax = new Ajax.Request\n\t\t( \n\t\t\turl,\n\t\t\t{\n\t\t\t\tmethod: 'POST',\n\t\t\t\tcontentType: 'application/x-www-form-urlencoded',\n\t\t\t\tencoding: \t'";
echo $charset;
echo "',\n\t\t\t\tparameters: pars,\n\t\t\t\tonLoading: showLoad,\n\t\t\t\tonComplete: showResponse,\n\t\t\t\tonFailure: function ()\n\t\t\t\t{\n\t\t\t\t\talert(l_ajaxerror);\n\t\t\t\t}\n\t\t\t}\n\t\t);\n\t}\n\tfunction showLoad ()\n\t{\n\t\t\$('load').style.$display = 'block';\n\t}\n\tfunction showResponse (originalRequest)\n\t{\n\t\tvar $newData = originalRequest.responseText;\n\t\t\$('load').style.$display = 'none';\n\t\t\$('showcontents').$innerHTML = newData;\n\t}\n\tfunction TSAjaxRequest(WhatToShow, UserIP)\n\t{\n\t\tvar TSElement = document.getElementById('hiddencontents');\n\t\tTSElement.style.$display = 'block';\n\t\tdocument.getElementById('showcontents').$innerHTML = '';\n\t\tgetTabData(WhatToShow, UserIP);\n\t}\n</script>\n<table $align = \"center\" $cellpadding = \"0\" $cellspacing = \"0\" $width = \"100%\" class=\"left_side_users\">\n\t<tbody>\n\t\t<tr $valign = \"top\">\n\t\t\t<td $valign = \"top\" class=\"none\">\n\t\t\t\t<div $style = \"padding-bottom: 15px;\">\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\n\t\t\t\t\t\t<tbody>\n\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t\t\t\t\t";
echo ts_collapse("content1s") . " " . $StaffTools . sprintf($lang->userdetails["title"], $user["username"]);
echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t";
echo ts_collapse("content1s", 2);
echo "\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td>\n\t\t\t\t\t\t\t\t\t<div $style = \"float: right;\">\n\t\t\t\t\t\t\t\t\t\t";
echo $onoffpic . $UserGender . "<br />" . $country . ($awards ? "<br />" . $awards : "");
echo "\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t<div $style = \"float: left;\">\n\t\t\t\t\t\t\t\t\t\t<table>\n\t\t\t\t\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t\t\t\t\t<td class=\"none\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t";
echo get_user_avatar($user["avatar"]) . "<br /> " . $image;
echo "\t\t\t\t\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t\t\t\t</table>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t<div $valign = \"top\">\n\t\t\t\t\t\t\t\t\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"2\">\n\t\t\t\t\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t\t\t\t\t<td $style = \"padding: 3px;\" class=\"none\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t";
echo $donoruntil . $donated . sprintf($lang->userdetails["stats1"], $uploaded, $downloaded, $ratio) . $kps . sprintf($lang->userdetails["duspeed"], $downloadspeed, $uploadspeed);
echo "\t\t\t\t\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t\t\t\t</table>\n\t\t\t\t\t\t\t\t\t<div>\n\t\t\t\t\t\t\t\t\t<div $valign = \"bottom\" $style = \"float: right;\">\n\t\t\t\t\t\t\t\t\t\t";
echo $Buttons;
echo "\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t</tbody>\n\t\t\t\t\t</table>\n\t\t\t\t</div>\n\t\t\t\t<div $style = \"display: none; padding-bottom: 15px;\" $name = \"hiddencontents\" $id = \"hiddencontents\">\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\n\t\t\t\t\t\t<tbody>\n\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t\t\t\t\t<a $id = \"goshowcontents\"></a>\n\t\t\t\t\t\t\t\t\t";
echo ts_collapse("content1b") . $lang->userdetails["serverresponse"];
echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t";
echo ts_collapse("content1b", 2);
echo "\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td>\n\t\t\t\t\t\t\t\t\t<div $id = \"load\" $style = \"display: none;\"><img $src = \"";
echo $dimagedir;
echo "loading.gif\" $alt = \"";
echo $lang->global["pleasewait"];
echo " $title = \"";
echo $lang->global["pleasewait"];
echo "\" \" class=\"inlineimg\" />";
echo $lang->global["pleasewait"];
echo "</div>\n\t\t\t\t\t\t\t\t\t<div $id = \"showcontents\" $name = \"showcontents\"></div>\n\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t</tbody>\n\t\t\t\t\t</table>\n\t\t\t\t</div>\n\t\t\t\t<div $style = \"padding-bottom: 15px;\">\n\t\t\t\t\t";
echo show_userdetails_errors();
echo "\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\n\t\t\t\t\t\t<tbody>\n\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t\t\t\t\t";
echo ts_collapse("content1a") . (isset($Vmsg) ? $lang->userdetails["editmsg2"] : $lang->userdetails["visitormsg"]);
echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t";
echo ts_collapse("content1a", 2);
echo "\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td>\n\t\t\t\t\t\t\t\t\t";
echo $VisitorMessagesForm;
echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t</tbody>\n\t\t\t\t\t</table>\n\t\t\t\t\t<br />\n\t\t\t\t\t";
echo $pagertop . $VisitorMessages . $pagerbottom;
echo "\t\t\t\t</div>\n\t\t\t</td>\n\t\t\t<td $style = \"padding-left: 15px\" $valign = \"top\" $width = \"210\" class=\"none\">\n\t\t\t\t<div $style = \"padding-bottom: 15px;\">\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\n\t\t\t\t\t\t<tbody>\n\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t\t\t\t\t";
echo ts_collapse("content2a") . $lang->userdetails["ministats"];
echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t";
echo ts_collapse("content2a", 2);
echo "\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td>\n\t\t\t\t\t\t\t\t\t";
echo sprintf($lang->userdetails["ministats2"], $UserInfo["username"] . ($userbday ? " (" . $userbday . ") " : "") . get_user_icons($user), $UserInfo["title"], $email, $UserInfo["joindate"], $UserInfo["lastaccess"], $UserInfo["page"]);
if ($user["invited_by"]) {
    echo sprintf($lang->userdetails["iby"], $user["invited_by"]);
}
echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t</tbody>\n\t\t\t\t\t</table>\n\t\t\t\t</div>\n\t\t\t\t";
if ($USERMOOD) {
    echo "\t\t\t\t\t<div $style = \"padding-bottom: 15px;\">\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\n\t\t\t\t\t\t<tbody>\n\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t\t\t\t\t";
    echo ts_collapse("content2aaaaaaaa") . $lang->userdetails["usermood"];
    echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t";
    echo ts_collapse("content2aaaaaaaa", 2);
    echo "\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td>\n\t\t\t\t\t\t\t\t\t";
    echo $USERMOOD;
    echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t</tbody>\n\t\t\t\t\t</table>\n\t\t\t\t</div>\n\t\t\t\t";
}
if ($IM) {
    echo "\t\t\t\t\t<div $style = \"padding-bottom: 15px;\">\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\n\t\t\t\t\t\t<tbody>\n\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t\t\t\t\t";
    echo ts_collapse("content2aaaaaa") . $lang->im["im"];
    echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t";
    echo ts_collapse("content2aaaaaa", 2);
    echo "\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td>\n\t\t\t\t\t\t\t\t\t";
    echo $IM;
    echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t</tbody>\n\t\t\t\t\t</table>\n\t\t\t\t</div>\n\t\t\t\t";
}
if ($ratingsystem == "yes") {
    echo "\t\t\t\t\t<div $style = \"padding-bottom: 15px;\">\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\n\t\t\t\t\t\t<tbody>\n\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t\t\t\t\t";
    echo ts_collapse("content2aaa") . $lang->userdetails["rateuser"];
    echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t";
    echo ts_collapse("content2aaa", 2);
    echo "\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td>\n\t\t\t\t\t\t\t\t\t";
    echo $rating;
    echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t</tbody>\n\t\t\t\t\t</table>\n\t\t\t\t\t</div>\n\t\t\t\t";
}
if ($SameUser || $IsStaff) {
    $TSSEConfig->TSLoadConfig("ANNOUNCE");
    echo "\n\t\t\t\t\t<div $style = \"padding-bottom: 15px;\">\n\t\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\n\t\t\t\t\t\t\t<tbody>\n\t\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t\t\t\t\t\t" . ts_collapse("content2b") . $lang->userdetails["torrentstats"] . "\n\t\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t\t" . ts_collapse("content2b", 2) . "\n\t\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t\t<td>\n\t\t\t\t\t\t\t\t\t\t<a $href = \"javascript:void(0);\" $onclick = \"TSAjaxRequest('showuploaded');bookmarkscroll.scrollTo('hiddencontents');\">" . $lang->userdetails["torrentstats1"] . "</a>\n\t\t\t\t\t\t\t\t\t\t" . ($xbt_active != "yes" ? "\n\t\t\t\t\t\t\t\t\t\t<br />\n\t\t\t\t\t\t\t\t\t\t<a $href = \"javascript:void(0);\" $onclick = \"TSAjaxRequest('showcompleted');bookmarkscroll.scrollTo('hiddencontents');\">" . $lang->userdetails["torrentstats2"] . "</a>" : "") . "\n\t\t\t\t\t\t\t\t\t\t<br />\n\t\t\t\t\t\t\t\t\t\t<a $href = \"javascript:void(0);\" $onclick = \"TSAjaxRequest('showleechs');bookmarkscroll.scrollTo('hiddencontents');\">" . $lang->userdetails["torrentstats3"] . "</a>\n\t\t\t\t\t\t\t\t\t\t<br />\n\t\t\t\t\t\t\t\t\t\t<a $href = \"javascript:void(0);\" $onclick = \"TSAjaxRequest('showseeds');bookmarkscroll.scrollTo('hiddencontents');\">" . $lang->userdetails["torrentstats4"] . "</a>\n\t\t\t\t\t\t\t\t\t\t<br />\n\t\t\t\t\t\t\t\t\t\t" . ($xbt_active != "yes" ? "\n\t\t\t\t\t\t\t\t\t\t<a $href = \"javascript:void(0);\" $onclick = \"TSAjaxRequest('showsnatches');bookmarkscroll.scrollTo('hiddencontents');\">" . $lang->userdetails["torrentstats5"] . "</a>" : "<a $href = \"" . $BASEURL . "/mysnatchlist.php?$uid = " . $userid . "\">" . $lang->userdetails["torrentstats5"] . "</a>") . "\n\t\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t</tbody>\n\t\t\t\t\t\t</table>\n\t\t\t\t\t</div>";
}
echo "\t\t\t\t<div $style = \"padding-bottom: 15px;\">\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\n\t\t\t\t\t\t<tbody>\n\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t\t\t\t\t";
echo ts_collapse("content2c") . $lang->userdetails["ipinfo"];
echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t";
echo ts_collapse("content2c", 2);
echo "\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td>\n\t\t\t\t\t\t\t\t\t";
echo $lang->userdetails["ipinfo1"] . ($SameUser || $IsStaff ? $user["ip"] : $lang->userdetails["hidden"]);
echo "<br />\n\t\t\t\t\t\t\t\t\t";
echo $lang->userdetails["ipinfo2"] . ($IsStaff ? "<a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=search_ip&$username = " . $user["username"] . "\">" . $lang->userdetails["clicktosee"] . "</a>" : $lang->userdetails["hidden"]);
echo "\t\t\t\t\t\t\t\t\t";
echo $IsStaff ? "<br /><hr /><a $href = \"#goshowcontents\" $onclick = \"TSAjaxRequest('detecthost', '" . $user["ip"] . "');\"><b>" . $lang->userdetails["detecthost"] . "</b></a> - <a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=iptocountry&$ip = " . htmlspecialchars($user["ip"]) . "\"><b>" . $lang->userdetails["detectcountry"] . "</b></a> - <a $href = \"" . $BASEURL . "/redirector.php?$url = http://whois.domaintools.com/" . $user["ip"] . "\" $target = \"_blank\"><b>" . $lang->userdetails["whois"] . "</b></a>" : "";
echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t</tbody>\n\t\t\t\t\t</table>\n\t\t\t\t</div>\n\t\t\t\t";
if ($SameUser || $IsStaff) {
    echo "\t\t\t\t<div $style = \"padding-bottom: 15px;\">\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\n\t\t\t\t\t\t<tbody>\n\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t\t\t\t\t";
    echo ts_collapse("content2d") . $lang->userdetails["usertools"];
    echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t";
    echo ts_collapse("content2d", 2);
    echo "\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td>\n\t\t\t\t\t\t\t\t\t<a $href = \"";
    echo $BASEURL;
    echo "/takeflush.php?$id = ";
    echo $userid;
    echo "\">";
    echo $lang->userdetails["usertools1"];
    echo "</a>\n\t\t\t\t\t\t\t\t\t<br />\n\t\t\t\t\t\t\t\t\t<a $href = \"";
    echo $BASEURL;
    echo "/invite.php\">";
    echo $lang->userdetails["usertools2"];
    echo "</a> (";
    echo ts_nf($user["invites"]);
    echo ")\n\t\t\t\t\t\t\t\t\t<br />\n\t\t\t\t\t\t\t\t\t<a $href = \"";
    echo $BASEURL;
    echo "/port_check.php\">";
    echo $lang->userdetails["usertools3"];
    echo "</a>\n\t\t\t\t\t\t\t\t\t<br />\n\t\t\t\t\t\t\t\t\t<a $href = \"";
    echo $BASEURL;
    echo "/ts_albums.php?$userid = ";
    echo $userid;
    echo "\">";
    echo $lang->userdetails["albums"];
    echo "</a>\n\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t</tbody>\n\t\t\t\t\t</table>\n\t\t\t\t</div>\n\t\t\t\t";
} else {
    echo "\t\t\t\t\t<div $style = \"padding-bottom: 15px;\">\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\n\t\t\t\t\t\t<tbody>\n\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t\t\t\t\t";
    echo ts_collapse("content2d") . $lang->userdetails["usertools"];
    echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t";
    echo ts_collapse("content2d", 2);
    echo "\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td>\n\t\t\t\t\t\t\t\t\t<a $href = \"";
    echo $BASEURL;
    echo "/ts_albums.php?$userid = ";
    echo $userid;
    echo "\">";
    echo $lang->userdetails["albums"];
    echo "</a>\n\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t</tbody>\n\t\t\t\t\t</table>\n\t\t\t\t</div>\n\t\t\t\t";
}
if (0 < count($SocialGroups)) {
    echo "\t\t\t\t<div $style = \"padding-bottom: 15px;\">\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\n\t\t\t\t\t\t<tbody>\n\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t\t\t\t\t";
    echo ts_collapse("content2f") . $lang->userdetails["gmember"];
    echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t";
    echo ts_collapse("content2f", 2);
    echo "\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td>\n\t\t\t\t\t\t\t\t\t\t";
    echo implode("<br />", $SocialGroups) . "<hr />";
    echo "<div $style = \"float: right\"><a $href = \"" . $BASEURL . "/ts_social_groups.php\">" . $lang->userdetails["showgrp"] . "</a></div>";
    echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t</tbody>\n\t\t\t\t\t</table>\n\t\t\t\t</div>\n\t\t\t\t";
}
if (0 < PROFILE_MAX_VISITOR) {
    echo "\t\t\t\t<div $style = \"padding-bottom: 15px;\">\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\" $width = \"100%\">\n\t\t\t\t\t\t<tbody>\n\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t\t\t\t\t";
    echo ts_collapse("content2e") . $lang->userdetails["recentvisitors"];
    echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\n\t\t\t\t\t\t\t";
    echo ts_collapse("content2e", 2);
    echo "\t\t\t\t\t\t\t\n\t\t\t\t\t\t\t<tr>\n\t\t\t\t\t\t\t\t<td>\n\t\t\t\t\t\t\t\t\t\t";
    if (0 < count($RecentVisitorsArray)) {
        echo sprintf($lang->userdetails["recentvisitors2"], PROFILE_MAX_VISITOR) . "<br />" . implode(", ", $RecentVisitorsArray) . "<hr />";
    }
    echo "<div $style = \"float: right\">" . sprintf($lang->userdetails["recentvisitors1"], ts_nf($user["visitorcount"])) . "</div>";
    echo "\t\t\t\t\t\t\t\t</td>\n\t\t\t\t\t\t\t</tr>\t\t\t\t\t\t\t\n\t\t\t\t\t\t</tbody>\n\t\t\t\t\t</table>\n\t\t\t\t</div>\n\t\t\t\t";
}
echo "\t\t\t</td>\n\t\t</tr>\n\t</tbody>\n</table>\n";
stdfoot();
function showFriendlyAge($date_of_birth, $expKey = "-")
{
    if (!$date_of_birth) {
        return "--";
    }
    list($day, $month, $year) = explode($expKey, $date_of_birth);
    list($cYear, $cMonth, $cDay) = explode("-", date("Y-m-d", TIMENOW));
    $age = $cYear - $year;
    if ($cMonth < $month || $cMonth == $month && $cDay < $day) {
        $age--;
    }
    return $age;
}
function show_userdetails_errors()
{
    global $error;
    global $lang;
    if (is_array($error) && 0 < count($error)) {
        $errors = implode("<br />", $error);
        echo "\n\t\t\t<table class=\"main\" $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\">\n\t\t\t<tr>\n\t\t\t\t<td class=\"thead\">\n\t\t\t\t\t" . $lang->global["error"] . "\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t\t<td>\n\t\t\t\t\t<font $color = \"red\">\n\t\t\t\t\t\t<strong>\n\t\t\t\t\t\t\t" . $errors . "\n\t\t\t\t\t\t</strong>\n\t\t\t\t\t</font>\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t\t</table>\n\t\t\t<br />\n\t\t";
    }
}

?>