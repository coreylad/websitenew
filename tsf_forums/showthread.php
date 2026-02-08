<?php
define("THIS_SCRIPT", "showthread.php");
require "./global.php";
$tid = intval(TS_Global("tid"));
if (!is_valid_id($tid)) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
if (!isset($_GET["nolastpage"]) && (!empty($action) && $action === "lastpost" || $pagenumber === "last")) {
    ($query = sql_query("SELECT subject, pid, fid\r\n\tFROM " . TSF_PREFIX . "posts\r\n\tWHERE $tid = " . sqlesc($tid) . " ORDER BY dateline DESC LIMIT 1")) || sqlerr(__FILE__, 40);
    if (mysqli_num_rows($query) == 0) {
        stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
        exit;
    }
    $Result = mysqli_fetch_assoc($query);
    $name = $Result["subject"];
    $pid = $Result["pid"];
    $fid = $Result["fid"];
    if (!isset($permissions[$fid]["canview"]) || $permissions[$fid]["canview"] != "yes") {
        print_no_permission();
        exit;
    }
    $lastpage = get_last_post($tid);
    redirect($BASEURL . "/tsf_forums/showthread.php?$tid = " . $tid . "&$page = " . $lastpage . "&$pid = " . $pid . "#pid" . $pid, $lang->tsf_forums["redirect_last_post"], "", true);
    exit;
}
$Query = sql_query("SELECT uid FROM " . TSF_PREFIX . "posts WHERE $tid = " . sqlesc($tid));
$totalposts = mysqli_num_rows($Query);
$UserHasPosted = false;
if (0 < $totalposts) {
    while ($ListUids = mysqli_fetch_assoc($Query)) {
        if ($ListUids["uid"] === $CURUSER["id"]) {
            $UserHasPosted = true;
        }
    }
}
if (0 < $CURUSER["postsperpage"] && is_valid_id($CURUSER["postsperpage"]) && $CURUSER["postsperpage"] <= 50) {
    $perpage = intval($CURUSER["postsperpage"]);
} else {
    $perpage = $f_postsperpage;
}
sanitize_pageresults($totalposts, $pagenumber, $perpage, 200);
if (isset($_GET["highlight"]) && !empty($_GET["highlight"])) {
    $h_link = "&amp;$highlight = " . htmlspecialchars_uni($_GET["highlight"]);
}
$limitlower = ($pagenumber - 1) * $perpage;
$limitupper = $pagenumber * $perpage;
if ($totalposts < $limitupper) {
    $limitupper = $totalposts;
    if ($totalposts < $limitlower) {
        $limitlower = $totalposts - $perpage - 1;
    }
}
if ($limitlower < 0) {
    $limitlower = 0;
}
($query = sql_query("\r\n\t\t\tSELECT p.*, p.subject as postsubject, f.password, f.name as currentforum, f.type, ff.name as realforum, ff.fid as realforumid, f.pid as parent, t.subject as threadsubject, t.closed, t.sticky, t.pollid, t.votenum, t.votetotal, t.firstpost, u.last_access, u.last_login, u.added, u.username AS userusername, u.totalposts, u.timeswarned, u.downloaded, u.uploaded, u.title as usertitle, u.country, u.avatar, u.options, u.donated, u.usergroup, u.signature, u.enabled, u.donor, u.leechwarn, u.warned, pp.canupload, pp.candownload, pp.cancomment, pp.canmessage, pp.canshout, eu.username AS editusername, gg.namestyle as editnamestyle, g.namestyle, g.title, c.name as countryname, c.flagpic\r\n\t\t\tFROM " . TSF_PREFIX . "posts p\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums f ON (p.$fid = f.fid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "forums ff ON (ff.$fid = f.pid)\r\n\t\t\tLEFT JOIN " . TSF_PREFIX . "threads t ON (p.$tid = t.tid)\r\n\t\t\tLEFT JOIN users u ON (u.`id` = p.uid)\r\n\t\t\tLEFT JOIN ts_u_perm pp ON (u.`id` = pp.userid)\r\n\t\t\tLEFT JOIN users eu ON (eu.$id = p.edituid)\r\n\t\t\tLEFT JOIN countries c ON (u.$country = c.id)\r\n\t\t\tLEFT JOIN usergroups g ON (u.`usergroup` = g.gid)\r\n\t\t\tLEFT JOIN usergroups gg ON (eu.$usergroup = gg.gid)\r\n\t\t\tWHERE p.$tid = " . sqlesc($tid) . "\r\n\t\t\tORDER BY p.dateline ASC\r\n\t\t\tLIMIT " . $limitlower . ", " . $perpage . "\r\n\t\t\t")) || sqlerr(__FILE__, 122);
if (mysqli_num_rows($query) == 0) {
    stderr($lang->global["error"], $lang->tsf_forums["invalid_tid"]);
    exit;
}
$a_query = sql_query("SELECT * FROM " . TSF_PREFIX . "attachments WHERE $a_tid = " . sqlesc($tid));
if (0 < mysqli_num_rows($a_query)) {
    while ($s_attachments = mysqli_fetch_assoc($a_query)) {
        $a_array[$s_attachments["a_pid"]][] = $s_attachments;
    }
}
($TQuery = sql_query("SELECT t.pid, t.uid, u.username, g.namestyle FROM " . TSF_PREFIX . "thanks t LEFT JOIN users u ON (t.$uid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE t.$tid = '" . $tid . "'")) || sqlerr(__FILE__, 145);
if (0 < mysqli_num_rows($TQuery)) {
    $TCache = [];
    while ($Thank = mysqli_fetch_assoc($TQuery)) {
        $TCache[$Thank["pid"]][$Thank["uid"]]["userid"] = $Thank["uid"];
        $TCache[$Thank["pid"]][$Thank["uid"]]["username"] = "<a $href = \"" . ts_seo($Thank["uid"], $Thank["username"]) . "\">" . get_user_color($Thank["username"], $Thank["namestyle"]) . "</a>";
        $TCache[$Thank["pid"]][$Thank["uid"]]["pid"] = $Thank["pid"];
    }
}
$lang->load("quick_editor");
require_once INC_PATH . "/class_tsquickbbcodeeditor.php";
$QuickEditor = new TSQuickBBCodeEditor();
$QuickEditor->ImagePath = $pic_base_url;
$QuickEditor->SmiliePath = $pic_base_url . "smilies/";
include_once INC_PATH . "/functions_ratio.php";
include_once INC_PATH . "/functions_icons.php";
include_once INC_PATH . "/function_warnlevel.php";
$subsquery = sql_query("SELECT userid FROM " . TSF_PREFIX . "subscribe WHERE $tid = " . sqlesc($tid) . " AND $userid = " . sqlesc($CURUSER["id"]));
$subslink = mysqli_num_rows($subsquery) == 0 ? "<a $href = \"" . $BASEURL . "/tsf_forums/subscription.php?do=addsubscription&amp;$tid = " . $tid . "\" $title = \"" . $lang->tsf_forums["isubs"] . "\"><b>" . $lang->tsf_forums["subs"] . "</b></a>" : "<a $href = \"" . $BASEURL . "/tsf_forums/subscription.php?do=removesubscription&amp;$tid = " . $tid . "\" $title = \"" . $lang->tsf_forums["delsubs"] . "\"><b>" . $lang->tsf_forums["delsubs"] . "</b></a>";
$count = 1;
$isthreadclosed = $isstickythread = $checkedpassword = $ajax_quick_edit_loaded = $isfirstpost = false;
$str2 = $quickmenu = "";
$showpagenumber = isset($_GET["page"]) && is_valid_id($_GET["page"]) ? "&amp;$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) : "";
require INC_PATH . "/function_user_rank.php";
$ABuffer = [];
($AwardQuery = sql_query("SELECT a.id, a.userid, a.reason, a.date, aw.award_name, aw.award_image FROM ts_awards_users a LEFT JOIN ts_awards aw ON (a.$award_id = aw.award_id)")) || sqlerr(__FILE__, 177);
if (mysqli_num_rows($AwardQuery)) {
    while ($Award = mysqli_fetch_assoc($AwardQuery)) {
        $ATooltip = "<strong>" . htmlspecialchars_uni($Award["award_name"]) . "</strong><br /><small>" . addslashes(htmlspecialchars_uni($Award["reason"])) . "</small>";
        $ABuffer[$Award["userid"]][$Award["id"]] = "\r\n\t\t<i $onmouseover = \"ddrivetip('" . $ATooltip . "', 200)\"; $onmouseout = \"hideddrivetip()\"><img $src = \"" . $pic_base_url . "awardmedals/" . htmlspecialchars_uni($Award["award_image"]) . "\" $border = \"0\" $alt = \"\" $title = \"\" class=\"inlineimg\" $width = \"10\" $height = \"19\" $style = \"padding-top: 3px; cursor: pointer;\" /></i>&nbsp;";
    }
}
for ($imagepath = $pic_base_url . "friends/"; $thread = mysqli_fetch_assoc($query); $count++) {
    if (1 < $count) {
        $isfirstpost = true;
    }
    $tid = 0 + $thread["tid"];
    $fid = 0 + $thread["fid"];
    $pid = 0 + $thread["pid"];
    $realforum = $thread["realforum"];
    $currentforum = $thread["currentforum"];
    $realforumid = 0 + $thread["realforumid"];
    $ftype = $thread["type"];
    $user_rank = "";
    if (!$checkedpassword) {
        check_forum_password($thread["password"], $fid, $BASEURL . "/tsf_forums/showthread.php?$tid = " . $tid . "&do=password");
        $checkedpassword = true;
    }
    if (!isset($forummoderator)) {
        $forummoderator = is_forum_mod($ftype == "s" ? $realforumid : $fid, $CURUSER["id"]);
    }
    if (!$moderator && !$forummoderator && (!isset($permissions[$fid]["canview"]) || $permissions[$fid]["canview"] != "yes" || !isset($permissions[$fid]["canviewthreads"]) || $permissions[$fid]["canviewthreads"] != "yes")) {
        print_no_permission();
        exit;
    }
    $editdate = $edittime = $editedby = $editbutton = $deletebutton = $quotebutton = $quickreplybutton = $signature = $avatar = $usertitle = $attachment = $display_attachment = $modnotice = $_warnlevel = $UserAwards = "";
    if (($moderator || $forummoderator || $thread["uid"] === $CURUSER["id"] || $UserHasPosted) && !defined("IS_THIS_USER_POSTED")) {
        define("IS_THIS_USER_POSTED", true);
    }
    if (preg_match("#\\[hide\\](.*?)\\[\\/hide\\](\r\n?|\n?)#si", $thread["message"])) {
        $Othread["message"] = $thread["message"];
        while (preg_match("#\\[hide\\](.*?)\\[\\/hide\\](\r\n?|\n?)#si", $thread["message"])) {
            $thread["message"] = preg_replace("#\\[hide\\](.*?)\\[\\/hide\\](\r\n?|\n?)#si", "", $thread["message"]);
        }
        $QuoteTag = htmlspecialchars(mysqli_real_escape_string($GLOBALS["DatabaseConnect"], "[$quote = " . $thread["userusername"] . "]" . $thread["message"] . "[/quote]"));
        $thread["message"] = $Othread["message"];
        unset($Othread["message"]);
    } else {
        $QuoteTag = htmlspecialchars(mysqli_real_escape_string($GLOBALS["DatabaseConnect"], "[$quote = " . $thread["userusername"] . "]" . $thread["message"] . "[/quote]"));
    }
    if ($thread["closed"] == "yes") {
        $isthreadclosed = true;
    }
    if ($thread["sticky"] == 1) {
        $isstickythread = true;
    }
    if (isset($a_array[$thread["pid"]])) {
        require_once INC_PATH . "/functions_get_file_icon.php";
        $display_attachment = "\r\n\t\t\t<!-- start: attachments -->\r\n\t\t\t<br />\r\n\t\t\t<br />\r\n\t\t\t<fieldset>\r\n\t\t\t\t<legend>\r\n\t\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/attachment.png\" $border = \"0\" class=\"inlineimg\" $alt = \"" . $lang->tsf_forums["a_info"] . "\" $title = " . $lang->tsf_forums["a_info"] . "\"\" /> <strong>" . $lang->tsf_forums["a_info"] . "</strong>\r\n\t\t\t\t\t</legend>";
        foreach ($a_array[$thread["pid"]] as $_a_left => $showperpost) {
            if ($showperpost["visible"] == 1 || $forummoderator || $moderator) {
                $ext = get_extension($showperpost["a_name"]);
                if (($showperpost["visible"] == 1 || $forummoderator || $moderator) && in_array($ext, ["png", "gif", "jpg", "jpeg"])) {
                    $link = "<a $href = \"" . $BASEURL . "/tsf_forums/attachment.php?$aid = " . $showperpost["a_id"] . "&amp;$tid = " . $showperpost["a_tid"] . "&amp;$pid = " . $thread["pid"] . "\" class=\"colorbox\" $rel = \"post_" . $thread["pid"] . "\">" . htmlspecialchars_uni($showperpost["a_name"]) . "</a>";
                } else {
                    $link = "<a $href = \"" . $BASEURL . "/tsf_forums/attachment.php?$aid = " . $showperpost["a_id"] . "&amp;$tid = " . $showperpost["a_tid"] . "&amp;$pid = " . $thread["pid"] . "\" $target = \"_blank\">" . htmlspecialchars_uni($showperpost["a_name"]) . "</a>";
                }
                $display_attachment .= ($showperpost["visible"] == 1 ? get_file_icon($showperpost["a_name"]) : "<img $src = \"" . $BASEURL . "/tsf_forums/images/moderation.png\" $alt = \"" . $lang->tsf_forums["moderatemsg9"] . "\" $title = \"" . $lang->tsf_forums["moderatemsg9"] . "\" $border = \"0\" class=\"inlineimg\" />") . "\r\n\t\t\t\t" . $link . " (<b>" . $lang->tsf_forums["a_size"] . "</b>" . mksize($showperpost["a_size"]) . " / <b>" . $lang->tsf_forums["a_count"] . "</b>" . ts_nf($showperpost["a_count"]) . ")<br />";
            } else {
                $display_attachment .= $lang->tsf_forums["moderatemsg9"];
            }
        }
        $display_attachment .= "\r\n\t\t\t</fieldset>\r\n\t\t\t<!-- end: attachments -->\r\n\t\t";
    }
    if (!isset($realsubject) || !isset($threadsubject)) {
        $realsubject = htmlspecialchars_uni(ts_remove_badwords($thread["threadsubject"]));
        $threadsubject = htmlspecialchars_uni(ts_remove_badwords($thread["postsubject"]));
    }
    if (!isset($multipage)) {
        $multipage = construct_page_nav($pagenumber, $perpage, $totalposts, tsf_seo_clean_text($realsubject, "t", $tid, isset($h_link) ? $h_link : ""));
    }
    if (!isset($orjsubject)) {
        $orjsubject = trim($thread["threadsubject"]);
    }
    if (!empty($thread["signature"]) && TS_Match($CURUSER["options"], "H1") && 0 < $CURUSER["id"]) {
        $signature = "<hr $align = \"left\" $size = \"1\" $width = \"65%\">" . format_comment($thread["signature"], true, true, true, true, "signatures");
    }
    if (TS_Match($CURUSER["options"], "D1")) {
        $avatar = get_user_avatar($thread["avatar"]);
    }
    $lastseen = my_datee($dateformat, $thread["last_access"]) . " " . my_datee($timeformat, $thread["last_access"]);
    $downloaded = mksize($thread["downloaded"]);
    $uploaded = mksize($thread["uploaded"]);
    $ratio = get_user_ratio($thread["uploaded"], $thread["downloaded"]);
    $ratio = str_replace("'", "\\'", $ratio);
    $join_date = $lang->tsf_forums["jdate"] . my_datee($regdateformat, $thread["added"]);
    $totalposts = $lang->tsf_forums["totalposts"] . ts_nf($thread["totalposts"]);
    $dt = TIMENOW - TS_TIMEOUT;
    $UserOff = sprintf($lang->tsf_forums["user_offline"], $thread["username"]);
    $UserOn = sprintf($lang->tsf_forums["user_online"], $thread["username"]);
    if (TS_Match($thread["options"], "B1") && !$moderator && !$forummoderator && $thread["uid"] != $CURUSER["id"]) {
        $lastseen = my_datee($dateformat, $thread["last_login"]) . " " . my_datee($timeformat, $thread["last_login"]);
        $status = "<img $src = \"" . $imagepath . "offline.png\" $border = \"0\" $alt = \"" . $UserOff . "\" $title = \"" . $UserOff . "\" class=\"inlineimg\" />";
    } else {
        if ($dt < TS_MTStoUTS($thread["last_access"]) || $thread["uid"] == $CURUSER["id"]) {
            $status = "<img $src = \"" . $imagepath . "online.png\" $border = \"0\" $alt = \"" . $UserOn . "\" $title = \"" . $UserOn . "\" class=\"inlineimg\" />";
        } else {
            $status = "<img $src = \"" . $imagepath . "offline.png\" $border = \"0\" $alt = \"" . $UserOff . "\" $title = \"" . $UserOff . "\" class=\"inlineimg\" />";
        }
    }
    if (TS_Match($thread["options"], "L1")) {
        $UserGender = "<img $src = \"" . $imagepath . "Male.png\" $alt = \"" . $lang->global["male"] . "\" $title = \"" . $lang->global["male"] . "\" $border = \"0\" class=\"inlineimg\" />";
    } else {
        if (TS_Match($thread["options"], "L2")) {
            $UserGender = "<img $src = \"" . $imagepath . "Female.png\" $alt = \"" . $lang->global["female"] . "\" $title = \"" . $lang->global["female"] . "\" $border = \"0\" class=\"inlineimg\" />";
        } else {
            $UserGender = "<img $src = \"" . $imagepath . "NA.png\" $alt = \"--\" $title = \"--\" $border = \"0\" class=\"inlineimg\" />";
        }
    }
    if ((TS_Match($thread["options"], "I3") || TS_Match($thread["options"], "I4")) && !$moderator && !$forummoderator || !isset($CURUSER) || $CURUSER["id"] == 0) {
        $tooltip = $lang->tsf_forums["deny"];
    } else {
        $tooltip = sprintf($lang->tsf_forums["tooltip"], $lastseen, $downloaded, $uploaded, $ratio);
    }
    if ($thread["userusername"] && $thread["uid"]) {
        $posterforthanks = get_user_color(htmlspecialchars_uni($thread["userusername"]), $thread["namestyle"]);
        $isuser = true;
        $poster = "<a $href = \"javascript:void(0);\" $id = \"quickmenu" . $pid . "\"><i $onmouseover = \"ddrivetip('" . $tooltip . "', 200)\"; $onmouseout = \"hideddrivetip()\">" . get_user_color(htmlspecialchars_uni($thread["userusername"]), $thread["namestyle"]) . "</i></a>";
    } else {
        $isuser = false;
        $poster = $posterforthanks = $lang->tsf_forums["guest"];
    }
    if ($thread["usertitle"]) {
        $usertitle = "<font class=\"smalltext\"><strong>" . htmlspecialchars_uni($thread["usertitle"]) . "</strong></font><br />";
    }
    if ($moderator || $forummoderator || isset($permissions[$fid]["caneditposts"]) && $permissions[$fid]["caneditposts"] == "yes" && $thread["closed"] != "yes" && $thread["uid"] == $CURUSER["id"]) {
        $onclick = "onclick=\"jumpto('" . $BASEURL . "/tsf_forums/editpost.php?$tid = " . $tid . "&amp;$pid = " . $pid . $showpagenumber . "');\"";
        if ($useajax == "yes") {
            if (!$ajax_quick_edit_loaded) {
                require_once INC_PATH . "/functions_quick_editor.php";
                $str2 .= "\r\n\t\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\t\tvar $l_quick_save_button = \"" . $lang->global["buttonsave"] . "\";\r\n\t\t\t\t\tvar $l_quick_cancel_button = \"" . $lang->tsf_forums["cancel"] . "\";\r\n\t\t\t\t\tvar $l_quick_adv_button = \"" . $lang->tsf_forums["goadvanced"] . "\";\r\n\t\t\t\t\tvar $bbcodes = '" . trim(str_replace(["'", "\n", "\r"], ["\\'", "", ""], ts_show_bbcode_links("quick_edit_form", "newContent"))) . "';\r\n\t\t\t\t</script>\r\n\t\t\t\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/tsf_forums/scripts/inline_quick_edit.js?$v = " . O_SCRIPT_VERSION . "\"></script>\r\n\t\t\t\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/quick_editor.js?$v = " . O_SCRIPT_VERSION . "\"></script>";
                $ajax_quick_edit_loaded = 1;
            }
            $onclick = "onclick=\"TSQuickEditPost('post_message_" . $pid . "','" . $tid . "','" . $BASEURL . "/tsf_forums/editpost.php?$tid = " . $tid . "&amp;$pid = " . $pid . $showpagenumber . "');bookmarkscroll.scrollTo('post_message_" . $pid . "');\"";
        }
        $editbutton = "<input $value = \"" . $lang->tsf_forums["edit_post"] . "\" " . $onclick . " $type = \"button\" />";
    }
    if ($moderator || $forummoderator || isset($permissions[$fid]["candeleteposts"]) && $permissions[$fid]["candeleteposts"] == "yes" && $thread["closed"] != "yes" && $thread["uid"] == $CURUSER["id"]) {
        $deletebutton = "<input $value = \"" . $lang->tsf_forums["delete_post"] . "\" $onclick = \"jumpto('" . $BASEURL . "/tsf_forums/deletepost.php?$tid = " . $tid . "&amp;$pid = " . $pid . "&amp;$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "');\" $type = \"button\" />";
    }
    if ($moderator || $forummoderator || isset($permissions[$fid]["canpostreplys"]) && $permissions[$fid]["canpostreplys"] == "yes" && $thread["closed"] != "yes") {
        if ($thread["visible"] == 0 && !$moderator && !$forummoderator) {
            $quotebutton = "";
            $quickreplybutton = "";
        } else {
            $quotebutton = "<input $value = \"" . $lang->tsf_forums["quote_post"] . "\" $onclick = \"jumpto('" . $BASEURL . "/tsf_forums/newreply.php?$tid = " . $tid . "&$pid = " . $pid . "');\" $type = \"button\" />";
            $quickreplybutton = "<input $type = \"button\" $id = \"quote_" . $pid . "\" $value = \"" . $lang->tsf_forums["quick_reply"] . "\" $onclick = \"parseQuote('" . $QuoteTag . "', 'message', " . $tid . ", " . $pid . ");\" />";
        }
    }
    $country = $lang->tsf_forums["country"] . "<img $src = '" . $pic_base_url . "flag/" . $thread["flagpic"] . "' $alt = '" . $thread["countryname"] . "' $title = '" . $thread["countryname"] . "' $style = 'margin-center: 2pt' $height = '10px' class='inlineimg' />";
    if (isset($ABuffer[$thread["uid"]])) {
        $UserAwards = $lang->tsf_forums["awards"] . ": ";
        foreach ($ABuffer[$thread["uid"]] as $Awid => $Awimage) {
            $UserAwards .= $Awimage;
        }
    }
    $usericons = get_user_icons($thread);
    $dateline = my_datee($dateformat, $thread["dateline"]);
    $timeline = my_datee($timeformat, $thread["dateline"]);
    $message = format_comment($thread["message"]);
    if (isset($_GET["highlight"]) && !empty($_GET["highlight"])) {
        $message = highlight(htmlspecialchars_uni($_GET["highlight"]), $message);
    }
    if (!empty($thread["edittime"]) && !empty($thread["edituid"])) {
        $editdate = my_datee($dateformat, $thread["edittime"]);
        $edittime = my_datee($timeformat, $thread["edittime"]);
        $editedby = sprintf($lang->tsf_forums["editedby"], $editdate, $edittime, build_profile_link(get_user_color($thread["editusername"], $thread["editnamestyle"]), $thread["edituid"]));
    }
    if (!empty($thread["modnotice"])) {
        $modnotice_info = @explode("~", $thread["modnotice_info"]);
        $modnotice_info[2] = my_datee($dateformat, $modnotice_info[2]) . " " . my_datee($timeformat, $modnotice_info[2]);
        $modnotice = "\r\n\t\t<br />\r\n\t\t<div class=\"modnotice\">\r\n\t\t\t" . sprintf($lang->global["modnotice"], $modnotice_info[1], $modnotice_info[0], $modnotice_info[2], format_comment($thread["modnotice"])) . "\r\n\t\t</div>\r\n\t\t";
    }
    if ($moderator || $forummoderator || $thread["uid"] == $CURUSER["id"]) {
        $_warnlevel = get_warn_level($thread["timeswarned"]);
    }
    $ThankButton = "";
    $Listthanks = "";
    $Showthanks = [];
    $Thanked = false;
    if (isset($TCache[$thread["pid"]])) {
        foreach ($TCache[$thread["pid"]] as $Thanks) {
            $Showthanks[] = $Thanks["username"];
            if ($Thanks["userid"] === $CURUSER["id"]) {
                $Thanked = true;
            }
        }
        $ThanksCount = count($Showthanks);
        if (0 < $ThanksCount) {
            $Listthanks = "\r\n\t\t\t<tr>\r\n\t\t\t\t<td $colspan = \"2\" $id = \"thanks_zone_" . $pid . "\" class=\"none\">\r\n\t\t\t\t\t<div $id = \"show_thanks_" . $pid . "\" $style = \"clear: both;\">\r\n\t\t\t\t\t\t<table $width = \"100%\" $align = \"center\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\" $style = \"clear: both;\">\r\n\t\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td class=\"subheader\" $style = \"padding: 0px;\">\r\n\t\t\t\t\t\t\t\t\t\t<strong>" . (1 < $ThanksCount ? sprintf($lang->tsf_forums["thanks"], ts_nf($ThanksCount), $posterforthanks) : sprintf($lang->tsf_forums["thank"], $posterforthanks)) . "</strong>\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t\t<td>\r\n\t\t\t\t\t\t\t\t\t\t<div>\r\n\t\t\t\t\t\t\t\t\t\t\t" . implode(", ", $Showthanks) . "\r\n\t\t\t\t\t\t\t\t\t\t</div>\r\n\t\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t\t</table>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t";
        }
    } else {
        $Listthanks = "\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"2\" $id = \"thanks_zone_" . $pid . "\" $style = \"display: none;\" class=\"none\">\r\n\t\t\t\t<div $id = \"show_thanks_" . $pid . "\" $style = \"clear: both;\">\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t";
    }
    if ($usergroups["canthanks"] == "yes" && $thankssystem == "yes" && $CURUSER["id"] != $thread["uid"] && $thread["visible"] == 1 || $thread["visible"] == 0 && ($moderator || $forummoderator) && $usergroups["canthanks"] == "yes" && $thankssystem == "yes" && $CURUSER["id"] != $thread["uid"]) {
        $ThankButton = "\r\n\t\t<span $id = \"loading-layerT\" $style = \"display:none;\"><img $src = \"" . $BASEURL . "/tsf_forums/images/spinner.gif\" $border = \"0\" $alt = \"\" $title = \"\" /></span>\r\n\t\t<span $id = \"thanks_button_" . $pid . "\" $style = \"display: " . (!$Thanked ? "inline" : "none") . ";\"><input $type = \"button\" $value = \"" . $lang->global["buttonthanks"] . "\" $onclick = \"javascript:TSFajaxquickthanks(" . $tid . ", " . $pid . ");\" /></span>\r\n\t\t<span $id = \"remove_thanks_button_" . $pid . "\" $style = \"display: " . (!$Thanked ? "none" : "inline") . ";\"><input $type = \"button\" $value = \"" . $lang->global["buttonthanks2"] . "\" $onclick = \"javascript:TSFajaxquickthanks(" . $tid . ", " . $pid . ", true);\" /></span>";
    }
    $str2 .= "\r\n\t\t<!-- start: post#" . $pid . " -->\r\n\t\t" . (!$isfirstpost ? "" : "</table><br /><table $width = \"100%\" $border = \"0\" $cellspacing = \"0\" $cellpadding = \"4\" $style = \"clear: both;\">") . "\r\n\t\t\t<tr>\r\n\t\t\t\t<td $colspan = \"2\" class=\"subheader\" $name = \"pid" . $pid . "\">\r\n\t\t\t\t\t<div $style = \"float: right;\">\r\n\t\t\t\t\t\t<strong>" . $lang->tsf_forums["post"] . "<a $href = \"" . tsf_seo_clean_text($realsubject, "t", $tid, "&$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "#pid" . $pid) . "\">#" . $count . "</a>" . ($usergroups["canmassdelete"] === "yes" ? " <input $type = \"checkbox\" $name = \"postids[]\" $value = \"" . $pid . "\" $style = \"margin: 0px 0px 0px 5px; padding: 0px; vertical-align: middle;\" $onclick = \"UpdateSelectedItems(this);\" />" : "") . "</strong>\r\n\t\t\t\t\t</div>\r\n\t\t\t\t\t<div $style = \"float: left;\">\r\n\t\t\t\t\t\t<a $name = \"pid" . $pid . "\" $id = \"pid" . $pid . "\"></a><img $src = \"" . $BASEURL . "/tsf_forums/images/post_old.gif\" $border = \"0\" class=\"inlineimg\" /> " . $dateline . " " . $timeline . " " . ($editedby ? "(" . $editedby . ")" : "") . "\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $style = \"text-align: center;\" $valign = \"top\" $width = \"20%\">\r\n\t\t\t\t\t" . $poster . "<br />\r\n\t\t\t\t\t" . $usertitle . "\r\n\t\t\t\t\t" . $avatar . "<br />\r\n\t\t\t\t\t" . user_rank($thread) . "<br />\r\n\t\t\t\t\t" . $join_date . "<br />\r\n\t\t\t\t\t" . $totalposts . "<br />\r\n\t\t\t\t\t" . $country . "<br />\r\n\t\t\t\t\t" . ($UserAwards ? $UserAwards . "<br />" : "") . "\r\n\t\t\t\t\t" . $UserGender . " " . $status . " " . $usericons . "\r\n\t\t\t\t\t" . $_warnlevel . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<script $type = \"text/javascript\">\r\n\t\t\t\t\tmenu_register(\"quickmenu" . $pid . "\", false);\r\n\t\t\t\t</script>\r\n\r\n\t\t\t\t<td $style = \"text-align: left;\" $valign = \"top\" $width = \"80%\">\r\n\t\t\t\t\t" . ($thread["visible"] == 1 || $forummoderator || $moderator || $thread["uid"] == $CURUSER["id"] ? "\r\n\t\t\t\t\t" . ($thread["visible"] == 1 || $thread["uid"] == $CURUSER["id"] ? "<img $src = \"" . $BASEURL . "/tsf_forums/images/icons/icon" . intval($thread["iconid"]) . ".gif\" $border = \"0\" class=\"inlineimg\" />" : "<img $src = \"" . $BASEURL . "/tsf_forums/images/moderation.png\" $alt = \"" . $lang->tsf_forums["moderatemsg7"] . "\" $title = \"" . $lang->tsf_forums["moderatemsg7"] . "\" $border = \"0\" class=\"inlineimg\" />") . "\r\n\t\t\t\t\t<span class=\"smalltext\"><strong>" . $threadsubject . "</strong></span><hr />\r\n\t\t\t\t\t<div $id = \"post_message_" . $pid . "\" $name = \"post_message_" . $pid . "\" $style = \"display: inline;\">" . $message . "</div>\r\n\t\t\t\t\t" . $display_attachment . "\r\n\t\t\t\t\t" . $modnotice . "\r\n\t\t\t\t\t" . $signature : show_notice($lang->tsf_forums["moderatemsg7"])) . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $width = \"15%\" $valign = \"middle\" $style = \"white-space: nowrap; text-align: center;\" class=\"subheader\">\r\n\t\t\t\t<input $value = \"" . $lang->tsf_forums["top"] . "\" $onclick = \"bookmarkscroll.scrollTo('top');\" $type = \"button\" /> <input $value = \"" . $lang->tsf_forums["report_post"] . "\" $onclick = \"TSOpenPopup('" . $BASEURL . "/report.php?$type = 4&$reporting = " . $pid . "&$extra = " . $tid . "&$page = " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . "', 'report', 500, 300); return false;\" $type = \"button\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td $style = \"text-align: center;\" $valign = \"top\" class=\"subheader\">\r\n\t\t\t\t\t<div $style = \"float: right;\">\r\n\t\t\t\t\t\t" . $ThankButton . "\r\n\t\t\t\t\t\t" . $deletebutton . "\r\n\t\t\t\t\t\t" . $editbutton . "\r\n\t\t\t\t\t\t" . $quotebutton . "\r\n\t\t\t\t\t\t" . $quickreplybutton . "\r\n\t\t\t\t\t</div>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t" . $Listthanks . "\r\n\t\t<!-- end: post#" . $pid . " -->\r\n\t";
    $quickmenu .= "\r\n\t\t<div $id = \"quickmenu" . $pid . "_menu\" class=\"menu_popup\" $style = \"display:none;\">\r\n\t\t\t<table $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $align = \"center\" class=\"thead\"><b>" . $lang->global["quickmenu"] . " " . $thread["userusername"] . "</b></td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . tsf_seo_clean_text($poster, "u", $thread["uid"]) . "\">" . $lang->global["qinfo1"] . "</a></td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/sendmessage.php?$receiver = " . $thread["uid"] . "\">" . sprintf($lang->global["qinfo2"], $thread["userusername"]) . "</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/tsf_forums/tsf_search.php?$action = finduserposts&amp;$id = " . $thread["uid"] . "\">" . sprintf($lang->global["qinfo3"], $thread["userusername"]) . "</a></td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/tsf_forums/tsf_search.php?$action = finduserthreads&amp;$id = " . $thread["uid"] . "\">" . sprintf($lang->global["qinfo4"], $thread["userusername"]) . "</a></td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/friends.php?$action = add_friend&amp;$friendid = " . $thread["uid"] . "\">" . sprintf($lang->global["qinfo5"], $thread["userusername"]) . "</td>\r\n\t\t\t\t</tr>\r\n\r\n\t\t\t\t" . ($moderator ? "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=edit_user&amp;$username = " . $thread["userusername"] . "\">" . $lang->global["qinfo6"] . "</a></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=warn_user&amp;$username = " . $thread["userusername"] . "\">" . $lang->global["qinfo7"] . "</a></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>" : "") . "\r\n\t\t\t</table>\r\n\t\t</div>";
    if ($thread["votenum"] && !isset($ratingimage)) {
        $thread["voteavg"] = number_format($thread["votetotal"] / $thread["votenum"], 2);
        $thread["rating"] = round($thread["votetotal"] / $thread["votenum"]);
        $ratingimgalt = sprintf($lang->tsf_forums["tratingimgalt"], $thread["votenum"], $thread["voteavg"]);
        $ratingimage = "<img $src = \"" . $BASEURL . "/tsf_forums/images/rating/rating_" . $thread["rating"] . ".gif\" $alt = \"" . $ratingimgalt . "\" $title = \"" . $ratingimgalt . "\" $border = \"0\" class=\"inlineimg\" />";
    }
    if ($thread["pollid"] && !isset($pollid)) {
        $pollid = intval($thread["pollid"]);
    }
    if (!$isfirstpost) {
        $str2 .= "\r\n\t\t\t</table>";
        if (!empty($f_ads)) {
            $str2 .= "\r\n\t\t\t\t<br />\r\n\t\t\t\t<table $width = \"100%\" $border = \"0\" $cellpadding = \"5\" $cellspacing = \"0\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t<td $align = \"center\">";
            if (strstr($f_ads, "[TS_ADS]")) {
                $f_ads = explode("[TS_ADS]", $f_ads);
                $getARandomAd = rand(0, count($f_ads) - 1);
                $str2 .= $f_ads[$getARandomAd];
            } else {
                $str2 .= $f_ads;
            }
            $str2 .= "\r\n\t\t\t\t\t\t</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t</table>";
        }
        $metadesc = substr(strip_tags($message), 0, 154) . "...";
    }
}
$tspoll = "";
if (isset($pollid)) {
    define("POLLID", $pollid);
    define("THREADID", $tid);
    define("IN_PLUGIN_SYSTEM", true);
    require_once INC_PATH . "/plugins/tspoll.php";
}
$massdelete = $massdelete2 = "";
if ($usergroups["canmassdelete"] === "yes") {
    $massdelete = "\r\n\t<form $method = \"post\" $name = \"massdeleteform\">\r\n\t\t<input $type = \"hidden\" $name = \"parentfid\" $value = \"" . $realforumid . "\" />\r\n\t\t<input $type = \"hidden\" $name = \"currentfid\" $value = \"" . $fid . "\" />\r\n\t\t<input $type = \"hidden\" $name = \"threadids[]\" $value = \"" . $tid . "\" />\r\n\t\t<input $type = \"hidden\" $name = \"hash\" $value = \"" . $forumtokencode . "\" />";
    $massdelete2 = "\r\n\t\t<select $name = \"type_to_go\" $id = \"type_to_go\">\r\n\t\t\t<option $value = \"\">" . $lang->tsf_forums["mod_options"] . "</option>\r\n\t\t\t<option $value = \"approve\">" . $lang->tsf_forums["moderatemsg5"] . "</option>\r\n\t\t\t<option $value = \"unapprove\">" . $lang->tsf_forums["moderatemsg6"] . "</option>\r\n\t\t\t<option $value = \"approveattachments\">" . $lang->tsf_forums["moderatemsg10"] . "</option>\r\n\t\t\t<option $value = \"unapproveattachments\">" . $lang->tsf_forums["moderatemsg11"] . "</option>\r\n\t\t\t<option $value = \"deleteposts\">" . $lang->tsf_forums["deleteposts"] . "</option>\r\n\t\t\t<option $value = \"mergeposts\">" . $lang->tsf_forums["mergeposts"] . "</option>\r\n\t\t\t<option $value = \"moveposts\">" . $lang->tsf_forums["moveposts"] . "</option>\r\n\t\t\t<option $value = \"copyposts\">" . $lang->tsf_forums["copyposts"] . "</option>\r\n\t\t</select>\r\n\t\t<input $type = \"button\" $value = \"" . $lang->tsf_forums["go_button"] . " (0)\" $id = \"mod_go_button\" $name = \"mod_go_button\" $onclick = \"SubmitModForm(); return false;\" />\r\n\t</form>";
}
$thread_search_options = "<a $href = \"javascript:void(0);\" $id = \"thread_search_options" . $tid . "\">" . $lang->tsf_forums["sthread"] . "</a>\r\n<script $type = \"text/javascript\">\r\n\tfunction quote(textarea,form,quote)\r\n\t{\r\n\t\tvar $area = document.forms[form].elements[textarea];\r\n\t\tarea.$value = area.value+quote+\"\\n\";\r\n\t\tarea.focus();\r\n\t};\r\n</script>\r\n<script $type = \"text/javascript\">\r\n\t\tmenu_register(\"thread_search_options" . $tid . "\",false);\r\n</script>\r\n<div $id = \"thread_search_options" . $tid . "_menu\" class=\"menu_popup\" $style = \"display:none;\">\r\n\t<table $cellspacing = \"0\" $cellpadding = \"5\" $width = \"250\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\"><b>" . $lang->tsf_forums["sthread"] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\">\r\n\t\t\t\t<form $method = \"post\" $action = \"" . $BASEURL . "/tsf_forums/tsf_search.php\">\r\n\t\t\t\t<input $type = \"hidden\" $name = \"action\" $value = \"searchinthread\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"threadid\" $value = \"" . $tid . "\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"author\" $value = \"\" />\r\n\t\t\t\t<input $type = \"hidden\" $name = \"matchusername\" $value = \"\" />\r\n\t\t\t\t<input $type = \"text\" $name = \"keywords\" $value = \"\" $size = \"20\" /> <input $type = \"submit\" $value = \"" . $lang->tsf_forums["search"] . "\" />\r\n\t\t\t\t<br />\r\n\t\t\t\t</form>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\" $align = \"center\"><a $href = \"" . $BASEURL . "/tsf_forums/tsf_search.php?$action = searchthread&amp;$threadid = " . $tid . "\">" . $lang->tsf_forums["goadvanced"] . "</a></td>\r\n\t\t</tr>\r\n\t</table>\r\n</div>";
$thread_options = "\r\n<a $href = \"javascript:void(0);\" $id = \"thread_options" . $tid . "\">" . $lang->tsf_forums["toptions"] . "</a>\r\n<script $type = \"text/javascript\">\r\n\t\tmenu_register(\"thread_options" . $tid . "\",false);\r\n</script>\r\n<div $id = \"thread_options" . $tid . "_menu\" class=\"menu_popup\" $style = \"display:none;\">\r\n\t<table $border = \"1\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"200\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"thead\"><b>" . $lang->tsf_forums["toptions"] . "</b></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\">" . $subslink . "</td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/tsf_forums/misc.php?$action = email_thread&amp;$tid = " . $tid . "\"><b>" . $lang->tsf_forums["ethread"] . "</b></a></td>\r\n\t\t</tr>\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\"><a $href = \"" . $BASEURL . "/tsf_forums/misc.php?$action = print_thread&amp;$tid = " . $tid . "\"><b>" . $lang->tsf_forums["pthread"] . "</b></a></td>\r\n\t\t</tr>\r\n\t</table>\r\n</div>";
$str = "\r\n\t<script $type = \"text/javascript\">\r\n\t\tjQuery(document).ready(function()\r\n\t\t{\r\n\t\t\tjQuery(\"a.colorbox\").colorbox({photo: true, maxWidth: \"90%\", maxHeight: \"90%\"});\r\n\t\t});\r\n\t</script>\r\n\t\r\n\t<script $type = \"text/javascript\">\r\n\t\tvar ArrayCount = 0;\r\n\t\tvar SelectedItems = new Array();\r\n\t\tvar Action = \"\";\r\n\t\tfunction UpdateSelectedItems(What)\r\n\t\t{\r\n\t\t\tif (What.checked)\r\n\t\t\t{\r\n\t\t\t\tArrayCount++;\r\n\t\t\t\tSelectedItems[ArrayCount] = What.value;\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\tif (SelectedItems[ArrayCount])\r\n\t\t\t\t{\r\n\t\t\t\t\tSelectedItems[ArrayCount] = \"\";\r\n\t\t\t\t}\r\n\t\t\t\tif (ArrayCount > 0)\r\n\t\t\t\t{\r\n\t\t\t\t\tArrayCount--;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t\tTSGetID(\"mod_go_button\").$value = \"" . $lang->tsf_forums["go_button"] . " (\"+ArrayCount+\")\";\r\n\t\t}\r\n\r\n\t\tfunction SubmitModForm()\r\n\t\t{\r\n\t\t\tif (ArrayCount > 0)\r\n\t\t\t{\r\n\t\t\t\tvar $page = 0;\r\n\t\t\t\t" . (isset($_GET["page"]) ? "page = \"" . intval($_GET["page"]) . "\";" : "") . "\r\n\r\n\t\t\t\$tAction = TSGetID(\"type_to_go\").value;\r\n\t\t\t\tif (Action == \"deleteposts\")\r\n\t\t\t\t{\r\n\t\t\t\t\tif (confirm(\"Are you sure that you want to delete selected posts?\"))\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tdocument.massdeleteform.$action = \"" . $BASEURL . "/tsf_forums/massdelete.php?$action = deleteposts&$tid = " . $tid . "&$page = \"+page;\r\n\t\t\t\t\t\tdocument.massdeleteform.submit();\r\n\t\t\t\t\t}\r\n\t\t\t\t\telse\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\treturn false;\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if(Action == \"mergeposts\")\r\n\t\t\t\t{\r\n\t\t\t\t\tif (ArrayCount <2)\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\talert(\"" . $lang->tsf_forums["mergeerror"] . "\");\r\n\t\t\t\t\t\treturn false;\r\n\t\t\t\t\t}\r\n\t\t\t\t\telse\r\n\t\t\t\t\t{\r\n\t\t\t\t\t\tdocument.massdeleteform.$action = \"" . $BASEURL . "/tsf_forums/mergeposts.php?$action = mergeposts&$tid = " . $tid . "&$page = \"+page;\r\n\t\t\t\t\t\tdocument.massdeleteform.submit();\r\n\t\t\t\t\t}\r\n\t\t\t\t}\r\n\t\t\t\telse if (Action == \"moveposts\")\r\n\t\t\t\t{\r\n\t\t\t\t\tdocument.massdeleteform.$action = \"" . $BASEURL . "/tsf_forums/moveposts.php?$action = moveposts&$tid = " . $tid . "&$page = \"+page;\r\n\t\t\t\t\tdocument.massdeleteform.submit();\r\n\t\t\t\t}\r\n\t\t\t\telse if (Action == \"copyposts\")\r\n\t\t\t\t{\r\n\t\t\t\t\tdocument.massdeleteform.$action = \"" . $BASEURL . "/tsf_forums/copyposts.php?$action = copyposts&$tid = " . $tid . "&$page = \"+page;\r\n\t\t\t\t\tdocument.massdeleteform.submit();\r\n\t\t\t\t}\r\n\t\t\t\telse if (Action == \"approve\")\r\n\t\t\t\t{\r\n\t\t\t\t\tdocument.massdeleteform.$action = \"" . $BASEURL . "/tsf_forums/approveposts.php?$action = approve&$tid = " . $tid . "&$page = \"+page;\r\n\t\t\t\t\tdocument.massdeleteform.submit();\r\n\t\t\t\t}\r\n\t\t\t\telse if (Action == \"unapprove\")\r\n\t\t\t\t{\r\n\t\t\t\t\tdocument.massdeleteform.$action = \"" . $BASEURL . "/tsf_forums/approveposts.php?$action = unapprove&$tid = " . $tid . "&$page = \"+page;\r\n\t\t\t\t\tdocument.massdeleteform.submit();\r\n\t\t\t\t}\r\n\t\t\t\telse if (Action == \"approveattachments\")\r\n\t\t\t\t{\r\n\t\t\t\t\tdocument.massdeleteform.$action = \"" . $BASEURL . "/tsf_forums/approveposts.php?$action = approveattachments&$tid = " . $tid . "&$page = \"+page;\r\n\t\t\t\t\tdocument.massdeleteform.submit();\r\n\t\t\t\t}\r\n\t\t\t\telse if (Action == \"unapproveattachments\")\r\n\t\t\t\t{\r\n\t\t\t\t\tdocument.massdeleteform.$action = \"" . $BASEURL . "/tsf_forums/approveposts.php?$action = unapproveattachments&$tid = " . $tid . "&$page = \"+page;\r\n\t\t\t\t\tdocument.massdeleteform.submit();\r\n\t\t\t\t}\r\n\t\t\t\telse\r\n\t\t\t\t{\r\n\t\t\t\t\talert(\"Invalid Action!\");\r\n\t\t\t\t\treturn false;\r\n\t\t\t\t}\r\n\t\t\t}\r\n\t\t\telse\r\n\t\t\t{\r\n\t\t\t\talert(\"Please select at least one post to moderate!\");\r\n\t\t\t\treturn false;\r\n\t\t\t}\r\n\t\t}\r\n\t</script>\r\n\t\r\n\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/tsf_forums/scripts/quick_thanks.js?$v = " . O_SCRIPT_VERSION . "\"></script>\r\n\t" . $tspoll . "\r\n\r\n\t<!-- start: forumdisplay_newthread -->\r\n\t<a $name = \"top\" $id = \"top\"></a>\r\n\t<div $style = \"float: left; margin-bottom: 3px;\" $id = \"navcontainer_f\">\r\n\t\t" . $multipage . "\r\n\t</div>\r\n\t<div $style = \"float: right; margin-bottom: 3px;\">\r\n\t\t<input $value = \"" . ($isthreadclosed === false ? $lang->tsf_forums["new_reply"] : $lang->tsf_forums["thread_locked"]) . "\" $onclick = \"jumpto('" . $BASEURL . "/tsf_forums/newreply.php?$tid = " . $tid . "');\" $type = \"button\" />\r\n\t</div>\r\n\t<!-- end: forumdisplay_newthread -->\r\n\r\n\t<table $width = \"100%\" $border = \"0\" $cellspacing = \"0\" $cellpadding = \"4\" $style = \"clear: both;\">\r\n\t\t<tr>\r\n\t\t\t<td $colspan = \"3\" class=\"thead\" $align = \"left\">\r\n\t\t\t\t<div $style = \"float: right;\"><b>" . $thread_options . "&nbsp;" . $thread_search_options . "&nbsp;" . show_rate_button() . "</b></div>\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t\t" . $massdelete . "\r\n\t\t" . $str2 . "\r\n\t\t<script $type = \"text/javascript\">\r\n\t\t\tmenu.activate(true);\r\n\t\t</script>\r\n\t</table>\r\n\t" . ($useajax == "yes" ? "\r\n\t\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/scripts/prototype.js?$v = " . O_SCRIPT_VERSION . "\"></script>\r\n\t\t<script $type = \"text/javascript\" $src = \"" . $BASEURL . "/tsf_forums/scripts/quick_reply.js?$v = " . O_SCRIPT_VERSION . "\"></script>\r\n\t\t<div $id = \"ajax_quick_reply\"></div>\r\n\t\t" : "") . "\r\n\t" . $quickmenu . "\r\n\t<p></p>";
$forumjump = build_forum_jump($fid);
$quickreplymod = "";
if ($moderator || $forummoderator) {
    $quickreplymod = "<label><input class=\"checkbox\" $name = \"closethread\" $value = \"yes\" $type = \"checkbox\"" . ($isthreadclosed ? " $checked = \"checked\"" : "") . " />" . $lang->tsf_forums["mod_options_c"] . "</label>\r\n\t\t\t\t<label><input class=\"checkbox\" $name = \"stickthread\" $value = \"yes\" $type = \"checkbox\"" . ($isstickythread ? " $checked = \"checked\"" : "") . " />" . $lang->tsf_forums["mod_options_s"] . "</label>\r\n\t\t\t\t</span>";
} else {
    $quickreplymod = "<input $name = \"closethread\" $value = \"no\" $type = \"hidden\"><input $name = \"stickthread\" $value = \"no\" $type = \"hidden\" />";
}
if ($moderator || $forummoderator) {
    $mod_options = "\r\n\t\t<form $action = \"" . $BASEURL . "/tsf_forums/moderation.php\" $method = \"get\" $style = \"margin-top: 0pt; margin-bottom: 0pt;\">\r\n\t\t\t<input $name = \"tid\" $value = \"" . $tid . "\" $type = \"hidden\" />\r\n\t\t\t<input $type = \"hidden\" $name = \"hash\" $value = \"" . $forumtokencode . "\" />\r\n\t\t\t<span class=\"smalltext\">\r\n\t\t\t<strong>" . $lang->tsf_forums["mod_options"] . "</strong></span><br />\r\n\t\t\t<select $name = \"action\">\r\n\t\t\t<optgroup $label = \"" . $lang->tsf_forums["mod_options"] . "\">\r\n\t\t\t\t<option $value = \"sticky\">" . $lang->tsf_forums["mod_options_ss"] . "</option>\r\n\t\t\t\t<option $value = \"openclosethread\">" . $lang->tsf_forums["mod_options_cc"] . "</option>\r\n\t\t\t\t<option $value = \"deletethread\">" . $lang->tsf_forums["mod_options_dd"] . "</option>\r\n\t\t\t\t<option $value = \"movethread\">" . $lang->tsf_forums["mod_options_m"] . "</option>\r\n\t\t\t</optgroup>\r\n\t\t\t</select>\r\n\t\t\t<!-- start: gobutton -->\r\n\t\t\t<input class=\"button\" $value = \"" . $lang->tsf_forums["go_button"] . "\" $type = \"submit\" />\r\n\t\t\t<!-- end: gobutton -->\r\n\t\t</form>";
}
require_once "./include/function_bookmarks.php";
$QuickEditor->FormName = "quickreply";
$QuickEditor->TextAreaName = "message";
$str .= "\r\n<!-- start: forumdisplay_newthread -->\r\n<div $style = \"float: left; margin-bottom: 5px;\" $id = \"navcontainer_f\">\r\n\t" . $multipage . "\r\n</div>\r\n<div $style = \"float: right; margin-bottom: 5px;\">\r\n\t<input $value = \"" . ($isthreadclosed === false ? $lang->tsf_forums["new_reply"] : $lang->tsf_forums["thread_locked"]) . "\" $onclick = \"jumpto('" . $BASEURL . "/tsf_forums/newreply.php?$tid = " . $tid . "');\" $type = \"button\" /> " . $massdelete2 . "\r\n</div>\r\n<!-- end: forumdisplay_newthread -->\r\n\r\n<!-- start: forumdisplay_quickreply -->\r\n" . $QuickEditor->GenerateJavascript() . "\r\n<form $method = \"post\" $action = \"" . $BASEURL . "/tsf_forums/newreply.php?$tid = " . $tid . (isset($_GET["page"]) ? "&$page = " . intval($_GET["page"]) : "") . "\" $name = \"quickreply\" $id = \"quickreply\">\r\n<input $type = \"hidden\" $name = \"tid\" $value = \"" . $tid . "\" />\r\n<input $type = \"hidden\" $name = \"subject\" $value = \"" . $threadsubject . "\" />\r\n<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\" $align = \"center\" $style = \"clear: both;\">\r\n\t<tr>\r\n\t\t<td class=\"thead\">" . ts_collapse("quickreply") . "<strong>" . $lang->tsf_forums["quick_reply"] . "</strong></td>\r\n\t</tr>\r\n\t\t" . ts_collapse("quickreply", 2) . "\r\n\t<tr>\r\n\t\t<td>\r\n\t\t\t" . $QuickEditor->GenerateBBCode() . "\r\n\t\t</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td $align = \"center\"><textarea $id = \"message\" $name = \"message\" $style = \"width:850px;height:120px;\"></textarea><br />" . $quickreplymod . "</td>\r\n\t</tr>\r\n\t<tr>\r\n\t\t<td $align = \"center\" class=\"thead\">\r\n\t\t\t<span $id = \"loading-layerS\" $style = \"display:none;\"><img $src = \"" . $BASEURL . "/tsf_forums/images/spinner.gif\" $border = \"0\" $alt = \"\" $title = \"\" /></span>\r\n\t\t\t" . ($useajax == "yes" ? "<input $type = \"button\" $value = \"" . $lang->tsf_forums["post_reply"] . "\" $id = \"quickreplybutton\" $name = \"quickreplybutton\" $onclick = \"TSajaxquickreply(" . $tid . ", " . $count . ", " . (isset($_GET["page"]) ? intval($_GET["page"]) : 0) . ");\" />" : "<input $name = \"submit\" $value = \"" . $lang->tsf_forums["post_reply"] . "\" $tabindex = \"3\" $accesskey = \"s\" $type = \"submit\" />") . " <input $name = \"previewpost\" $value = \"" . $lang->tsf_forums["preview_reply"] . "\" $tabindex = \"4\" $type = \"submit\" />\t<input $name = \"previewpost\" $value = \"" . $lang->tsf_forums["goadvanced"] . "\" $tabindex = \"4\" $type = \"submit\" />\r\n\t\t</td>\r\n\t</tr>\r\n</table>\r\n</form>\r\n<!-- end: forumdisplay_quickreply -->\r\n\r\n<!-- next / previous links -->\r\n<br />\r\n<div class=\"smallfont\" $align = \"center\">\r\n\t<strong>&laquo;</strong>\r\n\t<a $href = \"" . $BASEURL . "/tsf_forums/misc.php?$action = oldest-thread&$tid = " . $tid . "\" $rel = \"nofollow\">" . $lang->tsf_forums["p_thread"] . "</a>\r\n\t|\r\n\t<a $href = \"" . $BASEURL . "/tsf_forums/misc.php?$action = newest-thread&$tid = " . $tid . "\" $rel = \"nofollow\">" . $lang->tsf_forums["n_thread"] . "</a>\r\n\t<strong>&raquo;</strong>\r\n</div>\r\n<!-- / next / previous links -->\r\n\r\n<br />\r\n<table $border = \"0\" $cellspacing = \"0\" $cellpadding = \"5\" $width = \"100%\" $align = \"center\" class=\"subheader\" $style = \"clear: both;\">\r\n<tr>\r\n<td>\r\n" . (isset($mod_options) ? "\r\n\t<div $style = \"float: left; margin-bottom: 5px; margin-top: 5px;\">\r\n\t\t" . $mod_options . "\r\n\t</div>" : "") . "\r\n\t<div $style = \"float: right; margin-bottom: 5px; margin-top: 5px;\">\r\n\t\t" . $forumjump . "\r\n\t</div>\r\n</td>\r\n</tr>\r\n</table>" . elastic();
$metadesc = stdhead(strip_tags(unhtmlspecialchars($realsubject)));
if (isset($warningmessage)) {
    echo $warningmessage;
}
add_breadcrumb($realforum, tsf_seo_clean_text($realforum, $ftype == "s" ? "fd" : "f", $realforumid));
add_breadcrumb($currentforum, tsf_seo_clean_text($currentforum, "fd", $fid));
add_breadcrumb($realsubject, tsf_seo_clean_text($realsubject, "t", $tid));
build_breadcrumb();
echo $str;
sql_query("UPDATE " . TSF_PREFIX . "threads SET $views = views + 1 WHERE $tid = " . sqlesc($tid));
sql_query("REPLACE INTO " . TSF_PREFIX . "threadsread SET $tid = '" . $tid . "', $uid = '" . $CURUSER["id"] . "', $dateline = '" . TIMENOW . "'");
stdfoot();
function show_image($text, $size = 300)
{
    $content = "onmouseover=\"ddrivetip('" . $text . "', " . $size . ")\"; $onmouseout = \"hideddrivetip()\"";
    return $content;
}
function unhtmlspecialchars($text, $doUniCode = false)
{
    if ($doUniCode) {
        $text = preg_replace_callback("/&#([0-9]+);/siU", function ($matches) {
            return convert_int_to_utf8($matches[1]);
        }, $text);
    }
    return str_replace(["&lt;", "&gt;", "&quot;", "&amp;"], ["<", ">", "\"", "&"], $text);
}

?>