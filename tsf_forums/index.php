<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

define("THIS_SCRIPT", "forumindex.php");
require "./global.php";
if (isset($_GET["fid"]) && is_valid_id($_GET["fid"])) {
    $fid = intval($_GET["fid"]);
    if (!isset($permissions[$fid]["canview"]) || $permissions[$fid]["canview"] != "yes") {
        print_no_permission(true);
        exit;
    }
    $oneforum = $addnavbar = true;
}
$forumTitle = $f_forumname;
if (isset($warningmessage)) {
    echo $warningmessage;
}
$ViewingForums = [];
($Query = sql_query("SELECT location, inforum FROM ts_sessions WHERE lastactivity > '" . (TIMENOW - TS_TIMEOUT) . "'")) || sqlerr(__FILE__, 46);
if (0 < mysqli_num_rows($Query)) {
    while ($UB = mysqli_fetch_row($Query)) {
        if (!$UB[1]) {
            if (preg_match("@\\/tsf_forums\\/forumdisplay\\.php\\?$fid = (.*)@is", $UB[0], $Found)) {
                if (isset($ViewingForums[$Found[1]])) {
                    $ViewingForums[$Found[1]]++;
                } else {
                    $ViewingForums[$Found[1]] = 1;
                }
            }
        } else {
            if (isset($ViewingForums[$UB[1]])) {
                $ViewingForums[$UB[1]]++;
            } else {
                $ViewingForums[$UB[1]] = 1;
            }
        }
    }
}
($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.fid, f.pid, f.name, f.posts as sposts, f.threads as sthreads\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\tWHERE f.$type = 's' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 84);
$deepsubforums = [];
while ($subforum = mysqli_fetch_assoc($query)) {
    if (isset($permissions[$subforum["fid"]]["canview"]) && $permissions[$subforum["fid"]]["canview"] == "yes") {
        if (isset($ViewingForums[$subforum["fid"]])) {
            if (isset($ViewingForums[$subforum["pid"]])) {
                $ViewingForums[$subforum["pid"]] += $ViewingForums[$subforum["fid"]];
            } else {
                $ViewingForums[$subforum["pid"]] = 1;
            }
        }
        $deepposts[$subforum["pid"]] = isset($deepposts[$subforum["pid"]]) ? $deepposts[$subforum["pid"]] + $subforum["sposts"] : $subforum["sposts"];
        $deepthreads[$subforum["pid"]] = isset($deepthreads[$subforum["pid"]]) ? $deepthreads[$subforum["pid"]] + $subforum["sthreads"] : $subforum["sthreads"];
        $deepsubforums[$subforum["pid"]] = (isset($deepsubforums[$subforum["pid"]]) ? $deepsubforums[$subforum["pid"]] : "") . "<img $src = \"" . $BASEURL . "/tsf_forums/images/subforums.gif\" $alt = \"" . $subforum["name"] . "\" $title = \"" . $subforum["name"] . "\" /> <a $href = \"" . tsf_seo_clean_text($subforum["name"], "fd", $subforum["fid"]) . "\">" . $subforum["name"] . "</a> <font $size = \"1\">(" . ts_nf($subforum["sthreads"]) . "/" . ts_nf($subforum["sposts"]) . ")</font>~~~";
    }
}
$query = sql_query("SELECT m.userid, m.forumid, u.username, g.namestyle\r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "moderators m \r\n\t\t\t\t\t\t\tINNER JOIN users u ON (m.$userid = u.id)\r\n\t\t\t\t\t\t\tINNER JOIN usergroups g ON (u.$usergroup = g.gid)");
$imodcache = [];
while ($forummoderators = mysqli_fetch_assoc($query)) {
    $imodcache[(string) $forummoderators["forumid"]][(string) $forummoderators["userid"]] = $forummoderators;
}
($query = sql_query("\r\n\t\t\t\t\t\t\tSELECT f.*, u.username as realrealusername, u.id as reallastposteruserid, g.namestyle \r\n\t\t\t\t\t\t\tFROM " . TSF_PREFIX . "forums f \r\n\t\t\t\t\t\t\tLEFT JOIN users u ON (f.$lastposteruid = u.id) \r\n\t\t\t\t\t\t\tLEFT JOIN usergroups g ON (g.$gid = u.usergroup) \r\n\t\t\t\t\t\t\tWHERE f.$type = 'f' ORDER by f.pid, f.disporder\r\n\t\t\t\t\t\t")) || sqlerr(__FILE__, 126);
require_once INC_PATH . "/functions_cookies.php";
while ($forum = mysqli_fetch_assoc($query)) {
    if (isset($permissions[$forum["fid"]]["canview"]) && $permissions[$forum["fid"]]["canview"] == "yes") {
        $moderatorslist = "";
        if (isset($imodcache[(string) $forum["fid"]])) {
            foreach ($imodcache[(string) $forum["fid"]] as $fmoderator) {
                if ($moderatorslist == "") {
                    $moderatorslist = "<a $href = \"" . tsf_seo_clean_text($fmoderator["username"], "u", $fmoderator["userid"]) . "\" $rel = \"nofollow\">" . get_user_color($fmoderator["username"], $fmoderator["namestyle"]) . "</a>";
                } else {
                    $moderatorslist .= ", <a $href = \"" . tsf_seo_clean_text($fmoderator["username"], "u", $fmoderator["userid"]) . "\" $rel = \"nofollow\">" . get_user_color($fmoderator["username"], $fmoderator["namestyle"]) . "</a>";
                }
            }
        }
        $lastpost_data = $_clean_subject = "";
        $hideinfo = false;
        $posts = ts_nf($forum["posts"] + (isset($deepposts[$forum["fid"]]) ? $deepposts[$forum["fid"]] : 0));
        $threads = ts_nf($forum["threads"] + (isset($deepthreads[$forum["fid"]]) ? $deepthreads[$forum["fid"]] : 0));
        if ($forum["password"] != "" && (!isset($_COOKIE["forumpass_" . $forum["fid"]]) || $_COOKIE["forumpass_" . $forum["fid"]] != md5($CURUSER["id"] . $forum["password"] . $securehash) || empty($_COOKIE["forumpass_" . $forum["fid"]]) || strlen($_COOKIE["forumpass_" . $forum["fid"]]) != 32)) {
            $hideinfo = true;
        }
        $lastpost_data = ["lastpost" => $forum["lastpost"], "lastpostsubject" => $forum["lastpostsubject"], "lastposter" => get_user_color(htmlspecialchars_uni($forum["realrealusername"]), $forum["namestyle"]), "lastposttid" => $forum["lastposttid"], "lastposteruid" => $forum["reallastposteruserid"]];
        if ($hideinfo) {
            unset($lastpost_data);
        }
        if ((!isset($lastpost_data["lastpost"]) || $lastpost_data["lastpost"] == 0) && !$hideinfo) {
            $lastpost = "<span $style = \"text-align: center;\">" . $lang->tsf_forums["lastpost_never"] . "</span>";
        } else {
            if (!$hideinfo) {
                $lastpost_date = my_datee($dateformat, $forum["lastpost"]);
                $lastpost_time = my_datee($timeformat, $forum["lastpost"]);
                $lastpost_profilelink = build_profile_link($lastpost_data["lastposter"], $lastpost_data["lastposteruid"]);
                $lastposttid = $lastpost_data["lastposttid"];
                $lastpost_subject = $full_lastpost_subject = $lastpost_data["lastpostsubject"];
                if (30 < @strlen($lastpost_subject)) {
                    $lastpost_subject = cutename($lastpost_subject, 30, false);
                }
                $full_lastpost_subject = htmlspecialchars_uni(ts_remove_badwords($full_lastpost_subject));
                $_clean_subject = htmlspecialchars_uni(ts_remove_badwords($lastpost_subject));
                $lastpost = "\r\n\t\t<div class=\"smalltext\" $align = \"left\">\r\n\t\t\t<div>\r\n\t\t\t\t<span $style = \"white-space: nowrap;\">\t\t\r\n\t\t\t\t\t<a $href = \"" . tsf_seo_clean_text($_clean_subject, "lastpost", $lastposttid) . "\" $title = \"" . $full_lastpost_subject . "\" $title = \"" . $full_lastpost_subject . "\"><strong>" . $_clean_subject . "</strong></a>\r\n\t\t\t\t</span>\r\n\t\t\t</div>\r\n\t\t\t<div $style = \"white-space: nowrap;\">\r\n\t\t\t\t" . $lang->tsf_forums["by"] . " " . $lastpost_profilelink . "\r\n\t\t\t</div>\r\n\t\t\t<div $style = \"white-space: nowrap;\" $align = \"right\">\r\n\t\t\t\t" . $lastpost_date . " <span class=\"time\">" . $lastpost_time . "</span>\r\n\t\t\t\t<a $href = \"" . tsf_seo_clean_text($_clean_subject, "lastpost", $lastposttid) . "\" $alt = \"" . $full_lastpost_subject . "\" $title = \"" . $full_lastpost_subject . "\"><img $src = \"" . $BASEURL . "/tsf_forums/images/lastpost.gif\" class=\"inlineimg\" $border = \"0\" $alt = \"" . $lang->tsf_forums["gotolastpost"] . "\" $title = \"" . $lang->tsf_forums["gotolastpost"] . "\"></a>\r\n\t\t\t</div>\r\n\t\t</div>";
            }
        }
        $forumread = ts_get_array_cookie("forumread", $forum["fid"]);
        if (isset($lastpost_data["lastpost"]) && $CURUSER["last_forum_visit"] < $lastpost_data["lastpost"] && $forumread < $lastpost_data["lastpost"] && $lastpost_data["lastpost"] != 0) {
            $folder = "on";
            $altonoff = $lang->tsf_forums["new_posts"];
        } else {
            $folder = "off";
            $altonoff = $lang->tsf_forums["no_new_posts"];
        }
        $Showsubforums = "";
        if (isset($deepsubforums[$forum["fid"]])) {
            $DSFCount = 0;
            $ManageSFArray = explode("~~~", $deepsubforums[$forum["fid"]]);
            $Showsubforums .= "<fieldset><legend>" . $lang->tsf_forums["sforums"] . "</legend><table $width = \"100%\" $border = \"0\" $cellpadding = \"2\" $cellspacing = \"0\"><tr>";
            foreach ($ManageSFArray as $DSF) {
                if ($DSFCount % $f_sfpertr == 0) {
                    $Showsubforums .= "</tr><tr>";
                }
                $Showsubforums .= "<td class=\"none\">" . $DSF . "</td>";
                $DSFCount++;
            }
            $Showsubforums .= "</tr></table></fieldset>";
        }
        $subforums[$forum["pid"]] = (isset($subforums[$forum["pid"]]) ? $subforums[$forum["pid"]] : "") . "\r\n\t\r\n\t\t<!-- start: forums#" . $forum["fid"] . " for category#" . $forum["pid"] . " -->\r\n\t\t\t<tr>\r\n\t\t\t\t<td $align = \"center\" $valign = \"top\">\r\n\t\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/" . $folder . ".gif\" $alt = \"" . $altonoff . "\" $title = \"" . $altonoff . "\" />\r\n\t\t\t\t</td>\r\n\t\t\t\t<td $align = \"center\" $valign = \"top\">\r\n\t\t\t\t\t" . ($forum["image"] ? "<img $src = \"" . $BASEURL . "/tsf_forums/images/forumicons/" . $forum["image"] . "\" $alt = \"\" $title = \"\" />" : "") . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td $valign = \"top\">\r\n\t\t\t\t\t<strong><a $href = \"" . tsf_seo_clean_text($forum["name"], "fd", $forum["fid"]) . "\">" . $forum["name"] . "</a></strong> " . (isset($ViewingForums[$forum["fid"]]) ? sprintf($lang->tsf_forums["userviewing"], ts_nf($ViewingForums[$forum["fid"]])) : "") . "\r\n\t\t\t\t\t<div class=\"smalltext\">" . $forum["description"] . "</div>\r\n\t\t\t\t\t" . ($moderatorslist ? "<div class=\"smalltext\">" . sprintf($lang->tsf_forums["modlist"], $moderatorslist) . "</div>" : "") . "\r\n\t\t\t\t\t" . $Showsubforums . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td $style = \"white-space: nowrap;\" $align = \"left\" $valign = \"top\">\r\n\t\t\t\t\t" . ($hideinfo === false ? $lastpost : $lang->tsf_forums["hidden"]) . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td $style = \"white-space: nowrap;\" $align = \"center\" $valign = \"top\">\r\n\t\t\t\t\t" . $threads . "\r\n\t\t\t\t</td>\r\n\t\t\t\t<td $style = \"white-space: nowrap;\" $align = \"center\" $valign = \"top\">\r\n\t\t\t\t\t" . $posts . "\r\n\t\t\t\t</td>\t\t\t\t\r\n\t\t\t</tr>\t\t\t\r\n\t\t<!-- end: forums#" . $forum["fid"] . " for category#" . $forum["pid"] . " -->";
    }
}
if (isset($oneforum) && $oneforum === true) {
    ($query = sql_query("SELECT * FROM " . TSF_PREFIX . "forums WHERE $type = 'c' AND $fid = " . sqlesc($fid) . " ORDER by pid, disporder")) || sqlerr(__FILE__, 280);
    if (mysqli_num_rows($query) == 0) {
        stdhead();
        stdmsg($lang->global["error"], $lang->tsf_forums["invalidfid"]);
        stdfoot();
        exit;
    }
} else {
    ($query = sql_query("SELECT * FROM " . TSF_PREFIX . "forums WHERE $type = 'c' ORDER by pid, disporder")) || sqlerr(__FILE__, 291);
    if (mysqli_num_rows($query) == 0) {
        stdhead();
        stdmsg($lang->global["error"], $lang->tsf_forums["noforumsyet"]);
        stdfoot();
        exit;
    }
}
$str = "";
while ($category = mysqli_fetch_assoc($query)) {
    if (isset($addnavbar) && $addnavbar) {
        add_breadcrumb($category["name"]);
        $addnavbar = false;
        $forumTitle = $category["name"];
    }
    if (isset($permissions[$category["fid"]]["canview"]) && $permissions[$category["fid"]]["canview"] == "yes") {
        if (isset($subforums[$category["fid"]])) {
            $str .= "\r\n\t\t<!-- start: category#" . $category["fid"] . " -->\r\n\t\t\t<table $cellspacing = \"0\" $cellpadding = \"5\" $border = \"0\" $width = \"100%\" $align = \"center\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"thead\" $colspan = \"6\">\r\n\t\t\t\t\t\t" . ts_collapse("forum#" . $category["fid"]) . "\r\n\t\t\t\t\t\t<strong><a $href = \"" . tsf_seo_clean_text($category["name"], "f", $category["fid"]) . "\">" . $category["name"] . "</a></strong>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . ts_collapse("forum#" . $category["fid"], 2) . "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"subheader\" $width = \"32\">&nbsp;</td>\r\n\t\t\t\t\t<td class=\"subheader\" $width = \"32\">&nbsp;</td>\r\n\t\t\t\t\t<td class=\"subheader\"><strong>" . $lang->tsf_forums["forum"] . "</strong></td>\r\n\t\t\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"200\"><strong>" . $lang->tsf_forums["lastpost"] . "</strong></td>\r\n\t\t\t\t\t<td class=\"subheader\" $style = \"white-space: nowrap;\" $align = \"center\" $width = \"50\"><strong>" . $lang->tsf_forums["threads"] . "</strong></td>\r\n\t\t\t\t\t<td class=\"subheader\" $style = \"white-space: nowrap;\" $align = \"center\" $width = \"50\"><strong>" . $lang->tsf_forums["posts"] . "</strong></td>\t\t\t\t\t\t\r\n\t\t\t\t</tr>\r\n\t\t\t\t" . $subforums[$category["fid"]] . "\r\n\t\t\t</tbody>\r\n\t\t</table>\t\t\t\r\n\t\t<br />\r\n\t\t<!-- end: category#" . $category["fid"] . " -->\t\t\r\n\t\t";
        }
    }
}
stdhead($forumTitle);
build_breadcrumb();
echo $str;
unset($str);
if ($f_showstats == "yes" || $f_showstats == "staffonly" && $moderator) {
    tsf_forum_stats();
} else {
    show_semi_stats();
}
stdfoot();
function show_semi_stats()
{
    global $lang;
    echo "\t\r\n\t<!-- begin: footer -->\t\r\n\t<table $cellspacing = \"0\" $cellpadding = \"5\" $border = \"0\" $width = \"100%\" $align = \"center\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td>\t\t\t\t\t\r\n\t\t\t\t\t<table $width = \"100%\" $align = \"center\">\r\n\t\t\t\t\t\t<tbody>\r\n\t\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t\t<td $align = \"center\" $style = \"padding: 10px 0px 10px 0px; margin: 0px 0px 0px 0px;\">\t\t\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/on.gif\" $alt = \"" . $lang->tsf_forums["new_posts"] . "\" $title = \"" . $lang->tsf_forums["new_posts"] . "\" class=\"inlineimg\"> <span class=\"smalltext\">" . $lang->tsf_forums["new_posts"] . "</span>&nbsp;&nbsp;&nbsp;&nbsp;\r\n\t\t\t\t\t\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/off.gif\" $alt = \"" . $lang->tsf_forums["no_new_posts"] . "\" $title = \"" . $lang->tsf_forums["no_new_posts"] . "\" class=\"inlineimg\"> <span class=\"smalltext\">" . $lang->tsf_forums["no_new_posts"] . "</span>&nbsp;&nbsp;&nbsp;&nbsp;\r\n\t\t\t\t\t\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/offlock.gif\" $alt = \"" . $lang->tsf_forums["forum_locked"] . "\" $title = \"" . $lang->tsf_forums["forum_locked"] . "\" class=\"inlineimg\"> <span class=\"smalltext\">" . $lang->tsf_forums["forum_locked"] . "</span>\r\n\t\t\t\t\t\t\t\t\t <span class=\"smalltext\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[<a $href = \"" . $BASEURL . "/tsf_forums/misc.php?$action = markread\">" . $lang->tsf_forums["markallread"] . "</a>] [<a $href = \"" . $BASEURL . "/tsf_forums/syndication.php\">" . $lang->header["extrarssfeed"] . "</a>] </span>\t\t\t\t\t\t\t\t\t\r\n\t\t\t\t\t\t\t\t</td>\r\n\t\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t</tbody>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t<!-- end: footer -->";
}
function showFriendlyAge($p_strDate = "")
{
    if (!$p_strDate) {
        return "--";
    }
    list($d, $m, $Y) = @explode("-", $p_strDate);
    return date("md") < $m . $d ? date("Y") - $Y - 1 : date("Y") - $Y;
}
function tsf_forum_stats()
{
    global $cache;
    global $lang;
    global $rootpath;
    global $BASEURL;
    global $usergroups;
    global $CURUSER;
    global $pic_base_url;
    global $moderator;
    global $cachesystem;
    global $staffcp_path;
    include_once INC_PATH . "/ts_cache.php";
    update_cache("indexstats");
    include_once TSDIR . "/" . $cache . "/indexstats.php";
    include_once INC_PATH . "/functions_icons.php";
    $dt = TIMENOW - TS_TIMEOUT;
    ($res = sql_query("SELECT u.id, u.username, u.usergroup, u.options, u.enabled, u.donor, u.leechwarn, u.warned, p.canupload, p.candownload, p.cancomment, p.canmessage, p.canshout, g.namestyle, g.title FROM users u LEFT JOIN ts_u_perm p ON (u.$id = p.userid) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE u.last_forum_active >= " . $dt . " ORDER BY u.username")) || sqlerr(__FILE__, 400);
    $webtotal = 0;
    $activeusers = "";
    while ($arr = mysqli_fetch_array($res)) {
        if (!(TS_Match($arr["options"], "B1") && !$moderator && $arr["id"] != $CURUSER["id"])) {
            if ($activeusers) {
                $activeusers .= ", ";
            }
            if ($CURUSER) {
                $activeusers .= "<a $href = \"" . tsf_seo_clean_text($arr["username"], "u", $arr["id"]) . "\"><b>" . get_user_color($arr["username"], $arr["namestyle"]) . "</b></a>";
            } else {
                $activeusers .= "<b>" . get_user_color($arr["username"], $arr["namestyle"]) . "</b>";
            }
            if (TS_Match($arr["options"], "B1")) {
                $activeusers .= "+";
            }
            $activeusers .= get_user_icons($arr);
            $webtotal++;
        }
    }
    if (!$activeusers) {
        $activeusers = $lang->global["noactiveusersonline"];
    }
    define("SKIP_CACHE_MESSAGE", true);
    require_once INC_PATH . "/functions_cache2.php";
    $no_cache = false;
    if (!($showbday = cache_check2("tsf_forums_bday"))) {
        $no_cache = true;
    }
    if ($no_cache) {
        $todaybday = date("j-n");
        $todaybday2 = date("j-m");
        $query = sql_query("SELECT u.id,u.username,u.birthday,g.namestyle FROM users u LEFT JOIN usergroups g ON (u.$usergroup = g.gid) WHERE (u.birthday REGEXP '^" . $todaybday . "-([1-9][0-9][0-9][0-9])' OR u.birthday REGEXP '^" . $todaybday2 . "-([1-9][0-9][0-9][0-9])') AND u.$enabled = 'yes'");
        $bdaycount = mysqli_num_rows($query);
        if (0 < $bdaycount) {
            $showbday = "\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td class=\"subheader\" $colspan = \"2\">\r\n\t\t\t\t\t\t<b>" . $lang->tsf_forums["tbdays"] . "</b>\r\n\t\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t\t<td $width = \"1%\"><img $src = \"" . $BASEURL . "/tsf_forums/images/bday.gif\" $alt = \"\" $title = \"\" /></td>\r\n\t\t\t\t\t<td>" . sprintf($lang->tsf_forums["tbdayss"], $bdaycount) . "<br />\r\n\t\t\t";
            while ($bday = mysqli_fetch_assoc($query)) {
                $yearsold = showfriendlyage($bday["birthday"]);
                $showbday .= " <a $href = \"" . tsf_seo_clean_text($bday["username"], "u", $bday["id"]) . "\">" . get_user_color($bday["username"], $bday["namestyle"]) . "</a> (<b>" . $yearsold . "</b>) ";
            }
        }
        cache_save2("tsf_forums_bday", $showbday, "</td></tr>");
    }
    if ($cachesystem != "yes" || $no_cache) {
        $showbday .= "</td></tr>";
    }
    $no_cache = false;
    if (!($tsf_attachment_stats = cache_check2("tsf_attachment_stats"))) {
        $no_cache = true;
    }
    if ($no_cache) {
        $attachstats = mysqli_fetch_assoc(sql_query("\r\n\t\tSELECT COUNT(*) AS count, SUM(a_size) AS size, SUM(a_count) AS download\r\n\t\tFROM " . TSF_PREFIX . "attachments\r\n\t\t"));
        $attachstats_total = ts_nf($attachstats["count"]);
        $attachstats_total_space = mksize($attachstats["size"]);
        if ($attachstats["count"]) {
            $attachstats["average"] = $attachstats["size"] / $attachstats["count"];
        } else {
            $attachstats["average"] = "0.00";
        }
        $attachstats_average = mksize($attachstats["average"]);
        $attachstats_total_downloads = ts_nf($attachstats["download"]);
        $tsf_attachment_stats = sprintf($lang->tsf_forums["stats2_details"], $attachstats_total, $attachstats_total_space, $attachstats_average, $attachstats_total_downloads);
        cache_save2("tsf_attachment_stats", $tsf_attachment_stats, "</td></tr>");
    }
    echo "\r\n\t\r\n\t<!-- start: tsf forum stats -->\r\n\t<table $cellspacing = \"0\" $cellpadding = \"5\" $border = \"0\" $width = \"100%\" $align = \"center\">\r\n\t\t<thead>\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"thead\" $colspan = \"2\">\r\n\t\t\t\t\t" . ts_collapse("forumstats") . "\r\n\t\t\t\t\t<strong>" . $lang->tsf_forums["stats"] . "</strong>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</thead>\r\n\t\t" . ts_collapse("forumstats", 2) . "\r\n\t\t\t<tr>\r\n\t\t\t\t<td class=\"subheader\" $colspan = \"2\">\r\n\t\t\t\t\t" . ($moderator ? "<a $href = \"" . $BASEURL . "/" . $staffcp_path . "/index.php?do=who_is_online\">" . $lang->tsf_forums["whosonline"] . "</a>" : $lang->tsf_forums["whosonline"]) . " \r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $width = \"1%\"><img $src = \"" . $BASEURL . "/tsf_forums/images/whoisonline.gif\" $alt = \"\" $title = \"\" /></td>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t" . @sprintf($lang->tsf_forums["activeusers"], @ts_nf($webtotal), @floor(TS_TIMEOUT / 60)) . "\r\n\t\t\t\t\t" . $activeusers . "\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t" . $showbday . "\r\n\t\t\t<td class=\"subheader\" $colspan = \"2\">\r\n\t\t\t\t<div $style = \"float: right\"><a $href = \"" . $BASEURL . "/tsf_forums/misc.php?$action = markread\">" . $lang->tsf_forums["markallread"] . "</a> - <a $href = \"" . $BASEURL . "/tsf_forums/syndication.php\">" . $lang->header["extrarssfeed"] . "</a></div>\r\n\t\t\t\t" . $lang->tsf_forums["stats"] . "\r\n\t\t\t</td>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $width = \"1%\"><img $src = \"" . $BASEURL . "/tsf_forums/images/stats.gif\" $alt = \"\" $title = \"\" /></td>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<span class=\"smalltext\">\r\n\t\t\t\t\t\t" . @sprintf($lang->tsf_forums["stats_info"], @ts_nf($indexstats["totalposts"]), @ts_nf($indexstats["totalthreads"]), @ts_nf($indexstats["registered"]), $indexstats["latestuser"]) . "\r\n\t\t\t\t\t</span>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t\t<td class=\"subheader\" $colspan = \"2\">\r\n\t\t\t\t" . $lang->tsf_forums["stats2"] . "\r\n\t\t\t</td>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $width = \"1%\"><img $src = \"" . $BASEURL . "/tsf_forums/images/stats.gif\" $alt = \"\" $title = \"\" /></td>\r\n\t\t\t\t<td>\r\n\t\t\t\t\t<span class=\"smalltext\">\r\n\t\t\t\t\t\t" . $tsf_attachment_stats . "\r\n\t\t\t\t\t</span>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t<br />\r\n\t<!-- end: tsf forum stats -->\r\n\t\r\n\t<!-- begin: footer -->\r\n\t<table class=\"subheader\" $cellspacing = \"0\" $cellpadding = \"5\" $border = \"0\" $width = \"100%\" $align = \"center\">\r\n\t\t<tbody>\r\n\t\t\t<tr>\r\n\t\t\t\t<td $align = \"center\" $style = \"padding: 10px 0px 10px 0px; margin: 0px 0px 0px 0px;\">\t\t\t\t\t\t\t\t\t\r\n\t\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/on.gif\" $alt = \"" . $lang->tsf_forums["new_posts"] . "\" $title = \"" . $lang->tsf_forums["new_posts"] . "\" /> <span class=\"smalltext\">" . $lang->tsf_forums["new_posts"] . "</span>&nbsp;&nbsp;&nbsp;&nbsp;\r\n\t\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/off.gif\" $alt = \"" . $lang->tsf_forums["no_new_posts"] . "\" $title = \"" . $lang->tsf_forums["no_new_posts"] . "\" /> <span class=\"smalltext\">" . $lang->tsf_forums["no_new_posts"] . "</span>&nbsp;&nbsp;&nbsp;&nbsp;\r\n\t\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/offlock.gif\" $alt = \"" . $lang->tsf_forums["forum_locked"] . "\" $title = \"" . $lang->tsf_forums["forum_locked"] . "\"/> <span class=\"smalltext\">" . $lang->tsf_forums["forum_locked"] . "</span>\r\n\t\t\t\t</td>\r\n\t\t\t</tr>\r\n\t\t</tbody>\r\n\t</table>\r\n\t<!-- end: footer -->";
}

?>