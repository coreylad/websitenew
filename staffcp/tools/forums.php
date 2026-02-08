<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

checkStaffAuthentication();
$Language = file("languages/" . getStaffLanguage() . "/forums.lang");
$Language2 = file("languages/" . getStaffLanguage() . "/advertising.lang");
$Act = isset($_GET["act"]) ? trim($_GET["act"]) : (isset($_POST["act"]) ? trim($_POST["act"]) : "");
$Message = "";
define("IN_TRACKER", true);
require "../include/init.php";
if ($Act == "settings") {
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $FORUMCP = serialize($_POST["FORUMCP"]);
        mysqli_query($GLOBALS["DatabaseConnect"], "REPLACE INTO `ts_config` VALUES ('FORUMCP', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $FORUMCP) . "')");
        $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[72]);
        logStaffAction($Message);
        $Message = showAlertError($Message);
    }
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT `content` FROM `ts_config` WHERE `configname` = 'FORUMCP'");
    $Result = mysqli_fetch_assoc($query);
    $FORUMCP = unserialize($Result["content"]);
    $List = loadTinyMCEEditor(1, "exact", "f_offlinemsg") . "\r\n\t" . showAlertMessage("<a $href = \"index.php?do=forums\">" . $Language[36] . "</a>") . "\r\n\t" . $Message . "\r\n\t<form $method = \"post\" $action = \"index.php?do=forums&$act = settings\">\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\">" . $Language[71] . "</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[73] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t" . $Language[74] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"radio\" $name = \"FORUMCP[f_forum_online]\" $value = \"yes\"" . ($FORUMCP["f_forum_online"] == "yes" ? " $checked = \"checked\"" : "") . " /> " . $Language[26] . "\r\n\t\t\t\t<input $type = \"radio\" $name = \"FORUMCP[f_forum_online]\" $value = \"no\"" . ($FORUMCP["f_forum_online"] == "no" ? " $checked = \"checked\"" : "") . " /> " . $Language[27] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[75] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t" . $Language[76] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<textarea $name = \"FORUMCP[f_offlinemsg]\" $id = \"f_offlinemsg\" $rows = \"10\" $cols = \"99\">" . $FORUMCP["f_offlinemsg"] . "</textarea>\r\n\t\t\t\t<p><a $href = \"javascript:toggleEditor('f_offlinemsg');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[77] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t" . $Language[78] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"text\" $name = \"FORUMCP[f_forumname]\" $value = \"" . $FORUMCP["f_forumname"] . "\" $size = \"50\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[79] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t" . $Language[80] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"text\" $name = \"FORUMCP[f_upload_path]\" $value = \"" . $FORUMCP["f_upload_path"] . "\" $size = \"30\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[81] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t" . $Language[82] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"text\" $name = \"FORUMCP[f_upload_maxsize]\" $value = \"" . $FORUMCP["f_upload_maxsize"] . "\" $size = \"10\" /> " . formatBytes($FORUMCP["f_upload_maxsize"]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[83] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t" . $Language[84] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"text\" $name = \"FORUMCP[f_allowed_types]\" $value = \"" . $FORUMCP["f_allowed_types"] . "\" $size = \"30\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[85] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t" . $Language[86] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"text\" $name = \"FORUMCP[f_threadsperpage]\" $value = \"" . $FORUMCP["f_threadsperpage"] . "\" $size = \"10\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[87] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t" . $Language[88] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"text\" $name = \"FORUMCP[f_postsperpage]\" $value = \"" . $FORUMCP["f_postsperpage"] . "\" $size = \"10\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[89] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t" . $Language[90] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"text\" $name = \"FORUMCP[f_minmsglength]\" $value = \"" . $FORUMCP["f_minmsglength"] . "\" $size = \"10\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[91] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t" . $Language[92] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"text\" $name = \"FORUMCP[f_avatar_maxwidth]\" $value = \"" . $FORUMCP["f_avatar_maxwidth"] . "\" $size = \"10\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[93] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t" . $Language[94] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"text\" $name = \"FORUMCP[f_avatar_maxheight]\" $value = \"" . $FORUMCP["f_avatar_maxheight"] . "\" $size = \"10\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[106] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t" . $Language[107] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"radio\" $name = \"FORUMCP[f_resizeavatars]\" $value = \"yes\"" . ($FORUMCP["f_resizeavatars"] == "yes" ? " $checked = \"checked\"" : "") . " /> " . $Language[26] . "\r\n\t\t\t\t<input $type = \"radio\" $name = \"FORUMCP[f_resizeavatars]\" $value = \"no\"" . ($FORUMCP["f_resizeavatars"] == "no" ? " $checked = \"checked\"" : "") . " /> " . $Language[27] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[95] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t" . $Language[96] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"text\" $name = \"FORUMCP[f_avatar_maxsize]\" $value = \"" . $FORUMCP["f_avatar_maxsize"] . "\" $size = \"10\" /> " . formatBytes($FORUMCP["f_avatar_maxsize"]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[97] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t" . $Language[98] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"radio\" $name = \"FORUMCP[f_showstats]\" $value = \"yes\"" . ($FORUMCP["f_showstats"] == "yes" ? " $checked = \"checked\"" : "") . " /> " . $Language[26] . "\r\n\t\t\t\t<input $type = \"radio\" $name = \"FORUMCP[f_showstats]\" $value = \"no\"" . ($FORUMCP["f_showstats"] == "no" ? " $checked = \"checked\"" : "") . " /> " . $Language[27] . "\r\n\t\t\t\t<input $type = \"radio\" $name = \"FORUMCP[f_showstats]\" $value = \"staffonly\"" . ($FORUMCP["f_showstats"] == "staffonly" ? " $checked = \"checked\"" : "") . " /> " . $Language[105] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[99] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t" . $Language[100] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"text\" $name = \"FORUMCP[f_sfpertr]\" $value = \"" . $FORUMCP["f_sfpertr"] . "\" $size = \"10\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[101] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t" . $Language[102] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"radio\" $name = \"FORUMCP[f_EnableBookmarks]\" $value = \"yes\"" . ($FORUMCP["f_EnableBookmarks"] == "yes" ? " $checked = \"checked\"" : "") . " /> " . $Language[26] . "\r\n\t\t\t\t<input $type = \"radio\" $name = \"FORUMCP[f_EnableBookmarks]\" $value = \"no\"" . ($FORUMCP["f_EnableBookmarks"] == "no" ? " $checked = \"checked\"" : "") . " /> " . $Language[27] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[108] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t" . $Language[109] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<input $type = \"radio\" $name = \"FORUMCP[f_thanksbeforedl]\" $value = \"yes\"" . ($FORUMCP["f_thanksbeforedl"] == "yes" ? " $checked = \"checked\"" : "") . " /> " . $Language[26] . "\r\n\t\t\t\t<input $type = \"radio\" $name = \"FORUMCP[f_thanksbeforedl]\" $value = \"no\"" . ($FORUMCP["f_thanksbeforedl"] == "no" ? " $checked = \"checked\"" : "") . " /> " . $Language[27] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[103] . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t" . $Language[104] . " <div $style = \"margin-top: 10px; font-size: 11px;\">" . $Language2[6] . "</div>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t<textarea $name = \"FORUMCP[f_ads]\" $id = \"f_ads\" $rows = \"10\" $cols = \"99\">" . $FORUMCP["f_ads"] . "</textarea>\r\n\t\t\t\t<p><a $href = \"javascript:toggleEditor('f_ads');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></a></p>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\"></td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[17] . "\" /> <input $type = \"reset\" $value = \"" . $Language[18] . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\r\n\t</table>\r\n\t</form>\r\n\t";
}
if ($Act == "save_order") {
    foreach ($_POST["disporder"] as $fid => $disporder) {
        mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE " . TSF_PREFIX . "forums SET $disporder = " . intval($disporder) . " WHERE $fid = " . $fid);
    }
    $Message = str_replace("{1}", $_SESSION["ADMIN_USERNAME"], $Language[16]);
    logStaffAction($Message);
    $Message = showAlertError($Message);
}
if ($Act == "delete_forum" && ($fid = intval($_GET["fid"]))) {
    $Query3 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name FROM " . TSF_PREFIX . "forums WHERE $fid = " . $fid);
    $Result = mysqli_fetch_assoc($Query3);
    $ForumName = $Result["name"];
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "forums WHERE $fid = " . $fid);
    $delquery = "";
    $delquery2 = "";
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT fid FROM " . TSF_PREFIX . "forums WHERE CONCAT(',', parentlist, ',') LIKE '%," . $fid . ",%'");
    if (mysqli_num_rows($query)) {
        while ($f = mysqli_fetch_assoc($query)) {
            $delquery .= " OR $fid = '" . $f["fid"] . "'";
            $delquery2 .= " OR $forumid = '" . $f["fid"] . "'";
        }
    }
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "forums WHERE CONCAT(',',parentlist,',') LIKE '%," . $fid . ",%'");
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "threads WHERE $fid = " . $fid . " " . $delquery);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "posts WHERE $fid = " . $fid . " " . $delquery);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "forumpermissions WHERE $fid = " . $fid . " " . $delquery);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "announcement WHERE $forumid = " . $fid . " " . $delquery2);
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "moderators WHERE $forumid = " . $fid . " " . $delquery2);
    $Message = str_replace(["{1}", "{2}"], [$ForumName . " (" . $fid . ")", $_SESSION["ADMIN_USERNAME"]], $Language[39]);
    logStaffAction($Message);
    $Message = showAlertError($Message);
}
if ($Act == "add_moderator" && ($fid = intval($_GET["fid"]))) {
    $Query3 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT type, name FROM " . TSF_PREFIX . "forums WHERE $fid = " . $fid);
    $Result = mysqli_fetch_assoc($Query3);
    $ForumName = $Result["name"];
    $ForumType = $Result["type"];
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $plistforums = [];
        $doapplytochild = false;
        $applychild = isset($_POST["applychild"]) ? $_POST["applychild"] : "no";
        if ($applychild == "yes") {
            $doapplytochild = true;
            $plistforums[] = $fid;
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT fid FROM " . TSF_PREFIX . "forums WHERE $pid = '" . $fid . "'");
            if (0 < mysqli_num_rows($query)) {
                while ($plist = mysqli_fetch_assoc($query)) {
                    $plistforums[] = $plist["fid"];
                }
            }
        }
        $removemoderator = isset($_POST["removemoderator"]) ? $_POST["removemoderator"] : [];
        if (0 < count($removemoderator)) {
            foreach ($removemoderator as $ruserid) {
                mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "moderators WHERE `userid` = '" . $ruserid . "' AND $forumid = '" . $fid . "'");
                if ($doapplytochild && 0 < count($plistforums)) {
                    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "moderators WHERE `userid` = '" . $ruserid . "' AND forumid IN (" . implode(",", $plistforums) . ")");
                }
            }
        }
        $moderator = isset($_POST["moderator"]) ? $_POST["moderator"] : "";
        $alreadymod = [];
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT m.userid, u.username FROM " . TSF_PREFIX . "moderators m LEFT JOIN users u ON (m.$userid = u.id) WHERE m.$forumid = '" . $fid . "'");
        while ($mods = mysqli_fetch_assoc($query)) {
            $alreadymod[] = $mods["username"];
        }
        if ($doapplytochild && 0 < count($plistforums)) {
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT m.userid, u.username FROM " . TSF_PREFIX . "moderators m LEFT JOIN users u ON (m.$userid = u.id) WHERE m.forumid IN (" . implode(",", $plistforums) . ")");
            while ($mods = mysqli_fetch_assoc($query)) {
                $alreadymod[] = $mods["username"];
            }
        }
        if (0 < count($moderator)) {
            foreach ($moderator as $modname) {
                if ($doapplytochild && 0 < count($plistforums)) {
                    foreach ($plistforums as $pfid) {
                        if (in_array($modname, $alreadymod) !== true && $modname != "") {
                            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM users WHERE `username` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $modname) . "'");
                            if (0 < mysqli_num_rows($query)) {
                                $Result = mysqli_fetch_assoc($query);
                                $userid = $Result["id"];
                                if (@in_array($userid, $removemoderator) !== true && $userid != "") {
                                    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO " . TSF_PREFIX . "moderators (userid,forumid) VALUES (" . $userid . ", " . $pfid . ")");
                                }
                            }
                        }
                    }
                } else {
                    if (in_array($modname, $alreadymod) !== true && $modname != "") {
                        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT id FROM users WHERE `username` = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $modname) . "'");
                        if (0 < mysqli_num_rows($query)) {
                            $Result = mysqli_fetch_assoc($query);
                            $userid = $Result["id"];
                            if (@in_array($userid, $removemoderator) !== true && $userid != "") {
                                mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO " . TSF_PREFIX . "moderators (userid,forumid) VALUES (" . $userid . ", " . $fid . ")");
                            }
                        }
                    }
                }
            }
        }
        $Message = str_replace(["{1}", "{2}"], [$ForumName . " (" . $fid . ")", $_SESSION["ADMIN_USERNAME"]], $Language[52]);
        logStaffAction($Message);
        $Message = showAlertError($Message);
        $Updated = true;
    }
    if (!isset($Updated)) {
        $hiddenFields = "";
        $Moderators = "";
        if ($ForumType == "c") {
            $hiddenFields = "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $align = \"right\">\r\n\t\t\t\t\t\t" . $Language[55] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td>\r\n\t\t\t\t\t\t<input $type = \"checkbox\" $name = \"applychild\" $value = \"yes\" $checked = \"checked\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t";
        }
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT m.userid, u.username FROM " . TSF_PREFIX . "moderators m LEFT JOIN users u ON (m.$userid = u.id) WHERE m.$forumid = " . $fid);
        $rowcount = mysqli_num_rows($query);
        if (0 < $rowcount) {
            for ($whilecount = 1; $mods = mysqli_fetch_assoc($query); $whilecount++) {
                $Moderators .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $align = \"right\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $Language[53] . "[" . $whilecount . "]:\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td $align = \"left\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"moderator[" . $whilecount . "]\" $value = \"" . $mods["username"] . "\" $size = \"30\" /> <input $type = \"checkbox\" $name = \"removemoderator[]\" $value = \"" . $mods["userid"] . "\" /> " . $Language[54] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
            }
            for ($i = 1; $i <= 3; $i++) {
                $Moderators .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $align = \"right\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $Language[53] . "[" . $whilecount . "]:\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td $align = \"left\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"moderator[" . $whilecount . "]\" $value = \"\" $size = \"30\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
                $whilecount++;
            }
        } else {
            for ($i = 1; $i <= 3; $i++) {
                $Moderators .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $align = \"right\">\r\n\t\t\t\t\t\t<strong>\r\n\t\t\t\t\t\t\t" . $Language[53] . "[" . $i . "]:\r\n\t\t\t\t\t\t</strong>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td $align = \"left\">\r\n\t\t\t\t\t\t<input $type = \"text\" $name = \"moderator[" . $i . "]\" $value = \"\" $size = \"30\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t";
            }
        }
        $List = "\r\n\t\t" . showAlertMessage("<a $href = \"index.php?do=forums\">" . $Language[36] . "</a>") . "\r\n\t\t" . $Message . "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=forums&$act = add_moderator&$fid = " . $fid . "\" $name = \"add_moderator\">\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t\t\t" . $Language[2] . " - " . $Language[6] . " (" . $ForumName . ")\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\">\r\n\t\t\t\t\t<table $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $width = \"100%\">\r\n\t\t\t\t\t\t" . $Moderators . "\r\n\t\t\t\t\t\t" . $hiddenFields . "\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[17] . "\" /> <input $type = \"reset\" $value = \"" . $Language[18] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
if ($Act == "delete_announcement" && ($announcementid = intval($_GET["announcementid"]))) {
    $Query3 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT forumid FROM " . TSF_PREFIX . "announcement WHERE $announcementid = " . $announcementid);
    $Result = mysqli_fetch_assoc($Query3);
    $forumid = $Result["forumid"];
    $Query4 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name FROM " . TSF_PREFIX . "forums WHERE $fid = " . $forumid);
    $Result = mysqli_fetch_assoc($Query4);
    $ForumName = $Result["name"];
    mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "announcement WHERE $announcementid = " . $announcementid);
    $Message = str_replace(["{1}", "{2}"], [$ForumName . " (" . $forumid . ")", $_SESSION["ADMIN_USERNAME"]], $Language[67]);
    logStaffAction($Message);
    $Message = showAlertMessage($Message);
    $Act = "manage_announcements";
}
if ($Act == "add_announcement" && ($fid = intval($_GET["fid"])) || $Act == "edit_announcement" && ($announcementid = intval($_GET["announcementid"]))) {
    if ($Act == "add_announcement") {
        $title = "";
        $pagetext = "";
        $Query3 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name FROM " . TSF_PREFIX . "forums WHERE $fid = " . $fid);
        $Result = mysqli_fetch_assoc($Query3);
        $ForumName = $Result["name"];
    } else {
        $Query4 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT title, pagetext, forumid FROM " . TSF_PREFIX . "announcement WHERE $announcementid = " . $announcementid);
        $Ann = mysqli_fetch_assoc($Query4);
        $title = $Ann["title"];
        $pagetext = $Ann["pagetext"];
        $fid = $Ann["forumid"];
        $Query3 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name FROM " . TSF_PREFIX . "forums WHERE $fid = " . $fid);
        $Result = mysqli_fetch_assoc($Query3);
        $ForumName = $Result["name"];
    }
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $title = trim($_POST["title"]);
        $userid = intval($_SESSION["ADMIN_ID"]);
        $posted = time();
        $pagetext = trim($_POST["pagetext"]);
        $forumid = intval($_POST["forumid"]);
        if ($Act == "edit_announcement") {
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE " . TSF_PREFIX . "announcement SET $title = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', $pagetext = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $pagetext) . "', $forumid = " . $forumid . " WHERE $announcementid = " . $announcementid);
            $Message = str_replace(["{1}", "{2}"], [$ForumName . " (" . $fid . ")", $_SESSION["ADMIN_USERNAME"]], $Language[65]);
        } else {
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO " . TSF_PREFIX . "announcement (title, userid, posted, pagetext, forumid) VALUES ('" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $title) . "', " . $userid . ", " . $posted . ", '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $pagetext) . "', " . $forumid . ")");
            $Message = str_replace(["{1}", "{2}"], [$ForumName . " (" . $fid . ")", $_SESSION["ADMIN_USERNAME"]], $Language[62]);
        }
        $Updated = true;
        $Message = showAlertMessage($Message);
        $Act = "manage_announcements";
    }
    if (!isset($Updated)) {
        $SelectBox2 = "\r\n\t\t<select $name = \"forumid\">";
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT fid, name FROM " . TSF_PREFIX . "forums ORDER BY type, disporder");
        while ($ForumList = mysqli_fetch_assoc($query)) {
            $SelectBox2 .= "\r\n\t\t\t\t<option $value = \"" . $ForumList["fid"] . "\"" . ($ForumList["fid"] == $fid ? " $selected = \"selected\"" : "") . ">" . $ForumList["name"] . "</option>";
        }
        $SelectBox2 .= "\r\n\t\t</select>";
        $List = loadTinyMCEEditor(1, "exact", "pagetext") . "\r\n\t\t" . showAlertMessage("<a $href = \"index.php?do=forums" . ($Act == "edit_announcement" ? "&$act = manage_announcements" : "") . "\">" . $Language[36] . "</a>") . "\r\n\t\t" . $Message . "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=forums&$act = " . $Act . "&" . ($Act == "add_announcement" ? "fid=" . $fid : "announcementid=" . $announcementid) . "\">\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\">\r\n\t\t\t\t\t" . $Language[2] . " - " . $Language[$Act == "add_announcement" ? "12" : "63"] . " (" . $ForumName . ")\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[56] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[57] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"title\" $value = \"" . $title . "\" $style = \"width: 99%;\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[58] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t" . $Language[59] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<textarea $name = \"pagetext\" $id = \"pagetext\" $style = \"width: 100%; height: 60px;\">" . $pagetext . "</textarea>\r\n\t\t\t\t\t<p><a $href = \"javascript:toggleEditor('pagetext');\"><img $src = \"images/tool_refresh.png\" $border = \"0\" /></p>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[60] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\" $valign = \"top\">\r\n\t\t\t\t\t" . $Language[61] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t" . $SelectBox2 . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[17] . "\" /> <input $type = \"reset\" $value = \"" . $Language[18] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>";
    }
}
if ($Act == "manage_announcements") {
    $announcements = "";
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT a.announcementid, a.title, f.name, u.id, u.username, g.namestyle FROM " . TSF_PREFIX . "announcement a LEFT JOIN " . TSF_PREFIX . "forums f ON (a.$forumid = f.fid) LEFT JOIN users u ON (a.$userid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) ORDER by a.posted DESC");
    while ($a = mysqli_fetch_assoc($query)) {
        $announcements .= "\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $a["name"] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $a["title"] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t<a $href = \"index.php?do=forums&amp;$act = edit_announcement&amp;$announcementid = " . $a["announcementid"] . "\"><img $src = \"images/tool_edit.png\" $alt = \"" . trim($Language[63]) . "\" $title = \"" . trim($Language[63]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a> <a $href = \"index.php?do=forums&amp;$act = delete_announcement&amp;$announcementid = " . $a["announcementid"] . "\" $onclick = \"return confirm('" . trim($Language[69]) . "');\"><img $src = \"images/tool_delete.png\" $alt = \"" . trim($Language[64]) . "\" $title = \"" . trim($Language[64]) . "\" $border = \"0\" $style = \"vertical-align: middle;\" /></a>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
    $List = "\r\n\t" . showAlertMessage("<a $href = \"index.php?do=forums\">" . $Language[36] . "</a>") . "\r\n\t" . $Message . "\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"3\">\r\n\t\t\t\t" . $Language[2] . " - " . $Language[66] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . $Language[60] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . $Language[56] . "\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\">\r\n\t\t\t\t" . $Language[7] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . $announcements . "\r\n\t</table>\r\n\t";
}
if ($Act == "edit_forum" && ($fid = intval($_GET["fid"])) || $Act == "new_forum" || $Act == "add_child_forum" && ($fid = intval($_GET["fid"]))) {
    if ($Act == "edit_forum") {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM " . TSF_PREFIX . "forums WHERE $fid = " . $fid);
        $Forum = mysqli_fetch_assoc($query);
        $ForumName = $Forum["name"];
    } else {
        if ($Act == "new_forum") {
            $fid = 0;
            $Forum = [];
            $Forum["name"] = "";
            $Forum["description"] = "";
            $Forum["image"] = "";
            $Forum["moderate"] = "0";
            $Forum["disporder"] = "0";
            $Forum["pid"] = "0";
            $Forum["password"] = "";
        } else {
            if ($Act == "add_child_forum") {
                $Forum = [];
                $Forum["name"] = "";
                $Forum["description"] = "";
                $Forum["image"] = "";
                $Forum["moderate"] = "0";
                $Forum["disporder"] = "0";
                $Forum["pid"] = $fid;
                $Forum["password"] = "";
                $Query3 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name FROM " . TSF_PREFIX . "forums WHERE $fid = " . $fid);
                $Result = mysqli_fetch_assoc($Query3);
                $ForumName = $Result["name"];
            }
        }
    }
    $name = $Forum["name"];
    $description = $Forum["description"];
    $image = $Forum["image"];
    $moderate = $Forum["moderate"];
    $disporder = $Forum["disporder"];
    $pid = $Forum["pid"];
    $password = $Forum["password"];
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        $name = trim($_POST["name"]);
        $description = trim($_POST["description"]);
        $image = trim($_POST["image"]);
        $moderate = intval($_POST["moderate"]);
        $disporder = intval($_POST["disporder"]);
        $pid = isset($_POST["pid"]) ? intval($_POST["pid"]) : 0;
        if (!$pid) {
            $type = "c";
        } else {
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT type FROM " . TSF_PREFIX . "forums WHERE $fid = " . $pid);
            $Result = mysqli_fetch_assoc($query);
            $selectedpidtype = $Result["type"];
            if ($selectedpidtype == "f") {
                $type = "s";
            } else {
                $type = "f";
            }
        }
        $password = isset($_POST["password"]) ? trim($_POST["password"]) : "";
        if ($Act == "edit_forum") {
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE " . TSF_PREFIX . "forums SET $type = '" . $type . "', $name = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $name) . "', $description = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "', $image = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $image) . "', $moderate = " . $moderate . ", $disporder = " . $disporder . ", $pid = " . $pid . ", $password = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $password) . "' WHERE $fid = " . $fid);
            $parentlist = getForumData($fid);
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE " . TSF_PREFIX . "forums SET $parentlist = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $parentlist) . "' WHERE $fid = " . $fid);
            $Message = str_replace(["{1}", "{2}"], [$name . " (" . $fid . ")", $_SESSION["ADMIN_USERNAME"]], $Language[34]);
        } else {
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO " . TSF_PREFIX . "forums (type, name, description, image, moderate, disporder, pid, password) VALUES('" . $type . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $name) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $description) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $image) . "', " . $moderate . ",  " . $disporder . ", " . $pid . ", '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $password) . "')");
            $fid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
            $parentlist = getForumData($fid);
            mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE " . TSF_PREFIX . "forums SET $parentlist = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $parentlist) . "' WHERE $fid = " . $fid);
            if ($pid) {
                $parentlist = getForumData($pid);
                mysqli_query($GLOBALS["DatabaseConnect"], "UPDATE " . TSF_PREFIX . "forums SET $parentlist = '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $parentlist) . "' WHERE $fid = " . $pid);
            }
            $Message = $Language[37];
            $Act = "permissions";
            $_GET["fid"] = $fid;
            $_SERVER["REQUEST_METHOD"] = "GET";
        }
        logStaffAction($Message);
        $Message = showAlertError($Message);
    }
    if (!$Message) {
        $SelectBox = "\r\n\t\t<select $name = \"image\" $onchange = \"UpdateForumImage(this.value);\">\r\n\t\t\t<option $value = \"\"></option>";
        $ForumImages = scandir("../tsf_forums/images/forumicons/");
        foreach ($ForumImages as $ForumImage) {
            if ($ForumImage != "." && $ForumImage != ".." && in_array(function_149($ForumImage), ["gif", "jpg", "jpeg", "bmp", "png"])) {
                $SelectBox .= "\r\n\t\t\t\t<option $value = \"" . $ForumImage . "\"" . ($image == $ForumImage ? " $selected = \"selected\"" : "") . ">" . $ForumImage . "</option>";
            }
        }
        $SelectBox .= "\r\n\t\t</select>";
        $SelectBox2 = "\r\n\t\t<select $name = \"pid\">\r\n\t\t\t<option $value = \"0\"" . ($pid == 0 ? " $selected = \"selected\"" : "") . ">" . $Language[31] . "</option>";
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT fid, name FROM " . TSF_PREFIX . "forums WHERE" . ($Act == "add_child_forum" ? "" : " fid != " . $fid . " AND") . " `type` IN ('c','f') ORDER BY type, disporder");
        while ($ForumList = mysqli_fetch_assoc($query)) {
            $SelectBox2 .= "\r\n\t\t\t\t<option $value = \"" . $ForumList["fid"] . "\"" . ($ForumList["fid"] == $pid ? " $selected = \"selected\"" : "") . ">" . $ForumList["name"] . "</option>";
        }
        $SelectBox2 .= "\r\n\t\t</select>";
        $List = "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\tfunction UpdateForumImage(selected)\r\n\t\t\t{\r\n\t\t\t\tif (selected)\r\n\t\t\t\t{\r\n\t\t\t\t\tTSGetID(\"imagepreview\").$src = \"../tsf_forums/images/forumicons/\"+selected;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t</script>\r\n\t\t" . showAlertMessage("<a $href = \"index.php?do=forums\">" . $Language[36] . "</a>") . "\r\n\t\t" . $Message . "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=forums&$act = " . $Act . "&$fid = " . $fid . "\">\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"2\">\r\n\t\t\t\t\t" . $Language[2] . " - " . $Language[$Act == "edit_forum" ? "8" : ($Act == "new_forum" ? "35" : "11")] . " " . ($Act == "edit_forum" || $Act == "add_child_forum" ? "(" . $ForumName . ")" : "") . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[3] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[19] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"name\" $value = \"" . $name . "\" $size = \"50\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[20] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[21] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"description\" $value = \"" . $description . "\" $size = \"50\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[22] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[23] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t" . $SelectBox . " <img $src = \"../tsf_forums/images/forumicons/" . $image . "\" $id = \"imagepreview\" $alt = \"\" $title = \"\" $border = \"0\" $style = \"vertical-align: middle;\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[24] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[25] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"radio\" $name = \"moderate\" $value = \"1\"" . ($moderate == "1" ? " $checked = \"checked\"" : "") . " /> " . $Language[26] . "\r\n\t\t\t\t\t<input $type = \"radio\" $name = \"moderate\" $value = \"0\"" . ($moderate == "0" ? " $checked = \"checked\"" : "") . " /> " . $Language[27] . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[4] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[28] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"disporder\" $value = \"" . $disporder . "\" $size = \"10\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[29] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[30] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t" . $SelectBox2 . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"2\">" . $Language[32] . "</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $align = \"justify\">\r\n\t\t\t\t\t" . $Language[33] . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $width = \"50%\" $valign = \"top\">\r\n\t\t\t\t\t<input $type = \"text\" $name = \"password\" $value = \"" . $password . "\" $size = \"30\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat2\"></td>\r\n\t\t\t\t<td class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[17] . "\" /> <input $type = \"reset\" $value = \"" . $Language[18] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>\r\n\t\t";
    }
}
if ($Act == "permissions" && ($fid = intval($_GET["fid"]))) {
    $Query3 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name, type, pid FROM " . TSF_PREFIX . "forums WHERE $fid = " . $fid);
    $Result = mysqli_fetch_assoc($Query3);
    $ForumName = $Result["name"];
    $ForumType = $Result["type"];
    $ForumPID = $Result["pid"];
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST") {
        mysqli_query($GLOBALS["DatabaseConnect"], "DELETE FROM " . TSF_PREFIX . "forumpermissions WHERE $fid = " . $fid);
        $Query2 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid FROM usergroups ORDER BY gid");
        while ($Usergroup = mysqli_fetch_assoc($Query2)) {
            $canview = isset($_POST["canview"][$Usergroup["gid"]]) ? $_POST["canview"][$Usergroup["gid"]] : "no";
            $canviewthreads = isset($_POST["canviewthreads"][$Usergroup["gid"]]) ? $_POST["canviewthreads"][$Usergroup["gid"]] : "no";
            $canpostthreads = isset($_POST["canpostthreads"][$Usergroup["gid"]]) ? $_POST["canpostthreads"][$Usergroup["gid"]] : "no";
            $canpostreplys = isset($_POST["canpostreplys"][$Usergroup["gid"]]) ? $_POST["canpostreplys"][$Usergroup["gid"]] : "no";
            $caneditposts = isset($_POST["caneditposts"][$Usergroup["gid"]]) ? $_POST["caneditposts"][$Usergroup["gid"]] : "no";
            $candeleteposts = isset($_POST["candeleteposts"][$Usergroup["gid"]]) ? $_POST["candeleteposts"][$Usergroup["gid"]] : "no";
            $candeletethreads = isset($_POST["candeletethreads"][$Usergroup["gid"]]) ? $_POST["candeletethreads"][$Usergroup["gid"]] : "no";
            $canpostattachments = isset($_POST["canpostattachments"][$Usergroup["gid"]]) ? $_POST["canpostattachments"][$Usergroup["gid"]] : "no";
            $cansearch = isset($_POST["cansearch"][$Usergroup["gid"]]) ? $_POST["cansearch"][$Usergroup["gid"]] : "no";
            mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO " . TSF_PREFIX . "forumpermissions (fid, gid, canview, canviewthreads, canpostthreads, canpostreplys, caneditposts, candeleteposts, candeletethreads, canpostattachments, cansearch) VALUES (" . $fid . ", " . $Usergroup["gid"] . ", '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $canview) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $canviewthreads) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $canpostthreads) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $canpostreplys) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $caneditposts) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $candeleteposts) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $candeletethreads) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $canpostattachments) . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $cansearch) . "')");
        }
        $Message = str_replace(["{1}", "{2}"], [$ForumName . " (" . $fid . ")", $_SESSION["ADMIN_USERNAME"]], $Language[51]);
        logStaffAction($Message);
        $Message = showAlertError($Message);
        $Updated = true;
    }
    if (!isset($Updated)) {
        $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM " . TSF_PREFIX . "forumpermissions WHERE $fid = " . $fid);
        if ($ForumType != "c" && $ForumPID && !mysqli_num_rows($query)) {
            $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM " . TSF_PREFIX . "forumpermissions WHERE $fid = " . $ForumPID);
        }
        $ForumPermissions = [];
        if (mysqli_num_rows($query)) {
            while ($FP = mysqli_fetch_assoc($query)) {
                $ForumPermissions[$FP["gid"]] = $FP;
            }
        }
        $Query2 = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT gid, title, namestyle FROM usergroups ORDER BY gid");
        $ListUsergroups = "";
        while ($Usergroup = mysqli_fetch_assoc($Query2)) {
            preg_match("@color: (.*);@", $Usergroup["namestyle"], $color);
            $color = isset($color[1]) ? $color[1] : "#cccccc";
            $ListUsergroups .= "\r\n\t\t\t<tr data-$color = \"" . $color . "\" data-$gid = \"" . $Usergroup["gid"] . "\">\r\n\t\t\t\t<td class=\"alt1\"  $align = \"left\">\r\n\t\t\t\t\t" . str_replace("{username}", $Usergroup["title"], $Usergroup["namestyle"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"checkbox\" $name = \"canview[" . $Usergroup["gid"] . "]\" $value = \"yes\"" . (isset($ForumPermissions[$Usergroup["gid"]]) && $ForumPermissions[$Usergroup["gid"]]["canview"] == "yes" ? " $checked = \"checked\"" : "") . " $checkme = \"group" . $Usergroup["gid"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"checkbox\" $name = \"canviewthreads[" . $Usergroup["gid"] . "]\" $value = \"yes\"" . (isset($ForumPermissions[$Usergroup["gid"]]) && $ForumPermissions[$Usergroup["gid"]]["canviewthreads"] == "yes" ? " $checked = \"checked\"" : "") . " $checkme = \"group" . $Usergroup["gid"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"checkbox\" $name = \"canpostthreads[" . $Usergroup["gid"] . "]\" $value = \"yes\"" . (isset($ForumPermissions[$Usergroup["gid"]]) && $ForumPermissions[$Usergroup["gid"]]["canpostthreads"] == "yes" ? " $checked = \"checked\"" : "") . " $checkme = \"group" . $Usergroup["gid"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"checkbox\" $name = \"canpostreplys[" . $Usergroup["gid"] . "]\" $value = \"yes\"" . (isset($ForumPermissions[$Usergroup["gid"]]) && $ForumPermissions[$Usergroup["gid"]]["canpostreplys"] == "yes" ? " $checked = \"checked\"" : "") . " $checkme = \"group" . $Usergroup["gid"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"checkbox\" $name = \"caneditposts[" . $Usergroup["gid"] . "]\" $value = \"yes\"" . (isset($ForumPermissions[$Usergroup["gid"]]) && $ForumPermissions[$Usergroup["gid"]]["caneditposts"] == "yes" ? " $checked = \"checked\"" : "") . " $checkme = \"group" . $Usergroup["gid"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"checkbox\" $name = \"candeleteposts[" . $Usergroup["gid"] . "]\" $value = \"yes\"" . (isset($ForumPermissions[$Usergroup["gid"]]) && $ForumPermissions[$Usergroup["gid"]]["candeleteposts"] == "yes" ? " $checked = \"checked\"" : "") . " $checkme = \"group" . $Usergroup["gid"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"checkbox\" $name = \"candeletethreads[" . $Usergroup["gid"] . "]\" $value = \"yes\"" . (isset($ForumPermissions[$Usergroup["gid"]]) && $ForumPermissions[$Usergroup["gid"]]["candeletethreads"] == "yes" ? " $checked = \"checked\"" : "") . " $checkme = \"group" . $Usergroup["gid"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"checkbox\" $name = \"canpostattachments[" . $Usergroup["gid"] . "]\" $value = \"yes\"" . (isset($ForumPermissions[$Usergroup["gid"]]) && $ForumPermissions[$Usergroup["gid"]]["canpostattachments"] == "yes" ? " $checked = \"checked\"" : "") . " $checkme = \"group" . $Usergroup["gid"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"checkbox\" $name = \"cansearch[" . $Usergroup["gid"] . "]\" $value = \"yes\"" . (isset($ForumPermissions[$Usergroup["gid"]]) && $ForumPermissions[$Usergroup["gid"]]["cansearch"] == "yes" ? " $checked = \"checked\"" : "") . " $checkme = \"group" . $Usergroup["gid"] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t<input $type = \"checkbox\" $checkall = \"group" . $Usergroup["gid"] . "\" $onclick = \"javascript: return select_deselectAll ('forumpermissions', this, 'group" . $Usergroup["gid"] . "');\">\r\n\t\t\t\t</td>\r\n\t\t\t</tr>";
        }
        $List = "\r\n\t\t" . showAlertMessage("<a $href = \"index.php?do=forums\">" . $Language[36] . "</a>") . "\r\n\t\t" . $Message . "\r\n\t\t<form $method = \"post\" $action = \"index.php?do=forums&$act = permissions&$fid = " . $fid . "\" $name = \"forumpermissions\">\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"tcat\" $align = \"center\" $colspan = \"11\">\r\n\t\t\t\t\t" . $Language[2] . " - " . $Language[13] . " (" . $ForumName . ")\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt2\" $align = \"left\">\r\n\t\t\t\t\t<b>" . $Language[40] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t\t<b>" . $Language[41] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t\t<b>" . $Language[42] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t\t<b>" . $Language[43] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t\t<b>" . $Language[44] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t\t<b>" . $Language[45] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t\t<b>" . $Language[46] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t\t<b>" . $Language[47] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t\t<b>" . $Language[48] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t\t<b>" . $Language[49] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t\t<td class=\"alt2\" $align = \"center\">\r\n\t\t\t\t\t<b>" . $Language[50] . "</b>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t" . $ListUsergroups . "\r\n\t\t\t<tr>\r\n\t\t\t\t<td $colspan = \"11\" $align = \"center\" class=\"tcat2\">\r\n\t\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[17] . "\" /> <input $type = \"reset\" $value = \"" . $Language[18] . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>\r\n\t\t<script $type = \"text/javascript\">\r\n\t\tfunction select_deselectAll(formname,elm,group)\r\n\t\t{\r\n\t\t\tvar $justGroup = group.replace(/group/, \"\"), $isChecked = jQuery(elm).is(\":checked\");\r\n\t\t\tvar $tr = jQuery('tr[data-$gid = \"'+justGroup+'\"]');\r\n\t\t\t\r\n\t\t\tif(isChecked)\r\n\t\t\t{\r\n\t\t\t\ttr.css({background: tr.data(\"color\")});\r\n\t\t\t\ttr.find(\"td\").first().css({background: \"#ffffff\"});\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\ttr.css({background: \"none\"});\r\n\t\t\t}\r\n\r\n\t\t\tvar $frm = document.forms[formname];\r\n\t\t\tfor($i = 0;i<frm.length;i++)\r\n\t\t\t{\r\n\t\t\t\tif(elm.attributes[\"checkall\"] != null && elm.attributes[\"checkall\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[i].$checked = elm.checked;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if(frm.elements[i].attributes[\"checkme\"] != null && frm.elements[i].attributes[\"checkme\"].$value = = group)\r\n\t\t\t\t{\r\n\t\t\t\t\tif(frm.elements[i].$checked = = false)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tfrm.elements[1].$checked = false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t}\r\n\t</script>";
    }
}
if (!isset($List)) {
    $List = "";
    $MainForums = [];
    $SubForums = [];
    $DeepSubForums = [];
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT * FROM " . TSF_PREFIX . "forums ORDER BY disporder");
    while ($Forum = mysqli_fetch_assoc($query)) {
        if ($Forum["type"] == "c") {
            $MainForums[$Forum["fid"]] = $Forum;
        } else {
            if ($Forum["type"] == "f") {
                $SubForums[$Forum["pid"]][] = $Forum;
            } else {
                $DeepSubForums[$Forum["pid"]][] = $Forum;
            }
        }
    }
    $count = 0;
    foreach ($MainForums as $MainForum) {
        $List .= "\r\n\t\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt2\" $align = \"left\" $width = \"50%\">\r\n\t\t\t\t<b>" . $Language[3] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\" $width = \"20%\">\r\n\t\t\t\t<b>" . $Language[4] . "</b>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt2\" $align = \"center\" $width = \"30%\">\r\n\t\t\t\t<b>" . $Language[7] . "</b>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\" $align = \"left\">\r\n\t\t\t\t<img $src = \"../tsf_forums/images/forumicons/" . $MainForum["image"] . "\" $alt = \"\" $title = \"\" $border = \"0\" $style = \"vertical-align: middle;\" $width = \"16\" $height = \"16\" /> <span $style = \"font-size: 15px; font-weight: bold;\">" . $MainForum["name"] . "</span>\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<input $type = \"text\" $size = \"3\" $name = \"disporder[" . $MainForum["fid"] . "]\" $value = \"" . $MainForum["disporder"] . "\" />\r\n\t\t\t</td>\r\n\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t<select $name = \"action\" $onchange = \"Jump_Forum_Action(" . $MainForum["fid"] . ", this.value, true);\">\r\n\t\t\t\t\t<option $value = \"\"></option>\r\n\t\t\t\t\t<option $value = \"edit_forum\">" . $Language[8] . "</option>\r\n\t\t\t\t\t<option $value = \"add_child_forum\">" . $Language[11] . "</option>\r\n\t\t\t\t\t<option $value = \"add_announcement\">" . $Language[12] . "</option>\r\n\t\t\t\t\t<option $value = \"add_moderator\">" . $Language[6] . "</option>\r\n\t\t\t\t\t<option $value = \"permissions\">" . $Language[13] . "</option>\r\n\t\t\t\t\t<option $value = \"view_forum\">" . $Language[9] . "</option>\r\n\t\t\t\t\t<option $value = \"delete_forum\">" . $Language[10] . "</option>\r\n\t\t\t\t</select>\r\n\t\t\t</td>\r\n\t\t</tr>";
        if (isset($SubForums[$MainForum["fid"]])) {
            foreach ($SubForums[$MainForum["fid"]] as $SubForum) {
                $List .= "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"alt1\" $align = \"left\">\r\n\t\t\t\t\t\t<img $src = \"../tsf_forums/images/forumicons/" . $SubForum["image"] . "\" $alt = \"\" $title = \"\" $border = \"0\" $style = \"vertical-align: middle;\" $width = \"16\" $height = \"16\" /><img $src = \"images/tree_ltr.gif\" $border = \"0\" $alt = \"\" $title = \"\" $style = \"vertical-align: middle;\" />  " . $SubForum["name"] . "\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t\t<input $type = \"text\" $size = \"3\" $name = \"disporder[" . $SubForum["fid"] . "]\" $value = \"" . $SubForum["disporder"] . "\" />\r\n\t\t\t\t\t</td>\r\n\t\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t\t<select $name = \"action\" $onchange = \"Jump_Forum_Action(" . $SubForum["fid"] . ", this.value, false);\">\r\n\t\t\t\t\t\t\t<option $value = \"\"></option>\r\n\t\t\t\t\t\t\t<option $value = \"edit_forum\">" . $Language[8] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"add_child_forum\">" . $Language[11] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"add_announcement\">" . $Language[12] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"add_moderator\">" . $Language[6] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"permissions\">" . $Language[13] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"view_forum\">" . $Language[9] . "</option>\r\n\t\t\t\t\t\t\t<option $value = \"delete_forum\">" . $Language[10] . "</option>\r\n\t\t\t\t\t\t</select>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>";
                if (isset($DeepSubForums[$SubForum["fid"]])) {
                    foreach ($DeepSubForums[$SubForum["fid"]] as $DeepSubForum) {
                        $List .= "\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"alt1\" $align = \"left\">\r\n\t\t\t\t\t\t\t\t<img $src = \"../tsf_forums/images/forumicons/" . $DeepSubForum["image"] . "\" $alt = \"\" $title = \"\" $border = \"0\" $style = \"vertical-align: middle;\" $width = \"16\" $height = \"16\" />&nbsp; &nbsp; &nbsp;<img $src = \"images/tree_ltr.gif\" $border = \"0\" $alt = \"\" $title = \"\" $style = \"vertical-align: middle;\" />  " . $DeepSubForum["name"] . "\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t\t\t\t<input $type = \"text\" $size = \"3\" $name = \"disporder[" . $DeepSubForum["fid"] . "]\" $value = \"" . $DeepSubForum["disporder"] . "\" />\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t<td class=\"alt1\" $align = \"center\">\r\n\t\t\t\t\t\t\t\t<select $name = \"action\" $onchange = \"Jump_Forum_Action(" . $DeepSubForum["fid"] . ", this.value, false);\">\r\n\t\t\t\t\t\t\t\t\t<option $value = \"\"></option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"edit_forum\">" . $Language[8] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"add_announcement\">" . $Language[12] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"permissions\">" . $Language[13] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"view_forum\">" . $Language[9] . "</option>\r\n\t\t\t\t\t\t\t\t\t<option $value = \"delete_forum\">" . $Language[10] . "</option>\r\n\t\t\t\t\t\t\t\t</select>\r\n\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t</tr>";
                    }
                }
            }
        }
        $List .= "\r\n\t\t</table><br />";
        $count++;
    }
    echo "\r\n\t<script $type = \"text/javascript\">\r\n\t\tfunction Jump_Forum_Action(fid, action, mainForum)\r\n\t\t{\r\n\t\t\tif ($action = = \"view_forum\")\r\n\t\t\t{\r\n\t\t\t\tif (!mainForum)\r\n\t\t\t\t\tvar $url = \"../tsf_forums/forumdisplay.php?$fid = \"+fid;\r\n\t\t\t\telse\r\n\t\t\t\t\tvar $url = \"../tsf_forums/index.php?$fid = \"+fid;\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\tvar $url = \"index.php?do=forums&$act = \"+action+\"&$fid = \"+fid;\r\n\t\t\t}\r\n\t\t\tif ($action = = \"delete_forum\")\r\n\t\t\t{\r\n\t\t\t\tif (confirm(\"" . trim($Language[38]) . "\"))\r\n\t\t\t\t{\r\n\t\t\t\t\twindow.location.$href = url;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\twindow.location.$href = url;\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t" . showAlertMessage("<a $href = \"index.php?do=forums&amp;$act = new_forum\">" . trim($Language[35]) . "</a> / <a $href = \"index.php?do=forums&amp;$act = manage_announcements\">" . trim($Language[66]) . "</a> / <a $href = \"index.php?do=forums&amp;$act = settings\">" . trim($Language[71]) . "</a>") . "\r\n\t" . $Message . "\r\n\t<form $method = \"post\" $action = \"index.php?do=forums&$act = save_order\">\r\n\t<table $cellpadding = \"0\" $cellspacing = \"0\" $border = \"0\" class=\"mainTable\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat\" $align = \"center\">\r\n\t\t\t\t" . $Language[2] . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"alt1\">\r\n\t\t\t\t" . $List . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"tcat2\" $align = \"center\">\r\n\t\t\t\t<input $type = \"submit\" $value = \"" . $Language[14] . "\" /> <input $type = \"reset\" $value = \"" . $Language[15] . "\" />\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t</table>\r\n\t</form>";
} else {
    echo $List;
}
function loadTinyMCEEditor($type = 1, $mode = "textareas", $elements = "")
{
    define("EDITOR_TYPE", $type);
    define("TINYMCE_MODE", $mode);
    define("TINYMCE_ELEMENTS", $elements);
    define("WORKPATH", "./../scripts/");
    define("TINYMCE_EMOTIONS_URL", "./../tinymce_emotions.php");
    ob_start();
    include "./../tinymce.php";
    $editorContent = ob_get_contents();
    ob_end_clean();
    return $editorContent;
}
function getStaffLanguage()
{
    if (isset($_COOKIE["staffcplanguage"]) && is_dir("languages/" . $_COOKIE["staffcplanguage"]) && is_file("languages/" . $_COOKIE["staffcplanguage"] . "/staffcp.lang")) {
        return $_COOKIE["staffcplanguage"];
    }
    return "english";
}
function checkStaffAuthentication()
{
    if (!defined("IN-TSSE-STAFF-PANEL")) {
        redirectTo("../index.php");
    }
}
function redirectTo($url, $timeout = false)
{
    if (!headers_sent()) {
        if (!$timeout) {
            header("Location: " . $url);
        } else {
            header("Refresh: 5; $url = " . $url);
        }
    } else {
        if (!$timeout) {
            echo "\r\n\t\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\t\twindow.location.$href = \"" . $url . "\";\r\n\t\t\t\t</script>\r\n\t\t\t\t<noscript>\r\n\t\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"0;$url = " . $url . "\" />\r\n\t\t\t\t</noscript>";
        } else {
            echo "\r\n\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\tsetTimeout( \"window.location.$href = '" . $url . "'\", 5000);\r\n\t\t\t</script>\r\n\t\t\t<noscript>\r\n\t\t\t\t<meta http-$equiv = \"refresh\" $content = \"5;$url = " . $url . "\" />\r\n\t\t\t</noscript>\r\n\t\t\t";
        }
    }
    exit;
}
function showAlertError($Error)
{
    return "<div class=\"alert\"><div>" . $Error . "</div></div>";
}
function logStaffAction($log)
{
    mysqli_query($GLOBALS["DatabaseConnect"], "INSERT INTO ts_staffcp_logs (uid, date, log) VALUES ('" . $_SESSION["ADMIN_ID"] . "', '" . time() . "', '" . mysqli_real_escape_string($GLOBALS["DatabaseConnect"], $log) . "')");
}
function showAlertMessage($message = "")
{
    return "<div class=\"alert\"><div>" . $message . "</div></div>";
}
function function_149($file)
{
    return strtolower(substr(strrchr($file, "."), 1));
}
function function_160($fid, $navsep = ",")
{
    $query = mysqli_query($GLOBALS["DatabaseConnect"], "SELECT name,fid,pid FROM " . TSF_PREFIX . "forums ORDER BY disporder, pid");
    $forumId = [];
    while ($forumName = mysqli_fetch_assoc($query)) {
        $forumId[$forumName["fid"]][$forumName["pid"]] = $forumName;
    }
    reset($forumId);
    reset($forumId[$fid]);
    foreach ($forumId[$fid] as $key => $forumName) {
        if ($fid == $forumName["fid"]) {
            if (isset($forumId[$forumName["pid"]])) {
                $forumData = getForumData($forumName["pid"], $navsep) . (isset($forumData) ? $forumData : "");
            }
            if (isset($forumData)) {
                $forumData .= $navsep;
                $forumData .= $forumName["fid"];
            } else {
                $forumData = $forumName["fid"];
            }
        }
    }
    return $forumData;
}
function formatBytes($bytes = 0)
{
    if ($bytes < 1024000) {
        return number_format($bytes / 1024, 2) . " KB";
    }
    if ($bytes < 1048576000) {
        return number_format($bytes / 1048576, 2) . " MB";
    }
    if ($bytes < 0) {
        return number_format($bytes / 1073741824, 2) . " GB";
    }
    return number_format($bytes / 0, 2) . " TB";
}

?>