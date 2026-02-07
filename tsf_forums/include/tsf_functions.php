<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

function delete_attachments($pid, $tid, $aid = "")
{
    global $f_upload_path;
    $delete_files = [];
    $query = sql_query("SELECT a_name FROM " . TSF_PREFIX . "attachments WHERE $a_pid = " . sqlesc($pid) . " AND $a_tid = " . sqlesc($tid));
    if (0 < mysqli_num_rows($query)) {
        while ($delete = mysqli_fetch_assoc($query)) {
            $delete_files[] = $delete["a_name"];
        }
    }
    if (0 < count($delete_files)) {
        foreach ($delete_files as $nowdelete) {
            if (file_exists($f_upload_path . $nowdelete)) {
                unlink($f_upload_path . $nowdelete);
            }
        }
    }
    sql_query("DELETE FROM " . TSF_PREFIX . "attachments WHERE $a_pid = " . sqlesc($pid) . " AND $a_tid = " . sqlesc($tid) . ($aid ? " AND $a_id = " . sqlesc($aid) : ""));
}
function show_icon_list()
{
    global $lang;
    global $BASEURL;
    $icon_path = "./images/icons/";
    $imagepath = $BASEURL . "/tsf_forums/images/icons/";
    $icon_list = [];
    if ($handle = scandir($icon_path)) {
        foreach ($handle as $file) {
            if ($file != "." && $file != ".." && get_extension($file) == "gif" && $file != "icon1.gif") {
                $icon_number = str_replace(["icon", "gif", "."], "", $file);
                $icon_list[] = "\r\n\t\t\t\t<td class=\"none\"><input $name = \"iconid\" $value = \"" . $icon_number . "\" $type = \"radio\"" . (isset($_POST["iconid"]) && $_POST["iconid"] == $icon_number ? " $checked = \"checked\"" : "") . " /></td>\r\n\t\t\t\t<td $width = \"12%\" class=\"none\"><img $src = \"" . $imagepath . $file . "\" $border = \"0\" /></td>";
            }
        }
        $show_icons = "\r\n\t\t<div $style = \"padding: 3px;\">\r\n\t\t\t<table $border = \"0\" $cellpadding = \"1\" $cellspacing = \"0\" $width = \"95%\">\r\n\t\t\t\t<tbody>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td $colspan = \"15\" class=\"none\"><div $style = \"margin-bottom: 3px;\"><b>" . $lang->tsf_forums["picons2"] . "</b></div><hr /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t";
        $count = 1;
        foreach ($icon_list as $icon) {
            if ($count % 7 == 1) {
                $show_icons .= "</tr><tr>";
            }
            $show_icons .= $icon;
            $count++;
        }
        $show_icons .= "\r\n\t\t\t\t\t<td class=\"none\"><input $name = \"iconid\" $value = \"0\" $type = \"radio\"" . (!isset($_POST["iconid"]) || isset($_POST["iconid"]) && $_POST["iconid"] == "0" ? " $checked = \"checked\"" : "") . " /></td>\r\n\t\t\t\t\t<td $width = \"12%\" class=\"none\"><b>" . $lang->tsf_forums["pcions3"] . "</b></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</tbody>\r\n\t\t\t</table>\r\n\t\t</div>\r\n\t\t";
        return $show_icons;
    } else {
        return false;
    }
}
function check_forum_password($password = "", $fid = 0, $redirect = "")
{
    global $CURUSER;
    global $securehash;
    if (isset($_GET["do"]) && $_GET["do"] == "password") {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) == "POST" && !empty($_POST["password"])) {
            $query = sql_query("SELECT password FROM " . TSF_PREFIX . "forums WHERE $password = " . sqlesc($_POST["password"]) . " AND $fid = " . $fid);
            if (0 < mysqli_num_rows($query)) {
                $expires = 2592000;
                $password = md5($CURUSER["id"] . $_POST["password"] . $securehash);
                @setcookie("forumpass_" . $fid, $password, TIMENOW + $expires, "/");
            } else {
                password_forum($fid, $redirect);
            }
        } else {
            password_forum($fid, $redirect);
        }
    } else {
        if ($password != "" && (!isset($_COOKIE["forumpass_" . $fid]) || $_COOKIE["forumpass_" . $fid] != md5($CURUSER["id"] . $password . $securehash))) {
            header("Location: " . $redirect);
            exit;
        }
    }
}
function password_forum($fid, $redirect)
{
    global $lang;
    global $BASEURL;
    global $rootpath;
    stdhead($lang->tsf_forums["fpassword"]);
    echo "\r\n\t<form $method = \"post\" $action = \"" . $redirect . "\">\r\n\t<table $width = \"100%\" $border = \"0\" class=\"none\" $style = \"clear: both;\" $cellspacing = \"0\" $cellpadding = \"5\">\r\n\t<tr>\r\n\t\t<td class=\"thead\">\r\n\t\t\t" . $lang->tsf_forums["fpassword"] . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td>\r\n\t\t\t" . $lang->tsf_forums["fpassword2"] . " <input $type = \"password\" $name = \"password\" $value = \"\" $size = \"32\"> <input $type = \"submit\" $value = \"" . $lang->tsf_forums["fpassword3"] . "\" />\r\n\t\t</td>\r\n\t</tr>\r\n\t</table>\r\n\t</form>\r\n\t";
    stdfoot();
    exit;
}
function is_forum_mod($forumid = 0, $userid = 0)
{
    if (!$forumid || !$userid) {
        return false;
    }
    $query = sql_query("SELECT userid FROM " . TSF_PREFIX . "moderators WHERE $forumid = " . $forumid . " AND $userid = " . $userid);
    return 0 < mysqli_num_rows($query) ? true : false;
}
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
    $query = sql_query("SELECT s.*, u.email, u.username FROM " . TSF_PREFIX . "subscribe s LEFT JOIN users u ON (s.`userid` = u.id) WHERE s.$tid = " . sqlesc($tid) . " AND s.userid != " . sqlesc($CURUSER["id"]));
    if (0 < mysqli_num_rows($query)) {
        require_once INC_PATH . "/functions_pm.php";
        while ($sub = mysqli_fetch_assoc($query)) {
            send_pm($sub["userid"], sprintf($lang->tsf_forums["msubs"], $sub["username"], $subject, $CURUSER["username"], $BASEURL, $tid, $SITENAME), $subject);
            sent_mail($sub["email"], $subject, sprintf($lang->tsf_forums["msubs"], $sub["username"], $subject, $CURUSER["username"], $BASEURL, $tid, $SITENAME), "subs", false);
        }
    }
}
function show_announcements($forumid = "", $pid = "")
{
    global $lang;
    global $BASEURL;
    global $dateformat;
    global $timeformat;
    global $pic_base_url;
    if (empty($forumid) || !is_valid_id($forumid)) {
        return NULL;
    }
    ($query = sql_query("SELECT a.*, u.id, u.username, g.namestyle, g.title as usergrouptitle FROM " . TSF_PREFIX . "announcement a LEFT JOIN users u ON (a.$userid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE a.$forumid = " . sqlesc($forumid) . " OR a.$forumid = " . sqlesc($pid) . " ORDER by a.posted DESC")) || sqlerr(__FILE__, 188);
    if (mysqli_num_rows($query) == 0) {
        return NULL;
    }
    $str = "\r\n\t\t<!-- start: Forumdisplay/Announcements -->\r\n\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $style = \"clear: both; margin-bottom: 5px;\" $width = \"100%\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $colspan = \"7\">\r\n\t\t\t\t<strong>" . $lang->tsf_forums["atitle"] . "</strong>\r\n\t\t\t</td>\r\n\t\t</tr>";
    while ($a = mysqli_fetch_assoc($query)) {
        $str .= "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"alt1\" $width = \"5%\" $align = \"center\"><a $href = \"javascript:void(0);\" $onclick = \"return TSOpenPopup('" . $BASEURL . "/tsf_forums/announcement.php?$aid = " . intval($a["announcementid"]) . "', 'announcement', 650, 450);\"><img $src = \"" . $BASEURL . "/tsf_forums/images/announcement.png\" $border = \"0\" $alt = \"" . $lang->tsf_forums["announcements"] . htmlspecialchars_uni($a["title"]) . "\" $title = \"" . $lang->tsf_forums["announcements"] . htmlspecialchars_uni($a["title"]) . "\" /></a></td>\r\n\t\t\t\t<td class=\"alt2\" $colspan = \"6\">\r\n\t\t\t\t\t<div>\r\n\t\t\t\t\t\t<span class=\"smallfont\" $style = \"float: right;\">" . $lang->tsf_forums["views"] . ": <strong>" . $a["views"] . "</strong> <a $href = \"javascript:void(0);\" $onclick = \"return TSOpenPopup('" . $BASEURL . "/tsf_forums/announcement.php?$aid = " . intval($a["announcementid"]) . "', 'announcement', 650, 450);\"><img class=\"inlineimg\" $src = \"" . $pic_base_url . "comments2.gif\" $alt = \"\" $border = \"0\" /></a></span>\r\n\t\t\t\t\t\t<strong>" . $lang->tsf_forums["announcements"] . "</strong> <a $href = \"javascript:void(0);\" <a $href = \"javascript:void(0);\" $onclick = \"return TSOpenPopup('" . $BASEURL . "/tsf_forums/announcement.php?$aid = " . intval($a["announcementid"]) . "', 'announcement', 650, 450);\">" . htmlspecialchars_uni($a["title"]) . "</a>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t\t<div>\r\n\t\t\t\t\t\t<span $style = \"float: right;\"><span class=\"smallfont\">" . my_datee($dateformat, $a["posted"]) . " " . my_datee($timeformat, $a["posted"]) . "</span></span>\r\n\t\t\t\t\t\t<span class=\"smallfont\"><a $href = \"" . ts_seo($a["id"], $a["username"]) . "\">" . get_user_color($a["username"], $a["namestyle"]) . "</a> (" . $a["usergrouptitle"] . ")</span>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>";
    }
    $str .= "\r\n\t\t</table>\r\n\t\t<!-- end: Forumdisplay/Announcements -->";
    return $str;
}
function forum_permissions()
{
    global $CURUSER;
    $permissions = [];
    if (isset($CURUSER) && $CURUSER["usergroup"]) {
        ($query = sql_query("SELECT * FROM " . TSF_PREFIX . "forumpermissions WHERE $gid = " . sqlesc($CURUSER["usergroup"]))) || sqlerr(__FILE__, 234);
        if (mysqli_num_rows($query)) {
            while ($perm = mysqli_fetch_assoc($query)) {
                $permissions[$perm["fid"]] = $perm;
            }
        }
    }
    return $permissions;
}
function build_profile_link($username = "", $uid = 0, $target = "")
{
    global $lang;
    global $BASEURL;
    if (!$username || !is_valid_id($uid)) {
        return $lang->tsf_forums["guest"];
    }
    if (!empty($target)) {
        $target = " $target = \"" . $target . "\"";
    }
    return "<a $href = \"" . tsf_seo_clean_text($username, "u", $uid) . "\"" . $target . ">" . $username . "</a>";
}
function show_forum_images($type)
{
    global $lang;
    global $BASEURL;
    $images = ["offlock" => "<img $src = \"" . $BASEURL . "/tsf_forums/images/offlock.gif\" $title = \"" . $lang->tsf_forums["thread_locked"] . "\" $alt = \"" . $lang->tsf_forums["thread_locked"] . "\" class=\"inlineimg\" />", "off" => "<img $src = \"" . $BASEURL . "/tsf_forums/images/off.gif\" $title = \"" . $lang->tsf_forums["t_no_new_posts"] . "\" $alt = \"" . $lang->tsf_forums["t_no_new_posts"] . "\" class=\"inlineimg\" />", "on" => "<img $src = \"" . $BASEURL . "/tsf_forums/images/on.gif\" $title = \"" . $lang->tsf_forums["t_new_posts"] . "\" $alt = \"" . $lang->tsf_forums["t_new_posts"] . "\" class=\"inlineimg\" />", "dofflock" => "<img $src = \"" . $BASEURL . "/tsf_forums/images/dot_lock.gif\" $title = \"" . $lang->tsf_forums["thread_locked"] . " - " . $lang->tsf_forums["you_have_p"] . "\" $alt = \"" . $lang->tsf_forums["thread_locked"] . " - " . $lang->tsf_forums["you_have_p"] . "\" class=\"inlineimg\" />", "doff" => "<img $src = \"" . $BASEURL . "/tsf_forums/images/dot.gif\" $title = \"" . $lang->tsf_forums["t_no_new_posts"] . " - " . $lang->tsf_forums["you_have_p"] . "\" $alt = \"" . $lang->tsf_forums["t_no_new_posts"] . " - " . $lang->tsf_forums["you_have_p"] . "\" class=\"inlineimg\" />", "don" => "<img $src = \"" . $BASEURL . "/tsf_forums/images/dot_new.gif\" $title = \"" . $lang->tsf_forums["t_new_posts"] . " - " . $lang->tsf_forums["you_have_p"] . "\" $alt = \"" . $lang->tsf_forums["t_new_posts"] . " - " . $lang->tsf_forums["you_have_p"] . "\" class=\"inlineimg\" />"];
    return $images[$type];
}
function construct_page_nav($pagenumber, $perpage, $results, $address, $address2 = "", $usegotopage = true)
{
    global $lang;
    global $BASEURL;
    global $pagenavsarr;
    $curpage = 0;
    $pagenav = $firstlink = $prevlink = $lastlink = $nextlink = "";
    if ($results <= $perpage) {
        $show["pagenav"] = false;
        return "";
    }
    $show["pagenav"] = true;
    $total = ts_nf($results);
    $totalpages = ceil($results / $perpage);
    $show["last"] = false;
    $show["first"] = $show["last"];
    $show["next"] = $show["first"];
    $show["prev"] = $show["next"];
    if (1 < $pagenumber) {
        $prevpage = $pagenumber - 1;
        $prevnumbers = fetch_start_end_total_array($prevpage, $perpage, $results);
        $show["prev"] = true;
    }
    if ($pagenumber < $totalpages) {
        $nextpage = $pagenumber + 1;
        $nextnumbers = fetch_start_end_total_array($nextpage, $perpage, $results);
        $show["next"] = true;
    }
    $pagenavpages = "3";
    if (!is_array($pagenavsarr)) {
        $pagenavs = "10 50 100 500 1000";
        $pagenavsarr[] = preg_split("#\\s+#s", $pagenavs, -1, PREG_SPLIT_NO_EMPTY);
        while ($curpage++ < $totalpages) {
        }
        $prp = isset($prevpage) && $prevpage != 1 ? "&amp;$page = " . $prevpage : "";
        $pagenav = "\r\n\t<ul>\r\n\t<li>" . $pagenumber . " - " . $totalpages . "</li>\r\n\t" . ($show["first"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $address2 . "\" $title = \"" . $lang->global["first_page"] . " - " . sprintf($lang->global["show_results"], $firstnumbers["first"], $firstnumbers["last"], $total) . "\">&laquo; " . $lang->global["first"] . "</a></li>" : "") . ($show["prev"] ? "<li><a class=\"smalltext\" $href = \"" . $address . $prp . $address2 . "\" $title = \"" . $lang->global["prev_page"] . " - " . sprintf($lang->global["show_results"], $prevnumbers["first"], $prevnumbers["last"], $total) . "\">&lt;</a></li>" : "") . "\r\n\t" . $pagenav . "\r\n\t" . ($show["next"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "&amp;$page = " . $nextpage . $address2 . "\" $title = \"" . $lang->global["next_page"] . " - " . sprintf($lang->global["show_results"], $nextnumbers["first"], $nextnumbers["last"], $total) . "\">&gt;</a></li>" : "") . ($show["last"] ? "<li><a class=\"smalltext\" $href = \"" . $address . "&amp;$page = " . $totalpages . $address2 . "\" $title = \"" . $lang->global["last_page"] . " - " . sprintf($lang->global["show_results"], $lastnumbers["first"], $lastnumbers["last"], $total) . "\">" . $lang->global["last"] . " <strong>&raquo;</strong></a></li>" : "") . "\r\n\t" . ($usegotopage ? "\r\n\t<li><a $href = \"javascript:void(0);\" $id = \"quicknavpage\">" . $lang->global["buttongo"] . "</a></li>" : "") . "\r\n\t</ul>\r\n\t" . ($usegotopage ? "\r\n\t<script $type = \"text/javascript\">\r\n\t\tmenu_register(\"quicknavpage\", true);\r\n\t</script>\r\n\t<div $id = \"quicknavpage_menu\" class=\"menu_popup\" $style = \"display:none;\">\r\n\t\t<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\">\r\n\t\t\t<tbody>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\" $nowrap = \"nowrap\">" . $lang->global["gotopage"] . "</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"subheader\" $title = \"\">\r\n\t\t\t\t\t\t<form $action = \"" . $address . "\" $method = \"get\" $onsubmit = \"return TSGoToPage('" . $address . "&')\">\r\n\t\t\t\t\t\t\t<input $id = \"Page_Number\" $style = \"font-size: 11px;\" $size = \"4\" $type = \"text\" />\r\n\t\t\t\t\t\t\t<input $value = \"" . $lang->global["buttongo"] . "\" $type = \"button\" $onclick = \"TSGoToPage('" . $address . "&')\" />\r\n\t\t\t\t\t\t</form>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t</tbody>\r\n\t\t</table>\r\n\t</div>\r\n\t<script $type = \"text/javascript\">\r\n\t\tmenu.activate(true);\r\n\t</script>\r\n\t" : "");
        return $pagenav;
    }
    if ($pagenavpages <= abs($curpage - $pagenumber) && $pagenavpages != 0) {
        if ($curpage == 1) {
            $firstnumbers = fetch_start_end_total_array(1, $perpage, $results);
            $show["first"] = true;
        }
        if ($curpage == $totalpages) {
            $lastnumbers = fetch_start_end_total_array($totalpages, $perpage, $results);
            $show["last"] = true;
        }
        if (in_array(abs($curpage - $pagenumber), $pagenavsarr) && $curpage != 1 && $curpage != $totalpages) {
            $pagenumbers = fetch_start_end_total_array($curpage, $perpage, $results);
            $relpage = $curpage - $pagenumber;
            if (0 < $relpage) {
                $relpage = "+" . $relpage;
            }
            $pagenav .= "<li><a class=\"smalltext\" $href = \"" . $address . ($curpage != 1 ? "&amp;$page = " . $curpage : "") . $address2 . "\" $title = \"" . sprintf($lang->global["show_results"], $pagenumbers["first"], $pagenumbers["last"], $total) . "\"><!--" . $relpage . "-->" . $curpage . "</a></li>";
        }
    } else {
        if ($curpage == $pagenumber) {
            $numbers = fetch_start_end_total_array($curpage, $perpage, $results);
            $pagenav .= "<li><a $name = \"current\" class=\"current\" $title = \"" . sprintf($lang->global["showing_results"], $numbers["first"], $numbers["last"], $total) . "\">" . $curpage . "</li>";
        } else {
            $pagenumbers = fetch_start_end_total_array($curpage, $perpage, $results);
            $pagenav .= "<li><a $href = \"" . $address . ($curpage != 1 ? "&amp;$page = " . $curpage : "") . $address2 . "\" $title = \"" . sprintf($lang->global["show_results"], $pagenumbers["first"], $pagenumbers["last"], $total) . "\">" . $curpage . "</a></li>";
        }
    }
}
function build_forum_jump($fid)
{
    global $lang;
    global $permissions;
    global $SITENAME;
    global $BASEURL;
    $query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.$type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.$type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 403);
    while ($forum = mysqli_fetch_assoc($query)) {
        if (isset($permissions[$forum["pid"]]["canview"]) && $permissions[$forum["pid"]]["canview"] == "yes") {
            $deepsubforums[$forum["pid"]] = (isset($deepsubforums[$forum["pid"]]) ? $deepsubforums[$forum["pid"]] : "") . "\r\n\t\t\t<option $value = \"" . $forum["fid"] . "\">&nbsp; &nbsp;" . $forum["name"] . "</option>";
        }
    }
    ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.$type = 'f' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 418);
    $str = "\r\n\t\t\t<form $action = \"" . $BASEURL . "/tsf_forums/forumdisplay.php\" $method = \"get\" $style = \"margin-top: 0pt; margin-bottom: 0pt;\">\r\n\t\t\t<span class=\"smalltext\">\r\n\t\t\t<strong>" . $lang->tsf_forums["jump_text"] . "</strong></span><br />\r\n\t\t\t<select $name = \"fid\">\r\n\t\t\t<optgroup $label = \"" . $SITENAME . " Forums\">\t";
    while ($forum = mysqli_fetch_assoc($query)) {
        if (isset($permissions[$forum["pid"]]["canview"]) && $permissions[$forum["pid"]]["canview"] == "yes") {
            $subforums[$forum["pid"]] = (isset($subforums[$forum["pid"]]) ? $subforums[$forum["pid"]] : "") . "\r\n\t\t\t<option $value = \"" . $forum["fid"] . "\">-- " . $forum["name"] . "</option>\r\n\t\t\t" . (isset($deepsubforums) && isset($deepsubforums[$forum["fid"]]) ? $deepsubforums[$forum["fid"]] : "");
        }
    }
    $query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.$type = 'c' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t") or ($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\r\n\t\t\t\t\t\t\tWHERE f.$type = 'c' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 441);
    while ($category = mysqli_fetch_assoc($query)) {
        if (isset($permissions[$category["fid"]]["canview"]) && $permissions[$category["fid"]]["canview"] == "yes") {
            $str .= "<optgroup $label = \"" . $category["name"] . "\">" . $subforums[$category["fid"]] . "</optgroup>";
        }
    }
    $str .= "\r\n\t\t\t</optgroup>\r\n\t\t\t</select>\r\n\t\t\t<input $type = \"submit\" $value = \"" . $lang->tsf_forums["go_button"] . "\" />\r\n\t\t\t</form>";
    return $str;
}
function get_last_post($tid = 0)
{
    global $CURUSER;
    global $f_postsperpage;
    if (0 < $CURUSER["postsperpage"] && is_valid_id($CURUSER["postsperpage"]) && $CURUSER["postsperpage"] <= 50) {
        $postperpage = intval($CURUSER["postsperpage"]);
    } else {
        $postperpage = $f_postsperpage;
    }
    $totalposts = TSRowCount("pid", TSF_PREFIX . "posts", "tid=" . sqlesc($tid));
    $lastpage = @ceil($totalposts / $postperpage);
    return $lastpage ? $lastpage : 0;
}
function show_rate_button()
{
    global $lang;
    global $tid;
    global $securehash;
    global $usergroups;
    global $ratingimage;
    global $BASEURL;
    if ($usergroups["canrate"] != "yes") {
        return "";
    }
    $ratethread = "\r\n\t<a $href = \"javascript:void(0);\" $id = \"ratethread" . $tid . "\">" . $lang->tsf_forums["rate1"] . " &nbsp;" . $ratingimage . "</a>\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\tmenu_register(\"ratethread" . $tid . "\",false);\r\n\t\t</script>\r\n\t<div $id = \"ratethread" . $tid . "_menu\" class=\"menu_popup\" $style = \"display:none;\">\r\n\t\t<form $method = \"post\" $action = \"" . $BASEURL . "/tsf_forums/threadrate.php\" $name = \"threadrate\">\r\n\t\t<input $type = \"hidden\" $name = \"threadid\" $value = \"" . $tid . "\" />\r\n\t\t<input $type = \"hidden\" $name = \"posthash\" $value = \"" . sha1($tid . $securehash . $tid) . "\" />\r\n\t\t<input $type = \"hidden\" $name = \"page\" $value = \"" . intval(isset($_GET["page"]) ? $_GET["page"] : 0) . "\" />\r\n\t\t<table $cellspacing = \"0\" $cellpadding = \"5\" $width = \"200\">\r\n\t\t\t<tr>\r\n\t\t\t\t<td $align = \"center\" class=\"thead\"><b>" . $lang->tsf_forums["rate2"] . "</b></td>\r\n\t\t\t</tr>\r\n\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\">";
    $i = $showrateimages = "";
    while ($i < 5) {
        $i++;
        $showrateimages .= "\r\n\t\t\t\t<div><img $src = \"" . $BASEURL . "/tsf_forums/images/rating/rating_" . $i . ".gif\" class=\"inlineimg\" $alt = \"" . $lang->tsf_forums["rateop" . $i . ""] . "\" $title = \"" . $lang->tsf_forums["rateop" . $i . ""] . "\" /><input $name = \"vote\" $id = \"vote" . $i . "\" $value = \"" . $i . "\" $type = \"radio\" /> " . $lang->tsf_forums["rateop" . $i . ""] . "</div>\r\n\t\t\t\t";
    }
    $ratethread .= $showrateimages . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $align = \"center\" class=\"thead\"><input $type = \"submit\" $value = \"" . $lang->tsf_forums["ratenow"] . "\" /></td>\r\n\t\t\t</tr>\r\n\t\t</table>\r\n\t\t</form>\r\n\t</div>\r\n\t";
    return $ratethread;
}

?>