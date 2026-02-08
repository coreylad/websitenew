<?php
define("THIS_SCRIPT", "top_stats.php");
$rootpath = "./../";
require $rootpath . "global.php";
define("TS_VERSION", "v.1.1 by xam");
if ($usergroups["cantopten"] != "yes") {
    print_no_permission();
    exit;
}
require INC_PATH . "/functions_cache2.php";
if ($CachedStats = cache_check2("top_stats_" . $CURUSER["usergroup"])) {
    stdhead();
    echo $CachedStats;
    stdfoot();
    exit;
}
$lang->load("top_stats");
$Skip = get_hidden_forums();
$Stats = "\r\n<table $align = \"center\" $cellpadding = \"0\" $cellspacing = \"0\" $width = \"100%\">\r\n\t<tbody>\r\n\t\t<tr $valign = \"top\">\t\t\t\t\t\r\n";
$Stats .= "\r\n\t\t\t<td $valign = \"top\" $width = \"160\" class=\"none\">\r\n\t\t\t\t<div $style = \"padding-bottom: 0px;\">\r\n\t\t\t\t\t<table $width = \"100%\" $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $colspan = \"3\" class=\"thead\">" . $lang->top_stats["hottest"] . "</td>\r\n\t\t\t\t\t\t</tr>";
$query = sql_query("SELECT tid, subject, replies FROM " . TSF_PREFIX . "threads " . $Skip . " ORDER by lastpost DESC LIMIT 10") or ($query = sql_query("SELECT tid, subject, replies FROM " . TSF_PREFIX . "threads " . $Skip . " ORDER by lastpost DESC LIMIT 10")) || sqlerr(__FILE__, 89);
while ($HT = mysqli_fetch_assoc($query)) {
    $Stats .= "\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $width = \"1%\" $align = \"center\"><img $src = \"" . $BASEURL . "/tsf_forums/images/post_old.gif\" $border = \"0\" $title = \"\" /></td>\r\n\t\t\t\t\t\t\t<td $width = \"89%\" $align = \"left\"><a $href = \"" . tsf_seo_clean_text($HT["subject"], "t", $HT["tid"]) . "\" $alt = \"" . htmlspecialchars_uni($HT["subject"]) . "\"  $title = \"" . htmlspecialchars_uni($HT["subject"]) . "\">" . cutename($HT["subject"], 15) . "</a></td>\r\n\t\t\t\t\t\t\t<td $width = \"10%\" $align = \"center\">" . ts_nf($HT["replies"]) . "</td>\r\n\t\t\t\t\t\t</tr>\r\n\t";
}
$Stats .= "\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n";
$Stats .= "\r\n\t\t\t\t<div $style = \"padding-bottom: 0px;\">\r\n\t\t\t\t\t<table $width = \"100%\" $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $colspan = \"3\" class=\"thead\">" . $lang->top_stats["mostrated"] . "</td>\r\n\t\t\t\t\t\t</tr>";
$query = sql_query("SELECT tid, subject, round((votetotal / votenum),2) as rating FROM " . TSF_PREFIX . "threads " . $Skip . " GROUP BY tid ORDER BY rating DESC LIMIT 10") or ($query = sql_query("SELECT tid, subject, round((votetotal / votenum),2) as rating FROM " . TSF_PREFIX . "threads " . $Skip . " GROUP BY tid ORDER BY rating DESC LIMIT 10")) || sqlerr(__FILE__, 111);
while ($HT = mysqli_fetch_assoc($query)) {
    $Stats .= "\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $width = \"1%\" $align = \"center\"><img $src = \"" . $BASEURL . "/tsf_forums/images/post_old.gif\" $border = \"0\" $title = \"\" /></td>\r\n\t\t\t\t\t\t\t<td $width = \"89%\" $align = \"left\"><a $href = \"" . tsf_seo_clean_text($HT["subject"], "t", $HT["tid"]) . "\" $alt = \"" . htmlspecialchars_uni($HT["subject"]) . "\"  $title = \"" . htmlspecialchars_uni($HT["subject"]) . "\">" . cutename($HT["subject"], 15) . "</a></td>\r\n\t\t\t\t\t\t\t<td $width = \"10%\" $align = \"center\">" . $HT["rating"] . "</td>\r\n\t\t\t\t\t\t</tr>\r\n\t";
}
$Stats .= "\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n";
$Stats .= "\r\n\t\t\t\t<div $style = \"padding-bottom: 0px;\">\r\n\t\t\t\t\t<table $width = \"100%\" $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $colspan = \"3\" class=\"thead\">" . $lang->top_stats["topposters"] . "</td>\r\n\t\t\t\t\t\t</tr>";
$query = sql_query("SELECT u.id, u.username, u.totalposts, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$enabled = 'yes' ORDER by totalposts DESC LIMIT 10") or ($query = sql_query("SELECT u.id, u.username, u.totalposts, g.namestyle FROM users u LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) WHERE u.$enabled = 'yes' ORDER by totalposts DESC LIMIT 10")) || sqlerr(__FILE__, 133);
while ($HT = mysqli_fetch_assoc($query)) {
    $Stats .= "\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $width = \"1%\" $align = \"center\"><img $src = \"" . $BASEURL . "/tsf_forums/images/post_old.gif\" $border = \"0\" $title = \"\" /></td>\r\n\t\t\t\t\t\t\t<td $width = \"89%\" $align = \"left\"><a $href = \"" . ts_seo($HT["id"], $HT["username"]) . "\">" . get_user_color($HT["username"], $HT["namestyle"]) . "</a></td>\r\n\t\t\t\t\t\t\t<td $width = \"10%\" $align = \"center\">" . ts_nf($HT["totalposts"]) . "</td>\r\n\t\t\t\t\t\t</tr>\r\n\t";
}
$Stats .= "\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n";
$Stats .= "\r\n\t\t\t<td $valign = \"top\" $width = \"160\" class=\"none\" $style = \"padding-left: 6px\">\r\n\t\t\t\t<div $style = \"padding-bottom: 0px;\">\t\t\t\t\t\r\n\t\t\t\t\t<table $width = \"100%\" $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $colspan = \"3\" class=\"thead\">" . $lang->top_stats["mostviewed"] . "</td>\r\n\t\t\t\t\t\t</tr>";
$query = sql_query("SELECT tid, subject, views FROM " . TSF_PREFIX . "threads " . $Skip . " ORDER by views DESC LIMIT 10") or ($query = sql_query("SELECT tid, subject, views FROM " . TSF_PREFIX . "threads " . $Skip . " ORDER by views DESC LIMIT 10")) || sqlerr(__FILE__, 158);
while ($HT = mysqli_fetch_assoc($query)) {
    $Stats .= "\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $width = \"1%\" $align = \"center\"><img $src = \"" . $BASEURL . "/tsf_forums/images/post_old.gif\" $border = \"0\" $title = \"\" /></td>\r\n\t\t\t\t\t\t\t<td $width = \"89%\" $align = \"left\"><a $href = \"" . tsf_seo_clean_text($HT["subject"], "t", $HT["tid"]) . "\" $alt = \"" . htmlspecialchars_uni($HT["subject"]) . "\"  $title = \"" . htmlspecialchars_uni($HT["subject"]) . "\">" . cutename($HT["subject"], 15) . "</a></td>\r\n\t\t\t\t\t\t\t<td $width = \"10%\" $align = \"center\">" . ts_nf($HT["views"]) . "</td>\r\n\t\t\t\t\t\t</tr>\r\n\t";
}
$Stats .= "\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n";
$Stats .= "\r\n\t\t\t\t<div $style = \"padding-bottom: 0px;\">\r\n\t\t\t\t\t<table $width = \"100%\" $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $colspan = \"3\" class=\"thead\">" . $lang->top_stats["mostreplied"] . "</td>\r\n\t\t\t\t\t\t</tr>";
$query = sql_query("SELECT tid, subject, replies FROM " . TSF_PREFIX . "threads " . $Skip . " ORDER by replies DESC LIMIT 10") or ($query = sql_query("SELECT tid, subject, replies FROM " . TSF_PREFIX . "threads " . $Skip . " ORDER by replies DESC LIMIT 10")) || sqlerr(__FILE__, 180);
while ($HT = mysqli_fetch_assoc($query)) {
    $Stats .= "\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $width = \"1%\" $align = \"center\"><img $src = \"" . $BASEURL . "/tsf_forums/images/post_old.gif\" $border = \"0\" $title = \"\" /></td>\r\n\t\t\t\t\t\t\t<td $width = \"89%\" $align = \"left\"><a $href = \"" . tsf_seo_clean_text($HT["subject"], "t", $HT["tid"]) . "\" $alt = \"" . htmlspecialchars_uni($HT["subject"]) . "\"  $title = \"" . htmlspecialchars_uni($HT["subject"]) . "\">" . cutename($HT["subject"], 15) . "</a></td>\r\n\t\t\t\t\t\t\t<td $width = \"10%\" $align = \"center\">" . ts_nf($HT["replies"]) . "</td>\r\n\t\t\t\t\t\t</tr>\r\n\t";
}
$Stats .= "\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n";
$Stats .= "\r\n\t\t\t\t<div $style = \"padding-bottom: 0px;\">\r\n\t\t\t\t\t<table $width = \"100%\" $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $colspan = \"3\" class=\"thead\">" . $lang->top_stats["topthreadstarters"] . "</td>\r\n\t\t\t\t\t\t</tr>";
$query = sql_query("SELECT count(t.uid) AS totalthreads, u.id, u.username, g.namestyle FROM " . TSF_PREFIX . "threads t LEFT JOIN users u ON (t.$uid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) GROUP BY t.uid ORDER BY totalthreads DESC LIMIT 10") or ($query = sql_query("SELECT count(t.uid) AS totalthreads, u.id, u.username, g.namestyle FROM " . TSF_PREFIX . "threads t LEFT JOIN users u ON (t.$uid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) GROUP BY t.uid ORDER BY totalthreads DESC LIMIT 10")) || sqlerr(__FILE__, 202);
while ($HT = mysqli_fetch_assoc($query)) {
    $Stats .= "\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $width = \"1%\" $align = \"center\"><img $src = \"" . $BASEURL . "/tsf_forums/images/post_old.gif\" $border = \"0\" $title = \"\" /></td>\r\n\t\t\t\t\t\t\t<td $width = \"89%\" $align = \"left\"><a $href = \"" . ts_seo($HT["id"], $HT["username"]) . "\">" . get_user_color($HT["username"], $HT["namestyle"]) . "</a></td>\r\n\t\t\t\t\t\t\t<td $width = \"10%\" $align = \"center\">" . ts_nf($HT["totalthreads"]) . "</td>\r\n\t\t\t\t\t\t</tr>\r\n\t";
}
$Stats .= "\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n";
$Stats .= "\r\n\t\t\t<td $valign = \"top\" class=\"none\" $style = \"padding-left: 6px\" $rowspan = \"3\">\r\n\t\t\t\t<div $style = \"padding-bottom: 0px;\">\t\t\t\t\t\r\n\t\t\t\t\t<table $width = \"100%\" $align = \"center\" $border = \"0\" $cellpadding = \"3\" $cellspacing = \"0\">\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $colspan = \"3\" class=\"thead\">" . $lang->top_stats["latestposts"] . "</td>\r\n\t\t\t\t\t\t</tr>\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td class=\"subheader\"></td>\r\n\t\t\t\t\t\t\t<td class=\"subheader\">Thread</td>\r\n\t\t\t\t\t\t\t<td class=\"subheader\">Posted By</td>\t\t\t\t\t\t\r\n\t\t\t\t\t\t</tr>";
$query = sql_query("SELECT tid, subject, u.id, u.username, g.namestyle FROM " . TSF_PREFIX . "posts LEFT JOIN users u ON ($uid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) " . $Skip . " GROUP by tid ORDER by dateline DESC LIMIT 31") or ($query = sql_query("SELECT tid, subject, u.id, u.username, g.namestyle FROM " . TSF_PREFIX . "posts LEFT JOIN users u ON ($uid = u.id) LEFT JOIN usergroups g ON (u.`usergroup` = g.gid) " . $Skip . " GROUP by tid ORDER by dateline DESC LIMIT 31")) || sqlerr(__FILE__, 231);
while ($HT = mysqli_fetch_assoc($query)) {
    $Stats .= "\r\n\t\t\t\t\t\t<tr>\r\n\t\t\t\t\t\t\t<td $width = \"1%\" $align = \"center\"><img $src = \"" . $BASEURL . "/tsf_forums/images/post_old.gif\" $border = \"0\" $title = \"\" /></td>\r\n\t\t\t\t\t\t\t<td $width = \"890%\" $align = \"left\"><a $href = \"" . tsf_seo_clean_text($HT["subject"], "lastpost", $HT["tid"]) . "\" $alt = \"" . htmlspecialchars_uni($HT["subject"]) . "\"  $title = \"" . htmlspecialchars_uni($HT["subject"]) . "\">" . cutename($HT["subject"], 70) . "</a></td>\r\n\t\t\t\t\t\t\t<td $width = \"10%\"><a $href = \"" . ts_seo($HT["id"], $HT["username"]) . "\">" . get_user_color($HT["username"], $HT["namestyle"]) . "</a></td>\r\n\t\t\t\t\t\t</tr>\r\n\t";
}
$Stats .= "\r\n\t\t\t\t\t</table>\r\n\t\t\t\t</div>\r\n\t\t\t</td>\r\n";
$Stats .= "\r\n\t\t</tr>\r\n\t</tbody>\r\n</table>";
stdhead();
echo $Stats;
stdfoot();
cache_save2("top_stats_" . $CURUSER["usergroup"], $Stats);
function get_hidden_forums()
{
    global $CURUSER;
    global $securehash;
    ($query = sql_query("SELECT fp.fid,f.fid FROM " . TSF_PREFIX . "forumpermissions fp LEFT JOIN " . TSF_PREFIX . "forums f ON (fp.$fid = f.pid) WHERE fp.$canview = 'no' AND fp.$gid = " . sqlesc($CURUSER["usergroup"]))) || sqlerr(__FILE__, 41);
    if (0 < mysqli_num_rows($query)) {
        while ($notin = mysqli_fetch_assoc($query)) {
            $uf[] = 0 + $notin["fid"];
        }
        $unsearchforums = implode(",", $uf);
    }
    $query = sql_query("SELECT fid,password FROM " . TSF_PREFIX . "forums WHERE password != ''");
    if (0 < mysqli_num_rows($query)) {
        while ($notin = mysqli_fetch_assoc($query)) {
            if ($notin["password"] != "" && $_COOKIE["forumpass_" . $notin["fid"]] != md5($CURUSER["id"] . $notin["password"] . $securehash)) {
                $uf[] = 0 + $notin["fid"];
            }
        }
        if (isset($unsearchforums)) {
            $unsearchforums .= "," . @implode(",", $uf);
        } else {
            $unsearchforums = @implode(",", $uf);
        }
    }
    if (isset($unsearchforums)) {
        return "WHERE fid NOT IN (" . $unsearchforums . ")";
    }
    return "";
}

?>