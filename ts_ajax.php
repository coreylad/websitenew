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
define("THIS_SCRIPT", "ts_ajax.php");
require "./global.php";
define("TS_AJAX_VERSION", "1.2.6 by xam");
if (strtoupper($_SERVER["REQUEST_METHOD"]) != "POST" && $_GET["action"] != "quick_edit" && $_GET["action"] != "autocomplete") {
    exit;
}
if (isset($_POST["action"]) && $_POST["action"] == "save_quick_edit" && 0 < $CURUSER["id"]) {
    $lang->load("comment");
    $commentid = intval($_POST["cid"]);
    if (!is_valid_id($commentid)) {
        show_msg($lang->global["notorrentid"]);
    }
    if ($usergroups["cancomment"] == "no") {
        show_msg($lang->global["nopermission"]);
    }
    ($query = sql_query("SELECT cancomment FROM ts_u_perm WHERE userid = " . sqlesc($CURUSER["id"]))) || sqlerr(__FILE__, 100);
    if (0 < mysqli_num_rows($query)) {
        $commentperm = mysqli_fetch_assoc($query);
        if ($commentperm["cancomment"] == "0") {
            show_msg($lang->global["nopermission"]);
        }
    }
    ($res = sql_query("SELECT c.text, c.user, t.id as torrentid FROM comments AS c JOIN torrents AS t ON c.torrent = t.id WHERE c.id= " . sqlesc($commentid))) || sqlerr(__FILE__, 110);
    $arr = mysqli_fetch_assoc($res);
    if (!$arr) {
        show_msg($lang->global["notorrentid"]);
    }
    if ($arr["user"] != $CURUSER["id"] && !$is_mod) {
        show_msg($lang->global["nopermission"]);
    }
    if (!allowcomments($arr["torrentid"])) {
        show_msg($lang->comment["closed"]);
    }
    if ($_POST["text"] != $arr["text"]) {
        $msgtext = fixAjaxText($_POST["text"]);
        if ($msgtext == "") {
            show_msg($lang->global["dontleavefieldsblank"]);
        }
        if (strtolower($shoutboxcharset) != "utf-8") {
            if (function_exists("iconv")) {
                $msgtext = iconv("UTF-8", $shoutboxcharset, $msgtext);
            } else {
                if (function_exists("mb_convert_encoding")) {
                    $msgtext = mb_convert_encoding($msgtext, $shoutboxcharset, "UTF-8");
                } else {
                    if (strtolower($shoutboxcharset) == "iso-8859-1") {
                        $msgtext = utf8_decode($msgtext);
                    }
                }
            }
        }
        $editedat = get_date_time();
        sql_query("UPDATE comments SET text = " . sqlesc($msgtext) . ", editedat=" . sqlesc($editedat) . ", editedby=" . sqlesc($CURUSER["id"]) . " WHERE id= " . sqlesc($commentid)) || sqlerr(__FILE__, 150);
        $edit_date = my_datee($dateformat, $editedat);
        $edit_time = my_datee($timeformat, $editedat);
        $p_text = "<p><font size='1' class='small'>" . $lang->global["lastedited"] . " <a href='" . $BASEURL . "/userdetails.php?id=" . $CURUSER["id"] . "'><b>" . $CURUSER["username"] . "</b></a> " . $edit_date . " " . $edit_time . "</font></p>\n";
    }
    show_msg(format_comment($_POST["text"]) . (isset($p_text) ? $p_text : ""), false, NULL, false);
} else {
    if (isset($_GET["action"]) && $_GET["action"] == "quick_edit" && 0 < $CURUSER["id"]) {
        $lang->load("comment");
        $commentid = intval($_GET["cid"]);
        if (!is_valid_id($commentid)) {
            show_msg($lang->global["notorrentid"]);
        }
        if ($usergroups["cancomment"] == "no") {
            show_msg($lang->global["nopermission"]);
        }
        ($query = sql_query("SELECT cancomment FROM ts_u_perm WHERE userid = " . sqlesc($CURUSER["id"]))) || sqlerr(__FILE__, 169);
        if (0 < mysqli_num_rows($query)) {
            $commentperm = mysqli_fetch_assoc($query);
            if ($commentperm["cancomment"] == "0") {
                show_msg($lang->global["nopermission"]);
            }
        }
        ($res = sql_query("SELECT c.text, c.user, t.id as torrentid FROM comments AS c JOIN torrents AS t ON c.torrent = t.id WHERE c.id= " . sqlesc($commentid))) || sqlerr(__FILE__, 179);
        $arr = mysqli_fetch_assoc($res);
        if (!$arr) {
            show_msg($lang->global["notorrentid"]);
        }
        if ($arr["user"] != $CURUSER["id"] && !$is_mod) {
            show_msg($lang->global["nopermission"]);
        }
        if (!allowcomments($arr["torrentid"])) {
            show_msg($lang->comment["closed"]);
        }
        show_msg(htmlspecialchars_uni($arr["text"]), false, NULL, false);
    } else {
        if (isset($_POST["ajax_quick_reply"]) && isset($_POST["tid"]) && isset($_POST["message"]) && isset($CURUSER)) {
            if ($usergroups["isforummod"] == "yes" || $usergroups["cansettingspanel"] == "yes" || $usergroups["issupermod"] == "yes") {
                $moderator = true;
            } else {
                $moderator = false;
            }
            $lang->load("tsf_forums");
            $tid = isset($_POST["tid"]) ? intval($_POST["tid"]) : 0;
            if (!is_valid_id($tid)) {
                show_msg($lang->tsf_forums["invalid_tid"]);
            }
            ($query = sql_query("SELECT\n\t\t\tt.subject as threadsubject, t.closed, t.sticky, f.type, f.name as currentforum, f.fid as currentforumid, f.moderate, ff.name as deepforum, ff.fid as deepforumid, ff.moderate as moderaterf\n\t\t\tFROM " . TSF_PREFIX . "threads t\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (f.fid=t.fid)\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.fid=f.pid)\n\t\t\tWHERE t.tid = " . sqlesc($tid) . " LIMIT 0, 1")) || show_msg("dberror1");
            if (mysqli_num_rows($query) == 0) {
                show_msg($lang->tsf_forums["invalid_tid"]);
            }
            $thread = mysqli_fetch_assoc($query);
            $forummoderator = is_forum_mod($thread["type"] == "s" ? $thread["deepforumid"] : $thread["currentforumid"], $CURUSER["id"]);
            if (($thread["moderate"] == 1 || $thread["moderaterf"] == 1) && ($forummoderator || $moderator)) {
                $thread["moderate"] = 0;
                $thread["moderaterf"] = 0;
            }
            $visible = $thread["moderate"] == 1 || $thread["moderaterf"] == 1 ? 0 : 1;
            $query = sql_query("SELECT * FROM " . TSF_PREFIX . "forumpermissions WHERE gid = " . sqlesc($CURUSER["usergroup"])) or ($query = sql_query("SELECT * FROM " . TSF_PREFIX . "forumpermissions WHERE gid = " . sqlesc($CURUSER["usergroup"]))) || show_msg("dberror2");
            while ($perm = mysqli_fetch_assoc($query)) {
                $permissions[$perm["fid"]] = $perm;
            }
            if ($permissions[$thread["currentforumid"]]["canview"] != "yes" || $permissions[$thread["currentforumid"]]["canpostreplys"] != "yes") {
                show_msg($lang->global["nopermission"]);
            } else {
                if ($thread["closed"] == "yes" && !$moderator && !$forummoderator) {
                    show_msg($lang->tsf_forums["thread_closed"]);
                }
            }
            $useparent = false;
            if ($thread["type"] == "s") {
                $useparent = true;
            }
            $subject = $lang->tsf_forums["re"] . $thread["threadsubject"];
            $threadsubject = ts_remove_badwords($subject);
            $replyto = 0;
            $fid = 0 + $thread["currentforumid"];
            $error = "";
            $uid = sqlesc($CURUSER["id"]);
            $username = sqlesc($CURUSER["username"]);
            $dateline = sqlesc(TIMENOW);
            $message = fixAjaxText($_POST["message"]);
            $message = strval($message);
            if (strtolower($shoutboxcharset) != "utf-8") {
                if (function_exists("iconv")) {
                    $message = iconv("UTF-8", $shoutboxcharset, $message);
                } else {
                    if (function_exists("mb_convert_encoding")) {
                        $message = mb_convert_encoding($message, $shoutboxcharset, "UTF-8");
                    } else {
                        if (strtolower($shoutboxcharset) == "iso-8859-1") {
                            $message = utf8_decode($message);
                        }
                    }
                }
            }
            $ipaddress = sqlesc($CURUSER["ip"]);
            $closed = $_POST["closethread"] == "1" && ($moderator || $forummoderator) ? "yes" : "no";
            $sticky = $_POST["stickthread"] == "1" && ($moderator || $forummoderator) ? 1 : 0;
            $subscribe = isset($_POST["subscribe"]) && $_POST["subscribe"] == "yes" ? 1 : 0;
            if ($subscribe) {
                ($query = sql_query("SELECT userid FROM " . TSF_PREFIX . "subscribe WHERE tid = " . sqlesc($tid) . " AND userid = " . $uid)) || show_msg("dberror3");
                if (mysqli_num_rows($query) == 0) {
                    sql_query("INSERT INTO " . TSF_PREFIX . "subscribe (tid,userid) VALUES (" . sqlesc($tid) . "," . $uid . ")") || show_msg("dberror4");
                }
            }
            $extraquery = "";
            if ($moderator || $forummoderator) {
                $extraquery = ", closed = " . sqlesc($closed) . ", sticky = " . sqlesc($sticky);
            }
            if (strlen($_POST["message"]) < $f_minmsglength) {
                show_msg($lang->tsf_forums["too_short"]);
            }
            ($query = sql_query("SELECT dateline FROM " . TSF_PREFIX . "posts WHERE uid = " . sqlesc($CURUSER["id"]) . " ORDER by dateline DESC LIMIT 1")) || sqlerr(__FILE__, 314);
            if (mysqli_num_rows($query)) {
                $Result = mysqli_fetch_assoc($query);
                $last_post = $Result["dateline"];
                $floodcheck = flood_check($lang->tsf_forums["a_post"], $last_post, true);
                if ($floodcheck != "") {
                    show_msg(str_replace(["<font color=\"#9f040b\" size=\"2\">", "</font>", "<b>", "</b>"], "", $floodcheck));
                }
            }
            sql_query("INSERT INTO " . TSF_PREFIX . "posts (tid,replyto,fid,subject,uid,username,dateline,message,ipaddress,visible) VALUES (" . $tid . "," . $replyto . "," . $fid . ", " . sqlesc($subject) . ", " . $uid . ", " . $username . ", " . $dateline . ", " . sqlesc($message) . ", " . $ipaddress . "," . $visible . ")") || show_msg("dberror5");
            $pid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
            sql_query("UPDATE " . TSF_PREFIX . "threads SET replies = replies + 1, lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . $extraquery . " WHERE tid = " . sqlesc($tid)) || show_msg("dberror6");
            sql_query("UPDATE " . TSF_PREFIX . "forums SET posts = posts + 1, lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . ", lastposttid = " . $tid . ", lastpostsubject = " . sqlesc($subject) . " WHERE fid = '" . $fid . "'") || show_msg("dberror7");
            if ($useparent) {
                sql_query("UPDATE " . TSF_PREFIX . "forums SET lastpost = " . $dateline . ", lastposter = " . $username . ", lastposteruid = " . $uid . ", lastposttid = " . $tid . ", lastpostsubject = " . sqlesc($subject) . " WHERE fid = '" . $thread["deepforumid"] . "'") || show_msg("dberror7");
            }
            sql_query("UPDATE users SET totalposts = totalposts + 1 WHERE id = " . $uid) || show_msg("dberror8");
            sql_query("REPLACE INTO " . TSF_PREFIX . "threadsread SET tid='" . $tid . "', uid='" . $CURUSER["id"] . "', dateline='" . TIMENOW . "'");
            $TSSEConfig->TSLoadConfig("KPS");
            KPS("+", isset($kpscomment) && $kpscomment ? $kpscomment : "", $CURUSER["id"]);
            $lastseen = my_datee($dateformat, $CURUSER["last_access"]) . " " . my_datee($timeformat, $CURUSER["last_access"]);
            $downloaded = mksize($CURUSER["downloaded"]);
            $uploaded = mksize($CURUSER["uploaded"]);
            include_once INC_PATH . "/functions_ratio.php";
            $ratio = get_user_ratio($CURUSER["uploaded"], $CURUSER["downloaded"]);
            $ratio = str_replace("'", "\\'", $ratio);
            require INC_PATH . "/function_user_rank.php";
            if ((TS_Match($CURUSER["options"], "I3") || TS_Match($CURUSER["options"], "I4")) && !$moderator && !$forummoderator) {
                $tooltip = $lang->tsf_forums["deny"];
            } else {
                $tooltip = sprintf($lang->tsf_forums["tooltip"], $lastseen, $downloaded, $uploaded, $ratio);
            }
            $poster = "<a href=\"javascript:void(0);\" id=\"quickmenu" . $pid . "\"><i onmouseover=\"ddrivetip('" . $tooltip . "', 200)\"; onmouseout=\"hideddrivetip()\">" . get_user_color(htmlspecialchars_uni($CURUSER["username"]), $usergroups["namestyle"]) . "</i></a>";
            include_once INC_PATH . "/functions_icons.php";
            $usericons = get_user_icons(array_merge($CURUSER, $usergroups));
            $usertitle = "";
            if (!empty($CURUSER["title"])) {
                $usertitle = "<font class=\"smalltext\"><strong>" . htmlspecialchars_uni($CURUSER["title"]) . "</strong></font><br />";
            }
            $poster_title = $lang->tsf_forums["usergroup"] . $usergroups["title"];
            $avatar = "";
            if (TS_Match($CURUSER["options"], "D1")) {
                $avatar = get_user_avatar($CURUSER["avatar"]);
            }
            $join_date = $lang->tsf_forums["jdate"] . my_datee($regdateformat, $CURUSER["added"]);
            $totalposts = $lang->tsf_forums["totalposts"] . ts_nf($CURUSER["totalposts"] + 1);
            $UserOn = sprintf($lang->tsf_forums["user_online"], $CURUSER["username"]);
            $status = "<img src=\"" . $pic_base_url . "friends/online.png\" border=\"0\" alt=\"" . $UserOn . "\" title=\"" . $UserOn . "\" class=\"inlineimg\" />";
            $CURUSER["countryname"] = "";
            $CURUSER["flagpic"] = "";
            ($query = @sql_query("SELECT flagpic,name as countryname FROM countries WHERE id = " . @sqlesc($CURUSER["country"]))) || show_msg("dberror9");
            if (0 < mysqli_num_rows($query)) {
                $Result = mysqli_fetch_assoc($query);
                $CURUSER["countryname"] = $Result["countryname"];
                $CURUSER["flagpic"] = $Result["flagpic"];
            }
            $country = $lang->tsf_forums["country"] . "<img src='" . $pic_base_url . "flag/" . $CURUSER["flagpic"] . "' alt='" . $CURUSER["countryname"] . "' title='" . $CURUSER["countryname"] . "' style='margin-center: 2pt' height='10px' class='inlineimg'>";
            $signature = "";
            if (!empty($CURUSER["signature"]) && TS_Match($CURUSER["options"], "H1")) {
                $signature = "<hr align=\"left\" size=\"1\" width=\"65%\">" . format_comment($CURUSER["signature"], true, true, true, true, "signatures");
            }
            $ABuffer = [];
            ($AwardQuery = sql_query("SELECT a.id, a.userid, a.reason, a.date, aw.award_name, aw.award_image FROM ts_awards_users a LEFT JOIN ts_awards aw ON (a.award_id=aw.award_id)")) || sqlerr(__FILE__, 407);
            if (mysqli_num_rows($AwardQuery)) {
                while ($Award = mysqli_fetch_assoc($AwardQuery)) {
                    $ATooltip = "<strong>" . htmlspecialchars_uni($Award["award_name"]) . "</strong><br /><small>" . addslashes(htmlspecialchars_uni($Award["reason"])) . "</small>";
                    $ABuffer[$Award["userid"]][$Award["id"]] = "\n\t\t\t<i onmouseover=\"ddrivetip('" . $ATooltip . "', 200)\"; onmouseout=\"hideddrivetip()\"><img src=\"" . $pic_base_url . "awardmedals/" . htmlspecialchars_uni($Award["award_image"]) . "\" border=\"0\" alt=\"\" title=\"\" class=\"inlineimg\" width=\"10\" height=\"19\" style=\"padding-top: 3px; cursor: pointer;\" /></i>&nbsp;";
                }
            }
            if (isset($ABuffer[$CURUSER["id"]])) {
                $UserAwards = $lang->tsf_forums["awards"] . ": ";
                foreach ($ABuffer[$CURUSER["id"]] as $Awid => $Awimage) {
                    $UserAwards .= $Awimage;
                }
            }
            $imagepath = $pic_base_url . "friends/";
            if (TS_Match($CURUSER["options"], "L1")) {
                $UserGender = "<img src=\"" . $imagepath . "Male.png\" alt=\"" . $lang->global["male"] . "\" title=\"" . $lang->global["male"] . "\" border=\"0\" class=\"inlineimg\" />";
            } else {
                if (TS_Match($CURUSER["options"], "L2")) {
                    $UserGender = "<img src=\"" . $imagepath . "Female.png\" alt=\"" . $lang->global["female"] . "\" title=\"" . $lang->global["female"] . "\" border=\"0\" class=\"inlineimg\" />";
                } else {
                    $UserGender = "<img src=\"" . $imagepath . "NA.png\" alt=\"--\" title=\"--\" border=\"0\" class=\"inlineimg\" />";
                }
            }
            include_once INC_PATH . "/function_warnlevel.php";
            $_warnlevel = get_warn_level($CURUSER["timeswarned"]);
            $deletebutton = "<input value=\"" . $lang->tsf_forums["delete_post"] . "\" onclick=\"jumpto('" . $BASEURL . "/tsf_forums/deletepost.php?tid=" . $tid . "\\&amp;pid=" . $pid . "&amp;page=" . (isset($_POST["page"]) ? intval($_POST["page"]) : 0) . "');\" type=\"button\">";
            $post_date = my_datee($dateformat, TIMENOW) . " " . my_datee($timeformat, TIMENOW);
            define("IS_THIS_USER_POSTED", true);
            $deletebutton = "";
            $editbutton = "";
            $quotebutton = "";
            $quickreplybutton = "";
            $showpagenumber = isset($_POST["page"]) && is_valid_id($_POST["page"]) ? "&amp;page=" . intval($_POST["page"]) : "";
            if ($moderator || $forummoderator || $permissions[$fid]["candeleteposts"] == "yes" && $closed != "yes") {
                $deletebutton = "<input value=\"" . $lang->tsf_forums["delete_post"] . "\" onclick=\"jumpto('" . $BASEURL . "/tsf_forums/deletepost.php?tid=" . $tid . "&amp;pid=" . $pid . "&amp;page=" . (isset($_POST["page"]) ? intval($_POST["page"]) : 0) . "');\" type=\"button\" />";
            }
            if ($moderator || $forummoderator || $permissions[$fid]["canpostreplys"] == "yes" && $closed != "yes") {
                if ($visible == 0 && !$moderator && !$forummoderator) {
                    $quotebutton = "";
                    $quickreplybutton = "";
                } else {
                    $QuoteTag = htmlspecialchars(mysqli_real_escape_string($GLOBALS["DatabaseConnect"], "[quote=" . $CURUSER["username"] . "]" . $message . "[/quote]"));
                    $quotebutton = "<input value=\"" . $lang->tsf_forums["quote_post"] . "\" onclick=\"jumpto('" . $BASEURL . "/tsf_forums/newreply.php?tid=" . $tid . "&amp;pid=" . $pid . "');\" type=\"button\" />";
                    $quickreplybutton = "<input type=\"button\" id=\"quote_" . $pid . "\" value=\"" . $lang->tsf_forums["quick_reply"] . "\" onclick=\"parseQuote('" . $QuoteTag . "', 'message', " . $tid . ", " . $pid . ");\" />";
                }
            }
            if ($moderator || $forummoderator || $permissions[$fid]["caneditposts"] == "yes" && $closed != "yes") {
                $onclick = "onclick=\"jumpto('" . $BASEURL . "/tsf_forums/editpost.php?tid=" . $tid . "&amp;pid=" . $pid . $showpagenumber . "');\"";
                if ($useajax == "yes") {
                    $onclick = "onclick=\"TSQuickEditPost('post_message_" . $pid . "','" . $tid . "','" . $BASEURL . "/tsf_forums/editpost.php?tid=" . $tid . "&amp;pid=" . $pid . $showpagenumber . "');bookmarkscroll.scrollTo('post_message_" . $pid . "');\"";
                }
                $editbutton = "<input value=\"" . $lang->tsf_forums["edit_post"] . "\" " . $onclick . " type=\"button\" />";
            }
            $str2 = "\n\t\t<!-- start: post#" . $pid . " -->\n\t\t<br />\n\t\t<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"5\" style=\"clear: both;\">\n\t\t\t<tr>\n\t\t\t\t<td colspan=\"2\" class=\"subheader\" name=\"pid" . $pid . "\">\n\t\t\t\t\t<div style=\"float: right;\">\n\t\t\t\t\t\t<strong>" . $lang->tsf_forums["post"] . "<a href=\"" . tsf_seo_clean_text(htmlspecialchars_uni($threadsubject), "t", $tid, "#pid" . $pid) . "\">#" . intval($_POST["postcount"]) . "</a></strong>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div style=\"float: left;\">\n\t\t\t\t\t\t<a name=\"pid" . $pid . "\" id=\"pid" . $pid . "\"></a><img src=\"" . $BASEURL . "/tsf_forums/images/post_old.gif\" border=\"0\" class=\"inlineimg\" /> " . $post_date . "\n\t\t\t\t\t</div>\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t\t<td class=\"trow1\" style=\"text-align: center;\" valign=\"top\" width=\"20%\">\n\t\t\t\t\t" . $poster . "<br />\n\t\t\t\t\t" . $usertitle . "\n\t\t\t\t\t" . $avatar . "<br />\n\t\t\t\t\t" . user_rank($CURUSER) . "<br />\n\t\t\t\t\t" . $join_date . "<br />\n\t\t\t\t\t" . $totalposts . "<br />\n\t\t\t\t\t" . $country . "<br />\n\t\t\t\t\t" . (isset($UserAwards) ? $UserAwards . "<br />" : "") . "\n\t\t\t\t\t" . $UserGender . " " . $status . " " . $usericons . "\n\t\t\t\t\t" . $_warnlevel . "\n\t\t\t\t</td>\n\t\t\t\t<script type=\"text/javascript\">\n\t\t\t\t\tmenu_register(\"quickmenu" . $pid . "\", false);\n\t\t\t\t</script>\n\t\t\t\t<td class=\"trow1\" style=\"text-align: left;\" valign=\"top\" width=\"80%\">\n\t\t\t\t\t" . ($visible == 1 ? "<img src=\"" . $BASEURL . "/tsf_forums/images/icons/icon1.gif\" border=\"0\" class=\"inlineimg\" />" : "<img src=\"" . $BASEURL . "/tsf_forums/images/moderation.png\" alt=\"" . $lang->tsf_forums["moderatemsg7"] . "\" title=\"" . $lang->tsf_forums["moderatemsg7"] . "\" border=\"0\" class=\"inlineimg\" />") . "\n\t\t\t\t\t<span class=\"smalltext\"><strong>" . htmlspecialchars_uni($threadsubject) . "</strong></span><hr />\n\t\t\t\t\t" . ($thread["moderate"] == 0 && $thread["moderaterf"] == 0 ? "" : show_notice($lang->tsf_forums["moderatemsg1"]) . "<hr />") . "\n\t\t\t\t\t<div id=\"post_message_" . $pid . "\" name=\"post_message_" . $pid . "\" style=\"display: inline;\">" . format_comment($message) . "</div>\n\t\t\t\t\t" . $signature . "\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t\t<td class=\"subheader\" width=\"15%\" valign=\"middle\" style=\"white-space: nowrap; text-align: center;\">\n\t\t\t\t\t<input value=\"" . $lang->tsf_forums["top"] . "\"  onclick=\"bookmarkscroll.scrollTo('top');\" type=\"button\" /> <input value=\"" . $lang->tsf_forums["report_post"] . "\" onclick=\"TSOpenPopup('" . $BASEURL . "/report.php?type=4&reporting=" . $pid . "&extra=" . $tid . "&page=" . (isset($_POST["page"]) ? intval($_POST["page"]) : 0) . "', 'report', 500, 300); return false;\" type=\"button\" />\n\t\t\t\t</td>\n\t\t\t\t<td class=\"subheader\" style=\"text-align: center;\" valign=\"top\">\n\t\t\t\t\t<div style=\"float: right;\">\n\t\t\t\t\t\t" . $deletebutton . "\n\t\t\t\t\t\t" . $editbutton . "\n\t\t\t\t\t\t" . $quotebutton . "\n\t\t\t\t\t\t" . $quickreplybutton . "\n\t\t\t\t\t</div>\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t</table>\n\t\t<!-- end: post#" . $pid . " -->\n\n\t<div id=\"quickmenu" . $pid . "_menu\" class=\"menu_popup\" style=\"display:none;\">\n\t\t<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n\t\t\t<tr>\n\t\t\t\t<td align=\"center\" class=\"thead\"><b>" . $lang->global["quickmenu"] . " " . $CURUSER["username"] . "</b></td>\n\t\t\t</tr>\n\n\t\t\t<tr>\n\t\t\t\t<td class=\"subheader\"><a href=\"" . tsf_seo_clean_text($poster, "u", $CURUSER["id"]) . "\">" . $lang->global["qinfo1"] . "</a></td>\n\t\t\t</tr>\n\n\t\t\t<tr>\n\t\t\t\t<td class=\"subheader\"><a href=\"" . $BASEURL . "/sendmessage.php?receiver=" . $CURUSER["id"] . "\">" . sprintf($lang->global["qinfo2"], $CURUSER["username"]) . "</td>\n\t\t\t</tr>\n\n\t\t\t<tr>\n\t\t\t\t<td class=\"subheader\"><a href=\"" . $BASEURL . "/tsf_forums/tsf_search.php?action=finduserposts&amp;id=" . $CURUSER["id"] . "\">" . sprintf($lang->global["qinfo3"], $CURUSER["username"]) . "</a></td>\n\t\t\t</tr>\n\n\t\t\t<tr>\n\t\t\t\t<td class=\"subheader\"><a href=\"" . $BASEURL . "/tsf_forums/tsf_search.php?action=finduserthreads&amp;id=" . $CURUSER["id"] . "\">" . sprintf($lang->global["qinfo4"], $CURUSER["username"]) . "</a></td>\n\t\t\t</tr>\n\n\t\t\t<tr>\n\t\t\t\t<td class=\"subheader\"><a href=\"" . $BASEURL . "/friends.php?action=add_friend&amp;friendid=" . $CURUSER["id"] . "\">" . sprintf($lang->global["qinfo5"], $CURUSER["username"]) . "</td>\n\t\t\t</tr>\n\n\t\t\t" . ($moderator ? "\n\t\t\t<tr>\n\t\t\t\t<td class=\"subheader\"><a href=\"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=edit_user&amp;username=" . $CURUSER["username"] . "\">" . $lang->global["qinfo6"] . "</a></td>\n\t\t\t</tr>\n\t\t\t<tr>\n\t\t\t\t<td class=\"subheader\"><a href=\"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=warn_user&amp;username=" . $CURUSER["username"] . "\">" . $lang->global["qinfo7"] . "</a></td>\n\t\t\t</tr>\n\t\t\t<tr>" : "") . "\n\t\t</table>\n\t</div>";
            function send_sub_mails()
            {
                global $CURUSER;
                global $SITENAME;
                global $SITEEMAIL;
                global $BASEURL;
                global $tid;
                global $subject;
                global $lang;
                global $rootpath;
                require_once INC_PATH . "/functions_pm.php";
                ($query = sql_query("SELECT s.*, u.email, u.username FROM " . TSF_PREFIX . "subscribe s LEFT JOIN users u ON (s.userid=u.id) WHERE s.tid = " . sqlesc($tid) . " AND s.userid != " . sqlesc($CURUSER["id"]))) || sqlerr(__FILE__, 584);
                if (0 < mysqli_num_rows($query)) {
                    while ($sub = mysqli_fetch_assoc($query)) {
                        send_pm($sub["userid"], sprintf($lang->tsf_forums["msubs"], $sub["username"], $subject, $CURUSER["username"], $BASEURL, $tid, $SITENAME), $subject);
                        sent_mail($sub["email"], $subject, sprintf($lang->tsf_forums["msubs"], $sub["username"], $subject, $CURUSER["username"], $BASEURL, $tid, $SITENAME), "subs", false);
                    }
                }
            }
            if ($thread["moderate"] == 0 && $thread["moderaterf"] == 0) {
                $TSSEConfig->TSLoadConfig("SHOUTBOX");
                if ($tsshoutbot == "yes" && TS_Match($tsshoutboxoptions, "newpost")) {
                    require INC_PATH . "/functions_ajax_chatbot.php";
                    $shoutbOT = sprintf($lang->tsf_forums["x_replied_thread"], "[URL=" . ts_seo($CURUSER["id"], $CURUSER["username"]) . "]" . get_user_color($CURUSER["username"], $usergroups["namestyle"]) . "[/URL]", "[URL=" . tsf_seo_clean_text($subject, "t", $tid) . "]" . cutename($threadsubject, 50, false) . "[/URL]");
                    TSAjaxShoutBOT($shoutbOT);
                }
                send_sub_mails();
            }
            show_msg($str2, false, "", false);
        } else {
            if (isset($_POST["ajax_quick_comment"]) && isset($_POST["id"]) && isset($_POST["text"]) && isset($CURUSER)) {
                if ($usergroups["cancomment"] == "no") {
                    show_msg($lang->global["nopermission"]);
                }
                ($query = sql_query("SELECT cancomment FROM ts_u_perm WHERE userid = " . sqlesc($CURUSER["id"]))) || sqlerr(__FILE__, 616);
                if (0 < mysqli_num_rows($query)) {
                    $commentperm = mysqli_fetch_assoc($query);
                    if ($commentperm["cancomment"] == "0") {
                        show_msg($lang->global["nopermission"]);
                    }
                }
                $torrentid = intval($_POST["id"]);
                $lang->load("comment");
                if (!allowcomments($torrentid)) {
                    show_msg($lang->comment["closed"]);
                }
                $text = fixAjaxText($_POST["text"]);
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
                ($query = sql_query("SELECT added FROM comments WHERE user = " . sqlesc($CURUSER["id"]) . " ORDER by added DESC LIMIT 1")) || sqlerr(__FILE__, 652);
                if (0 < mysqli_num_rows($query)) {
                    $Result = mysqli_fetch_assoc($query);
                    $last_comment = $Result["added"];
                } else {
                    $last_comment = "";
                }
                $floodmsg = flood_check($lang->comment["floodcomment"], $last_comment, true);
                $res = sql_query("SELECT name, owner FROM torrents WHERE id = " . sqlesc($torrentid));
                $arr = mysqli_fetch_assoc($res);
                if (!empty($floodmsg)) {
                    show_msg(str_replace(["<font color=\"#9f040b\" size=\"2\">", "</font>", "<b>", "</b>"], "", $floodmsg));
                } else {
                    if (!$arr) {
                        show_msg($lang->global["notorrentid"]);
                    } else {
                        if (empty($text) || empty($torrentid) || !is_valid_id($torrentid)) {
                            show_msg($lang->global["dontleavefieldsblank"]);
                        }
                    }
                }
                $commentposted = false;
                if (!$is_mod && 0 < $CURUSER["id"]) {
                    $query = sql_query("SELECT id, user, text FROM comments WHERE torrent = " . sqlesc($torrentid) . " ORDER by added DESC LIMIT 1");
                    if (0 < mysqli_num_rows($query)) {
                        $Result = mysqli_fetch_assoc($query);
                        $lastcommentuserid = $Result["user"];
                        if ($lastcommentuserid == $CURUSER["id"]) {
                            $oldtext = $Result["text"];
                            $newid = $cid = $Result["id"];
                            if (strtoupper(substr(PHP_OS, 0, 3) == "WIN")) {
                                $eol = "\r\n";
                            } else {
                                if (strtoupper(substr(PHP_OS, 0, 3) == "MAC")) {
                                    $eol = "\r";
                                } else {
                                    $eol = "\n";
                                }
                            }
                            $newtext = $text = $oldtext . $eol . $eol . $text;
                            if ($usergroups["cancomment"] == "moderate") {
                                $message = sprintf($lang->comment["modmsg"], $CURUSER["username"], "[URL]" . $BASEURL . "/details.php?id=" . $torrentid . "&tab=comments&showlast=true&viewcomm=" . $newid . "#cid" . $newid . "[/URL]");
                                sql_query("INSERT INTO staffmessages (sender, added, msg, subject) VALUES(0, NOW(), " . sqlesc($message) . ", " . sqlesc($lang->comment["modmsgsubject"]) . ")");
                                sql_query("UPDATE comments SET text = " . $newtext . ", visible = 0 WHERE id = '" . $newid . "'");
                            } else {
                                sql_query("UPDATE comments SET text = " . sqlesc($newtext) . " WHERE id = '" . $newid . "'");
                            }
                            if (mysqli_affected_rows($GLOBALS["DatabaseConnect"])) {
                                $commentposted = true;
                            }
                        }
                    }
                }
                if (!$commentposted) {
                    sql_query("INSERT INTO comments (user, torrent, added, text, visible) VALUES (" . sqlesc($CURUSER["id"]) . ", " . sqlesc($torrentid) . ", " . sqlesc(get_date_time()) . ", " . sqlesc($text) . ", " . ($usergroups["cancomment"] == "moderate" ? 0 : 1) . ")");
                    $cid = mysqli_insert_id($GLOBALS["DatabaseConnect"]);
                    sql_query("UPDATE torrents SET comments = comments + 1 WHERE id = " . sqlesc($torrentid));
                    $ras = sql_query("SELECT options FROM users WHERE id = " . sqlesc($arr["owner"]));
                    $arg = mysqli_fetch_assoc($ras);
                    if (TS_Match($arg["options"], "C1") && $CURUSER["id"] != $arr["owner"]) {
                        require_once INC_PATH . "/functions_pm.php";
                        send_pm($arr["owner"], sprintf($lang->comment["newcommenttxt"], "[url=" . $BASEURL . "/details.php?id=" . $torrentid . "#startcomments]" . $arr["name"] . "[/url]"), $lang->comment["newcommentsub"]);
                    }
                    if ($usergroups["cancomment"] == "moderate") {
                        $message = sprintf($lang->comment["modmsg"], $CURUSER["username"], "[URL]" . $BASEURL . "/details.php?id=" . $torrentid . "&tab=comments&showlast=true&viewcomm=" . $cid . "#cid" . $cid . "[/URL]");
                        sql_query("INSERT INTO staffmessages (sender, added, msg, subject) VALUES(0, NOW(), " . sqlesc($message) . ", " . sqlesc($lang->comment["modmsgsubject"]) . ")");
                    } else {
                        KPS("+", isset($kpscomment) && $kpscomment ? $kpscomment : "", $CURUSER["id"]);
                    }
                }
                require_once INC_PATH . "/commenttable.php";
                require_once INC_PATH . "/functions_quick_editor.php";
                ($subres = sql_query("SELECT c.id, c.torrent as torrentid, c.text, c.user, c.added, c.editedby, c.editedat, c.modnotice, c.modeditid, c.modeditusername, c.modedittime, c.totalvotes, c.visible, uu.username as editedbyuname, gg.namestyle as editbynamestyle, u.added as registered, u.enabled, u.warned, u.leechwarn, u.username, u.title, u.usergroup, u.last_access, u.options, u.donor, u.uploaded, u.downloaded, u.avatar as useravatar, u.signature, g.title as grouptitle, g.namestyle FROM comments c LEFT JOIN users uu ON (c.editedby=uu.id) LEFT JOIN usergroups gg ON (uu.usergroup=gg.gid) LEFT JOIN users u ON (c.user=u.id) LEFT JOIN usergroups g ON (u.usergroup=g.gid) WHERE c.id = " . sqlesc($cid) . " ORDER BY c.id")) || sqlerr(__FILE__, 753);
                $allrows = [];
                while ($subrow = mysqli_fetch_assoc($subres)) {
                    $allrows[] = $subrow;
                }
                $lcid = 0;
                if (isset($_POST["lcid"])) {
                    $lcid = intval($_POST["lcid"]);
                }
                define("LCID", $lcid);
                $showcommenttable = commenttable($allrows, "", "", false, true, true);
                show_msg($showcommenttable, false, "", false);
            } else {
                if (!empty($_POST["username"])) {
                    $lang->load("signup");
                    $username = @trim($_POST["username"]);
                    if (empty($username) || !isvalidusername($username)) {
                        show_msg($lang->signup["une3"], false);
                    }
                    if (strlen($username) < 3) {
                        show_msg($lang->signup["une1"], false);
                    }
                    if (12 < strlen($username)) {
                        show_msg($lang->signup["une2"], false);
                    }
                    $query = sql_query("SELECT username FROM users WHERE username = " . sqlesc($username));
                    if (0 < mysqli_num_rows($query)) {
                        show_msg($lang->signup["une4"], false);
                    } else {
                        $TSSEConfig->TSLoadConfig("SIGNUP");
                        $usernames = preg_split("/\\s+/", $illegalusernames, -1, PREG_SPLIT_NO_EMPTY);
                        foreach ($usernames as $val) {
                            if (strpos(strtolower($username), strtolower($val)) !== false) {
                                show_msg($lang->signup["une4"], false);
                            }
                        }
                        show_msg($lang->signup["uavailable"], false, "green");
                    }
                } else {
                    if (!empty($_POST["email"])) {
                        $lang->load("signup");
                        $email = @trim($_POST["email"]);
                        require_once INC_PATH . "/functions_EmailBanned.php";
                        if (empty($email) || !check_email($email)) {
                            show_msg($lang->signup["invalidemail"], false);
                        } else {
                            if (EmailBanned($email)) {
                                show_msg($lang->signup["invalidemail2"], false);
                            }
                        }
                        $query = sql_query("SELECT email FROM users WHERE email = " . sqlesc($email));
                        if (mysqli_num_rows($query) == 0) {
                            show_msg($lang->signup["eavailable"], false, "green");
                        } else {
                            show_msg($lang->signup["invalidemail3"], false);
                        }
                    } else {
                        if (isset($_POST["vid"]) && !empty($_POST["cid"])) {
                            $Cid = intval($_POST["cid"]);
                            $Uid = intval($CURUSER["id"]);
                            $Vid = $_POST["vid"] == "1" ? "1" : "-1";
                            if (is_valid_id($Cid) && is_valid_id($Uid)) {
                                sql_query("REPLACE INTO comments_votes VALUES ('" . $Cid . "', '" . $Uid . "', '" . $Vid . "')") || sqlerr(__FILE__, 838);
                                ($Query = sql_query("SELECT vid FROM comments_votes WHERE cid = '" . $Cid . "'")) || sqlerr(__FILE__, 839);
                                $Negative = 0;
                                $Positive = 0;
                                if (0 < mysqli_num_rows($Query)) {
                                    while ($Votes = mysqli_fetch_assoc($Query)) {
                                        if ($Votes["vid"] == "-1") {
                                            $Negative += 1;
                                        } else {
                                            $Positive += 1;
                                        }
                                    }
                                } else {
                                    if ($Vid == "-1") {
                                        $Negative += 1;
                                    } else {
                                        $Positive += 1;
                                    }
                                }
                                sql_query("UPDATE comments SET totalvotes = '" . $Positive . "|" . $Negative . "' WHERE id = '" . $Cid . "'") || sqlerr(__FILE__, 869);
                                echo $Positive - $Negative;
                                exit;
                            }
                        }
                    }
                }
            }
        }
    }
}
function isvalidusername($username)
{
    if (!preg_match("|[^a-z\\|A-Z\\|0-9]|", $username)) {
        return true;
    }
    return false;
}
function show_response($message)
{
    global $shoutboxcharset;
    header("Expires: Sat, 1 Jan 2000 01:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Content-type: text/plain; charset=" . $shoutboxcharset);
    exit($message);
}
function show_msg($message = "", $error = true, $color = "red", $strong = true, $extra = "", $extra2 = "")
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
    exit($extra . (!empty($color) ? "<font color=\"" . $color . "\">" : "") . ($strong ? "<strong>" : "") . $message . ($strong ? "</strong>" : "") . (!empty($color) ? "</font>" : "") . $extra2);
}
function is_forum_mod($forumid = 0, $userid = 0)
{
    if (!$forumid || !$userid) {
        return false;
    }
    ($query = sql_query("SELECT userid FROM " . TSF_PREFIX . "moderators WHERE forumid=" . $forumid . " AND userid=" . $userid)) || sqlerr(__FILE__, 63);
    return 0 < mysqli_num_rows($query) ? true : false;
}
function allowcomments($torrentid = 0)
{
    global $is_mod;
    $query = sql_query("SELECT allowcomments FROM torrents WHERE id = " . sqlesc($torrentid));
    if (!mysqli_num_rows($query)) {
        return false;
    }
    $Result = mysqli_fetch_assoc($query);
    $allowcomments = $Result["allowcomments"];
    if ($allowcomments != "yes" && !$is_mod) {
        return false;
    }
    return true;
}

?>