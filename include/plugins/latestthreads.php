<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

if (!defined("TS_P_VERSION")) {
    define("TS_P_VERSION", "1.3 by xam");
}
if (!defined("IN_PLUGIN_SYSTEM")) {
    exit("<font $face = 'verdana' $size = '2' $color = 'darkred'><b>Error!</b> Direct initialization of this file is not allowed.</font>");
}
$i_post_limit = 5;
$_res_lt_query = sql_query("SELECT fp.fid,f.fid FROM " . TSF_PREFIX . "forumpermissions fp LEFT JOIN " . TSF_PREFIX . "forums f ON (fp.$fid = f.pid) WHERE (fp.$canview = 'no' OR fp.$cansearch = 'no') AND fp.$gid = " . sqlesc($CURUSER ? $CURUSER["usergroup"] : 1));
if (0 < mysqli_num_rows($_res_lt_query)) {
    while ($notin = mysqli_fetch_assoc($_res_lt_query)) {
        $uf[] = 0 + $notin["fid"];
    }
    $unsearchforums = implode(",", $uf);
}
$_res_lt_query = sql_query("SELECT fid,password FROM " . TSF_PREFIX . "forums WHERE password != ''");
if (0 < mysqli_num_rows($_res_lt_query)) {
    $uf2 = [];
    require_once INC_PATH . "/functions_cookies.php";
    while ($notin = mysqli_fetch_assoc($_res_lt_query)) {
        if (ts_get_array_cookie("forumpass", $notin["fid"]) != md5($CURUSER["id"] . $notin["password"])) {
            $uf2[] = 0 + $notin["fid"];
        }
    }
    if (0 < count($uf2)) {
        if (isset($unsearchforums)) {
            $unsearchforums .= "," . implode(",", $uf2);
        } else {
            $unsearchforums = implode(",", $uf2);
        }
    }
}
$__fp = __fp();
$where_sql = "";
if (isset($unsearchforums)) {
    $where_sql = " AND t.fid NOT IN (" . $unsearchforums . ")";
}
$_res_lt_query = sql_query("SELECT t.tid, t.iconid, t.subject, t.dateline, t.uid, t.replies, t.lastpost, t.lastposter, t.lastposteruid, t.views, t.fid, f.image, u.username, g.namestyle, uu.username as lusername, gg.namestyle as lnamestyle FROM " . TSF_PREFIX . "threads t LEFT JOIN " . TSF_PREFIX . "forums f ON (f.$fid = t.fid) LEFT JOIN users u ON (t.$uid = u.id) LEFT JOIN usergroups g ON (u.$usergroup = g.gid) LEFT JOIN users uu ON (t.$lastposteruid = uu.id) LEFT JOIN usergroups gg ON (uu.$usergroup = gg.gid) WHERE 1=1 " . $where_sql . " ORDER BY t.lastpost DESC LIMIT 0, " . $i_post_limit);
if (!mysqli_num_rows($_res_lt_query)) {
    $latestthreads = "";
    return $latestthreads;
}
$latestthreads = "<!-- begin lastXforumposts -->";
$latestthreads .= "\r\n\t<table $width = \"100%\" $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t<tr>\r\n\t\t\t<td class=\"subheader\" $width = \"2%\"></td>\r\n\t\t\t<td class=\"subheader\" $width = \"2%\"></td>\r\n\t\t\t<td class=\"subheader\" $align = \"left\" $width = \"60%\">" . $lang->index["topictitle"] . "</td>\t\t\t\r\n\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"20%\">" . $lang->index["lastposter"] . "</td>\r\n\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"8%\">" . $lang->index["replies"] . "</td>\r\n\t\t\t<td class=\"subheader\" $align = \"center\" $width = \"8%\">" . $lang->index["views"] . "</td>\t\t\t\r\n\t\t</tr>\r\n";
if (0 < $CURUSER["postsperpage"] && is_valid_id($CURUSER["postsperpage"]) && $CURUSER["postsperpage"] <= 50) {
    $postperpage = intval($CURUSER["postsperpage"]);
    while ($thread = mysqli_fetch_assoc($_res_lt_query)) {
    }
    $latestthreads .= "\t\r\n</table>\r\n<!-- end lastXforumposts -->";
} else {
    $postperpage = $f_postsperpage;
}
if (isset($__fp[$thread["fid"]]) && $__fp[$thread["fid"]]["canview"] == "yes") {
    $thread["pages"] = 0;
    $thread["multipage"] = "";
    $threadpages = "";
    $morelink = "";
    $thread["posts"] = $thread["replies"] + 1;
    if ($postperpage < $thread["posts"]) {
        $thread["pages"] = $thread["posts"] / $postperpage;
        $thread["pages"] = @ceil($thread["pages"]);
        if (4 < $thread["pages"]) {
            $pagesstop = 4;
            $morelink = "... <a $href = \"" . tsf_seo_clean_text($thread["subject"], "lastpost", $thread["tid"]) . "\">" . $lang->global["last"] . "</a>";
        } else {
            $pagesstop = $thread["pages"];
        }
        for ($i = 1; $i <= $pagesstop; $i++) {
            $threadpages .= " <a $href = \"" . tsf_seo_clean_text($thread["subject"], "page", $thread["tid"], $i) . "\">" . $i . "</a> ";
        }
        $thread["multipage"] = " <span class=\"smalltext\">(<img $src = \"" . $BASEURL . "/tsf_forums/images/multipage.gif\" $border = \"0\" class=\"inlineimg\"> " . $threadpages . $morelink . ")</span>";
    } else {
        $threadpages = "";
        $morelink = "";
        $thread["multipage"] = "";
    }
    $latestthreads .= "\r\n\t\t<tr>\r\n\t\t\t<td $width = \"1\" $align = \"center\">\r\n\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/forumicons/" . $thread["image"] . "\" $border = \"0\" class=\"inlineimg\" $alt = \"\" />\r\n\t\t\t</td>\r\n\t\t\t<td $width = \"1\" $align = \"center\">\r\n\t\t\t\t<img $src = \"" . $BASEURL . "/tsf_forums/images/icons/icon" . $thread["iconid"] . ".gif\" $border = \"0\" class=\"inlineimg\" $alt = \"\" />\r\n\t\t\t</td>\r\n\t\t\t<td $align = \"left\">\r\n\t\t\t\t<a $href = \"" . tsf_seo_clean_text($thread["subject"], "t", $thread["tid"]) . "\" $title = \"" . htmlspecialchars_uni($thread["subject"]) . "\"><b>" . cutename($thread["subject"], $__cute) . "</b></a> " . $thread["multipage"] . "<br />\r\n\t\t\t\t" . sprintf($lang->index["by"], "<a $href = \"" . ts_seo($thread["uid"], $thread["username"]) . "\">" . get_user_color($thread["username"], $thread["namestyle"]) . "</a>") . "<br />\r\n\t\t\t\t" . my_datee($dateformat, $thread["dateline"]) . " " . my_datee($timeformat, $thread["dateline"]) . "\r\n\t\t\t</td>\t\t\t\r\n\t\t\t<td $align = \"right\">\r\n\t\t\t\t" . my_datee($dateformat, $thread["lastpost"]) . " " . my_datee($timeformat, $thread["lastpost"]) . "\t<br />\r\n\t\t\t\t" . sprintf($lang->index["by"], "<a $href = \"" . ts_seo($thread["lastposteruid"], $thread["lastposter"]) . "\">" . get_user_color($thread["lastposter"], $thread["lnamestyle"]) . "</a>") . "\r\n\t\t\t\t <a $href = \"" . tsf_seo_clean_text($thread["subject"], "lastpost", $thread["tid"]) . "\"><img $src = \"" . $BASEURL . "/tsf_forums/images/lastpost.gif\" class=\"inlineimg\" $border = \"0\" $alt = \"" . $lang->index["last"] . "\" $title = \"" . $lang->index["last"] . "\" /></a>\t\t\t\t\r\n\t\t\t</td>\r\n\t\t\t<td $align = \"center\">\r\n\t\t\t\t" . ts_nf($thread["replies"]) . "\r\n\t\t\t</td>\r\n\t\t\t<td $align = \"center\">\r\n\t\t\t\t" . ts_nf($thread["views"]) . "\r\n\t\t\t</td>\r\n\t\t</tr>\r\n\t";
}
function __fp()
{
    global $CURUSER;
    $permissions = [];
    if (isset($CURUSER) && $CURUSER["usergroup"]) {
        ($query = sql_query("SELECT * FROM " . TSF_PREFIX . "forumpermissions WHERE $gid = " . sqlesc($CURUSER["usergroup"]))) || sqlerr(__FILE__, 164);
        if (mysqli_num_rows($query)) {
            while ($perm = mysqli_fetch_assoc($query)) {
                $permissions[$perm["fid"]] = $perm;
            }
        }
    }
    return $permissions;
}

?>